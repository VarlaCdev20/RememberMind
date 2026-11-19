<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos;
use DomainException;
use Illuminate\Support\Facades\DB;
use MotorExperto\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\conservacion\CodecInstantanea as Codec;

/** Reconstrucción nominal desde SQL experto: jamás consulta expedientes actuales ni ejecuta inferencias. */
final class ResultadoInstitucionalSQL
{
    public function leer(string $id, bool $verificar = true): array
    {
        $e = DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $id)->where('version_conservacion', R::VERSION)->where('estado_ejecucion', 'FINALIZADA')->first();
        if (! $e) { throw new DomainException('No existe un resultado institucional conservado con esa identidad.'); }
        $dominios = $requisitos = [];
        foreach (R::DOMINIOS as $codigo => $campo) { $dominios[$codigo] = $e->{'institucional_'.$campo}; }
        foreach (['C01', 'C02', 'C03', 'C04', 'C05'] as $codigo) { $requisitos[$codigo] = $e->{'institucional_'.strtolower($codigo)}; }
        $hechos = [];
        foreach (DB::table('hechos_instantanea_experta')->where('cod_evaluacion_experta', $id)->orderBy('orden')->get() as $h) {
            $hechos[] = ['id' => $h->cod_hecho_instantanea, 'orden' => (int) $h->orden, 'tipo' => $h->tipo_origen,
                'fuente' => $h->fuente_id_dominio, 'origen' => $h->origen_id_dominio, 'concepto' => $h->concepto_dominio,
                'autor' => $h->cod_personal_autor, 'respuesta' => $h->cod_respuesta, 'aplicacion' => $h->cod_aplicacion,
                'valoracion' => $h->cod_valoracion_petersen, 'fecha' => Codec::fecha('fecha', Codec::leerFecha('fecha_hecho', $h)), 'valor' => Codec::leerEscalar('valor', $h)];
        }
        $criterios = [];
        $ecs = DB::table('evaluacion_criterios')->where('cod_evaluacion_experta', $id)->orderBy('cod_nodo_criterio')->get();
        $rs = BloquesExpertos::consulta('evaluaciones_reglas')->whereIn('cod_traza_inferencia', $ecs->pluck('cod_traza_inferencia')->filter())->orderBy('cod_regla_experta')->get();
        $cs = BloquesExpertos::consulta('evaluaciones_condiciones_regla')->whereIn('cod_evaluacion_regla', $rs->pluck('cod_evaluacion_regla'))->orderBy('cod_condicion_regla')->get()->groupBy('cod_evaluacion_regla');
        $evidencias = DB::table('evidencias_evaluacion')->where('cod_evaluacion_experta', $id)->orderBy('cod_evidencia_evaluacion')->get()
            ->map(fn ($v) => ['id' => $v->cod_evidencia_evaluacion, 'mapeo' => $v->cod_mapeo_variable_fuente, 'origen' => $v->cod_registro_fuente,
                'valor' => $v->cod_valor_semantico, 'representacion' => $v->estado_representacion, 'admisibilidad' => $v->estado_admisibilidad, 'motivo' => $v->motivo_admisibilidad])->all();
        $soportes = BloquesExpertos::consulta('evidencias_soporte_condicion')->whereIn('cod_evaluacion_condicion', $cs->flatten(1)->pluck('cod_evaluacion_condicion'))
            ->orderBy('cod_evaluacion_condicion')->orderBy('cod_evidencia_evaluacion')->get()->groupBy('cod_evaluacion_condicion');
        $participaciones = BloquesExpertos::consulta('evidencias_criterio_evaluacion')->whereIn('cod_evaluacion_criterio', $ecs->pluck('cod_evaluacion_criterio'))
            ->orderBy('cod_evidencia_evaluacion')->get()->groupBy('cod_evaluacion_criterio');
        foreach ($ecs as $ec) {
            $reglas = [];
            foreach ($rs->where('cod_traza_inferencia', $ec->cod_traza_inferencia) as $r) {
                $reglas[] = ['id' => $r->cod_evaluacion_regla, 'regla' => $r->cod_regla_experta, 'estado' => $r->estado_regla,
                    'condiciones' => ($cs[$r->cod_evaluacion_regla] ?? collect())->map(fn ($c) => ['condicion' => $c->cod_condicion_regla, 'estado' => $c->estado_condicion,
                        'soportes' => ($soportes[$c->cod_evaluacion_condicion] ?? collect())->pluck('cod_evidencia_evaluacion')->all()])->all()];
            }
            $criterios[] = ['criterio' => $ec->cod_nodo_criterio, 'estado' => $ec->estado_evaluabilidad, 'traza' => $ec->traza_estado_inferencia, 'reglas' => $reglas,
                'evidencias' => ($participaciones[$ec->cod_evaluacion_criterio] ?? collect())->map(fn ($v) => ['evidencia' => $v->cod_evidencia_evaluacion,
                    'rol' => $v->rol_en_criterio, 'participacion' => $v->estado_participacion])->all()];
        }
        $contenido = CapturaInstrumentalCanonica::ordenar(['contrato' => R::VERSION, 'id' => $id, 'solicitud' => $e->id_evaluacion_dominio,
            'residente' => $e->cod_residente, 'usuario' => $e->cod_usuario_ejecucion, 'personal' => $e->cod_personal_solicitante,
            'version_cognitiva' => $e->cod_version_modelo, 'version_petersen' => $e->cod_version_petersen,
            'fecha_corte' => Codec::fecha('corte', Codec::leerFecha('fecha_corte', $e)), 'dominios' => $dominios, 'requisitos' => $requisitos,
            'resultado' => $e->institucional_resultado, 'patron' => $e->institucional_patron, 'explicacion' => $e->institucional_explicacion,
            'ejecutor' => $e->institucional_ejecutor, 'hechos' => $hechos, 'criterios' => $criterios, 'evidencias' => $evidencias]);
        $huella = Codec::huella($contenido);
        if ($verificar && ! hash_equals($e->huella_memoria_cognicion ?? '', $huella)) {
            throw new DomainException('El resultado guardado no supera la comprobación de integridad.');
        }
        return ['contenido' => $contenido, 'huella' => $huella, 'persistido' => $verificar, 'replay_completo' => false];
    }
}
