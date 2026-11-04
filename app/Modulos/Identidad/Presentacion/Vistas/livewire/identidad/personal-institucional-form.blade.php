<div class="rm-staff-workspace" x-data="{ dirty: false }" @input="dirty = true" @change="dirty = true" @gerencia-personal-cerrar.window="if (!dirty || confirm('¿Descartar los cambios sin guardar?')) { $dispatch('cerrar-modal-gestion-personal') }">
    <x-ui.collection-header :title="$esEdicion ? 'Editar trabajador' : 'Registrar trabajador'" subtitle="Identidad, información laboral, cuenta y documentación" icon="ph-identification-badge" />
    <nav class="rm-management-tabs" aria-label="Pasos del registro">
        @foreach([1=>'Identidad y contacto',2=>'Información laboral',3=>'Cuenta de acceso',4=>'Documentación y revisión'] as $numero=>$nombre)
            <button type="button" wire:click="gotoStep({{ $numero }})" @disabled($numero > $pasoActual) @if($pasoActual === $numero) aria-current="step" @endif class="{{ $pasoActual === $numero ? 'rm-btn-primary' : 'rm-btn-secondary' }}">{{ $numero }}. {{ $nombre }}</button>
        @endforeach
    </nav>
    <p class="rm-help mt-3" role="status">Paso {{ $pasoActual }} de 4. Se guarda todo al finalizar.</p>
    @if($errors->any())<x-ui.callout variant="warning" title="Revisa los datos indicados"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></x-ui.callout>@endif
    <form wire:submit="guardar" class="space-y-4 mt-4">
        @if($pasoActual === 1)
        <div class="grid gap-4 md:grid-cols-2">
            @foreach(['nombres'=>'Nombres','ap_paterno'=>'Apellido paterno','ap_materno'=>'Apellido materno (opcional)','numero_documento'=>'Número de documento','expedido'=>'Expedido en','telefono'=>'Celular'] as $campo=>$etiqueta)
                <x-ui.field :label="$etiqueta" :for="'trabajador-'.$campo"><input id="trabajador-{{ $campo }}" class="rm-input" type="text" wire:model="{{ $campo }}" @if($campo !== 'ap_materno') required @endif>@error($campo)<p role="alert">{{ $message }}</p>@enderror</x-ui.field>
            @endforeach
            <x-ui.field label="Fecha de nacimiento" for="trabajador-nacimiento"><input id="trabajador-nacimiento" class="rm-input" type="date" wire:model="fecha_nacimiento" required></x-ui.field>
            <x-ui.field label="Género" for="trabajador-genero"><select id="trabajador-genero" class="rm-input" wire:model="genero" required><option value="">Seleccionar</option><option value="F">Femenino</option><option value="M">Masculino</option></select></x-ui.field>
            <x-ui.field label="Dirección" for="trabajador-direccion"><textarea id="trabajador-direccion" class="rm-input" wire:model="direccion" maxlength="255" required></textarea></x-ui.field>
        </div>
        @elseif($pasoActual === 2)
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Función institucional y rol de acceso" for="trabajador-rol"><select id="trabajador-rol" class="rm-input" wire:model.live="rol_seleccionado" @disabled($esEdicion)><option value="">Seleccionar función</option>@foreach($roles as $rol)<option value="{{ $rol->name }}">{{ $rol->name }}</option>@endforeach</select><p class="rm-help">La edición de datos no cambia el rol de una cuenta existente.</p></x-ui.field>
            <x-ui.field label="Fecha de ingreso laboral (opcional)" for="trabajador-ingreso"><input id="trabajador-ingreso" class="rm-input" type="date" wire:model="fecha_ingreso"></x-ui.field>
            <x-ui.field label="Matrícula profesional (cuando corresponda)" for="trabajador-matricula"><input id="trabajador-matricula" class="rm-input" wire:model="matricula_prof" maxlength="50"></x-ui.field>
            <x-ui.field label="Observaciones laborales (opcional)" for="trabajador-observaciones"><textarea id="trabajador-observaciones" class="rm-input" wire:model="observaciones"></textarea></x-ui.field>
        </div>
        @elseif($pasoActual === 3)
        <div class="grid gap-4 md:grid-cols-2">
            <x-ui.field label="Correo de acceso" for="trabajador-correo"><input id="trabajador-correo" class="rm-input" type="email" wire:model="correo" autocomplete="off" maxlength="120" required></x-ui.field>
            @unless($esEdicion)<x-ui.field label="Contraseña temporal" for="trabajador-clave"><input id="trabajador-clave" class="rm-input" type="password" wire:model="contrasena_temporal" autocomplete="new-password" minlength="12" maxlength="72" required><p class="rm-help">Entrega el acceso por el canal institucional. No lo incluyas en observaciones.</p></x-ui.field>@endunless
        </div>
        <x-ui.callout variant="info" title="Cuenta y trabajo son distintos"><p>La nueva cuenta queda activa cuando el registro completo se confirma. El estado de documentos y la competencia profesional se revisan por separado.</p></x-ui.callout>
        @elseif($pasoActual === 4)
        <x-ui.section-card title="Comprobar los datos" icon="ph-checklist"><dl class="rm-management-context"><div><dt>Trabajador</dt><dd>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</dd></div><div><dt>Función y acceso</dt><dd>{{ $rol_seleccionado }} · {{ $correo }}</dd></div></dl></x-ui.section-card>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($this->documentosConfigurados as $documento)
                @if($documento['tipo'] === 'ingresante')
                <x-ui.field :label="$documento['nombre'].($documento['obligatorio_inmediato'] ? ' · requerido al alta' : ' · opcional')" :for="'documento-'.$documento['id']">
                    <input id="documento-{{ $documento['id'] }}" type="file" wire:model="archivos_temporales.{{ $documento['id'] }}" accept="application/pdf,image/jpeg,image/png,image/webp">
                    <p class="rm-help">{{ $documento['desc'] ?? '' }} PDF, JPG, PNG o WebP; hasta 5 MB. Cargar no equivale a aprobar.</p>
                    @if(isset($estado_documentos[$documento['id']]))<p>Estado registrado: {{ str_replace('_',' ', $estado_documentos[$documento['id']]) }}</p>@endif
                    @error('archivos_temporales.'.$documento['id'])<p role="alert">{{ $message }}</p>@enderror
                </x-ui.field>
                @endif
            @endforeach
        </div>
        <p class="rm-help" role="status" wire:loading wire:target="archivos_temporales">Preparando archivos…</p>
        @endif
        <div class="rm-management-actions">
            @if($pasoActual > 1)<button class="rm-btn-secondary" type="button" wire:click="retrocederPaso" wire:loading.attr="disabled">Atrás</button>@endif
            @if($pasoActual < 4)<button class="rm-btn-primary" type="button" wire:click="avanzarPaso" wire:loading.attr="disabled">Continuar</button>@else<button class="rm-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="guardar,archivos_temporales">{{ $esEdicion ? 'Guardar cambios' : 'Registrar trabajador' }}</button>@endif
            <button class="rm-btn-secondary" type="button" @click="if (!dirty || confirm('¿Descartar los cambios sin guardar?')) { $dispatch('cerrar-modal-gestion-personal') }">Cancelar</button>
            <span class="rm-help" wire:loading wire:target="guardar" role="status">Guardando el registro…</span>
        </div>
    </form>
</div>
