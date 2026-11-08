@php($limitesNacimiento = \App\Modulos\Identidad\Dominio\Reglas\DatosPersonalesTrabajador::limitesNacimiento())
@php($idPaisDocumento = 'trabajador-pais-documento'.($otro_pais_documento ? '-otro' : ''))
@php($idPaisTelefono = 'trabajador-pais-telefono'.($otro_pais_telefono ? '-otro' : ''))
@php($destinoSelectoresPersonal = $esEdicion && auth()->user()->hasRole('SUPERADMINISTRADOR') ? '#gerencia-personal-editor-superadmin' : '#gerencia-personal-ficha')
<div class="rm-staff-workspace rm-staff-registration" x-data="rmPersonalRegistration()" @input.capture="normalizar($event)" @change.capture="retenerArchivo($event)" @documentos-personal-catalogo.window="actualizarCatalogo($event.detail.codigos)" @focusin="if ($event.target.matches('input:not([hidden]), textarea, .rm-choice__trigger, .rm-staff-registration__other-country, [data-completar-direccion], .rm-staff-registration__location summary, .rm-staff-registration__remove-file, .rm-staff-registration__password button, .rm-staff-registration__preview-button') && !$event.target.closest('.rm-control-popover')) ($event.target.closest('.rm-field') || $event.target).scrollIntoView({ block: 'nearest', inline: 'nearest' })" @personal-validacion-fallida.window="$nextTick(() => $el.querySelector('[aria-invalid=true]:not([hidden])')?.focus())" @input="dirty = true" @change="dirty = true" @gerencia-personal-cerrar.window="if (await confirmarSalida()) { $dispatch('cerrar-modal-gestion-personal') }">
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
            <x-ui.field :label="$otro_pais_documento ? 'Especifica el país del documento' : 'País emisor del documento'" :for="$idPaisDocumento" error="pais_documento" :valid="$camposValidados['pais_documento'] ?? false" required>
                <x-ui.selector :id="$idPaisDocumento" :label="$otro_pais_documento ? 'Especifica el país del documento' : 'País emisor del documento'" :search-only="$otro_pais_documento" wire:model.live="pais_documento" :teleport="$destinoSelectoresPersonal" :search-threshold="8" aria-invalid="{{ $errors->has('pais_documento') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('pais_documento') ? $idPaisDocumento.'-error' : 'trabajador-pais-documento-help' }}">
                    <option value="">Selecciona un país</option>@foreach(($otro_pais_documento ? $this->todosPaises : $this->paises) as $iso=>$pais)<option value="{{ $iso }}" data-search="{{ implode(' ', $pais['aliases'] ?? []) }}" data-flag="{{ asset('images/flags/'.strtolower($iso).'.svg') }}">{{ $iso }} · {{ $pais['nombre'] }}</option>@endforeach
                </x-ui.selector>
                <p id="trabajador-pais-documento-help" class="rm-help">{{ $otro_pais_documento ? 'Busca por nombre y selecciona el país.' : 'País que emitió el documento.' }}</p>
                <button type="button" class="rm-btn-secondary rm-staff-registration__other-country" data-otro-pais="documento" @click="dirty = true; await $wire.activarOtroPais('documento'); if ($wire.otro_pais_documento) $nextTick(() => document.getElementById('trabajador-pais-documento-otro')?.click())" wire:loading.attr="disabled"><i class="ph-bold ph-globe" aria-hidden="true"></i>{{ $otro_pais_documento ? 'Usar países frecuentes' : 'Otro país / escribir' }}</button>
            </x-ui.field>
            <x-ui.field label="Tipo de documento" for="trabajador-tipo-documento" error="tipo_documento" :valid="$camposValidados['tipo_documento'] ?? false" required>
                <x-ui.selector id="trabajador-tipo-documento" label="Tipo de documento" wire:model.live="tipo_documento" :teleport="$destinoSelectoresPersonal" aria-invalid="{{ $errors->has('tipo_documento') ? 'true' : 'false' }}" :aria-describedby="$errors->has('tipo_documento') ? 'trabajador-tipo-documento-error' : null">
                    <option value="">Seleccionar</option>@foreach($this->tiposDocumento as $tipo)<option value="{{ $tipo }}">{{ str_replace('_', ' ', $tipo) }}</option>@endforeach
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
            <x-ui.field :label="$otro_pais_telefono ? 'Especifica el país del celular' : 'País del celular'" :for="$idPaisTelefono" error="pais_telefono" :valid="$camposValidados['pais_telefono'] ?? false" required>
                <x-ui.selector :id="$idPaisTelefono" :label="$otro_pais_telefono ? 'Especifica el país del celular' : 'País del celular'" :search-only="$otro_pais_telefono" wire:model.live="pais_telefono" :teleport="$destinoSelectoresPersonal" :search-threshold="8" aria-invalid="{{ $errors->has('pais_telefono') ? 'true' : 'false' }}" :aria-describedby="$errors->has('pais_telefono') ? $idPaisTelefono.'-error' : null">
                    <option value="">Selecciona un país</option>@foreach(($otro_pais_telefono ? $this->todosPaisesTelefono : $this->paises) as $iso=>$pais)<option value="{{ $iso }}" data-search="{{ implode(' ', $pais['aliases'] ?? []) }}" data-flag="{{ asset('images/flags/'.strtolower($iso).'.svg') }}">{{ $pais['nombre'] }} {{ $pais['codigo'] }}</option>@endforeach
                </x-ui.selector>
                @if($otro_pais_telefono)<p class="rm-help">Busca por nombre y selecciona el país.</p>@endif
                <button type="button" class="rm-btn-secondary rm-staff-registration__other-country" data-otro-pais="telefono" @click="dirty = true; await $wire.activarOtroPais('telefono'); if ($wire.otro_pais_telefono) $nextTick(() => document.getElementById('trabajador-pais-telefono-otro')?.click())" wire:loading.attr="disabled"><i class="ph-bold ph-globe" aria-hidden="true"></i>{{ $otro_pais_telefono ? 'Usar países frecuentes' : 'Otro país / escribir' }}</button>
            </x-ui.field>
            <x-ui.field label="Celular" for="trabajador-telefono" error="telefono" :valid="$camposValidados['telefono'] ?? false" required>
                <div class="rm-staff-registration__phone"><span aria-label="Prefijo internacional">{{ $this->todosPaisesTelefono[$pais_telefono]['codigo'] ?? '—' }}</span><input id="trabajador-telefono" class="rm-input" type="tel" data-captura="numero" inputmode="numeric" autocomplete="tel-national" maxlength="{{ $this->todosPaisesTelefono[$pais_telefono]['max_tel'] ?? 15 }}" placeholder="Número de celular" wire:model.live.blur="telefono" required aria-invalid="{{ $errors->has('telefono') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('telefono') ? 'trabajador-telefono-error' : 'trabajador-telefono-help' }}"></div>
                <p id="trabajador-telefono-help" class="rm-help">{{ $pais_telefono === 'BO' ? '8 dígitos. Empieza por 5, 6 o 7.' : 'Número nacional, sin repetir el prefijo internacional.' }}</p>
            </x-ui.field>
            <x-ui.field label="Fecha de nacimiento" for="trabajador-nacimiento" required error="fecha_nacimiento" :valid="$camposValidados['fecha_nacimiento'] ?? false"><input id="trabajador-nacimiento" class="rm-input" type="date" min="{{ $limitesNacimiento['min'] }}" max="{{ $limitesNacimiento['max'] }}" wire:model.live.blur="fecha_nacimiento" aria-invalid="{{ $errors->has('fecha_nacimiento') ? 'true' : 'false' }}" @if($errors->has('fecha_nacimiento')) aria-describedby="trabajador-nacimiento-error" @elseif($camposValidados['fecha_nacimiento'] ?? false) aria-describedby="trabajador-nacimiento-valid trabajador-nacimiento-help" @else aria-describedby="trabajador-nacimiento-help" @endif required><p id="trabajador-nacimiento-help" class="rm-help">De 18 a 60 años, inclusive.</p></x-ui.field>
            <x-ui.field label="Género" for="trabajador-genero" required error="genero" :valid="$camposValidados['genero'] ?? false"><x-ui.selector id="trabajador-genero" label="Género" wire:model.live="genero" :teleport="$destinoSelectoresPersonal" aria-invalid="{{ $errors->has('genero') ? 'true' : 'false' }}" :aria-describedby="$errors->has('genero') ? 'trabajador-genero-error' : (($camposValidados['genero'] ?? false) ? 'trabajador-genero-valid' : null)"><option value="" disabled>Seleccionar género</option><option value="F">Femenino</option><option value="M">Masculino</option></x-ui.selector></x-ui.field>
            <div class="rm-staff-registration__address">
                @include('livewire.identidad.partials.personal-direccion')
                <div class="rm-staff-registration__address-preview" aria-live="polite">
                    @if(filled($direccion))<span class="rm-help">Dirección seleccionada</span>@endif
                    <p id="trabajador-direccion">{{ $direccion ?: 'Selecciona tu ubicación y pulsa «Usar esta dirección».' }}</p>
                    @if($this->googleMapsUrl)<a class="rm-btn-secondary rm-staff-registration__maps" href="{{ $this->googleMapsUrl }}" target="_blank" rel="noopener noreferrer"><i class="ph-bold ph-map-pin" aria-hidden="true"></i> BUSCAR EN GOOGLE MAPS</a>@endif
                    @error('direccion')<p id="trabajador-direccion-error" class="rm-error" role="alert"><i class="ph-bold ph-warning-circle" aria-hidden="true"></i>{{ $message }}</p>@enderror
                </div>
            </div>
                </div>
            </div>
        </x-ui.form-section>
        @elseif($pasoActual === 2)
        <x-ui.form-section title="Información laboral" description="Función institucional y datos del expediente laboral." icon="ph-briefcase" :columns="2">
            <x-ui.field label="Función institucional y rol de acceso" for="trabajador-rol" error="roles_seleccionados" :valid="$camposValidados['roles_seleccionados'] ?? false" required><x-ui.selector id="trabajador-rol" label="Función institucional" :teleport="$destinoSelectoresPersonal" wire:model.live="rol_seleccionado" aria-invalid="{{ $errors->has('roles_seleccionados') ? 'true' : 'false' }}" :aria-describedby="$errors->has('roles_seleccionados') ? 'trabajador-rol-error' : (($camposValidados['roles_seleccionados'] ?? false) ? 'trabajador-rol-valid' : null)" :disabled="$esEdicion"><option value="">Seleccionar función</option>@foreach($roles as $rol)<option value="{{ $rol->name }}">{{ $rol->name }}</option>@endforeach</x-ui.selector><p class="rm-help">La edición de datos no cambia el rol de una cuenta existente.</p></x-ui.field>
            <x-ui.field label="Fecha de ingreso laboral" for="trabajador-ingreso" error="fecha_ingreso" :valid="$camposValidados['fecha_ingreso'] ?? false"><input id="trabajador-ingreso" class="rm-input" type="date" value="{{ $fecha_ingreso }}" readonly aria-invalid="{{ $errors->has('fecha_ingreso') ? 'true' : 'false' }}" @if($errors->has('fecha_ingreso')) aria-describedby="trabajador-ingreso-error" @elseif($camposValidados['fecha_ingreso'] ?? false) aria-describedby="trabajador-ingreso-valid" @endif></x-ui.field>

            <x-ui.field label="Observaciones laborales (opcional)" for="trabajador-observaciones" error="observaciones" :valid="$camposValidados['observaciones'] ?? false"><textarea id="trabajador-observaciones" data-captura="texto" class="rm-input" placeholder="Información laboral adicional" wire:model.live.blur="observaciones" aria-invalid="{{ $errors->has('observaciones') ? 'true' : 'false' }}" @if($errors->has('observaciones')) aria-describedby="trabajador-observaciones-error" @elseif($camposValidados['observaciones'] ?? false) aria-describedby="trabajador-observaciones-valid" @endif></textarea></x-ui.field>
            <p class="rm-help rm-staff-registration__automatic-registration">@if($esEdicion) FECHA Y HORA DE ALTA: {{ $fecha_registro ? $fecha_registro.' '.$hora_registro : 'SIN REGISTRO HISTÓRICO' }}.@else LA FECHA Y HORA DEL ALTA SE ASIGNAN AUTOMÁTICAMENTE AL CONFIRMAR EL REGISTRO.@endif</p>
        </x-ui.form-section>
        @elseif($pasoActual === 3)
        <x-ui.form-section title="Cuenta de acceso" description="Credenciales de acceso institucional." icon="ph-shield-check" :columns="2">
            <x-ui.field label="Correo de acceso" for="trabajador-correo" error="correo" :valid="$camposValidados['correo'] ?? false" required><input id="trabajador-correo" class="rm-input" placeholder="Ej. nombre@institucion.com" type="email" wire:model.live.blur="correo" aria-invalid="{{ $errors->has('correo') ? 'true' : 'false' }}" @if($errors->has('correo')) aria-describedby="trabajador-correo-error" @elseif($camposValidados['correo'] ?? false) aria-describedby="trabajador-correo-valid" @endif autocomplete="off" maxlength="120" required></x-ui.field>
            @unless($esEdicion)<x-ui.field label="Contraseña temporal" for="trabajador-clave" error="contrasena_temporal" :valid="$camposValidados['contrasena_temporal'] ?? false" required><div class="rm-staff-registration__password" x-data="{ verClave: false }"><input id="trabajador-clave" class="rm-input" placeholder="Mínimo 12 caracteres" type="password" :type="verClave ? 'text' : 'password'" wire:model.live.blur="contrasena_temporal" aria-invalid="{{ $errors->has('contrasena_temporal') ? 'true' : 'false' }}" @if($errors->has('contrasena_temporal')) aria-describedby="trabajador-clave-error" @elseif($camposValidados['contrasena_temporal'] ?? false) aria-describedby="trabajador-clave-valid" @endif autocomplete="new-password" minlength="12" maxlength="72" required><button type="button" class="rm-btn-secondary" @click="verClave = !verClave" :aria-pressed="verClave" :aria-label="verClave ? 'Ocultar contraseña temporal' : 'Mostrar contraseña temporal'"><i class="ph-bold" :class="verClave ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button></div><p class="rm-help">Entrega el acceso por el canal institucional. No lo incluyas en observaciones.</p></x-ui.field>@endunless
        </x-ui.form-section>
        <p class="rm-help">Al confirmar se enviarán las credenciales temporales a {{ $correo ?: 'el correo registrado' }}, con un enlace personal para crear una contraseña nueva. La revisión documental y la competencia profesional siguen su propio proceso.</p>
        @elseif($pasoActual === 4)
        <x-ui.form-section title="Resumen del registro" description="Revisa los datos antes de confirmar. Puedes volver a los pasos anteriores para corregirlos." icon="ph-checklist" :columns="1">
            <dl class="rm-management-context rm-staff-registration__review">
                <div><dt>Trabajador</dt><dd>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</dd></div>
                <div><dt>Documento de identidad</dt><dd>{{ str_replace('_', ' ', $tipo_documento ?? '') }} · {{ $numero_documento }} · {{ $this->todosPaises[$pais_documento]['nombre'] ?? '' }}</dd></div>
                <div><dt>Nacimiento</dt><dd>{{ $fecha_nacimiento }} · {{ $genero === 'F' ? 'Femenino' : 'Masculino' }}</dd></div>
                <div><dt>Celular</dt><dd>{{ $this->todosPaisesTelefono[$pais_telefono]['codigo'] ?? '' }} {{ $telefono }}</dd></div>
                <div><dt>Dirección</dt><dd>{{ $direccion }}</dd></div>
                <div><dt>Función institucional</dt><dd>{{ $rol_seleccionado }}</dd></div>
                <div><dt>Día de ingreso</dt><dd>{{ $esEdicion ? ($fecha_ingreso ?: 'SIN REGISTRO HISTÓRICO') : today()->toDateString() }}</dd></div>
                <div><dt>Fecha y hora del alta</dt><dd>{{ $esEdicion ? ($fecha_registro ? $fecha_registro.' '.$hora_registro : 'SIN REGISTRO HISTÓRICO') : 'AUTOMÁTICAS AL CONFIRMAR' }}</dd></div>

                <div><dt>Correo de acceso</dt><dd>{{ $correo }}</dd></div>
            </dl>
        </x-ui.form-section>
        <x-ui.form-section title="Documentación" description="Comprueba los datos y adjunta los documentos del alta." icon="ph-files" :columns="2">
            @foreach([true, false] as $grupoInmediato)
                <div class="rm-staff-registration__document-group"><h3>{{ $grupoInmediato ? 'MÍNIMO DOCUMENTAL DEL ALTA' : 'DOCUMENTOS COMPLEMENTARIOS' }}</h3><p class="rm-help">{{ $grupoInmediato ? 'Adjunta estos documentos antes de registrar. El mínimo corresponde a la función elegida.' : 'Puedes adjuntarlos ahora o solicitar con plazo los que correspondan. Los no solicitados siguen siendo opcionales.' }}</p></div>
            @foreach($this->documentosConfigurados as $documento)
                @if($documento['tipo'] === 'ingresante' && $documento['obligatorio_inmediato'] === $grupoInmediato)
                <x-ui.field :label="($documento['id'] === 'CI' && $tipo_documento !== 'CI' ? 'Documento de identidad ('.str_replace('_', ' ', $tipo_documento).')' : $documento['nombre']).($documento['obligatorio_inmediato'] ? ' · REQUERIDO AL ALTA' : (in_array($documento['id'], $documentos_con_plazo, true) ? ' · PENDIENTE CON PLAZO' : ' · OPCIONAL'))" :for="'documento-'.$documento['id']" :error="'archivos_temporales.'.$documento['id']" :valid="$camposValidados['archivos_temporales.'.$documento['id']] ?? false" :required="!$esEdicion && $documento['obligatorio_inmediato']">
                    <input id="documento-{{ $documento['id'] }}" data-documento-personal="{{ $documento['id'] }}" type="file" class="rm-input" wire:model="archivos_temporales.{{ $documento['id'] }}" aria-invalid="{{ $errors->has('archivos_temporales.'.$documento['id']) ? 'true' : 'false' }}" @if($errors->has('archivos_temporales.'.$documento['id'])) aria-describedby="documento-{{ $documento['id'] }}-error" @elseif($camposValidados['archivos_temporales.'.$documento['id']] ?? false) aria-describedby="documento-{{ $documento['id'] }}-valid" @endif accept="application/pdf,image/jpeg,image/png,image/webp">
                    <p class="rm-help">{{ $documento['id'] === 'CI' && $tipo_documento !== 'CI' ? 'Copia legible del documento seleccionado.' : ($documento['desc'] ?? '') }} PDF, JPG, PNG o WebP; hasta 5 MB. Cargar no equivale a aprobar.</p>
                    @if(($camposValidados['archivos_temporales.'.$documento['id']] ?? false) && isset($archivos_temporales[$documento['id']]))
                        <button id="vista-documento-{{ $documento['id'] }}" type="button" class="rm-btn-secondary rm-staff-registration__preview-button" data-documento-codigo="{{ $documento['id'] }}" data-documento-mime="{{ $archivos_temporales[$documento['id']]->getMimeType() }}" data-documento-nombre="{{ $documento['nombre'] }}" @click="abrirVistaDocumento($event.currentTarget)"><i class="ph-bold ph-eye" aria-hidden="true"></i> VER DOCUMENTO</button>
                    @endif
                    @if(isset($archivos_temporales[$documento['id']]))<button type="button" class="rm-btn-secondary rm-staff-registration__remove-file" wire:click="removerDocumento('{{ $documento['id'] }}')" @click="dirty = true; quitarArchivoLocal('{{ $documento['id'] }}'); document.getElementById('documento-{{ $documento['id'] }}').value = ''" wire:loading.attr="disabled"><i class="ph-bold ph-x" aria-hidden="true"></i> Quitar selección</button>@endif
                    <span class="rm-help" role="status" wire:loading wire:target="archivos_temporales.{{ $documento['id'] }}">Preparando archivo…</span>
                    @if(!$esEdicion && $documento['permite_plazo'] && !$documento['obligatorio_inmediato'] && in_array($documento['id'], array_column($this->documentosPostergables, 'id'), true) && !isset($archivos_temporales[$documento['id']]))
                        <label class="rm-staff-registration__request-document"><input type="checkbox" class="rm-checkbox" wire:model.live="documentos_con_plazo" value="{{ $documento['id'] }}"> SOLICITAR CON PLAZO</label>
                    @endif
                    @if(isset($estado_documentos[$documento['id']]))<p>Estado del documento: {{ str_replace('_',' ', $estado_documentos[$documento['id']]) }}</p>@endif
                </x-ui.field>
                    @if($documento['id'] === 'MATRICULA')
