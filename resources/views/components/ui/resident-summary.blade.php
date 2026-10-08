@props(['name', 'metadata', 'context' => null, 'alertLabel' => null, 'hasAlerts' => false, 'id' => 'resident-summary'])
<div {{ $attributes->class(['rm-resident-summary']) }}>
    <header class="rm-resident-summary__identity">
        <h3>{{ \Illuminate\Support\Str::title(mb_strtolower($name)) }}</h3>
        <p>{{ $metadata }}</p>
        @if($context)<p>{{ $context }}</p>@endif
        @if($alertLabel)<span class="rm-resident-summary__alert {{ $hasAlerts ? 'has-alerts' : '' }}"><i class="ph-bold {{ $hasAlerts ? 'ph-warning-circle' : 'ph-check-circle' }}" aria-hidden="true"></i>{{ $alertLabel }}</span>@endif
    </header>
    @if(isset($current) && trim((string) $current) !== '')
        <section aria-labelledby="{{ $id }}-current-title"><h4 id="{{ $id }}-current-title" class="rm-resident-summary__section-title">Estado actual</h4>{{ $current }}</section>
    @endif
    @if(isset($important) && trim((string) $important) !== '')
        <section aria-labelledby="{{ $id }}-important-title"><h4 id="{{ $id }}-important-title" class="rm-resident-summary__section-title">Información importante</h4>{{ $important }}</section>
    @endif
    @if(isset($recent) && trim((string) $recent) !== '')
        <section aria-labelledby="{{ $id }}-recent-title"><h4 id="{{ $id }}-recent-title" class="rm-resident-summary__section-title">Seguimiento reciente</h4>{{ $recent }}</section>
    @endif
</div>
