{{-- TAB: SIGNOS VITALES — GOLDEN REFERENCE CON RANGOS, ACCIONES Y VALORES NUMÉRICOS --}}
@php
    $grafica = $this->resumenLongitudinal['grafica_signos'] ?? [];
    $ultimo = $grafica['ultimo'] ?? $adultoMayor->signosVitales->first();
    $metricasInfo = $grafica['metricas_info'] ?? [];
    $resumenPeriodo = $grafica['resumen_periodo'] ?? ($metricasInfo['PA'] ?? []);
    $eventosRelevantes = $grafica['eventos_relevantes'] ?? collect();
    $labels = $grafica['labels'] ?? [];
    $labelsLargas = $grafica['labels_largas'] ?? $labels;
    $sistolica = $grafica['sistolica'] ?? [];
    $diastolica = $grafica['diastolica'] ?? [];
    $fc = $grafica['fc'] ?? [];
    $spo2 = $grafica['spo2'] ?? [];
    $temp = $grafica['temp'] ?? [];
    $fr = $grafica['fr'] ?? [];
    $dolor = $grafica['dolor'] ?? [];
    $glucosa = $grafica['glucosa'] ?? [];
    $peso = $grafica['peso'] ?? [];
    $totalRegistros = $adultoMayor->signosVitales->count();
    $metricaActiva = $this->metricaSignosSeleccionada ?? 'PA';
    $periodoActivo = $this->periodoSignos ?? '7d';

    $registrosHistoricos = collect($grafica['registros_historicos'] ?? []);

    $configJson = json_encode([
        'metricaActiva' => $metricaActiva,
        'periodoActivo' => $periodoActivo,
        'labels' => $labels,
        'labelsLargas' => $labelsLargas,
        'sistolica' => $sistolica,
        'diastolica' => $diastolica,
        'fc' => $fc,
        'spo2' => $spo2,
        'temp' => $temp,
        'fr' => $fr,
        'dolor' => $dolor,
        'glucosa' => $glucosa,
        'peso' => $peso,
        'metricasInfo' => $metricasInfo,
        'resumenPeriodo' => $resumenPeriodo,
        'registros' => $registrosHistoricos->values()->all(),
        'interpretaciones' => $grafica['interpretaciones'] ?? [],
    ]);
@endphp

<div x-data="rmSignosVitalesModule({{ $configJson }})"
     class="space-y-4">

    {{-- ========================================================================= --}}
    @include('clinica::livewire.cuidados.ficha.signos.cabecera')
    @include('clinica::livewire.cuidados.ficha.signos.grafica')
    @include('clinica::livewire.cuidados.ficha.signos.metricas')
    @include('clinica::livewire.cuidados.ficha.signos.historial')
    @include('clinica::livewire.cuidados.ficha.signos.rangos')
    @include('clinica::livewire.cuidados.ficha.signos.detalle')
    {{-- 7. TOAST FEEDBACK VISUAL                                                  --}}
    {{-- ========================================================================= --}}
    <div x-show="toastMensaje"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 rounded-xl bg-[var(--rm-surface-soft)] text-[var(--rm-text-on-primary)] px-4 py-2.5 shadow-2xl border border-[var(--rm-border)] text-xs font-semibold"
         style="display: none;">
        <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)] text-base"></i>
        <span x-text="toastMensaje"></span>
    </div>

</div>

{{-- ========================================================================= --}}
