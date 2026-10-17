@props(['title', 'subtitle', 'turno' => null, 'modoConsulta' => false])

<x-ui.collection-header
    class="rm-resident-directory__header"
    :title="$title"
    :subtitle="$subtitle"
    icon="ph-users-three"
    :date="now()"
>
    @if($modoConsulta || $turno)
        <x-slot:context>
            <span class="rm-resident-directory__shift-status">
                <i class="ph-bold {{ $modoConsulta ? 'ph-eye' : 'ph-clock' }}" aria-hidden="true"></i>
                {{ $modoConsulta ? 'MODO CONSULTA / SOLO LECTURA' : 'Jornada activa' }}
            </span>
            @if($turno && ! $modoConsulta)
                <span class="rm-resident-directory__shift-name">{{ $turno->nombre ?: 'Turno en curso' }}</span>
            @endif
        </x-slot:context>
    @endif
    @if(isset($actions))
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endif
</x-ui.collection-header>
