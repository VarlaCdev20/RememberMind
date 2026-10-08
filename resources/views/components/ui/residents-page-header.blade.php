@props(['title', 'subtitle', 'turno' => null, 'modoConsulta' => false])

<x-ui.collection-header
    class="rm-resident-directory__header"
    :title="$title"
    :subtitle="$subtitle"
    icon="ph-users-three"
    :context="$modoConsulta ? 'MODO CONSULTA / SOLO LECTURA' : ($turno ? 'Jornada activa · '.($turno->nombre ?: 'Turno en curso') : null)"
    :date="now()"
>
    @if(isset($actions))
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endif
</x-ui.collection-header>
