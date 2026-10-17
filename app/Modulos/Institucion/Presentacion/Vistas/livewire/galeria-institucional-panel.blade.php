<div class="rm-gallery space-y-4" x-data="{ editorDirty: false }" x-on:gallery-editor-reset.window="editorDirty = false">
    <x-ui.collection-header title="Galería del centro" subtitle="Fotografías de nuestros espacios y actividades." icon="ph-images">
        <x-slot:actions>
            @if($puedeGestionar)
                <button type="button" class="rm-btn-primary" wire:click="nuevo" x-on:click.capture="if (editorDirty &amp;&amp; !confirm('¿Descartar los cambios sin guardar?')) { $event.preventDefault(); $event.stopImmediatePropagation(); }" ><i class="ph-bold ph-upload-simple" aria-hidden="true"></i> Subir fotografías</button>
@endif
</x-slot:actions>
    </x-ui.collection-header>

    @if($mensaje)<div class="rm-alert rm-alert--info" role="status">{{ $mensaje }}</div>
@endif
@error('guardado')<div class="rm-alert rm-alert--danger" role="alert">{{ $message }}</div>@enderror
    @if($resultados)<ul class="rm-gallery-results" aria-label="Resultado de la carga">@foreach($resultados as $resultado)<li><strong>{{ $resultado['nombre'] }}</strong><span>{{ $resultado['estado'] }}</span></li>
@endforeach
</ul>
@endif
@if($formularioAbierto)
        <section class="rm-card rm-gallery-editor" aria-labelledby="gallery-editor-title">
            <h2 id="gallery-editor-title" class="rm-section-title">{{ $editando ? 'Editar datos de la fotografía' : 'Subir fotografías' }}</h2>
            <form wire:submit="guardar" x-data="{ errorCarga: '' }" x-on:input="editorDirty = true" x-on:change="editorDirty = true">
                @unless($editando)
                    <div><label for="gallery-files">Fotografías</label><input id="gallery-files" type="file" accept="image/jpeg,image/png,image/webp" multiple wire:model="archivos" aria-describedby="gallery-file-help" x-on:change.capture="const fotos = Array.from($event.target.files || []); errorCarga = fotos.length > 10 ? 'Selecciona hasta 10 fotografías.' : fotos.some(f => f.size > 2097152) ? 'Cada fotografía debe pesar como máximo 2 MB.' : fotos.reduce((n, f) => n + f.size, 0) > 6291456 ? 'El lote completo debe pesar como máximo 6 MB.' : ''; if (errorCarga) { $event.stopImmediatePropagation(); $event.target.value = ''; }"><p id="gallery-file-help">JPG, PNG o WebP · hasta 2 MB y 12 megapíxeles por foto · hasta 10 fotos y 6 MB por lote.</p><p x-show="errorCarga" x-text="errorCarga" role="alert" x-cloak></p>@error('archivos')<p role="alert">{{ $message }}</p>@enderror</div>
                    <p wire:loading wire:target="archivos" role="status">Preparando las fotografías…</p>
                    @if($archivos)<ul class="rm-gallery-previews">@foreach($archivos as $archivo)<li>@if(in_array($archivo->getMimeType(), ['image/jpeg', 'image/png', 'image/webp'], true))<img src="{{ $archivo->temporaryUrl() }}" alt="Vista previa de {{ $archivo->getClientOriginalName() }}">
@endif
<span>{{ $archivo->getClientOriginalName() }}</span></li>
@endforeach
</ul>
@endif

@endunless
<div class="rm-gallery-fields">
                    <div><label for="gallery-title">Título descriptivo</label><input id="gallery-title" type="text" wire:model="titulo" maxlength="160" required>@unless($editando)<p>Se aplica al lote. Después puedes editar cada fotografía.</p>
