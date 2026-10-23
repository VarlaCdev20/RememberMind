@php
    $presentacion = match ($seccion) {
        'agenda' => ['titulo' => 'Agenda y sesiones', 'subtitulo' => 'Atenciones próximas registradas a tu nombre.', 'icono' => 'ph-calendar-check', 'vacio' => 'No hay sesiones próximas registradas.'],
        'seguimiento' => ['titulo' => 'Seguimiento psicológico', 'subtitulo' => 'Atenciones psicológicas documentadas.', 'icono' => 'ph-heartbeat', 'vacio' => 'No hay atenciones psicológicas documentadas.'],
        'informes' => ['titulo' => 'Informes psicológicos', 'subtitulo' => 'Conclusiones de valoraciones registradas. El detalle está en la ficha del residente.', 'icono' => 'ph-file-text', 'vacio' => 'No hay conclusiones de valoraciones registradas.'],
    };
@endphp

<div class="space-y-6 text-rm-primary">
    <x-ui.residents-page-header :title="$presentacion['titulo']" :subtitle="$presentacion['subtitulo']">
        <x-slot:actions>
            <a href="{{ route('admin.psicologia.evaluaciones') }}" class="rm-btn rm-btn-secondary">
                <i class="ph-bold ph-users-three" aria-hidden="true"></i><span>Mis residentes</span>
            </a>
        </x-slot:actions>
    </x-ui.residents-page-header>

    <x-ui.filter-bar as="div" aria-label="Buscar registros psicológicos">
        <div class="rm-resident-directory__filter-search">
            <label for="actividad-psicologia-buscar">Buscar residente por nombre o apellido</label>
            <div class="rm-resident-directory__filter-search-control">
                <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
                <input id="actividad-psicologia-buscar" type="search" wire:model.live.debounce.300ms="buscar" placeholder="Escribe un nombre o apellido" autocomplete="off">
            </div>
        </div>
    </x-ui.filter-bar>

    <section class="rm-card rm-surface--default" aria-labelledby="actividad-psicologia-titulo">
        <div class="rm-card-content space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="actividad-psicologia-titulo" class="text-lg font-semibold">{{ $presentacion['titulo'] }}</h2>
                <span class="text-sm text-rm-secondary" aria-live="polite">{{ $registros->total() }} {{ $registros->total() === 1 ? 'registro' : 'registros' }}</span>
            </div>

            <div wire:loading wire:target="buscar" role="status" class="text-sm text-rm-secondary">Actualizando registros…</div>

            @if($registros->isEmpty())
                <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-6 text-center">
                    <i class="ph {{ $presentacion['icono'] }} text-2xl" aria-hidden="true"></i>
                    <p class="mt-2 font-medium">{{ $buscar !== '' ? 'No encontramos registros con esa búsqueda.' : $presentacion['vacio'] }}</p>
                    @if($buscar !== '')
                        <button type="button" wire:click="$set('buscar', '')" class="rm-btn rm-btn-secondary mt-3">Limpiar búsqueda</button>
                    @endif
                </div>
            @else
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3" wire:loading.remove wire:target="buscar">
                    @foreach($registros as $registro)
                        @php
                            $nombre = trim($registro->nombres.' '.$registro->apellido_paterno.' '.($registro->apellido_materno ?? ''));
                            $fecha = \Illuminate\Support\Carbon::parse($registro->fecha_hora);
                        @endphp
                        <article wire:key="actividad-{{ $seccion }}-{{ $registro->clave_registro }}" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 space-y-3 min-w-0">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-semibold break-words">{{ $nombre }}</h3>
                                <i class="ph {{ $presentacion['icono'] }} text-xl text-rm-secondary shrink-0" aria-hidden="true"></i>
                            </div>
                            <p class="text-sm text-rm-secondary"><time datetime="{{ $fecha->toIso8601String() }}">{{ $fecha->translatedFormat('d M Y · H:i') }}</time></p>
                            @if($seccion === 'informes')
                                <p class="text-sm"><strong>Conclusión:</strong> {{ \Illuminate\Support\Str::limit($registro->conclusion ?: 'Sin conclusión textual registrada.', 180) }}</p>
                            @else
                                <p class="text-sm"><strong>{{ $seccion === 'agenda' ? 'Motivo' : 'Atención' }}:</strong> {{ $registro->motivo ?: str_replace('_', ' ', (string) $registro->tipo_atencion) }}</p>
                                <span class="text-xs text-rm-secondary">{{ ucfirst(mb_strtolower(str_replace('_', ' ', (string) $registro->estado))) }}</span>
                            @endif
                            <a class="rm-btn rm-btn-secondary w-full justify-center" href="{{ route('admin.psicologia.residente.ficha', ['residente' => $registro->cod_residente, 'tab' => $seccion === 'informes' ? 'informes' : 'seguimiento']) }}">
                                Abrir ficha <i class="ph ph-arrow-right" aria-hidden="true"></i>
                            </a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($registros->hasPages())
        <nav aria-label="Páginas de registros psicológicos">{{ $registros->links() }}</nav>
    @endif
</div>
