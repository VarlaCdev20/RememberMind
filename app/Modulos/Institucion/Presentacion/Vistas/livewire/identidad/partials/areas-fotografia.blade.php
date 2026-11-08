<x-ui.form-section title="Imagen del área" icon="ph-image" :columns="1">
    <div class="rm-area-photo">
        <div class="rm-area-photo__preview">
            @if($archivo_fotografia && !$errors->has('archivo_fotografia'))
                <img src="{{ $archivo_fotografia->temporaryUrl() }}" alt="Vista previa de la fotografía seleccionada">
                <span class="rm-area-photo__caption">Nueva fotografía · sin guardar</span>
            @elseif($fotoArea && $fotoArea->estado === 'ACTIVA')
                <img src="{{ route('admin.galeria.archivo', [$fotoArea->getKey(), 'miniatura']) }}" alt="{{ $fotoArea->texto_alternativo ?: $fotoArea->titulo }}">
            @else
                <i class="ph-bold ph-image" aria-hidden="true"></i>
                <span>{{ $fotoArea ? 'Fotografía archivada' : 'Sin fotografía' }}</span>
            @endif
        </div>
        <div class="rm-area-photo__controls">
            <p class="rm-label">Fotografía principal <span class="rm-help">(opcional)</span></p>
            <p class="rm-help">{{ $archivo_fotografia ? 'La imagen se guardará en Galería al confirmar el área.' : ($fotoArea ? $fotoArea->titulo : 'Elige una fotografía del espacio o del equipo.') }}</p>
            <div class="rm-area-photo__actions">
                @if($puedeVerFotos)
                    <x-ui.action-button variant="secondary" icono="ph-images" wire:click="abrirGaleriaArea" :aria-expanded="$selectorFotografiaAbierto ? 'true' : 'false'" aria-controls="area-galeria-selector" loading="guardarArea">Elegir de Galería</x-ui.action-button>
                @endif
                @if($puedeSubirFoto)
                    <x-ui.action-button variant="secondary" icono="ph-upload-simple" @click="$refs.archivoArea.click()" x-bind:disabled="subiendoFoto" loading="guardarArea">Subir fotografía</x-ui.action-button>
                    <input id="area-registro-archivo" x-ref="archivoArea" type="file" wire:model="archivo_fotografia" accept="image/jpeg,image/png,image/webp" class="hidden" aria-label="Seleccionar fotografía del área" aria-describedby="area-registro-archivo-help">
                @endif
                @if($cod_fotografia || $archivo_fotografia)
                    <x-ui.action-button variant="ghost" icono="ph-x" wire:click="quitarFotografia" loading="guardarArea">Quitar imagen</x-ui.action-button>
                @endif
            </div>
            <p id="area-registro-archivo-help" class="rm-help">JPG, PNG o WebP · hasta 2 MB y 12 megapíxeles.</p>
            <p x-show="subiendoFoto" x-cloak class="rm-help" role="status">Subiendo imagen…</p>
            @unless($puedeVerFotos)<p class="rm-help">La selección de imágenes requiere acceso autorizado a Galería.</p>@endunless
            @error('archivo_fotografia')<p class="rm-error" role="alert"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ $message }}</p>@enderror
            @error('cod_fotografia')<p class="rm-error" role="alert"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ $message }}</p>@enderror
        </div>
    </div>
    @if($archivo_fotografia)
        <x-ui.field label="Descripción de la imagen" for="area-registro-texto-alternativo" error="texto_alternativo" hint="Opcional" help="Describe brevemente lo que se ve, para personas que usan lectores de pantalla.">
            <input id="area-registro-texto-alternativo" class="rm-input" type="text" wire:model="texto_alternativo" maxlength="255" placeholder="EJ. JARDÍN DEL ÁREA CON BANCOS Y SOMBRA"
                aria-invalid="{{ $errors->has('texto_alternativo') ? 'true' : 'false' }}" aria-describedby="area-registro-texto-alternativo-help{{ $errors->has('texto_alternativo') ? ' area-registro-texto-alternativo-error' : '' }}">
        </x-ui.field>
    @endif
    @if($selectorFotografiaAbierto && $fotosGaleriaArea)
        <section id="area-galeria-selector" class="rm-area-photo__gallery" aria-label="Fotografías de Galería">
            <x-ui.field label="Buscar fotografía" for="area-registro-buscar-foto">
                <input id="area-registro-buscar-foto" type="search" class="rm-input" wire:model.live.debounce.300ms="busquedaFotografia" placeholder="TÍTULO DE LA FOTOGRAFÍA">
            </x-ui.field>
            <div class="rm-area-photo__options">
                @forelse($fotosGaleriaArea as $foto)
                    <button type="button" class="rm-area-photo__option" wire:key="area-foto-{{ $foto->getKey() }}" wire:click="seleccionarFotografia('{{ $foto->getKey() }}')" aria-label="Elegir {{ $foto->titulo }}" aria-pressed="{{ $cod_fotografia === $foto->getKey() ? 'true' : 'false' }}" wire:loading.attr="disabled" wire:target="seleccionarFotografia">
                        <img src="{{ route('admin.galeria.archivo', [$foto->getKey(), 'miniatura']) }}" alt="" loading="lazy">
                        <span>{{ $foto->titulo }}</span>
                    </button>
                @empty
                    <p class="rm-help">{{ trim($busquedaFotografia) !== '' ? 'No hay fotografías con ese título.' : 'Todavía no hay fotografías activas en Galería.' }}</p>
                @endforelse
            </div>
            @if($fotosGaleriaArea->hasPages())
                <x-ui.paginacion :paginator="$fotosGaleriaArea" mode="livewire" label="fotografías" :show-per-page="false" class="rm-pagination--compact" />
            @endif
        </section>
    @endif
</x-ui.form-section>