@endunless
@error('titulo')<p role="alert">{{ $message }}</p>@enderror</div>
                    <div><label for="gallery-date">Fecha de la fotografía (opcional)</label><input id="gallery-date" type="date" wire:model="fecha_fotografia">@error('fecha_fotografia')<p role="alert">{{ $message }}</p>@enderror</div>
                    <div class="rm-gallery-fields__wide"><label for="gallery-description">Descripción (opcional)</label><textarea id="gallery-description" wire:model="descripcion" maxlength="2000" rows="3"></textarea>@error('descripcion')<p role="alert">{{ $message }}</p>@enderror</div>
                    <div class="rm-gallery-fields__wide"><label for="gallery-alt">Descripción accesible (opcional)</label><input id="gallery-alt" type="text" wire:model="texto_alternativo" maxlength="255"><p>Si la dejas vacía se utiliza el título.</p>@error('texto_alternativo')<p role="alert">{{ $message }}</p>@enderror</div>
                </div>
                <label class="rm-gallery-checkbox"><input type="checkbox" wire:model="usar_en_bienvenida"><span>Ofrecer en la bienvenida de Gerencia y Administración</span></label>
                <div class="rm-gallery-actions"><button type="submit" class="rm-btn-primary" x-bind:disabled="errorCarga !== ''" wire:loading.attr="disabled" wire:target="guardar,archivos">{{ $editando ? 'Guardar cambios' : 'Guardar fotografías' }}</button><button type="button" class="rm-btn-secondary" wire:click="cerrar" x-on:click.capture="if (editorDirty &amp;&amp; !confirm('¿Descartar los cambios sin guardar?')) { $event.preventDefault(); $event.stopImmediatePropagation(); }" >Cancelar</button><span wire:loading wire:target="guardar" role="status">Guardando…</span></div>
            </form>
        </section>
@endif
<x-ui.filter-bar class="rm-gallery-filters">
        <div><label for="gallery-search">Buscar fotografías</label><input id="gallery-search" type="search" wire:model.live.debounce.300ms="busqueda" maxlength="160" placeholder="Título o descripción"></div>
        <div><label for="gallery-state">Mostrar</label><select id="gallery-state" wire:model.live="estado"><option value="ACTIVA">Activas</option><option value="ARCHIVADA">Archivadas</option></select></div>
    </x-ui.filter-bar>
    <p class="rm-body" aria-live="polite">{{ trans_choice($estado === 'ACTIVA' ? ':count fotografía activa|:count fotografías activas' : ':count fotografía archivada|:count fotografías archivadas', $fotografias->total()) }}</p>
    <ul class="rm-gallery-grid" aria-label="Fotografías del centro">
        @forelse($fotografias as $foto)
            <li wire:key="gallery-{{ $foto->cod_fotografia }}"><article class="rm-card rm-gallery-photo">
                @if($foto->estado === 'ACTIVA')<a href="{{ route('admin.galeria.archivo', [$foto->getKey(), 'imagen']) }}" target="_blank" rel="noopener" aria-label="Ver fotografía: {{ $foto->titulo }}"><img src="{{ route('admin.galeria.archivo', [$foto->getKey(), 'miniatura']) }}" alt="{{ $foto->texto_alternativo ?: $foto->titulo }}" loading="lazy" width="480" height="360"></a>@else<div class="rm-gallery-archived"><i class="ph-bold ph-archive" aria-hidden="true"></i><span>Fotografía archivada</span></div>
@endif
<div class="rm-gallery-photo__body"><h2>{{ $foto->titulo }}</h2>@if($foto->descripcion)<p>{{ $foto->descripcion }}</p>
@endif
<p class="rm-gallery-meta">{{ $foto->fecha_fotografia?->format('d/m/Y') ?? 'Fecha de fotografía sin registrar' }}</p>
                    @if($foto->usar_en_bienvenida && $foto->estado === 'ACTIVA')<span class="rm-badge">Disponible en bienvenida</span>
@endif
@if($puedeGestionar)<div class="rm-gallery-actions"><button type="button" class="rm-btn-secondary" wire:click="editar('{{ $foto->getKey() }}')" x-on:click.capture="if (editorDirty &amp;&amp; !confirm('¿Descartar los cambios sin guardar?')) { $event.preventDefault(); $event.stopImmediatePropagation(); }" >Editar datos</button><button type="button" class="rm-btn-secondary" wire:click="cambiarEstado('{{ $foto->getKey() }}', '{{ $foto->estado === 'ACTIVA' ? 'ARCHIVADA' : 'ACTIVA' }}')" wire:loading.attr="disabled">{{ $foto->estado === 'ACTIVA' ? 'Archivar' : 'Reactivar' }}</button></div>
@endif
</div>
            </article></li>
        @empty
            <li class="rm-gallery-empty"><x-ui.empty-state icono="ph-images" titulo="No hay fotografías para mostrar" texto="Prueba otra búsqueda o estado. Las nuevas fotografías aparecerán aquí cuando se guarden." /></li>
@endforelse
</ul>
    @if($fotografias->hasPages())<x-ui.paginacion :paginator="$fotografias" mode="livewire" :show-per-page="false" label="fotografías" scroll-to=".rm-gallery-filters" />
@endif
</div>
