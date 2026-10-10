<div class="rm-residents-actions"><a wire:navigate href="{{ $enlace(['residente' => $registro->codigo]) }}" class="rm-btn-secondary"><i class="ph-bold ph-identification-card" aria-hidden="true"></i> Detalle</a>
    @can('ocupaciones_cama.gestionar')
        @if(in_array($registro->estado, ['ADMITIDO', 'ACTIVO'], true))<button type="button" class="rm-btn-icon" @click="$dispatch('abrir-alojamiento-residente', {codResidente: @js($registro->codigo)})" aria-label="Gestionar alojamiento de {{ $registro->titulo }}" title="Gestionar alojamiento"><i class="ph-bold ph-bed" aria-hidden="true"></i></button>@endif
    @endcan
</div>
