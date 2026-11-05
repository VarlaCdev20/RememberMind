@php($destinoSelectoresPersonal = $esEdicion && auth()->user()->hasRole('SUPERADMINISTRADOR') ? '#gerencia-personal-editor-superadmin' : '#gerencia-personal-ficha')
<div class="rm-staff-workspace rm-staff-registration" x-data="{ dirty: false, normalizar(event) { const campo = event.target; const tipo = campo.dataset.captura; if (!tipo || !campo.value) return; const cursor = campo.selectionStart; const original = campo.value; const filtrar = valor => tipo === 'numero' ? valor.replace(/[^0-9]/g, '') : tipo === 'documento' ? valor.toLocaleUpperCase('es').replace(/[^A-Z0-9]/g, '') : tipo === 'run' ? valor.toLocaleUpperCase('es').replace(/[^0-9K-]/g, '') : tipo === 'letras' ? valor.replace(/[^\p{L}\p{M}\s'’\-]/gu, '').toLocaleUpperCase('es') : valor.toLocaleUpperCase('es'); const valor = filtrar(original); if (valor !== original) { campo.value = valor; if (cursor !== null) { const posicion = filtrar(original.slice(0, cursor)).length; campo.setSelectionRange(posicion, posicion); } } } }" @input.capture="normalizar($event)" @focusin="if ($event.target.matches('input:not([hidden]), textarea, .rm-choice__trigger') && !$event.target.closest('.rm-control-popover')) $event.target.scrollIntoView({ block: 'nearest', inline: 'nearest' })" @personal-validacion-fallida.window="$nextTick(() => $el.querySelector('[aria-invalid=true]:not([hidden])')?.focus())" @input="dirty = true" @change="dirty = true" @gerencia-personal-cerrar.window="if (!dirty || confirm('¿Descartar los cambios sin guardar?')) { $dispatch('cerrar-modal-gestion-personal') }">
    @if($esEdicion && (!auth()->user()->hasRole('GERENTE') || auth()->user()->hasRole('SUPERADMINISTRADOR')))
        <x-ui.collection-header :title="$esEdicion ? 'Editar trabajador' : 'Registrar trabajador'" subtitle="Identidad, información laboral, cuenta y documentación" icon="ph-identification-badge" />
    @endif
    <nav class="rm-staff-registration__steps" aria-label="Pasos del registro">
        @foreach([1=>['Identidad y contacto','ph-user-circle'],2=>['Información laboral','ph-briefcase'],3=>['Cuenta de acceso','ph-shield-check'],4=>['Documentación y revisión','ph-check-square']] as $numero=>[$nombre,$icono])
            <button type="button" wire:click="gotoStep({{ $numero }})" @disabled($numero > $pasoActual) @if($pasoActual === $numero) aria-current="step" @endif @class(['rm-staff-registration__step','is-complete'=>$numero < $pasoActual])>
                <span class="rm-staff-registration__step-icon"><i class="ph-bold {{ $numero < $pasoActual ? 'ph-check' : $icono }}" aria-hidden="true"></i></span><span class="rm-staff-registration__step-label">{{ $numero }}. {{ $nombre }}</span><span class="rm-staff-registration__step-short" aria-hidden="true">{{ [1=>'Identidad',2=>'Laboral',3=>'Acceso',4=>'Revisión'][$numero] }}</span>
            </button>
        @endforeach
    </nav>
    <form wire:submit="guardar" class="rm-staff-registration__form space-y-4 mt-4" novalidate>
        @if($pasoActual === 1)
        <x-ui.form-section title="Identidad y contacto" description="Datos personales y medios de contacto del trabajador." icon="ph-user-circle" :columns="1">
            <div class="rm-staff-registration__identity-layout">
                <aside class="rm-staff-registration__portrait">
                    <div class="rm-staff-registration__photo">
                        @if($foto_perfil && !is_string($foto_perfil) && !$errors->has('foto_perfil'))
                            <img src="{{ $foto_perfil->temporaryUrl() }}" alt="Vista previa de la fotografía del trabajador">
                        @else
                            <i class="ph-bold ph-user" aria-hidden="true"></i><span>{{ is_string($foto_perfil) && $foto_perfil !== '' ? 'Fotografía registrada' : 'Sin fotografía' }}</span>
                        @endif
                    </div>
                    <x-ui.field label="Fotografía (opcional)" for="trabajador-fotografia" error="foto_perfil" :valid="$camposValidados['foto_perfil'] ?? false">
                        <label class="rm-btn rm-btn-secondary rm-staff-registration__photo-picker" for="trabajador-fotografia"><i class="ph-bold ph-upload-simple" aria-hidden="true"></i> Elegir foto<input id="trabajador-fotografia" class="sr-only" type="file" wire:model="foto_perfil" aria-invalid="{{ $errors->has('foto_perfil') ? 'true' : 'false' }}" @if($errors->has('foto_perfil')) aria-describedby="trabajador-fotografia-error" @elseif($camposValidados['foto_perfil'] ?? false) aria-describedby="trabajador-fotografia-valid" @endif accept="image/jpeg,image/png,image/webp" aria-label="Fotografía del trabajador"></label>
                        <p class="rm-help">JPG, PNG o WebP · Hasta 2 MB</p>
                        @if($esEdicion && is_string($foto_perfil))<p class="rm-help">La fotografía guardada se conserva si no eliges otra.</p>@endif
                        <p class="rm-help" wire:loading wire:target="foto_perfil" role="status">Preparando fotografía…</p>
                    </x-ui.field>
                </aside>
                <div class="rm-staff-registration__details">
            @foreach(['nombres'=>'Nombres','ap_paterno'=>'Apellido paterno','ap_materno'=>'Apellido materno'] as $campo=>$etiqueta)
                <x-ui.field :label="$etiqueta" :for="'trabajador-'.$campo" :required="!in_array($campo, ['ap_paterno', 'ap_materno'])" :error="$campo" :valid="$camposValidados[$campo] ?? false"><input id="trabajador-{{ $campo }}" class="rm-input" type="text" data-captura="{{ in_array($campo, ['numero_documento', 'telefono']) ? 'numero' : 'letras' }}" @if(in_array($campo, ['numero_documento', 'telefono'])) inputmode="numeric" maxlength="{{ $campo === 'telefono' ? 8 : 10 }}" @endif placeholder="{{ ['nombres'=>'Ej. María Victoria','ap_paterno'=>'Ej. Encinas','ap_materno'=>'Ej. Cano','numero_documento'=>'Ej. 7654321','expedido'=>'Ej. LP','telefono'=>'Ej. 77712345'][$campo] }}" wire:model.live.blur="{{ $campo }}" aria-invalid="{{ $errors->has($campo) ? 'true' : 'false' }}" @if($errors->has($campo) || ($camposValidados[$campo] ?? false)) aria-describedby="trabajador-{{ $campo }}-{{ $errors->has($campo) ? 'error' : 'valid' }}" @endif @if(!in_array($campo, ['ap_paterno', 'ap_materno'])) required @endif></x-ui.field>
                @if($campo === 'ap_materno')<p class="rm-help rm-staff-registration__surname-help">Completa al menos uno de los dos apellidos.</p>@endif
            @endforeach
            <x-ui.field label="País emisor del documento" for="trabajador-pais-documento" error="pais_documento" :valid="$camposValidados['pais_documento'] ?? false" required>
                <x-ui.selector id="trabajador-pais-documento" label="País emisor del documento" wire:model.live="pais_documento" :teleport="$destinoSelectoresPersonal" :search-threshold="8" aria-invalid="{{ $errors->has('pais_documento') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('pais_documento') ? 'trabajador-pais-documento-error' : 'trabajador-pais-documento-help' }}">
                    <option value="">Selecciona un país</option>@foreach($this->paises as $iso=>$pais)<option value="{{ $iso }}" data-flag="{{ asset('images/flags/'.strtolower($iso).'.svg') }}">{{ $iso }} · {{ $pais['nombre'] }}</option>@endforeach
                </x-ui.selector>
                <p id="trabajador-pais-documento-help" class="rm-help">País que emitió el documento.</p>
            </x-ui.field>
            <x-ui.field label="Tipo de documento" for="trabajador-tipo-documento" error="tipo_documento" :valid="$camposValidados['tipo_documento'] ?? false" required>
                <x-ui.selector id="trabajador-tipo-documento" label="Tipo de documento" wire:model.live="tipo_documento" :teleport="$destinoSelectoresPersonal" aria-invalid="{{ $errors->has('tipo_documento') ? 'true' : 'false' }}" :aria-describedby="$errors->has('tipo_documento') ? 'trabajador-tipo-documento-error' : null">
                    <option value="">Seleccionar</option>@foreach($this->tiposDocumento as $tipo)<option value="{{ $tipo }}">{{ $tipo }}</option>@endforeach
                </x-ui.selector>
            </x-ui.field>
            <x-ui.field label="Número de documento" for="trabajador-numero_documento" error="numero_documento" :valid="$camposValidados['numero_documento'] ?? false" required>
                <input id="trabajador-numero_documento" class="rm-input" type="text" data-captura="{{ $this->formatoDocumento['captura'] }}" inputmode="{{ $this->formatoDocumento['captura'] === 'numero' ? 'numeric' : 'text' }}" maxlength="{{ $this->formatoDocumento['max'] }}" placeholder="Número de documento" wire:model.live.blur="numero_documento" required aria-invalid="{{ $errors->has('numero_documento') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('numero_documento') ? 'trabajador-numero_documento-error' : 'trabajador-numero_documento-help' }}">
                <p id="trabajador-numero_documento-help" class="rm-help">{{ $this->formatoDocumento['ayuda'] }}</p>
            </x-ui.field>
            @if($pais_documento === 'BO' && $tipo_documento === 'CI')
            <x-ui.field label="Expedido en" for="trabajador-expedido" error="expedido" :valid="$camposValidados['expedido'] ?? false" required>
                <x-ui.selector id="trabajador-expedido" label="Departamento de expedición" wire:model.live="expedido" :teleport="$destinoSelectoresPersonal" aria-invalid="{{ $errors->has('expedido') ? 'true' : 'false' }}" :aria-describedby="$errors->has('expedido') ? 'trabajador-expedido-error' : null">
                    <option value="">Seleccionar departamento</option>@foreach(['LP'=>'La Paz','CB'=>'Cochabamba','SC'=>'Santa Cruz','OR'=>'Oruro','PT'=>'Potosí','CH'=>'Chuquisaca','TJ'=>'Tarija','BE'=>'Beni','PN'=>'Pando'] as $codigo=>$departamento)<option value="{{ $codigo }}">{{ $codigo }} · {{ $departamento }}</option>@endforeach
                </x-ui.selector>
            </x-ui.field>
            @endif
            <x-ui.field label="País del celular" for="trabajador-pais-telefono" error="pais_telefono" :valid="$camposValidados['pais_telefono'] ?? false" required>
                <x-ui.selector id="trabajador-pais-telefono" label="País del celular" wire:model.live="pais_telefono" :teleport="$destinoSelectoresPersonal" :search-threshold="8" aria-invalid="{{ $errors->has('pais_telefono') ? 'true' : 'false' }}" :aria-describedby="$errors->has('pais_telefono') ? 'trabajador-pais-telefono-error' : null">
                    <option value="">Selecciona un país</option>@foreach($this->paises as $iso=>$pais)<option value="{{ $iso }}" data-flag="{{ asset('images/flags/'.strtolower($iso).'.svg') }}">{{ $pais['nombre'] }} {{ $pais['codigo'] }}</option>@endforeach
                </x-ui.selector>
            </x-ui.field>
            <x-ui.field label="Celular" for="trabajador-telefono" error="telefono" :valid="$camposValidados['telefono'] ?? false" required>
                <div class="rm-staff-registration__phone"><span aria-label="Prefijo internacional">{{ $this->paises[$pais_telefono]['codigo'] ?? '—' }}</span><input id="trabajador-telefono" class="rm-input" type="tel" data-captura="numero" inputmode="numeric" autocomplete="tel-national" maxlength="{{ $this->paises[$pais_telefono]['max_tel'] ?? 15 }}" placeholder="Número de celular" wire:model.live.blur="telefono" required aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('telefono') ? 'trabajador-telefono-error' : 'trabajador-telefono-help' }}"></div>
                <p id="trabajador-telefono-help" class="rm-help">{{ $pais_telefono === 'BO' ? '8 dígitos. Empieza por 5, 6 o 7.' : 'Número nacional, sin repetir el prefijo internacional.' }}</p>
            </x-ui.field>
            <x-ui.field label="Fecha de nacimiento" for="trabajador-nacimiento" required error="fecha_nacimiento" :valid="$camposValidados['fecha_nacimiento'] ?? false"><input id="trabajador-nacimiento" class="rm-input" type="date" wire:model.live.blur="fecha_nacimiento" aria-invalid="{{ $errors->has('fecha_nacimiento') ? 'true' : 'false' }}" @if($errors->has('fecha_nacimiento')) aria-describedby="trabajador-nacimiento-error" @elseif($camposValidados['fecha_nacimiento'] ?? false) aria-describedby="trabajador-nacimiento-valid" @endif required></x-ui.field>
            <x-ui.field label="Género" for="trabajador-genero" required error="genero" :valid="$camposValidados['genero'] ?? false"><x-ui.selector id="trabajador-genero" label="Género" wire:model.live="genero" :teleport="$destinoSelectoresPersonal" aria-invalid="{{ $errors->has('genero') ? 'true' : 'false' }}" :aria-describedby="$errors->has('genero') ? 'trabajador-genero-error' : (($camposValidados['genero'] ?? false) ? 'trabajador-genero-valid' : null)"><option value="" disabled>Seleccionar género</option><option value="F">Femenino</option><option value="M">Masculino</option></x-ui.selector></x-ui.field>
            <x-ui.field label="Dirección" for="trabajador-direccion" required error="direccion" :valid="$camposValidados['direccion'] ?? false"><textarea id="trabajador-direccion" data-captura="texto" class="rm-input" placeholder="Ej. Calle Los Pinos, n.º 120" wire:model.live.blur="direccion" aria-invalid="{{ $errors->has('direccion') ? 'true' : 'false' }}" @if($errors->has('direccion')) aria-describedby="trabajador-direccion-error" @elseif($camposValidados['direccion'] ?? false) aria-describedby="trabajador-direccion-valid" @endif rows="2" maxlength="255" required></textarea></x-ui.field>
                </div>
            </div>
        </x-ui.form-section>
        @elseif($pasoActual === 2)
        <x-ui.form-section title="Información laboral" description="Función institucional y datos del expediente laboral." icon="ph-briefcase" :columns="2">
            <x-ui.field label="Función institucional y rol de acceso" for="trabajador-rol" error="roles_seleccionados" :valid="$camposValidados['roles_seleccionados'] ?? false" required><select id="trabajador-rol" class="rm-input" wire:model.live="rol_seleccionado" aria-invalid="{{ $errors->has('roles_seleccionados') ? 'true' : 'false' }}" @if($errors->has('roles_seleccionados')) aria-describedby="trabajador-rol-error" @elseif($camposValidados['roles_seleccionados'] ?? false) aria-describedby="trabajador-rol-valid" @endif @disabled($esEdicion)><option value="">Seleccionar función</option>@foreach($roles as $rol)<option value="{{ $rol->name }}">{{ $rol->name }}</option>@endforeach</select><p class="rm-help">La edición de datos no cambia el rol de una cuenta existente.</p></x-ui.field>
            <x-ui.field label="Fecha de ingreso laboral (opcional)" for="trabajador-ingreso" error="fecha_ingreso" :valid="$camposValidados['fecha_ingreso'] ?? false"><input id="trabajador-ingreso" class="rm-input" type="date" wire:model.live.blur="fecha_ingreso" aria-invalid="{{ $errors->has('fecha_ingreso') ? 'true' : 'false' }}" @if($errors->has('fecha_ingreso')) aria-describedby="trabajador-ingreso-error" @elseif($camposValidados['fecha_ingreso'] ?? false) aria-describedby="trabajador-ingreso-valid" @endif></x-ui.field>
            <x-ui.field label="Matrícula profesional (cuando corresponda)" for="trabajador-matricula" error="matricula_prof" :valid="$camposValidados['matricula_prof'] ?? false"><input id="trabajador-matricula" data-captura="texto" class="rm-input" placeholder="Ej. matrícula profesional" wire:model.live.blur="matricula_prof" aria-invalid="{{ $errors->has('matricula_prof') ? 'true' : 'false' }}" @if($errors->has('matricula_prof')) aria-describedby="trabajador-matricula-error" @elseif($camposValidados['matricula_prof'] ?? false) aria-describedby="trabajador-matricula-valid" @endif maxlength="50"></x-ui.field>
            <x-ui.field label="Observaciones laborales (opcional)" for="trabajador-observaciones" error="observaciones" :valid="$camposValidados['observaciones'] ?? false"><textarea id="trabajador-observaciones" data-captura="texto" class="rm-input" placeholder="Información laboral adicional" wire:model.live.blur="observaciones" aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}" @if($errors->has('observaciones')) aria-describedby="trabajador-observaciones-error" @elseif($camposValidados['observaciones'] ?? false) aria-describedby="trabajador-observaciones-valid" @endif></textarea></x-ui.field>
        </x-ui.form-section>
        @elseif($pasoActual === 3)
        <x-ui.form-section title="Cuenta de acceso" description="Credenciales de acceso institucional." icon="ph-shield-check" :columns="2">
            <x-ui.field label="Correo de acceso" for="trabajador-correo" error="correo" :valid="$camposValidados['correo'] ?? false" required><input id="trabajador-correo" class="rm-input" placeholder="Ej. nombre@institucion.com" type="email" wire:model.live.blur="correo" aria-invalid="{{ $errors->has('correo') ? 'true' : 'false' }}" @if($errors->has('correo')) aria-describedby="trabajador-correo-error" @elseif($camposValidados['correo'] ?? false) aria-describedby="trabajador-correo-valid" @endif autocomplete="off" maxlength="120" required></x-ui.field>
            @unless($esEdicion)<x-ui.field label="Contraseña temporal" for="trabajador-clave" error="contrasena_temporal" :valid="$camposValidados['contrasena_temporal'] ?? false" required><input id="trabajador-clave" class="rm-input" placeholder="Mínimo 12 caracteres" type="password" wire:model.live.blur="contrasena_temporal" aria-invalid="{{ $errors->has('contrasena_temporal') ? 'true' : 'false' }}" @if($errors->has('contrasena_temporal')) aria-describedby="trabajador-clave-error" @elseif($camposValidados['contrasena_temporal'] ?? false) aria-describedby="trabajador-clave-valid" @endif autocomplete="new-password" minlength="12" maxlength="72" required><p class="rm-help">Entrega el acceso por el canal institucional. No lo incluyas en observaciones.</p></x-ui.field>@endunless
        </x-ui.form-section>
        <x-ui.callout variant="info" title="Cuenta y trabajo son distintos"><p>La nueva cuenta queda activa cuando el registro completo se confirma. El estado de documentos y la competencia profesional se revisan por separado.</p></x-ui.callout>
        @elseif($pasoActual === 4)
        <x-ui.section-card title="Comprobar los datos" icon="ph-checklist"><dl class="rm-management-context"><div><dt>Trabajador</dt><dd>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</dd></div><div><dt>Función y acceso</dt><dd>{{ $rol_seleccionado }} · {{ $correo }}</dd></div></dl></x-ui.section-card>
        <x-ui.form-section title="Documentación" description="Comprueba los datos y adjunta los documentos del alta." icon="ph-files" :columns="2">
            @foreach($this->documentosConfigurados as $documento)
                @if($documento['tipo'] === 'ingresante')
                <x-ui.field :label="($documento['id'] === 'CI' && $tipo_documento !== 'CI' ? 'Documento de identidad ('.$tipo_documento.')' : $documento['nombre']).($documento['obligatorio_inmediato'] ? ' · requerido al alta' : ' · opcional')" :for="'documento-'.$documento['id']" :error="'archivos_temporales.'.$documento['id']" :valid="$camposValidados['archivos_temporales.'.$documento['id']] ?? false" :required="!$esEdicion && $documento['obligatorio_inmediato']">
                    <input id="documento-{{ $documento['id'] }}" type="file" class="rm-input" wire:model="archivos_temporales.{{ $documento['id'] }}" aria-invalid="{{ $errors->has('archivos_temporales.'.$documento['id']) ? 'true' : 'false' }}" @if($errors->has('archivos_temporales.'.$documento['id'])) aria-describedby="documento-{{ $documento['id'] }}-error" @endif accept="application/pdf,image/jpeg,image/png,image/webp">
                    <p class="rm-help">{{ $documento['id'] === 'CI' && $tipo_documento !== 'CI' ? 'Copia legible del documento seleccionado.' : ($documento['desc'] ?? '') }} PDF, JPG, PNG o WebP; hasta 5 MB. Cargar no equivale a aprobar.</p>
                    @if(isset($estado_documentos[$documento['id']]))<p>Estado registrado: {{ str_replace('_',' ', $estado_documentos[$documento['id']]) }}</p>@endif
                </x-ui.field>
                @endif
            @endforeach
        </x-ui.form-section>
        <p class="rm-help" role="status" wire:loading wire:target="archivos_temporales">Preparando archivos…</p>
        @endif
        @error('guardado')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        @error('archivos_temporales')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        <div class="rm-management-actions rm-staff-registration__footer">
            <button class="rm-btn-secondary" type="button" @click="if (!dirty || confirm('¿Descartar los cambios sin guardar?')) { $dispatch('cerrar-modal-gestion-personal') }">Cancelar</button>
            @if($pasoActual > 1)<button class="rm-btn-secondary" type="button" wire:click="retrocederPaso" wire:loading.attr="disabled">Atrás</button>@endif
            @if($pasoActual < 4)<button class="rm-btn-primary" type="button" wire:click="avanzarPaso" wire:loading.attr="disabled">Continuar</button>@else<button class="rm-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="guardar,archivos_temporales,foto_perfil">{{ $esEdicion ? 'Guardar cambios' : 'Registrar trabajador' }}</button>@endif
            <span class="rm-help" wire:loading wire:target="guardar" role="status">Guardando el registro…</span>
        </div>
    </form>
</div>