<x-ui.field label="Número de matrícula (opcional)" for="trabajador-matricula" error="matricula_prof" :valid="$camposValidados['matricula_prof'] ?? false"><input id="trabajador-matricula" data-captura="texto" class="rm-input" type="text" placeholder="Ej. matrícula profesional" wire:model.live.blur="matricula_prof" aria-invalid="{{ $errors->has('matricula_prof') ? 'true' : 'false' }}" @if($errors->has('matricula_prof')) aria-describedby="trabajador-matricula-error" @elseif($camposValidados['matricula_prof'] ?? false) aria-describedby="trabajador-matricula-valid" @endif maxlength="50"></x-ui.field>
                    @endif

                @endif
            @endforeach
            @endforeach
        </x-ui.form-section>
        <p class="rm-help" role="status" wire:loading wire:target="archivos_temporales">Preparando archivos…</p>
        @if($documentos_con_plazo)
            <x-ui.form-section title="DOCUMENTACIÓN A COMPLETAR" description="Gerencia elige la fecha límite de los documentos solicitados. El mínimo inmediato debe adjuntarse al alta." icon="ph-calendar-check" :columns="2">
                <x-ui.field label="FECHA LÍMITE DE ENTREGA" for="trabajador-fecha-limite" error="fecha_limite_documentacion" :valid="$camposValidados['fecha_limite_documentacion'] ?? false" required><input id="trabajador-fecha-limite" class="rm-input" type="date" min="{{ today()->toDateString() }}" wire:model.live.blur="fecha_limite_documentacion" aria-invalid="{{ $errors->has('fecha_limite_documentacion') ? 'true' : 'false' }}" @if($errors->has('fecha_limite_documentacion')) aria-describedby="trabajador-fecha-limite-error" @endif></x-ui.field>
                <ul class="rm-staff-registration__pending-list">@foreach($this->documentosPostergables as $pendiente) @if(in_array($pendiente['id'], $documentos_con_plazo, true))<li>{{ $pendiente['nombre'] }}</li>@endif @endforeach</ul>
                @error('documentos_con_plazo')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
                @foreach($errors->get('documentos_con_plazo.*') as $mensajes) @foreach($mensajes as $mensaje)<p class="rm-error" role="alert">{{ $mensaje }}</p>@endforeach @endforeach
            </x-ui.form-section>
        @endif
        <template x-if="vistaDocumento">
            <section id="personal-vista-documento" class="rm-staff-registration__preview" aria-label="Vista previa del documento" @keydown.escape.stop.prevent="cerrarVistaDocumento()">
                <header><h3 x-text="vistaDocumento?.nombre"></h3><button id="personal-cerrar-vista-documento" type="button" class="rm-btn-secondary" @click="cerrarVistaDocumento()">CERRAR VISTA PREVIA</button></header>
                <template x-if="vistaDocumento?.mime === 'application/pdf'"><iframe :src="vistaDocumento?.url" :title="'Vista previa de '+vistaDocumento.nombre" referrerpolicy="no-referrer"></iframe></template>
                <template x-if="vistaDocumento.mime !== 'application/pdf'"><img :src="vistaDocumento.url" :alt="'Vista previa de '+vistaDocumento.nombre"></template>
                <p class="rm-help">Vista previa del archivo seleccionado. Revisarlo no lo aprueba ni confirma el registro.</p>
            </section>
        </template>
        @endif
        @error('guardado')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        @error('archivos_temporales')<p class="rm-error" role="alert">{{ $message }}</p>@enderror
        <div class="rm-management-actions rm-staff-registration__footer">
            <button class="rm-btn-secondary" type="button" @click="if (await confirmarSalida()) { $dispatch('cerrar-modal-gestion-personal') }">Cancelar</button>
            @if($pasoActual > 1)<button class="rm-btn-secondary" type="button" wire:click="retrocederPaso" wire:loading.attr="disabled">Atrás</button>@endif
            @if($pasoActual < 4)<button class="rm-btn-primary" type="button" wire:click="avanzarPaso" wire:loading.attr="disabled">Continuar</button>@else<button class="rm-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="guardar,archivos_temporales,foto_perfil">{{ $esEdicion ? 'Guardar cambios' : 'Registrar trabajador' }}</button>@endif
            <span class="rm-help" wire:loading wire:target="guardar" role="status">Guardando el registro…</span>
        </div>
    </form>
</div>
