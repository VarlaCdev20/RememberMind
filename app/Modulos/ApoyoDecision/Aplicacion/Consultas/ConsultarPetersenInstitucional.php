<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\ControlFuncionalIadl;
use App\Models\DocumentoClinico;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen as F;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\VerificarOriginalDocumentalPetersen;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\petersen\EvaluarPetersenInstitucional as E;
use MotorExperto\dominio\petersen\FuentePetersenInstitucional as Fuente;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use MotorExperto\soporte\conservacion\MembresiasOriginalesPetersen;

/** Adquiere juicios originales autorizados al corte y orquesta el motor; no guarda ni atribuye resultados al laboratorio. */
final class ConsultarPetersenInstitucional
{
    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte, string $version): array
    {
        return $this->preparar($usuario, $residente, $corte, $version)->contenido['salida'];
    }

    /** La captura compartida solo puede proceder del DTO instrumental autorizado y verificado del backend. */
    public function preparar(User $usuario, Residente $residente, CarbonImmutable $corte, string $version,
        ?InstantaneaInstrumental $instrumental = null): InstantaneaPetersen
    {
        return DB::transaction(function () use ($usuario, $residente, $corte, $version, $instrumental): InstantaneaPetersen {
            $usuario = User::query()->findOrFail($usuario->getKey());
            $residente = Residente::query()->findOrFail($residente->getKey());
            Gate::forUser($usuario)->authorize('consultarOriginales', [ValoracionPetersenOriginal::class, $residente]);
            if ($instrumental !== null && ($instrumental->contenido['contexto']['residente'] !== $residente->getKey()
                || $instrumental->contenido['contexto']['version'] !== $version
                || $instrumental->contenido['contexto']['usuario_ejecucion'] !== $usuario->getKey()
                || $instrumental->contenido['capturas']['fecha_corte'] !== $corte->format('Y-m-d\TH:i:s.uP'))) {
                throw new DomainException('La captura compartida pertenece a otro contexto de evaluación.');
            }
            if ($instrumental !== null) {
                Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
                foreach (CapturasInstrumentalesResidente::PERMISOS_LECTURA as $permiso) {
                    abort_unless($usuario->checkPermissionTo($permiso, 'web'), 403);
                }
                if (collect($instrumental->contenido['capturas']['capturas'])->contains(
                    fn ($captura) => $captura['procedencia']['cod_documento_clinico'] !== null
                )) {
                    abort_unless($usuario->checkPermissionTo('documentos_clinicos.ver', 'web'), 403);
                }
            }
            if ($corte->greaterThan(CarbonImmutable::now())) {
                throw new DomainException('El corte de Petersen no puede ser futuro.');
            }
            $filas = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())
                ->where('fecha_hora', '<=', $corte->format('Y-m-d H:i:s.u'))->where('registrado_en', '<=', $corte->format('Y-m-d H:i:s.u'))
                ->with('soportes')->orderBy('fecha_hora')->orderBy('cod_valoracion_petersen')->get();
            $rectificados = $filas->pluck('cod_valoracion_anterior')->filter()->all();
            $contexto = new ContextoEjecucion('PET-'.substr(hash('sha256', $residente->getKey().$corte->format('c.u').$version), 0, 12),
                $residente->getKey(), $version, $corte->toDateTimeImmutable());
            $capturas = $instrumental?->contenido['capturas'];
            $originales = [];
            $solicitudes = $filas->flatMap(fn ($f) => $f->soportes);
            // Una consulta por tipo de fuente; no una por soporte o residente ajeno.
            foreach (F::FUENTES as $fk => [$modelo, $permiso]) {
                $ids = $solicitudes->pluck($fk)->filter()->unique();
                if ($ids->isEmpty()) {
                    continue;
                }
                // Un soporte visible sin permiso sobre su fuente no puede producir una evaluación recortada.
                abort_unless($usuario->checkPermissionTo($permiso, 'web'), 403);
                if ($fk === 'cod_control_iadl') {
                    Gate::forUser($usuario)->authorize('consultarFuncionalidad', [EvaluacionExperta::class, $residente]);
                }
                $originales[$fk] = $modelo::query()->where('cod_residente', $residente->getKey())->whereKey($ids)->with('atencion')->get()->keyBy(fn ($m) => $m->getKey());
            }
            $idsDocumentos = $filas->pluck('cod_documento_clinico')->filter()->unique();
            if ($idsDocumentos->isNotEmpty()) {
                abort_unless($usuario->checkPermissionTo('documentos_clinicos.ver', 'web'), 403);
            }
            $documentosOriginales = $idsDocumentos->isEmpty() ? collect() : DocumentoClinico::query()
                ->where('cod_residente', $residente->getKey())->whereKey($idsDocumentos)->get()->keyBy(fn ($m) => $m->getKey());
            $idsIadl = ($originales['cod_control_iadl'] ?? collect())->keys();
            $iadlRectificadas = $idsIadl->isEmpty() ? [] : array_fill_keys(ControlFuncionalIadl::query()
                ->where('cod_residente', $residente->getKey())->whereIn('cod_control_anterior', $idsIadl)
                ->where('fecha_hora', '<=', $corte->format('Y-m-d H:i:s.u'))->where('registrado_en', '<=', $corte->format('Y-m-d H:i:s.u'))
                ->pluck('cod_control_anterior')->all(), true);
            $valores = $excluidos = $documentos = $procedencia = $membresias = $soportesOriginales = [];
            $campos = array_merge(...array_values(V::COMPROBACIONES));
            foreach ($filas as $f) {
                $membresias[] = ['valoracion' => $f->getKey(), 'soportes' => $f->soportes->modelKeys()];
                foreach ($f->soportes as $soporteOriginal) {
                    // También antes de excluir el juicio: identidad y orden no se reconstruyen buscando fuentes después.
                    $soportesOriginales[] = $soporteOriginal->only(MembresiasOriginalesPetersen::CAMPOS);
                }
                $procedencia[] = $f->only(['cod_valoracion_petersen', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'elemento', 'tipo_registro',
                    'cod_valoracion_anterior', 'cod_documento_clinico', 'localizador_fuente']) + ['registrado_en' => $f->registrado_en->format('Y-m-d\TH:i:s.uP'),
                        'fecha_hora' => $f->fecha_hora->format('Y-m-d\TH:i:s.uP')];
                if (in_array($f->getKey(), $rectificados, true) || $f->tipo_registro === 'ANULACION') {
                    $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento,
                        'motivo' => $f->tipo_registro === 'ANULACION' ? 'ANULACION_ORIGINAL' : 'RECTIFICADA_AL_CORTE'];

                    continue;
                }
                if ($f->cod_documento_clinico !== null) {
                    $doc = $documentosOriginales->get($f->cod_documento_clinico);
                    $verificado = $doc !== null && app(VerificarOriginalDocumentalPetersen::class)->verificar($doc);
                    $documentos[] = $this->constancia($f->getKey(), 'ORIGINAL', $f->cod_documento_clinico, $doc, $verificado, $f->localizador_fuente);
                    if (! $verificado || $doc->fecha_hora === null || $doc->fecha_hora->greaterThan($corte)) {
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento,
                            'motivo' => ! $verificado ? 'DOCUMENTO_ORIGINAL_NO_VERIFICABLE'
                                : ($doc->fecha_hora === null ? 'FUENTE_SIN_INSTANTE_CLINICO_IDENTIFICABLE' : 'DOCUMENTO_ORIGINAL_FUERA_DE_CORTE')];

                        continue;
                    }
                }
                $origen = new Fuente($f->getTable(), $f->getKey(), $residente->getKey(), $f->cod_personal,
                    $f->fecha_hora->toDateTimeImmutable(), $f->cod_atencion === null ? null : 'ATENCION:'.$f->cod_atencion, 'VALORACION_ORIGINAL', true);
                $soportes = $revisados = [];
                $incompatible = false;
                foreach ($f->soportes as $s) {
                    $fks = array_filter(array_keys(F::FUENTES), fn ($fk) => $s->$fk !== null);
                    if (count($fks) !== 1) {
                        throw new DomainException('Un soporte conservado no tiene una referencia original única.');
                    }
                    $fk = reset($fks);
                    $o = ($originales[$fk] ?? collect())->get($s->$fk);
                    if ($fk === 'cod_documento_clinico') {
                        $verificado = $o !== null && app(VerificarOriginalDocumentalPetersen::class)->verificar($o);
                        $documentos[] = $this->constancia($f->getKey(), 'SOPORTE', $s->$fk, $o, $verificado, null);
                    }
                    if (! $o || ! in_array($fk, F::admitidas($f->elemento, $s->tipo_soporte), true)) {
                        $incompatible = true;
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'SOPORTE_NO_DISPONIBLE_O_INCOMPATIBLE'];

                        continue;
                    }
                    if ($s->tipo_soporte === 'DOMINIO_REVISADO') {
                        if (! in_array($o->elemento, E::DOMINIOS, true) || $o->fecha_hora->greaterThan($f->fecha_hora)
                            || $o->registrado_en->greaterThan($f->registrado_en)
                            || isset($revisados[$o->elemento])) {
                            $incompatible = true;
                        } else {
                            $revisados[$o->elemento] = $o->getTable().':'.$o->getKey();
                        }

                        continue;
                    }
                    $fecha = $o->fecha_hora;
                    if (! $fecha) {
                        // fecha_referencia sin hora se conserva como contexto; nunca se inventa medianoche clínica.
                        $incompatible = true;
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'FUENTE_SIN_INSTANTE_CLINICO_IDENTIFICABLE'];

                        continue;
                    }
                    $vigente = ! in_array($o->estado, ['ANULADA', 'ANULADO', 'INACTIVA', 'INACTIVO', 'ARCHIVADO'], true);
                    // controles_cognitivos también tiene una columna clínica «atencion»; usar la relación cargada explícita.
                    if (($o->cod_atencion !== null && $o->getRelation('atencion')?->cod_residente !== $residente->getKey())
                        || $o->cod_personal === null || ($o->registrado_en && $o->registrado_en->greaterThan($corte))) {
                        $incompatible = true;
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'PROCEDENCIA_ORIGINAL_INCOMPATIBLE'];

                        continue;
                    }
                    if ($o instanceof DocumentoClinico && ! $verificado) {
                        $incompatible = true;
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'DOCUMENTO_ORIGINAL_NO_VERIFICABLE'];

                        continue;
                    }
                    if ($o instanceof DocumentoClinico && $fecha->greaterThan($corte)) {
                        $incompatible = true;
                        $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'DOCUMENTO_ORIGINAL_FUERA_DE_CORTE'];

                        continue;
                    }
                    $episodio = $o->cod_atencion === null ? null : 'ATENCION:'.$o->cod_atencion;
                    $datos = ['descripcion' => trim((string) ($o->observacion ?? $o->descripcion ?? $o->conclusion ?? $o->titulo ?? ''))];
                    if ($s->tipo_soporte === 'IADL') {
                        $datos = $o->only(['actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente']);
                        $vigente = $vigente && ! isset($iadlRectificadas[$o->getKey()]);
                    }
                    if ($s->tipo_soporte === 'MEDICION') {
                        $capturas ??= app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, $corte);
                        $captura = collect($capturas['capturas'])->firstWhere('cod_aplicacion', $o->getKey());
                        $perfil = $captura['perfil_captura_original'] ?? null;
                        if (! is_array($perfil) || $s->metodo_original !== ($perfil['metodo'] ?? null)
                            || ! hash_equals($s->huella_perfil_original, CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($perfil)))) {
                            $incompatible = true;
                            $excluidos[] = ['valoracion' => $f->getKey(), 'elemento' => $f->elemento, 'motivo' => 'CONTRATO_INSTRUMENTAL_ORIGINAL_NO_RECUPERABLE'];

                            continue;
                        }
                        $datos = ['dominio' => $f->elemento, 'captura_original' => $captura];
                    }
                    if ($fecha->greaterThan($f->fecha_hora)) {
                        $vigente = false;
                    }
                    $soportes[] = new Fuente($o->getTable(), $o->getKey(), $residente->getKey(), $o->cod_personal,
                        CarbonImmutable::instance($fecha)->toDateTimeImmutable(), $episodio, $s->tipo_soporte, $vigente, $datos);
                }
                if ($incompatible) {
                    continue;
                }
                $condiciones = array_intersect_key($f->only($campos), array_flip(V::COMPROBACIONES[str_starts_with($f->elemento, 'COG-') ? 'DOMINIO' : $f->elemento]));
                $valores[] = new V($f->elemento, $f->estado_original, $f->justificacion, $origen, $soportes, $condiciones, $revisados);
            }
            $familia = app(ContratosConocimiento::class);
            $versionPetersen = $familia->versionPetersenVigente($corte);
            $paquete = $cabeceraOriginal = null;
            if ($versionPetersen !== null) {
                $cabecera = VersionModeloExperto::query()->whereKey($versionPetersen)->lockForUpdate()->sole();
                if ($cabecera->fecha_hora_retiro !== null && CarbonImmutable::parse($cabecera->getRawOriginal('fecha_hora_retiro'))->lessThanOrEqualTo($corte)) {
                    throw new DomainException('El gobierno Petersen cambió durante la consulta. Actualice el informe.');
                }
                $contrato = $familia->porVersion($versionPetersen);
                $paquete = new PaqueteConocimiento($versionPetersen, app(ConocimientoVersionado::class)->instantanea($versionPetersen),
                    $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], []);
                $cabeceraOriginal = $cabecera->getAttributes();
                foreach (['fecha_hora_creacion', 'fecha_hora_vigencia', 'fecha_hora_retiro'] as $campo) {
                    $cabeceraOriginal[$campo] = $cabecera->$campo?->format('Y-m-d\TH:i:s.uP');
                }
            }
            $adquisicion = ['valoraciones_al_corte' => $filas->count(), 'exclusiones' => $excluidos,
                'fuentes_originales_mutables' => true, 'conservacion_completa' => false];

            return InstantaneaPetersen::crear($contexto, $valores, $paquete, $cabeceraOriginal, $usuario->getKey(), $adquisicion, $documentos, $procedencia, $membresias,
                $soportesOriginales);
        });
    }

    private function constancia(string $valoracion, string $papel, string $codigo, ?DocumentoClinico $documento, bool $verificado, ?string $localizador): array
    {
        return ['valoracion' => $valoracion, 'papel' => $papel, 'cod_documento_clinico' => $codigo,
            'cod_residente' => $documento?->cod_residente, 'cod_personal' => $documento?->cod_personal,
            'hash_archivo' => $documento?->hash_archivo, 'fecha_hora' => $documento?->fecha_hora?->format('Y-m-d\TH:i:s.uP'),
            'estado' => $documento?->estado, 'localizador_fuente' => $localizador, 'verificado' => $verificado];
    }
}
