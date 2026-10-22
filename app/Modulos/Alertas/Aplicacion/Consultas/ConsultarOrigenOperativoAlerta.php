<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Compartido\Aplicacion\Formato\TextoVisible;
use App\Models\AdministracionMedicacion;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\Incidente;
use App\Models\IntervencionCuidado;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta;

/** Referencia tipada por los productores existentes; nunca busca coincidencias en narrativas. */
final class ConsultarOrigenOperativoAlerta
{
    public function consultar(User $usuario, string $codigo): array
    {
        $alerta = app(BandejaAlertas::class)->consulta($usuario)->findOrFail($codigo);
        $modulo = mb_strtoupper(trim((string) $alerta->modulo));
        $nodos = $aristas = [];
        $incidente = null;
        if (filled($alerta->cod_registro) && in_array($modulo, ['INCIDENTE', 'INCIDENTES'], true) && $usuario->can('incidentes.ver')) {
            $incidente = Incidente::where('cod_residente', $alerta->cod_residente)->with('personal')->find($alerta->cod_registro);
        }
        if ($incidente) {
            $nodos[] = $this->nodo('origen-operativo', 'Incidente: '.$this->texto($incidente->tipo_incidente), $incidente->descripcion, [
                'Fecha del hecho' => $incidente->fecha_hora?->format('d/m/Y H:i'), 'Lugar' => $incidente->lugar,
                'Gravedad registrada' => $incidente->gravedad, 'Situación del incidente' => $incidente->estado,
                'Profesional que registró' => LenguajeAlerta::responsable($incidente->personal),
            ]);
            $nodos[] = $this->nodo('medida-origen', 'Medida inmediata', $incidente->medida_inmediata ?: 'No consta una medida inmediata en el incidente.', [
                'Requiere médico (dato registrado)' => $incidente->requiere_medico ? 'Sí' : 'No',
                'Requiere derivación (dato registrado)' => $incidente->requiere_derivacion ? 'Sí' : 'No',
                'Observación guardada' => $incidente->observacion,
            ]);
            $aristas[] = $this->arista('alerta', 'origen-operativo', 'referencia este incidente');
            $aristas[] = $this->arista('origen-operativo', 'medida-origen', 'documenta actuación inicial');
        } elseif (filled($alerta->cod_registro) && ($modulo === 'SOLICITUD_MEDICA' || ($modulo === 'INCIDENTE' && $alerta->tipo === 'INCIDENTE EN SEGUIMIENTO')) && $usuario->can('atenciones.ver')) {
            // El detector general referencia atenciones en estos dos módulos.
            // INCIDENTES plural se reserva al incidente; no se sustituye por una atención.
            $atencion = Atencion::where('cod_residente', $alerta->cod_residente)->with(['personal', 'area'])->find($alerta->cod_registro);
            if ($atencion) {
                $nodos[] = $this->nodo('origen-operativo', 'Atención referenciada', $atencion->motivo, [
                    'Tipo de atención' => $atencion->getRawOriginal('tipo_atencion'), 'Fecha' => $atencion->fecha_hora?->format('d/m/Y H:i'),
                    'Área' => $atencion->area?->nombre, 'Profesional' => LenguajeAlerta::responsable($atencion->personal),
                    'Observación' => $atencion->observacion, 'Estado' => $atencion->estado,
                ]);
                $aristas[] = $this->arista('alerta', 'origen-operativo', 'referencia esta atención');
                if ($atencion->area) {
                    $nodos[] = $this->nodo('area-origen', 'Área de la atención', $atencion->area->nombre, ['Vínculo' => 'Área guardada en la atención de origen']);
                    $aristas[] = $this->arista('origen-operativo', 'area-origen', 'fue registrada en');
                }
            }
        }
        if (filled($alerta->cod_registro) && $modulo === 'PLAN' && $usuario->can('ejecuciones_cuidado.ver')) {
            $ejecucion = EjecucionCuidado::where('cod_residente', $alerta->cod_residente)->with('personal')->find($alerta->cod_registro);
            if ($ejecucion) {
                $nodos[] = $this->nodo('origen-operativo', 'Ejecución: '.$this->texto($ejecucion->estado), 'Cuidado programado que referencia esta alerta.', [
                    'Programada para' => $ejecucion->fecha_hora_programada?->format('d/m/Y H:i'),
                    'Ejecución documentada' => $ejecucion->fecha_hora_ejecucion?->format('d/m/Y H:i'),
                    'Resultado registrado' => $ejecucion->resultado, 'Motivo de omisión' => $ejecucion->motivo_omision,
                    'Observación' => $ejecucion->observacion, 'Profesional' => LenguajeAlerta::responsable($ejecucion->personal),
                ]);
                $aristas[] = $this->arista('alerta', 'origen-operativo', 'referencia esta ejecución');
                if ($usuario->can('intervenciones_cuidado.ver')) {
                    $intervencion = IntervencionCuidado::whereKey($ejecucion->cod_intervencion)
                        ->whereHas('plan', fn ($q) => $q->where('cod_residente', $alerta->cod_residente))->first();
                    // La intervención solo es coherente si el plan pertenece al mismo residente.
                    if ($intervencion) {
                        $nodos[] = $this->nodo('intervencion-origen', $intervencion->nombre, $intervencion->descripcion,
                            ['Objetivo específico' => $intervencion->objetivo_especifico, 'Estado' => $intervencion->estado]);
                        $aristas[] = $this->arista('intervencion-origen', 'origen-operativo', 'tiene ejecución registrada');
                        if ($usuario->can('planes_cuidado.ver')) {
                            $plan = $intervencion->plan;
                            $nodos[] = $this->nodo('plan-origen', $plan->nombre, $plan->objetivo_general, [
                                'Tipo de plan' => $plan->tipo_plan, 'Estado' => $plan->estado,
                                'Apertura' => $plan->fecha_hora_apertura?->format('d/m/Y H:i'),
                                'Cierre' => $plan->fecha_hora_cierre?->format('d/m/Y H:i'),
                            ]);
                            $aristas[] = $this->arista('plan-origen', 'intervencion-origen', 'incluye intervención');
                            if ($plan->area) {
                                $nodos[] = $this->nodo('area-origen', 'Área responsable del plan', $plan->area->nombre, ['Vínculo' => 'Área guardada en el plan de origen']);
                                $aristas[] = $this->arista('plan-origen', 'area-origen', 'pertenece al área');
                            }
                        }
                    }
                }
            }
        }
        if (filled($alerta->cod_registro) && $modulo === 'MEDICACION' && $usuario->can('administraciones_medicacion.ver')) {
            $administracion = AdministracionMedicacion::where('cod_residente', $alerta->cod_residente)->with('personal')->find($alerta->cod_registro);
            if ($administracion) {
                $nodos[] = $this->nodo('origen-operativo', 'Administración de medicación', $administracion->resultado, [
                    'Programada para' => $administracion->fecha_hora_programada?->format('d/m/Y H:i'),
                    'Administración documentada' => $administracion->fecha_hora_administracion?->format('d/m/Y H:i'),
                    'Motivo de omisión' => $administracion->motivo_omision, 'Efecto observado' => $administracion->efecto_observado,
                    'Reacción adversa registrada' => $administracion->reaccion_adversa,
                    'Observación' => $administracion->observacion, 'Profesional' => LenguajeAlerta::responsable($administracion->personal),
                ]);
                $aristas[] = $this->arista('alerta', 'origen-operativo', 'referencia esta administración');
            }
        }

        return ['nodos' => $nodos, 'aristas' => $aristas, 'disponible' => $nodos !== []];
    }

    private function nodo(string $id, ?string $label, ?string $texto, array $campos): array
    {
        return ['id' => $id, 'label' => $this->texto($label), 'texto' => $this->texto($texto), 'tono' => 'info', 'grupo' => 'origen',
            'detalles' => collect($campos)->map(fn ($valor, $etiqueta) => ['etiqueta' => $etiqueta, 'valor' => $this->texto($valor)])->values()->all()];
    }

    private function arista(string $desde, string $hasta, string $label): array
    {
        return compact('desde', 'hasta', 'label') + ['tipo' => 'relacion'];
    }

    private function texto(?string $valor): string
    {
        return filled($valor) ? (TextoVisible::sinCodigosInternos($valor) ?: 'Sin dato visible') : 'Sin registrar';
    }
}
