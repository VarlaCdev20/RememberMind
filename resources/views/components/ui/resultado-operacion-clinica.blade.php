@props([
    'variant' => 'success',
    'resident',
    'dateTime' => null,
    'professional' => null,
    'measurements' => [],
    'title' => null,
    'message' => null,
    'alertCode' => null,
    'warnings' => [],
])

<section {{ $attributes->class(['rm-clinical-result']) }} data-variant="{{ $variant }}" role="{{ $variant === 'error' ? 'alert' : 'status' }}" aria-live="{{ $variant === 'error' ? 'assertive' : 'polite' }}">
    <span class="rm-clinical-result__icon" aria-hidden="true"><i class="ph-bold {{ $variant === 'error' ? 'ph-warning-circle' : 'ph-check' }}"></i></span>
    <h4>{{ $title ?? match($variant) { 'critical' => 'Medición registrada', 'error' => 'No se pudo registrar', default => 'Signos vitales registrados' } }}</h4>
    <p>{{ $message ?? ($variant === 'error' ? 'Los datos introducidos permanecen en el formulario para que puedas intentarlo nuevamente.' : 'La lectura fue incorporada al seguimiento clínico de '.\Illuminate\Support\Str::title(mb_strtolower($resident)).'.') }}</p>
    @if($dateTime || $professional)<span class="rm-clinical-result__date">{{ $dateTime }}@if($professional) · {{ $professional }}@endif</span>@endif
    @if($measurements !== [])
        <dl class="rm-clinical-result__measurements" aria-label="Mediciones registradas">
            @foreach($measurements as $measurement)
                <div><dt>{{ $measurement['nombre'] }}</dt><dd>{{ $measurement['valor'] }}</dd></div>
            @endforeach
        </dl>
    @endif

    @if($variant === 'critical' && $alertCode)
        <div class="rm-clinical-result__notice rm-clinical-result__notice--critical" role="alert">
            <strong><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Alerta crítica generada</strong>
            <p>La alerta quedó abierta y vinculada a esta medición. Requiere atención y seguimiento según el protocolo institucional.</p>
            <span>Estado: ABIERTA</span>
        </div>
    @elseif($variant === 'critical')
        <div class="rm-clinical-result__notice rm-clinical-result__notice--critical" role="alert">
            <strong><i class="ph-bold ph-warning-circle" aria-hidden="true"></i> Lectura crítica registrada</strong>
            <p>Requiere revisión y seguimiento según el protocolo institucional. Esta medición no generó una alerta automática.</p>
        </div>
    @elseif($warnings !== [])
        <div class="rm-clinical-result__notice rm-clinical-result__notice--warning">
            <strong><i class="ph-bold ph-warning" aria-hidden="true"></i> {{ count($warnings) }} {{ count($warnings) === 1 ? 'medición requiere' : 'mediciones requieren' }} revisión</strong>
            <ul>
                @foreach($warnings as $warning)
                    <li>{{ \Illuminate\Support\Str::ucfirst($warning['variable']) }} · {{ $warning['valor'] }} {{ $warning['unidad'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
