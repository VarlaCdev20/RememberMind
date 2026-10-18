<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\Residente;
use Carbon\CarbonImmutable;

final class ResumenSeguimientoResidente
{
    /**
     * Recupera evaluaciones sucesivas de una ventana explícita tras reautorizar la ficha.
     * Respeta permisos de cada fuente y conserva valoraciones históricas legítimas.
     * Devuelve modelos al coordinador de Application; ninguna relación se entrega a JS.
     */
    public function evaluacionesDelPeriodo(Residente $residente, CarbonImmutable $desde, CarbonImmutable $hasta, bool $incluirHasta = true): array
    {
        abort_unless($desde->lt($hasta) && $hasta->lte(now()), 422, 'El período consultado no es válido.');
        app(PrepararFichaClinica::class)->preparar($residente);
        // Ambas puertas existentes consultan valoraciones_funcionales; la segunda
        // es la lectura institucional utilizada por Medicina y el panel de Barthel.
        $funcionalAutorizada = auth()->user()?->canAny(['valoraciones_funcionales.ver', 'valoracion_enfermeria.ver']) ?? false;
        $instrumentosAutorizados = auth()->user()?->can('aplicaciones_instrumento.ver') ?? false;
        $final = $incluirHasta ? '<=' : '<';

        return [
            'funcional_autorizada' => $funcionalAutorizada,
            'instrumentos_autorizados' => $instrumentosAutorizados,
            'funcionales' => $funcionalAutorizada ? $residente->valoracionesFuncionales()
                ->where('fecha_hora', '>=', $desde)->where('fecha_hora', $final, $hasta)
                ->whereIn('estado', ['ACTIVA', 'VIGENTE', 'HISTORICA'])
                ->whereHas('atencion', fn ($q) => $q->where('cod_residente', $residente->getKey())
                    ->whereNotIn('estado', ['ANULADA', 'ANULADO', 'CANCELADA', 'CANCELADO']))
                ->with('registradoPor')->orderByDesc('fecha_hora')->orderBy('cod_valoracion_funcional')->get() : collect(),
            'instrumentos' => $instrumentosAutorizados ? $residente->evaluacionesGeriatricas()
                ->where('fecha_hora', '>=', $desde)->where('fecha_hora', $final, $hasta)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'COMPLETA', 'VIGENTE'])
                ->where(fn ($q) => $q->whereNull('cod_atencion')->orWhereHas('atencion', fn ($a) => $a->where('cod_residente', $residente->getKey())
                    ->whereNotIn('estado', ['ANULADA', 'ANULADO', 'CANCELADA', 'CANCELADO'])))
                ->with(['instrumento', 'evaluador'])->orderByDesc('fecha_hora')->orderBy('cod_aplicacion')->get() : collect(),
        ];
    }

    public function consultar(Residente $residente): array
    {
        // 1. Ficha médica activa
        $fichaMedica = $residente->fichasMedicas()->whereNotIn('estado', ['ANULADA', 'CANCELADA'])->latest('fecha_hora')->first();
        // 2. Medicaciones activas (enum correcto: ACTIVO)
        $medicacionActiva = $residente->medicaciones()->where('estado', 'ACTIVA')->latest('fecha_hora_prescripcion')->get();
        // 3. Última administración (sin filtro estado: columna no existe en administracion_medicacion)
        $ultimaAdministracion = $residente->administracionesMedicacion()->latest('fecha_hora_programada')->first();
        // 4. Tomas no administradas recientes (columna boolean: administrado)
        $dosisOmitidas = $residente->administracionesMedicacion()->where('resultado', 'OMITIDA')->latest('fecha_hora_programada')->take(3)->get();
        // 5. Último registro de signos vitales (tabla no tiene columna estado)
        $ultimoSigno = $residente->signosVitales()->latest('fecha_hora')->first();
        // 6. Valoración funcional vigente (tabla sí tiene columna estado)
        $valoracionFuncional = $residente->valoracionesFuncionales()->whereIn('estado', ['ACTIVA', 'VIGENTE'])->latest('fecha_hora')->first();
        // 7. Observaciones recientes
        $observaciones = $residente->observaciones()->latest('fecha_hora')->take(3)->get();
        // 8. Atenciones recientes
        $atenciones = $residente->atenciones()->latest('fecha_hora')->take(3)->get();
        // 9. Evaluación cognitiva más reciente (columna canónica V2: fecha_hora)
        $evaluacionCognitiva = $residente->evaluacionesGeriatricas()->latest('fecha_hora')->first();
        // ── Alertas orientativas ─────────────────────────────────
        $alertas = collect();
        if (! $fichaMedica) {
            $alertas->push(['tipo' => 'Ficha Médica', 'nivel' => 'atencion', 'mensaje' => 'No se ha registrado una ficha médica activa. Seguimiento pendiente.']);
        }
        if ($medicacionActiva->isNotEmpty()) {
            $ultimaToma = $residente->administracionesMedicacion()->where('resultado', 'ADMINISTRADA')->latest('fecha_hora_administracion')->first();
            if (! $ultimaToma || $ultimaToma->fecha_hora_administracion?->diffInDays(now()) >= 1) {
                $alertas->push(['tipo' => 'Administración Pendiente', 'nivel' => 'atencion', 'mensaje' => 'Alerta orientativa: hay medicación activa sin registro de administración confirmada en las últimas 24 horas. Requiere revisión.']);
            }
        }
        if ($dosisOmitidas->isNotEmpty()) {
            $alertas->push(['tipo' => 'Tomas No Administradas', 'nivel' => 'requiere_revision', 'mensaje' => 'Se registran tomas sin administrar recientemente. Requiere revisión.']);
        }
        if ($valoracionFuncional) {
            if (in_array($valoracionFuncional->nivel_funcional, ['DEPENDENCIA_ALTA', 'SUPERVISION_PERMANENTE'])) {
                $alertas->push(['tipo' => 'Dependencia Elevada', 'nivel' => 'atencion', 'mensaje' => 'Se registra nivel de dependencia elevado en la valoración funcional. Seguimiento pendiente.']);
            }
        }
        if (! $ultimoSigno || $ultimoSigno->fecha_hora?->diffInDays(now()) > 7) {
            $alertas->push(['tipo' => 'Signos Vitales', 'nivel' => 'seguimiento', 'mensaje' => 'No se han registrado signos vitales en los últimos 7 días. Seguimiento pendiente.']);
        }
        if ($evaluacionCognitiva && $evaluacionCognitiva->fecha_hora?->diffInMonths(now()) >= 6) {
            $alertas->push(['tipo' => 'Evaluación Cognitiva', 'nivel' => 'seguimiento', 'mensaje' => 'Alerta orientativa: la última evaluación cognitiva tiene más de 6 meses. Seguimiento pendiente.']);
        }

        return ['fichaMedica' => $fichaMedica, 'medicacionActiva' => $medicacionActiva, 'ultimaAdministracion' => $ultimaAdministracion, 'dosisOmitidas' => $dosisOmitidas, 'ultimoSigno' => $ultimoSigno, 'valoracionFuncional' => $valoracionFuncional, 'alertas' => $alertas, 'observaciones' => $observaciones, 'atenciones' => $atenciones, 'evaluacionCognitiva' => $evaluacionCognitiva];
    }
}
