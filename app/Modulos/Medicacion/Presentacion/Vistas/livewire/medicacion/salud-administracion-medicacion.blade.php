<div class="rm-medication-workspace">
    <x-ui.collection-header title="MEDICACIÓN" subtitle="Administración y seguimiento del turno, con sus horarios y registros." icon="ph-pill" :date="now()">
        <x-slot:context>
            <i class="ph-bold {{ $esModoConsulta ? 'ph-eye' : 'ph-clock' }}" aria-hidden="true"></i>
            {{ $esModoConsulta ? 'Solo consulta' : 'Jornada activa' }}
        </x-slot:context>
        <x-slot:actions><span class="rm-medication-workspace__shift">{{ $turnoNombre }}</span></x-slot:actions>
    </x-ui.collection-header>

    @if(session()->has('mensaje_exito') || session()->has('mensaje'))
        <div class="rm-medication-workspace__notice" role="status">
            <i class="ph-bold ph-check-circle" aria-hidden="true"></i>
            <p>{{ session('mensaje_exito') ?? session('mensaje') }}</p>
        </div>
    @endif
    @if($residentesConAlergias > 0 || $conteoRetrasadas > 0)
        <section class="rm-medication-workspace__notice rm-medication-workspace__notice--attention" aria-label="Información para revisar">
            <i class="ph-bold ph-warning-circle" aria-hidden="true"></i>
            <div>
                @if($residentesConAlergias > 0)<p><strong>Alergia relevante: {{ $residentesConAlergias }} registrada{{ $residentesConAlergias === 1 ? '' : 's' }}.</strong> Revisa las alergias del residente antes de registrar.</p>@endif
                @if($conteoRetrasadas > 0)<p><strong>{{ $conteoRetrasadas }} dosis retrasada{{ $conteoRetrasadas === 1 ? '' : 's' }}.</strong> Consulta el detalle de las dosis en la agenda.</p>@endif
            </div>
        </section>
    @endif

    <nav class="rm-medication-workspace__navigation" aria-label="Vistas de medicación">
        @foreach([
            ['id' => 'kardex', 'label' => 'Kardex', 'icon' => 'ph-calendar-check'],
            ['id' => 'proximas', 'label' => 'Próximas dosis', 'icon' => 'ph-hourglass-medium'],
            ['id' => 'omisiones', 'label' => 'Omisiones', 'icon' => 'ph-pause-circle'],
            ['id' => 'historial', 'label' => 'Historial', 'icon' => 'ph-clock-counter-clockwise'],
        ] as $tab)
            <button type="button" wire:click="cambiarTab('{{ $tab['id'] }}')" @if($tabActivo === $tab['id']) aria-current="page" @endif class="rm-medication-workspace__tab">
                <i class="ph-bold {{ $tab['icon'] }}" aria-hidden="true"></i><span>{{ $tab['label'] }}</span>
            </button>
        @endforeach
    </nav>

    <div class="rm-medication-workspace__content" wire:loading.class="is-loading" wire:target="cambiarTab,filtroKardexBusqueda,filtroKardexEstado,filtroKardexResidente,filtroKardexHorario,filtroKardexVia,filtroKardexPrn,filtroKardexFecha,filtroHistorialBusqueda,filtroHistorialResultado,filtroHistorialResidente,filtroHistorialVia,filtroHistorialMedicamento,filtroHistorialFechaDesde,filtroHistorialFechaHasta,resetFilters,limpiarFiltro">
        <span class="sr-only" role="status" wire:loading wire:target="cambiarTab,resetFilters,limpiarFiltro">Actualizando medicación…</span>
        @if($tabActivo === 'kardex')
            @include('livewire.medicacion.partials.kardex-matriz')
        @elseif($tabActivo === 'proximas')
            @include('livewire.medicacion.partials.proximas-dosis')
        @elseif($tabActivo === 'omisiones')
            @include('livewire.medicacion.partials.omisiones')
        @elseif($tabActivo === 'historial')
            @include('livewire.medicacion.partials.historial')
        @endif
    </div>

    @include('livewire.medicacion.partials.drawer-dosis')
    @include('livewire.medicacion.partials.modal-administrar')
    @include('livewire.medicacion.partials.modal-omision')
    @include('livewire.medicacion.partials.modal-medicamento')
</div>
