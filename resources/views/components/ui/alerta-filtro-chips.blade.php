@props(['label', 'campo', 'actual' => '', 'opciones' => []])
<fieldset class="rm-alert-chip-group"><legend>{{ $label }}</legend><div>
    @foreach($opciones as $valor => $etiqueta)
        <button type="button" class="rm-btn rm-btn-secondary" wire:click="setFiltroRapido(@js($campo), @js((string) $valor))" aria-pressed="{{ $actual === (string) $valor ? 'true' : 'false' }}">{{ $etiqueta }}</button>
    @endforeach
</div></fieldset>
