<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Models\AplicacionInstrumento;
use App\Models\EvaluacionExperta;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\instrumentos\ExtractorCapturaInstrumental;

/** Lectura institucional por lotes; captura originales sin escribir resultados ni identificar pruebas por similitud. */
final class CapturasInstrumentalesResidente
{
    public const PERMISOS_LECTURA = ['aplicaciones_instrumento.ver', 'respuestas_instrumento.ver', 'instrumentos.ver', 'preguntas_instrumento.ver', 'opciones_pregunta.ver'];

    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte, array $preguntasDeclarativas = []): array
    {
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        foreach (self::PERMISOS_LECTURA as $permiso) {
            if (! $usuario->checkPermissionTo($permiso, 'web')) {
                return ['disponible' => false, 'motivo' => 'ACCESO_A_FUENTES_NO_DISPONIBLE', 'capturas' => []];
            }
        }
        if (! array_is_list($preguntasDeclarativas) || array_filter($preguntasDeclarativas, static fn ($p) => ! is_string($p) || $p === '')) {
            throw new DomainException('Las preguntas declarativas requieren identidades explícitas.');
        }
        $puedeConsultarDocumentos = $usuario->checkPermissionTo('documentos_clinicos.ver', 'web');
        $relaciones = ['instrumento', 'evaluador', 'atencion', 'respuestas'];
        if ($puedeConsultarDocumentos) {
            $relaciones[] = 'documentoOriginal';
        }
        $aplicaciones = FuentesValoracionOperativa::limitar(AplicacionInstrumento::query())->where('cod_residente', $residente->getKey())
            ->where('fecha_hora', '<=', $corte->format('Y-m-d H:i:s.u'))
            ->where(fn ($q) => $q->whereNull('registrado_en')->orWhere('registrado_en', '<=', $corte->format('Y-m-d H:i:s.u')))
            ->with($relaciones)->orderBy('fecha_hora')->orderBy('cod_aplicacion')->get();
        if (! $puedeConsultarDocumentos && $aplicaciones->contains(fn ($a) => $a->cod_documento_clinico !== null)) {
            return ['disponible' => false, 'motivo' => 'ACCESO_A_FUENTES_NO_DISPONIBLE', 'capturas' => []];
        }
        $respuestas = $aplicaciones->flatMap(fn ($a) => $a->respuestas);
        $perfiles = config('sistema_experto.perfiles_instrumentales', []);
        $preguntasSeleccionadas = [];
        foreach ($perfiles as $perfil) {
            foreach ($perfil['selectores'] ?? [] as $selector) {
                if (is_array($selector) && is_string($selector['cod_pregunta'] ?? null)) {
                    $preguntasSeleccionadas[] = $selector['cod_pregunta'];
                }
            }
        }
        $preguntas = PreguntaInstrumento::query()->whereIn('cod_pregunta', $respuestas->pluck('cod_pregunta')
            ->merge($preguntasSeleccionadas)->merge($preguntasDeclarativas)->unique())->get()->keyBy('cod_pregunta');
        $opciones = OpcionPregunta::query()->whereIn('cod_opcion', $respuestas->pluck('cod_opcion')->filter()->unique())->get()->keyBy('cod_opcion');
        $capturas = [];
        $documentosVerificados = [];
        foreach ($aplicaciones as $a) {
            $instrumento = $a->instrumento;
            $autor = $a->evaluador;
            $motivos = [];
            if ($a->estado !== 'COMPLETA') {
                $motivos[] = 'APLICACION_NO_COMPLETA';
            }
            if (! $instrumento || $instrumento->getKey() !== $a->cod_instrumento || ! $autor || $autor->getKey() !== $a->cod_personal
                || ($a->cod_atencion !== null && $a->atencion?->cod_residente !== $residente->getKey())) {
                $motivos[] = 'PROCEDENCIA_INCOMPATIBLE';
            }
            $estadoDocumento = 'SIN_VINCULO_DOCUMENTAL';
            $documentoOriginal = null;
            if ($a->cod_documento_clinico !== null) {
                $doc = $a->documentoOriginal;
                if ($doc && $doc->cod_residente === $residente->getKey()) {
                    $documentoOriginal = $doc->only(['cod_documento_clinico', 'cod_residente', 'cod_personal', 'hash_archivo', 'estado']);
                    $documentoOriginal['fecha_hora'] = $doc->fecha_hora?->format('Y-m-d\TH:i:s.uP');
                }
                if (! array_key_exists($a->cod_documento_clinico, $documentosVerificados)) {
                    $archivos = app(ArchivosAplicacion::class);
                    $hash = $doc && $archivos->existe('local', $doc->ruta_archivo) ? $archivos->hash('local', $doc->ruta_archivo) : false;
                    $documentosVerificados[$a->cod_documento_clinico] = $doc !== null && $doc->estado === 'VIGENTE'
                        && $doc->cod_residente === $residente->getKey() && is_string($hash) && hash_equals($doc->hash_archivo, $hash);
                }
                $estadoDocumento = $documentosVerificados[$a->cod_documento_clinico] ? 'VERIFICADO' : 'ORIGINAL_NO_VERIFICABLE';
                if ($estadoDocumento !== 'VERIFICADO') {
                    $motivos[] = $estadoDocumento;
                }
            }
            $brutos = [];
            foreach ($a->respuestas->sortBy('cod_respuesta', SORT_STRING) as $r) {
                $q = $preguntas->get($r->cod_pregunta);
                $o = $r->cod_opcion === null ? null : $opciones->get($r->cod_opcion);
                $coherente = $r->cod_aplicacion === $a->getKey() && $q?->cod_instrumento === $a->cod_instrumento
                    && ($r->cod_opcion === null || $o?->cod_pregunta === $r->cod_pregunta);
                $brutos[] = ['cod_respuesta' => $r->getKey(), 'cod_aplicacion' => $r->cod_aplicacion, 'cod_pregunta' => $r->cod_pregunta,
                    'cod_opcion' => $r->cod_opcion, 'valor_numero' => $r->getRawOriginal('valor_numero'), 'valor_logico' => $r->getRawOriginal('valor_logico'),
                    'valor_texto' => $r->getRawOriginal('valor_texto'), 'puntaje' => $r->getRawOriginal('puntaje'),
                    'observacion' => $r->getRawOriginal('observacion'), 'opcion_actual' => $o?->getRawOriginal(), 'pregunta_actual' => $q?->getRawOriginal(),
                    'codigo_pregunta' => $q?->codigo, 'pregunta' => $q?->enunciado, 'dominio_original' => $q?->dominio, 'tipo_respuesta' => $q?->tipo_respuesta,
                    'estado_pregunta_actual' => $q?->estado, 'coherente' => $coherente];
                if (! $coherente) {
                    $motivos[] = 'RESPUESTA_CON_REFERENCIAS_INCOMPATIBLES';
                }
            }
            $perfilesAplicables = array_values(array_filter($perfiles, fn ($p) => ($p['cod_instrumento'] ?? null) === $a->cod_instrumento
                && ($p['version_instrumento'] ?? null) === $instrumento?->version));
            if (count($perfilesAplicables) > 1) {
                throw new DomainException('Hay más de un perfil para la misma identidad instrumental.');
            }
            $esObservacion = $instrumento?->tipo === 'OBSERVACIONAL';
            if ($esObservacion && $perfilesAplicables !== []) {
                throw new DomainException('Una observación clínica no admite un perfil de prueba instrumental.');
            }
            if ($perfilesAplicables === [] && ! $esObservacion) {
                $motivos[] = 'SIN_PERFIL_EXPLICITO';
            }
            $vinculos = [];
            foreach ($perfilesAplicables[0]['selectores'] ?? [] as $entrada => $selector) {
                $pregunta = is_array($selector) ? $preguntas->get($selector['cod_pregunta'] ?? '') : null;
                $vinculos[$entrada] = ['selector' => $selector, 'pregunta_actual' => $pregunta?->getRawOriginal(),
                    'pertenece_al_instrumento' => $pregunta !== null && $pregunta->cod_instrumento === $a->cod_instrumento,
                    'tiene_respuesta' => $pregunta !== null && $a->respuestas->contains('cod_pregunta', $pregunta->getKey())];
            }
            $salida = ['cod_aplicacion' => $a->getKey(), 'cod_residente' => $a->cod_residente, 'cod_instrumento' => $a->cod_instrumento,
                'codigo_instrumento' => $instrumento?->codigo, 'nombre_instrumento' => $instrumento?->nombre, 'version_instrumento' => $instrumento?->version,
                'cod_personal' => $a->cod_personal, 'autor' => trim(implode(' ', [$autor?->nombres, $autor?->apellido_paterno, $autor?->apellido_materno])),
                'fecha_hora' => $a->fecha_hora->format('Y-m-d\TH:i:s.uP'), 'estado_original' => $a->estado,
                'aplicacion_actual' => $a->getRawOriginal(), 'instrumento_actual' => $instrumento?->getRawOriginal(),
                'documento_original' => $documentoOriginal,
                'atencion_original' => $a->atencion?->only(['cod_atencion', 'cod_residente']),
                'procedencia' => ['cod_personal_aplicador' => $a->cod_personal, 'cod_usuario_registro' => $a->cod_usuario_registro,
                    'registrado_en' => $a->registrado_en?->format('Y-m-d\TH:i:s.uP'), 'cod_documento_clinico' => $a->cod_documento_clinico,
                    'localizador_fuente' => $a->localizador_fuente, 'cod_aplicacion_anterior' => $a->cod_aplicacion_anterior,
                    'estado_documento' => $estadoDocumento],
                'respuestas_originales' => $brutos, 'vinculos_preguntas_actuales' => $vinculos,
                'preguntas_declarativas_originales' => array_combine($preguntasDeclarativas,
                    array_map(static fn ($id) => $preguntas->get($id)?->getRawOriginal(), $preguntasDeclarativas)),
                'motivos' => array_values(array_unique($motivos)), 'extraccion' => null];
            // Solo el contrato instrumental aplicado; no se conserva configuración local completa.
            $salida['perfil_captura_original'] = $perfilesAplicables[0] ?? null;
            $nacimiento = $residente->fecha_nacimiento;
            $fecha = CarbonImmutable::instance($a->fecha_hora);
            $edad = $nacimiento && $nacimiento <= $fecha ? (int) $nacimiento->diffInYears($fecha) : null;
            $salida['edad_en_aplicacion'] = $edad;
            $salida['fuente_edad'] = ['tabla' => 'residentes', 'campo' => 'fecha_nacimiento', 'registro' => $residente->getKey(),
                'fecha_nacimiento' => $nacimiento?->format('Y-m-d'), 'fecha_aplicacion' => $salida['fecha_hora']];
            if ($motivos === [] && ! $esObservacion) {
                $salida['extraccion'] = (new ExtractorCapturaInstrumental)->extraer($perfilesAplicables[0], $salida, $brutos, $edad);
            }
            $capturas[] = $salida;
        }

        return ['disponible' => true, 'fecha_corte' => $corte->format('Y-m-d\TH:i:s.uP'), 'capturas' => $capturas, 'persistido' => false];
    }
}
