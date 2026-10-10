<div class="rm-dashboard-composition space-y-6 pb-8">
    <x-ui.role-dashboard-hero
        role="Psicología"
        :date="now()->locale('es')->translatedFormat('D d M Y')"
        scope="Atenciones y valoraciones propias"
        eyebrow="CENTRO GERIÁTRICO LOS ALMENDROS"
        title="Comprender hoy"
        highlight="es acompañar mejor mañana"
        description="Consulta tus atenciones, instrumentos aplicados y alertas pendientes para acompañar el bienestar de cada residente."
        :image="asset('images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg')"
        rotation-context="psicologia"
        image-alt="Profesional acompañando a un residente durante una actividad cognitiva"
        quote="Cada recuerdo merece tiempo y presencia"
        :meta="[['icon' => 'ph-calendar-blank', 'label' => now()->locale('es')->translatedFormat('d M Y')], ['icon' => 'ph-identification-badge', 'label' => 'Psicología']]"
    >
        <button type="button" wire:click="$refresh" class="rm-btn-primary min-h-9 px-3.5 text-xs">
            <i class="ph-bold ph-arrows-clockwise" aria-hidden="true"></i><span>Actualizar panel</span>
        </button>
    </x-ui.role-dashboard-hero>

    <x-ui.dashboard-divider />

    @if($metrics)
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores de psicología">
            @foreach($metrics as $metric)
                <x-ui.metric-card :icon="$metric['icon']" :value="$metric['value']" :label="$metric['label']" :description="$metric['description']" :variant="$metric['variant']" />
            @endforeach
        </section>
    @endif

    <div class="rm-dashboard-data-grid" aria-label="Seguimiento de psicología">
        @foreach($panels as $panel)
            <x-ui.dashboard-data-panel :panel="$panel" />
        @endforeach
    </div>

    <section class="rm-psychology-areas" aria-labelledby="psychology-areas-title">
        <h2 id="psychology-areas-title">Áreas de evaluación</h2>
        <div class="rm-psychology-areas__links">
            @foreach([
                ['route' => 'admin.psicologia.evaluacion.cognitiva', 'label' => 'Cognitivo', 'icon' => 'ph-brain'],
                ['route' => 'admin.psicologia.evaluacion.afectiva', 'label' => 'Afectivo', 'icon' => 'ph-heart'],
                ['route' => 'admin.psicologia.evaluacion.funcionamiento', 'label' => 'Funcionamiento', 'icon' => 'ph-person-simple-walk'],
                ['route' => 'admin.psicologia.evaluacion.nutricional', 'label' => 'Nutricional', 'icon' => 'ph-apple-logo'],
                ['route' => 'admin.psicologia.evaluacion.entorno', 'label' => 'Entorno', 'icon' => 'ph-users-four'],
            ] as $area)
                <a href="{{ route($area['route']) }}" wire:navigate><i class="ph-bold {{ $area['icon'] }}" aria-hidden="true"></i><span>{{ $area['label'] }}</span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>
            @endforeach
        </div>
    </section>
</div>
