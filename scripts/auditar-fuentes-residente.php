<?php

declare(strict_types=1);

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$residente = $argv[1] ?? '';
if (! preg_match('/^COD_RES_[0-9]{6}$/D', $residente) || DB::getDriverName() !== 'pgsql') {
    throw new RuntimeException('Indique un residente explícito para la auditoría PostgreSQL de solo lectura.');
}
DB::beginTransaction();
DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
try {
    if (! DB::table('residentes')->where('cod_residente', $residente)->exists()) {
        throw new RuntimeException('Residente inexistente.');
    }
    // Auditoría técnica autorizada por la propietaria. No suplanta una sesión clínica,
    // no ejecuta una valoración y no exporta nombres, textos clínicos ni expedientes ajenos.
    $tablas = ['atenciones', 'controles_cognitivos', 'aplicaciones_instrumento', 'valoraciones_psicologicas',
        'valoraciones_funcionales', 'controles_funcionales_iadl', 'antecedentes_clinicos', 'diagnosticos',
        'incidentes', 'signos_vitales', 'registros_sueno', 'registros_conductuales', 'documentos_clinicos',
        'valoraciones_petersen_originales'];
    $conteos = $vinculos = $estados = $fechas = $autores = [];
    $corte = now();
    foreach ($tablas as $tabla) {
        $q = DB::table($tabla)->where('cod_residente', $residente);
        $conteos[$tabla] = $q->count();
        $columnas = Schema::getColumnListing($tabla);
        if (in_array('estado', $columnas, true)) {
            $estados[$tabla] = (clone $q)->select('estado')->selectRaw('count(*) as cantidad')->groupBy('estado')->orderBy('estado')->get()->all();
        }
        if (in_array('fecha_hora', $columnas, true)) {
            $fechas[$tabla]['posteriores_al_corte_clinico'] = (clone $q)->where('fecha_hora', '>', $corte)->count();
        }
        if (in_array('registrado_en', $columnas, true)) {
            $fechas[$tabla]['posteriores_al_corte_tecnico'] = (clone $q)->where('registrado_en', '>', $corte)->count();
            $fechas[$tabla]['sin_instante_tecnico'] = (clone $q)->whereNull('registrado_en')->count();
        }
        if (in_array('cod_personal', $columnas, true)) {
            $autores[$tabla] = ['sin_autor_documentado' => (clone $q)->whereNull('cod_personal')->count(),
                'referencia_a_autor_inexistente' => DB::table($tabla.' as f')->leftJoin('personal as p', 'p.cod_personal', '=', 'f.cod_personal')
                    ->where('f.cod_residente', $residente)->whereNotNull('f.cod_personal')->whereNull('p.cod_personal')->count()];
        }
        if (Schema::hasColumn($tabla, 'cod_atencion') && $tabla !== 'atenciones') {
            $vinculos[$tabla] = [
                'sin_atencion' => (clone $q)->whereNull('cod_atencion')->count(),
                'atencion_ajena_o_inexistente' => DB::table($tabla.' as f')
                    ->leftJoin('atenciones as a', 'a.cod_atencion', '=', 'f.cod_atencion')
                    ->where('f.cod_residente', $residente)->whereNotNull('f.cod_atencion')
                    ->where(fn ($w) => $w->whereNull('a.cod_atencion')->orWhere('a.cod_residente', '!=', $residente))->count(),
            ];
        }
    }
    $aplicaciones = DB::table('aplicaciones_instrumento as a')->leftJoin('instrumentos as i', 'i.cod_instrumento', '=', 'a.cod_instrumento')
        ->where('a.cod_residente', $residente);
    $tipos = (clone $aplicaciones)->select('i.tipo')->selectRaw('count(*) as cantidad')->groupBy('i.tipo')->orderBy('i.tipo')->get()->all();
    $respuestas = DB::table('respuestas_instrumento as r')->join('aplicaciones_instrumento as a', 'a.cod_aplicacion', '=', 'r.cod_aplicacion')
        ->where('a.cod_residente', $residente);
    $incompatibles = (clone $respuestas)->leftJoin('preguntas_instrumento as p', 'p.cod_pregunta', '=', 'r.cod_pregunta')
        ->leftJoin('opciones_pregunta as o', 'o.cod_opcion', '=', 'r.cod_opcion')
        ->where(fn ($w) => $w->whereNull('p.cod_pregunta')->orWhereColumn('p.cod_instrumento', '!=', 'a.cod_instrumento')
            ->orWhere(fn ($x) => $x->whereNotNull('r.cod_opcion')->where(fn ($y) => $y->whereNull('o.cod_opcion')->orWhereColumn('o.cod_pregunta', '!=', 'r.cod_pregunta'))))->count();
    $originales = DB::table('valoraciones_petersen_originales')->where('cod_residente', $residente)
        ->select('elemento', 'estado_original')->selectRaw('count(*) as cantidad')->groupBy('elemento', 'estado_original')->orderBy('elemento')->get()->all();
    $sinAutor = (clone $aplicaciones)->leftJoin('personal as p', 'p.cod_personal', '=', 'a.cod_personal')->whereNull('p.cod_personal')->count();
    $originalesPorElemento = [];
    foreach (['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS', 'C01', 'C03', 'C04', 'C05'] as $elemento) {
        $cantidad = DB::table('valoraciones_petersen_originales')->where('cod_residente', $residente)->where('elemento', $elemento)->count();
        $originalesPorElemento[$elemento] = ['registros_originales' => $cantidad,
            'situacion' => $cantidad === 0 ? 'SIN_VALORACION_ORIGINAL' : 'REQUIERE_LECTURA_DEL_CONTRATO_Y_SUS_SOPORTES'];
    }
    $procedencia = ['sin_usuario_de_registro' => 0, 'sin_fecha_tecnica' => 0, 'pareja_registro_incompleta' => 0,
        'usuario_inexistente' => 0, 'sin_vinculo_documental' => 0, 'pareja_documental_incompleta' => 0,
        'documento_ajeno_o_inexistente' => 0, 'documento_no_verificable' => 0, 'documento_bytes_verificados' => 0];
    $archivos = app(ArchivosAplicacion::class);
    foreach (DB::table('aplicaciones_instrumento')->where('cod_residente', $residente)
        ->get(['cod_usuario_registro', 'registrado_en', 'cod_documento_clinico', 'localizador_fuente']) as $a) {
        $usuario = $a->cod_usuario_registro !== null;
        $tecnica = $a->registrado_en !== null;
        $procedencia['sin_usuario_de_registro'] += (int) ! $usuario;
        $procedencia['sin_fecha_tecnica'] += (int) ! $tecnica;
        $procedencia['pareja_registro_incompleta'] += (int) ($usuario !== $tecnica);
        $procedencia['usuario_inexistente'] += (int) ($usuario && ! DB::table('usuarios')->where('cod_usuario', $a->cod_usuario_registro)->exists());
        $documento = $a->cod_documento_clinico !== null;
        $localizador = trim((string) $a->localizador_fuente) !== '';
        $procedencia['sin_vinculo_documental'] += (int) ! $documento;
        $procedencia['pareja_documental_incompleta'] += (int) ($documento !== $localizador);
        if ($documento) {
            $doc = DB::table('documentos_clinicos')->where('cod_documento_clinico', $a->cod_documento_clinico)
                ->first(['cod_residente', 'ruta_archivo', 'hash_archivo']);
            if ($doc === null || $doc->cod_residente !== $residente) {
                $procedencia['documento_ajeno_o_inexistente']++;
            } else {
                $hash = $archivos->existe('local', $doc->ruta_archivo) ? $archivos->hash('local', $doc->ruta_archivo) : false;
                $verificado = is_string($hash) && hash_equals($doc->hash_archivo, $hash);
                $procedencia[$verificado ? 'documento_bytes_verificados' : 'documento_no_verificable']++;
            }
        }
    }
    $cadenas = [];
    foreach ([['aplicaciones_instrumento', 'cod_aplicacion', 'cod_aplicacion_anterior'], ['controles_funcionales_iadl', 'cod_control_iadl', 'cod_control_anterior']] as [$tabla, $pk, $fk]) {
        $filas = DB::table($tabla)->where('cod_residente', $residente)->get([$pk, $fk])->keyBy($pk);
        $cadenas[$tabla] = ['antecesores_ajenos_o_inexistentes' => 0, 'ciclos_o_autorreferencias' => 0,
            'sucesores' => $filas->whereNotNull($fk)->count(), 'cabezas' => 0, 'bifurcaciones' => 0];
        $antecesores = $filas->pluck($fk)->filter()->countBy();
        $cadenas[$tabla]['bifurcaciones'] = $antecesores->filter(fn ($n) => $n > 1)->count();
        foreach ($filas as $fila) {
            $cadenas[$tabla]['cabezas'] += (int) ! isset($antecesores[$fila->$pk]);
            if ($fila->$fk !== null && ! $filas->has($fila->$fk)) {
                $cadenas[$tabla]['antecesores_ajenos_o_inexistentes']++;
            }
            $vistos = [];
            $actual = $fila;
            while ($actual !== null) {
                if (isset($vistos[$actual->$pk])) {
                    $cadenas[$tabla]['ciclos_o_autorreferencias']++;
                    break;
                }
                $vistos[$actual->$pk] = true;
                $actual = $actual->$fk === null ? null : $filas->get($actual->$fk);
            }
        }
    }
    $sinRespuestas = (clone $aplicaciones)->whereNotExists(fn ($q) => $q->selectRaw('1')->from('respuestas_instrumento as r')
        ->whereColumn('r.cod_aplicacion', 'a.cod_aplicacion'))->count();
    $duplicadas = (clone $respuestas)->select('r.cod_aplicacion', 'r.cod_pregunta')->groupBy('r.cod_aplicacion', 'r.cod_pregunta')->havingRaw('count(*) > 1')->get()->count();
    $sinValor = (clone $respuestas)->whereNull('r.cod_opcion')->whereNull('r.valor_numero')->whereNull('r.valor_logico')
        ->where(fn ($q) => $q->whereNull('r.valor_texto')->orWhere('r.valor_texto', ''))->count();
    $informe = ['contrato' => 'AUDITORIA-FUENTES-RESIDENTE-1', 'residente' => $residente,
        'consultado_en' => $corte->toIso8601String(), 'solo_lectura' => true, 'inferencia_ejecutada' => false,
        'registros_por_fuente' => $conteos, 'aplicaciones_por_tipo' => $tipos,
        'respuestas' => ['total' => (clone $respuestas)->count(), 'referencias_incompatibles' => $incompatibles,
            'aplicaciones_sin_respuestas' => $sinRespuestas, 'pares_aplicacion_pregunta_duplicados' => $duplicadas, 'sin_valor_explicito' => $sinValor],
        'aplicaciones_sin_instrumento_existente' => (clone $aplicaciones)->whereNull('i.cod_instrumento')->count(),
        'aplicaciones_sin_autor_existente' => $sinAutor, 'vinculos_atencion' => $vinculos,
        'autores_inexistentes_por_fuente' => $autores, 'estados_originales' => $estados, 'corte_y_fechas' => $fechas,
        'procedencia_aplicaciones' => $procedencia, 'cadenas_correccion' => $cadenas,
        'juicios_originales_por_elemento' => $originales,
        'necesidades_captura_petersen' => $originalesPorElemento, 'C02' => 'DERIVADO_NO_SE_CAPTURA',
        'perfiles_instrumentales_configurados' => count(config('sistema_experto.perfiles_instrumentales', [])),
        'perfiles_interpretativos_configurados' => count(config('sistema_experto.perfiles_interpretativos', [])),
        'completitud_por_metodo' => null,
        'admisibilidad_clinica' => 'NO_EVALUADA_POR_ESTE_INVENTARIO_TECNICO',
        'limite' => 'La ausencia de vínculo no prueba que el registro sea falso. No se atribuye validez clínica ni se transforma una observación en medición.'];
    echo json_encode($informe, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;
} finally {
    DB::rollBack();
}
