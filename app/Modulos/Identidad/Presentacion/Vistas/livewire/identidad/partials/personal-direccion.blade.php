<details @if(!filled($direccion)) open @endif class="rm-staff-registration__location" wire:ignore.self @direccion-personal-completada.window="$el.open = false; dirty = true; $nextTick(() => document.getElementById('direccion-encabezado')?.focus())">
    <summary id="direccion-encabezado"><i class="ph-bold ph-map-pin" aria-hidden="true"></i> Dirección de residencia</summary>
    <p class="rm-help">Las sugerencias locales son parciales. Elige «Otra» si no encuentras tu dirección y revisa el texto antes de guardar.</p>
    @include('livewire.identidad.partials.personal-direccion-mapa')
    <div class="rm-staff-registration__location-fields">
        <x-ui.field label="País de residencia" for="direccion-pais" error="pais_direccion" :valid="$camposValidados['pais_direccion'] ?? false" required>
            <x-ui.selector id="direccion-pais" label="País de residencia" wire:model.live="pais_direccion" :teleport="$destinoSelectoresPersonal" search-only aria-invalid="{{ $errors->has('pais_direccion') ? 'true' : 'false' }}" :aria-describedby="$errors->has('pais_direccion') ? 'direccion-pais-error' : null">
                <option value="">Selecciona un país</option>
                @foreach($this->todosPaises as $iso=>$pais)<option value="{{ $iso }}" data-search="{{ implode(' ', $pais['aliases'] ?? []) }}" data-flag="{{ asset('images/flags/'.strtolower($iso).'.svg') }}">{{ $pais['nombre'] }}</option>@endforeach
            </x-ui.selector>
        </x-ui.field>
        @foreach([
            ['campo'=>'ciudad_id', 'label'=>'Departamento o región', 'opciones'=>$this->getDepartamentosCatalogo(), 'contexto'=>$pais_direccion, 'padre'=>$pais_direccion, 'manual'=>'otro_departamento', 'otra'=>'Otro departamento o región'],
            ['campo'=>'municipio_id', 'label'=>'Municipio o ciudad', 'opciones'=>$this->getMunicipiosCatalogo($ciudad_id), 'contexto'=>implode('|', [$pais_direccion, $ciudad_id]), 'padre'=>$ciudad_id, 'manual'=>'otro_municipio', 'otra'=>'Otro municipio o ciudad'],
            ['campo'=>'zona_id', 'label'=>'Zona o barrio', 'opciones'=>$this->getZonasCatalogo($municipio_id), 'contexto'=>implode('|', [$pais_direccion, $ciudad_id, $municipio_id]), 'padre'=>$municipio_id, 'manual'=>'otra_zona', 'otra'=>'Otra zona o barrio'],
            ['campo'=>'calle_id', 'label'=>'Calle o avenida', 'opciones'=>$this->getCallesCatalogo($zona_id), 'contexto'=>implode('|', [$pais_direccion, $ciudad_id, $municipio_id, $zona_id]), 'padre'=>$zona_id, 'manual'=>'otra_calle', 'otra'=>'Otra calle o avenida'],
        ] as $nivel)
            <x-ui.field :label="$nivel['label']" :for="'direccion-'.$nivel['campo']" :error="$nivel['campo']" :valid="$camposValidados[$nivel['campo']] ?? false" required>
                <x-ui.selector :id="'direccion-'.$nivel['campo']" :label="$nivel['label']" :context="$nivel['contexto']" wire:model.live="{{ $nivel['campo'] }}" :teleport="$destinoSelectoresPersonal" :disabled="!filled($nivel['padre'])" aria-invalid="{{ $errors->has($nivel['campo']) ? 'true' : 'false' }}" :aria-describedby="$errors->has($nivel['campo']) ? 'direccion-'.$nivel['campo'].'-error' : null">
                    <option value="">Seleccionar</option>
                    @foreach($nivel['opciones'] as $opcion)<option value="{{ $opcion->id }}">{{ $opcion->nombre }}</option>@endforeach
                    <option value="OTRA">{{ $nivel['otra'] }}…</option>
                </x-ui.selector>
            </x-ui.field>
            @if($this->{$nivel['campo']} === 'OTRA')
                <x-ui.field :label="$nivel['otra']" :for="'direccion-'.$nivel['manual']" :error="$nivel['manual']" :valid="$camposValidados[$nivel['manual']] ?? false" required>
                    <input id="direccion-{{ $nivel['manual'] }}" class="rm-input" data-captura="texto" type="text" maxlength="{{ $nivel['manual'] === 'otra_calle' ? 80 : 60 }}" wire:model.live.blur="{{ $nivel['manual'] }}" placeholder="Escribe {{ mb_strtolower($nivel['label']) }}" aria-invalid="{{ $errors->has($nivel['manual']) ? 'true' : 'false' }}" @if($errors->has($nivel['manual'])) aria-describedby="direccion-{{ $nivel['manual'] }}-error" @endif>
                </x-ui.field>
            @endif
        @endforeach
        <x-ui.field label="Número de vivienda (opcional)" for="direccion-nro-casa" error="nro_casa" :valid="$camposValidados['nro_casa'] ?? false">
            <input id="direccion-nro-casa" class="rm-input" type="text" data-captura="texto" maxlength="15" wire:model.live.blur="nro_casa" placeholder="Ej. 120 o S/N" aria-invalid="{{ $errors->has('nro_casa') ? 'true' : 'false' }}" @if($errors->has('nro_casa')) aria-describedby="direccion-nro-casa-error" @endif>
        </x-ui.field>
    </div>
    <div class="rm-staff-registration__location-footer">
        <span class="rm-help">Solo se reemplaza la dirección al pulsar «Usar esta dirección».</span>
        <button type="button" class="rm-btn-primary" data-completar-direccion wire:click="completarDireccion" wire:loading.attr="disabled" @click="dirty = true"><i class="ph-bold ph-check" aria-hidden="true"></i> Usar esta dirección</button>
    </div>
</details>
