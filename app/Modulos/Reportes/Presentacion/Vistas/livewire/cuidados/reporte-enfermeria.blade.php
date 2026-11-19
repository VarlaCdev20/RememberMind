<div class="rm-page-layout rm-nursing-report text-rm-primary">
    <x-ui.collection-header title="Reportes de Enfermería" subtitle="Elige una sección, revisa los registros y prepara tu reporte." icon="ph-files" eyebrow="Consulta y exportación">
        <x-slot:actions><a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn rm-btn-secondary" data-rm-action-icon aria-label="Volver a Mi turno"><i class="ph ph-arrow-left" aria-hidden="true"></i><span>Volver</span></a></x-slot:actions>
    </x-ui.collection-header>
    <div class="rm-nursing-report__content">
        <x-ui.filter-bar as="form" wire:submit="aplicar" class="rm-card rm-nursing-report__filters rm-nursing-filters" aria-label="Filtros del reporte">
            <div class="rm-nursing-filters__row">
                <div class="rm-field"><label for="reporte-desde" class="rm-label">Desde</label><input id="reporte-desde" wire:model="desde" type="date" class="rm-input" aria-invalid="{{ $errors->has('desde') ? 'true' : 'false' }}">@error('desde')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</div>
                <div class="rm-field"><label for="reporte-hasta" class="rm-label">Hasta</label><input id="reporte-hasta" wire:model="hasta" type="date" max="{{ today()->toDateString() }}" class="rm-input" aria-invalid="{{ $errors->has('hasta') ? 'true' : 'false' }}">@error('hasta')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</div>
                <div class="rm-field"><label for="reporte-residente" class="rm-label">Residente</label><select id="reporte-residente" wire:model="codResidente" class="rm-select"><option value="">Todos</option>@foreach($reporte['residentes'] as $persona)<option value="{{ $persona->getKey() }}">{{ $persona->nombre_completo }}</option>@endforeach</select></div>
                <div class="rm-field"><label for="reporte-tipo" class="rm-label">Sección</label><select id="reporte-tipo" wire:model="tipo" class="rm-select">@foreach(\App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria::TIPOS as $clave => $nombre)@if($clave === 'TODOS' || auth()->user()->can(\App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria::PERMISOS[$clave]))<option value="{{ $clave }}">{{ $clave === 'TODOS' ? 'General' : $nombre }}</option>@endif @endforeach</select>@error('tipo')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</div>
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled"><i class="ph ph-funnel" aria-hidden="true"></i><span wire:loading.remove wire:target="aplicar">Aplicar filtros</span><span wire:loading wire:target="aplicar">Consultando…</span></button>
            </div>
        </x-ui.filter-bar>
        <section class="rm-card rm-nursing-report__summary" aria-labelledby="resumen-reporte-titulo" wire:loading.class="opacity-60">
            <div><h2 id="resumen-reporte-titulo">{{ $aplicados['tipo'] === 'TODOS' ? 'Reporte general' : \App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria::TIPOS[$aplicados['tipo']] }}</h2><p>{{ $reporte['alcance'] }} · {{ \Carbon\Carbon::parse($aplicados['desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($aplicados['hasta'])->format('d/m/Y') }}</p></div>
            <div class="rm-nursing-report__metrics">
                <div><strong class="rm-nursing-report__count">{{ $reporte['filas']->count() }}</strong><span>Registros encontrados</span></div>
                <div><strong class="rm-nursing-report__count">{{ $reporte['totales']->filter(fn($t) => $t['cantidad'] > 0)->count() }}</strong><span>Secciones con registros</span></div>
                <div><strong class="rm-nursing-report__count">{{ $reporte['dias']->count() }}</strong><span>Días con registros</span></div>
            </div>
            <div class="rm-nursing-report__export" aria-label="Opciones del reporte">
                <a href="{{ route('admin.enfermeria.reportes.preview', $aplicados) }}" target="_blank" rel="noopener" class="rm-btn rm-btn-secondary" data-rm-action-icon aria-label="Vista previa (otra pestaña)"><i class="ph ph-eye" aria-hidden="true"></i></a>
                @can('enfermeria.ver_ficha_paciente')
                <x-ui.nursing-report-link :area="$aplicados['tipo']" :residente="$aplicados['codResidente']" :desde="$aplicados['desde']" :hasta="$aplicados['hasta']"
                    :alcance="$reporte['alcance']" identificador="reporte" />
                <a href="{{ route('admin.enfermeria.reportes.preview', array_merge($aplicados, ['imprimir' => 1])) }}" target="_blank" rel="noopener" class="rm-btn rm-btn-secondary" data-rm-action-icon aria-label="Imprimir (otra pestaña)"><i class="ph ph-printer" aria-hidden="true"></i></a>
                @endcan
            </div>
            <p class="text-sm text-rm-secondary">Se excluyen registros anulados. Los pendientes mantienen su estado. La cantidad de registros no indica gravedad.</p>
        </section>
        @if($reporte['residentes']->isEmpty())<div class="rm-card p-4" role="status">No tienes residentes disponibles para consulta. Revisa tus asignaciones con la persona responsable.</div>@endif
        @php
            $serie = $reporte['totales']->filter(fn ($total) => $total['cantidad'] > 0)
                ->map(fn ($total) => ['etiqueta' => $total['nombre'], 'valor' => $total['cantidad']])->values();
            $tipos = ['barras', 'dona', 'torta'];
            if ($serie->count() >= 3) $tipos[] = 'radar';
            $grafica = [
                'id' => 'registros-por-seccion', 'titulo' => 'Registros por sección', 'unidad' => 'registros',
                'serie' => $serie, 'total' => $serie->sum('valor'), 'maximo' => $serie->max('valor') ?? 0,
                'composicion' => true, 'tipos' => $tipos, 'recomendado' => $serie->count() > 1 ? 'dona' : 'barras',
                'nota' => 'Conteos del período aplicado. Cada registro se cuenta en su sección.',
            ];
        @endphp
        <section class="rm-card rm-nursing-report__breakdown" aria-labelledby="report-distribution">
            <div class="grid gap-3" wire:key="grafica-enfermeria-{{ md5(json_encode([$aplicados, $grafica])) }}"
                x-data="rmReportStatistics()" data-graficas-iniciales="{{ json_encode([$grafica]) }}">
                <div class="flex items-center justify-between gap-2">
                    <h2 id="report-distribution">Resumen por secciones</h2>
                    @include('reportes::paginas.gerencia.partials.selector-graficas', ['selectorGraficaCompacto' => true, 'idSelectorGrafica' => 'enfermeria-tipos-grafica'])
                </div>
                @include('reportes::paginas.gerencia.partials.visor-graficas', ['ocultarToolbarGraficas' => true])
            </div>
            <details><summary>Ver cantidad de registros por día</summary>@forelse($reporte['dias'] as $dia)<div class="rm-nursing-report__day"><time datetime="{{ $dia['fecha'] }}">{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</time><meter min="0" max="{{ $reporte['dias']->max('cantidad') }}" value="{{ $dia['cantidad'] }}" aria-label="{{ $dia['cantidad'] }} registros el {{ $dia['fecha'] }}"></meter><span>{{ $dia['cantidad'] }}</span></div>@empty<p>No hay registros en este período.</p>@endforelse</details>
        </section>
        <section class="rm-nursing-report__records grid gap-2" aria-labelledby="detalle-reporte-titulo">
            <h2 id="detalle-reporte-titulo">Detalle de los registros</h2>
            <p class="text-sm text-rm-secondary">El PDF y el CSV incluyen todas las páginas del período elegido.</p>
            <x-ui.table-surface>
                @include('reportes::reportes.enfermeria-registros', ['filas' => $registros, 'interactivo' => true])
                @if($registros->isNotEmpty())
                    <x-slot:footer>
                        <x-ui.paginacion class="rm-pagination--compact" :paginator="$registros" mode="livewire" :show-per-page="false" label="registros" />
                    </x-slot:footer>
                @endif
            </x-ui.table-surface>
        </section>
    </div>
</div>
