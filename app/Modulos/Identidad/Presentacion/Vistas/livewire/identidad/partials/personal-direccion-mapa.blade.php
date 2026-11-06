<div class="rm-address-map" x-data="rmPersonalAddressMap($wire, @js(config('services.direccion_personal.tiles_url')))"
     x-effect="cambiarContexto(JSON.stringify([$wire.pais_direccion, $wire.ciudad_id, $wire.municipio_id, $wire.otro_departamento, $wire.otro_municipio])); cambiarZona($wire.zona_id)">
    <div class="rm-address-map__intro">
        <div><strong>Elegir la dirección en el mapa</strong><p class="rm-help">Se centra en la zona, municipio y departamento elegidos. Si escribes otra zona, pulsa «Ubicar zona». OpenStreetMap recibe esos lugares y el punto consultado; no se envían datos de identidad.</p></div>
        <button id="personal-abrir-mapa" x-ref="abrir" type="button" class="rm-btn-secondary" @click="abrir()" :disabled="cargando || consultando" :aria-expanded="abierto" aria-controls="personal-panel-mapa"><i class="ph-bold ph-map-trifold" aria-hidden="true"></i><span x-text="cargando ? 'ABRIENDO…' : 'ELEGIR EN EL MAPA'"></span></button>
    </div>
    <div id="personal-panel-mapa" x-show="abierto" x-cloak>
        <div class="rm-address-map__heading"><p class="rm-help" x-text="contexto" role="status"></p><button type="button" class="rm-btn-secondary" @click="abrir()" :disabled="cargando || consultando">UBICAR ZONA</button><button type="button" class="rm-btn-secondary" @click="cerrar()">CERRAR MAPA</button></div>
        <p id="personal-ayuda-mapa" class="rm-help">Marca el lugar con un clic o mueve el mapa con las flechas y pulsa «Marcar el centro». Luego consulta la dirección. Revisa barrio, calle y número: la información puede ser aproximada.</p>
        <div wire:ignore><div x-ref="mapa" id="personal-mapa-direccion" class="rm-address-map__canvas" role="region" tabindex="0" aria-label="Mapa para elegir la dirección" aria-describedby="personal-ayuda-mapa"></div></div>
        <div class="rm-address-map__actions">
            <button type="button" class="rm-btn-secondary" @click="marcarCentro()" :disabled="consultando">MARCAR EL CENTRO</button>
            <button id="personal-consultar-punto" type="button" class="rm-btn-primary" @click="consultar()" :disabled="!punto || consultando"><span x-text="consultando ? 'CONSULTANDO…' : 'CONSULTAR DIRECCIÓN DEL PUNTO'"></span></button>
        </div>
        <div class="rm-address-map__result" x-show="propuesta" x-cloak>
            <label for="personal-direccion-mapa">DIRECCIÓN ENCONTRADA · REVISA Y CORRIGE ANTES DE USARLA</label>
            <textarea id="personal-direccion-mapa" x-ref="direccion" class="rm-input" rows="3" maxlength="255" x-model="propuesta" @input="propuesta = $event.target.value.toLocaleUpperCase('es')" aria-describedby="personal-referencia-mapa"></textarea>
            <p id="personal-referencia-mapa" class="rm-help" x-text="referencia"></p>
            <button id="personal-usar-punto" type="button" class="rm-btn-primary" @click="usar()" :disabled="consultando || !propuesta.trim()">USAR ESTA UBICACIÓN</button>
        </div>
    </div>
    <p x-show="mensaje" x-text="mensaje" class="rm-error" role="alert" x-cloak></p>
    @error('mapa_direccion')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
</div>
