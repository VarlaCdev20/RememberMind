@php
    $ultimoCuidado = $ultAtencion ? \Carbon\Carbon::parse($ultAtencion->fecha)->format('d/m/Y') : 'Ninguna';
    $ultimaEvaluacion = $ultEval ? \Carbon\Carbon::parse($ultEval->fecha_eval)->format('d/m/Y') : 'Sin evaluar';
    $nombreContacto = $famPrincipal?->nombre_completo ?: 'Sin registrar';
@endphp
<x-ui.resident-card :resident="$adulto" :age="$adulto->edad" variant="inherit" :primary-href="route('admin.adultos-mayores.show', $adulto->cod_residente)" primary-label="Ficha integral" context-label="Última atención" :context-value="$ultimoCuidado">
    <x-slot:status><x-ui.status-badge :estado="$estado" /></x-slot:status>
    <x-slot:details>
        <p><i class="ph-bold ph-identification-card" aria-hidden="true"></i><span>{{ $adulto->ci ? 'CI '.$adulto->ci : 'CI no registrado' }}</span></p>
        <p><i class="ph-bold ph-user" aria-hidden="true"></i><span>Familiar responsable: {{ $nombreContacto }}</span></p>
        <p><i class="ph-bold ph-brain" aria-hidden="true"></i><span>Última evaluación cognitiva: {{ $ultimaEvaluacion }}</span></p>
    </x-slot:details>
    @can('residentes.gestionar')
        <x-slot:menu>
            <div class="rm-resident-compact-card__menu-heading">Acciones del residente</div>
            <button type="button" wire:click="editarAdultoMayor('{{ $adulto->cod_residente }}')" @click="open = false"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i></span>Editar ficha</button>
            <div class="rm-resident-compact-card__menu-divider" aria-hidden="true"></div>
            @foreach($estadosAdulto as $est)
                @if(strtoupper($est->estado) !== $estado)
                    <form method="POST" action="{{ route('admin.adultos-mayores.estado', $adulto->cod_residente) }}" onsubmit="confirmarAccion(event, '¿Cambiar estado a {{ $est->estado }}?', 'Se registrará en el historial de estados de forma automática.')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="cod_est_adul" value="{{ $est->cod_est_adul }}">
                        <button type="submit"><span class="rm-resident-compact-card__menu-icon"><i class="ph-bold ph-arrows-left-right" aria-hidden="true"></i></span>Cambiar a {{ $est->estado }}</button>
                    </form>
                @endif
            @endforeach
        </x-slot:menu>
    @endcan
</x-ui.resident-card>
