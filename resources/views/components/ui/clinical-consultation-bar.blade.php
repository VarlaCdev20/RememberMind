@props(['label' => 'Consultas del residente', 'meta' => null])
<div {{ $attributes->class(['rm-clinical-consultation-bar']) }}>
    @if(isset($meta))<div class="rm-clinical-consultation-bar__meta">{{ $meta }}</div>@endif
    <nav class="rm-clinical-consultation-bar__actions" aria-label="{{ $label }}">{{ $slot }}</nav>
</div>
