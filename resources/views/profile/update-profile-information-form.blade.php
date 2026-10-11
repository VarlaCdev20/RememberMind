<form wire:submit="updateProfileInformation" x-data="{
        editandoContacto: false,
        editandoEmergencia: false,

        modalFoto: false,
        photoName: null,
        photoPreview: null,
        eliminarFoto: false,

        paisTelefonoOriginal: @js((string) ($this->user->pais_telefono ?? '')),
        telefonoOriginal: @js((string) ($this->user->telefono ?? '')),

        contactoEmergenciaOriginal: @js((string) ($this->user->contacto_emergencia ?? '')),
        apellidoPaternoEmergenciaOriginal: @js((string) ($this->user->ap_paterno_emergencia ?? '')),
        apellidoMaternoEmergenciaOriginal: @js((string) ($this->user->ap_materno_emergencia ?? '')),
        parentescoEmergenciaOriginal: @js((string) ($this->user->parentesco_emergencia ?? '')),
        celularEmergenciaOriginal: @js((string) ($this->user->celular_emergencia ?? '')),

        codigosTelefono: {
            'Bolivia': '+591',
            'Argentina': '+54',
            'Brasil': '+55',
            'Chile': '+56',
            'Perú': '+51',
            'Paraguay': '+595',
            'Uruguay': '+598',
            'Colombia': '+57',
            'Ecuador': '+593',
            'Venezuela': '+58',
            'México': '+52',
            'España': '+34',
            'Estados Unidos': '+1',
            'Otro': ''
        },

        codigoPais(pais) {
            return this.codigosTelefono[pais] ?? '';
        },

        editarContacto() {
            this.editandoEmergencia = false;

            $wire.$set(
                'state._seccion',
                'contacto',
                false
            );

            if (!$wire.state.pais_telefono) {
                $wire.$set(
                    'state.pais_telefono',
                    'Bolivia',
                    false
                );
            }

            this.editandoContacto = true;

            this.$nextTick(() => {
                document
                    .getElementById('pais_telefono')
                    ?.focus();
            });
        },

        cancelarContacto() {
            $wire.$set(
                'state.pais_telefono',
                this.paisTelefonoOriginal || null,
                false
            );

            $wire.$set(
                'state.telefono',
                this.telefonoOriginal || null,
                false
            );

            $wire.$set(
                'state._seccion',
                null,
                false
            );

            this.editandoContacto = false;
        },

        editarEmergencia() {
            this.editandoContacto = false;

            $wire.$set(
                'state._seccion',
                'emergencia',
                false
            );

            this.editandoEmergencia = true;

            this.$nextTick(() => {
                document
                    .getElementById('contacto_emergencia')
                    ?.focus();
            });
        },

        cancelarEmergencia() {
            $wire.$set(
                'state.contacto_emergencia',
                this.contactoEmergenciaOriginal || null,
                false
            );

            $wire.$set(
                'state.ap_paterno_emergencia',
                this.apellidoPaternoEmergenciaOriginal || null,
                false
            );

            $wire.$set(
                'state.ap_materno_emergencia',
                this.apellidoMaternoEmergenciaOriginal || null,
                false
            );

            $wire.$set(
                'state.parentesco_emergencia',
                this.parentescoEmergenciaOriginal || null,
                false
            );

            $wire.$set(
                'state.celular_emergencia',
                this.celularEmergenciaOriginal || null,
                false
            );

            $wire.$set(
                'state._seccion',
                null,
                false
            );

            this.editandoEmergencia = false;
        },

        abrirFoto() {
            this.editandoContacto = false;
            this.editandoEmergencia = false;

            this.photoName = null;
            this.photoPreview = null;
            this.eliminarFoto = false;

            $wire.$set(
                'state._seccion',
                'fotografia',
                false
            );

            $wire.$set(
                'state.eliminar_foto',
                false,
                false
            );

            this.modalFoto = true;

            this.$nextTick(() => {
                document
                    .getElementById('cerrar-modal-foto')
                    ?.focus();
            });
        },

        seleccionarFoto(event) {
            const file = event.target.files?.[0];

            if (!file) {
                return;
            }

            this.eliminarFoto = false;
            this.photoName = file.name;

            $wire.$set(
                'state._seccion',
                'fotografia',
                false
            );

            $wire.$set(
                'state.eliminar_foto',
                false,
                false
            );

            const reader = new FileReader();

            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };

            reader.readAsDataURL(file);
        },

        marcarEliminarFoto() {
            this.photoName = null;
            this.photoPreview = null;
            this.eliminarFoto = true;

            $wire.$set(
                'state._seccion',
                'fotografia',
                false
            );

            $wire.$set(
                'state.eliminar_foto',
                true,
                false
            );

            $wire.$set(
                'photo',
                null
            );

            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
        },

        cancelarFoto() {
            this.modalFoto = false;
            this.photoName = null;
            this.photoPreview = null;
            this.eliminarFoto = false;

            $wire.$set(
                'state.eliminar_foto',
                false,
                false
            );

            $wire.$set(
                'state._seccion',
                null,
                false
            );

            $wire.$set(
                'photo',
                null
            );

            if (this.$refs.photoInput) {
                this.$refs.photoInput.value = '';
            }
        },

        finalizarGuardado() {
            /*
             * Actualizamos las copias de restauración
             * después de una operación exitosa.
             */
            this.paisTelefonoOriginal =
                $wire.state.pais_telefono ?? '';

            this.telefonoOriginal =
                $wire.state.telefono ?? '';

            this.contactoEmergenciaOriginal =
                $wire.state.contacto_emergencia ?? '';

            this.apellidoPaternoEmergenciaOriginal =
                $wire.state.ap_paterno_emergencia ?? '';

            this.apellidoMaternoEmergenciaOriginal =
                $wire.state.ap_materno_emergencia ?? '';

            this.parentescoEmergenciaOriginal =
                $wire.state.parentesco_emergencia ?? '';

            this.celularEmergenciaOriginal =
                $wire.state.celular_emergencia ?? '';

            this.editandoContacto = false;
            this.editandoEmergencia = false;

            this.modalFoto = false;
            this.photoName = null;
            this.photoPreview = null;
            this.eliminarFoto = false;

            $wire.$set(
                'state.eliminar_foto',
                false,
                false
            );

            $wire.$set(
                'state._seccion',
                null,
                false
            );
        }
    }" x-on:saved.window="finalizarGuardado()" @keydown.escape.window="
        if (modalFoto) {
            cancelarFoto();
        }
    " class="space-y-4">
    @php
        $usuario = $this->user;

        $nombreCompleto = trim(
            collect([
                $usuario->nombres,
                $usuario->ap_paterno,
                $usuario->ap_materno,
            ])
                ->filter()
                ->implode(' ')
        );

        $nombreEmergencia = trim(
            collect([
                $usuario->contacto_emergencia,
                $usuario->ap_paterno_emergencia,
                $usuario->ap_materno_emergencia,
            ])
                ->filter()
                ->implode(' ')
        );

        $telefonoVisible = $usuario->telefono
            ? trim(
                ($usuario->codigo_telefono
                    ? $usuario->codigo_telefono . ' '
                    : ''
                )
                . $usuario->telefono
            )
            : 'No registrado';

        $direccionResumida = collect([
            $usuario->calle
            ? trim(
                $usuario->calle
                . (
                    $usuario->nro_domicilio
                    ? ' N.º ' . $usuario->nro_domicilio
                    : ''
                )
            )
            : null,

            $usuario->zona,
            $usuario->ciudad,
        ])
            ->filter()
            ->implode(' · ');

        $direccionResumida = $direccionResumida !== ''
            ? $direccionResumida
            : ($usuario->direccion ?: 'No registrada');
    @endphp


    {{-- =========================================================
    ESTADO DE CARGA
    ========================================================== --}}
    <div wire:loading.flex wire:target="updateProfileInformation,photo" class="
            items-center
            gap-2
            rounded-2xl
            border border-borde-suave
            bg-fondo-cardSuave
            px-4
            py-3
            text-xs
            font-bold
            text-meta
        ">
        <i class="
                ph-bold
                ph-circle-notch
                animate-spin
                text-boton-acento
            "></i>

        Procesando información...
    </div>


    {{-- =========================================================
    IDENTIDAD + FOTOGRAFÍA
    ========================================================== --}}
    <div class="
            grid
            gap-4
            xl:grid-cols-[1.35fr_0.65fr]
        ">

        {{-- =====================================================
        IDENTIDAD
        ====================================================== --}}
        <section class="
                rounded-[1.25rem]
                border border-borde-suave
                bg-fondo-cardSuave
                p-5
            ">

            <div class="
                    mb-5
                    flex
                    flex-wrap
                    items-start
                    justify-between
                    gap-3
                ">

                <div>

                    <h3 class="
                            flex
                            items-center
                            gap-2
                            font-outfit
                            text-lg
                            font-extrabold
                            text-titulo
                        ">
                        <i class="
                                ph-bold
                                ph-identification-card
                                text-boton-acento
                            "></i>

                        Datos de identidad
                    </h3>


                    <p class="
                            mt-1
                            text-sm
                            font-medium
                            text-meta
                        ">
                        Información oficial registrada
                        por la institución.
                    </p>

                </div>


                <span class="
                        inline-flex
                        items-center
                        gap-1.5
                        rounded-full
                        border border-borde-suave
                        bg-fondo-card
                        px-3
                        py-1.5
                        text-xs
                        font-black
                        text-apoyo
                    ">
                    <i class="ph-bold ph-lock-simple"></i>

                    Solo lectura
                </span>

            </div>


            <dl class="
                    grid
                    gap-x-6
                    gap-y-5
                    sm:grid-cols-2
                    lg:grid-cols-3
                ">

                {{-- Nombres --}}
                <div>
                    <dt class="rm-label-soft">
                        Nombres
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->nombres ?: 'No registrado' }}
                    </dd>
                </div>


                {{-- Paterno --}}
                <div>
                    <dt class="rm-label-soft">
                        Apellido paterno
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->ap_paterno ?: 'No registrado' }}
                    </dd>
                </div>


                {{-- Materno --}}
                <div>
                    <dt class="rm-label-soft">
                        Apellido materno
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->ap_materno ?: 'No registrado' }}
                    </dd>
                </div>


                {{-- País --}}
                <div>
                    <dt class="rm-label-soft">
                        País del documento
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{
    $usuario->pais_documento
    ? \Illuminate\Support\Str::headline(
        mb_strtolower(
            $usuario->pais_documento,
            'UTF-8'
        )
    )
    : 'No registrado'
                        }}
                    </dd>
                </div>


                {{-- Documento --}}
                <div>
                    <dt class="rm-label-soft">
                        Tipo de documento
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->tipo_documento ?: 'No registrado' }}
                    </dd>
                </div>


                {{-- Número --}}
                <div>
                    <dt class="rm-label-soft">
                        N.º de documento
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->numero_documento ?: 'No registrado' }}
                    </dd>
                </div>


                {{-- Expedido --}}
                <div>
                    <dt class="rm-label-soft">
                        Expedido en
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{ $usuario->expedido ?: 'No aplica' }}
                    </dd>
                </div>


                {{-- Nacimiento --}}
                <div>
                    <dt class="rm-label-soft">
                        Fecha de nacimiento
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{
    $usuario->fecha_nacimiento
    ? $usuario->fecha_nacimiento->format('d/m/Y')
    : 'No registrada'
                        }}
                    </dd>
                </div>


                {{-- Género --}}
                <div>
                    <dt class="rm-label-soft">
                        Género
                    </dt>

                    <dd class="
                            mt-1
                            font-bold
                            text-titulo
                        ">
                        {{
    $usuario->genero
    ? \Illuminate\Support\Str::headline(
        mb_strtolower(
            $usuario->genero,
            'UTF-8'
        )
    )
    : 'No registrado'
                        }}
                    </dd>
                </div>

            </dl>


            <div class="
                    mt-5
                    flex
                    items-start
                    gap-2
                    rounded-xl
                    border border-borde-info
                    bg-estado-infoBg
                    p-3
                    text-xs
                    font-semibold
                    leading-5
                    text-estado-info
                ">
                <i class="
                        ph-bold
                        ph-info
                        mt-0.5
                        shrink-0
                    "></i>

                <p>
                    Estos datos forman parte de tu identidad institucional.
                    Si necesitas corregirlos, solicita la actualización
                    correspondiente a Administración.
                </p>
            </div>

        </section>


        {{-- =====================================================
        FOTOGRAFÍA
        ====================================================== --}}
        <section class="
                rounded-[1.25rem]
                border border-borde-suave
                bg-fondo-cardSuave
                p-5
            ">

            <div>

                <h3 class="
                        flex
                        items-center
                        gap-2
                        font-outfit
                        text-lg
                        font-extrabold
                        text-titulo
                    ">
                    <i class="
                            ph-bold
                            ph-camera
                            text-boton-acento
                        "></i>

                    Fotografía de perfil
                </h3>


                <p class="
                        mt-1
                        text-sm
                        font-medium
                        text-meta
                    ">
                    Imagen utilizada para identificarte
                    dentro del sistema.
                </p>

            </div>


            <div class="
                    mt-6
                    flex
                    flex-col
                    items-center
                    text-center
                ">

                <div class="relative">

                    <img src="{{ $usuario->profile_photo_url }}" alt="Fotografía de {{ $nombreCompleto }}" class="
                            h-36
                            w-36
                            rounded-[1.7rem]
                            border-4
                            border-fondo-card
                            object-cover
                            shadow-card
                        ">


                    <button type="button" @click="abrirFoto()" class="
                            absolute
                            -bottom-2
                            -right-2
                            flex
                            h-10
                            w-10
                            items-center
                            justify-center
                            rounded-xl
                            bg-boton-acento
                            text-inverso
                            shadow-card
                            transition
                            hover:scale-105
                        " aria-label="Cambiar fotografía de perfil" title="Cambiar fotografía">
                        <i class="ph-bold ph-camera"></i>
                    </button>

                </div>


                <p class="
                        mt-5
                        max-w-xs
                        text-sm
                        font-medium
                        leading-6
                        text-meta
                    ">
                    Utiliza una fotografía clara y reciente
                    que permita reconocerte fácilmente.
                </p>


                <button type="button" @click="abrirFoto()" class="rm-btn-secondary mt-4">
                    <i class="ph-bold ph-camera"></i>

                    Cambiar fotografía
                </button>


                <p class="
                        mt-3
                        text-[11px]
                        font-semibold
                        leading-5
                        text-meta
                    ">
                    JPG, JPEG, PNG o WEBP · Máximo 4 MB
                </p>

            </div>

        </section>

    </div>


    {{-- =========================================================
    CONTACTO
    ========================================================== --}}
    <section class="
            rounded-[1.25rem]
            border border-borde-suave
            bg-fondo-cardSuave
            p-5
        ">

        <div class="
                flex
                flex-wrap
                items-start
                justify-between
                gap-3
            ">

            <div>

                <h3 class="
                        flex
                        items-center
                        gap-2
                        font-outfit
                        text-lg
                        font-extrabold
                        text-titulo
                    ">
                    <i class="
                            ph-bold
                            ph-phone
                            text-boton-acento
                        "></i>

                    Información de contacto
                </h3>


                <p class="
                        mt-1
                        text-sm
                        font-medium
                        text-meta
                    ">
                    Número telefónico utilizado
                    para comunicaciones personales.
                </p>

            </div>


            <button x-cloak x-show="!editandoContacto" type="button" @click="editarContacto()" class="rm-btn-secondary">
                <i class="ph-bold ph-pencil-simple"></i>

                Editar
            </button>

        </div>


        {{-- Consulta --}}
        <div x-show="!editandoContacto" class="
                mt-5
                grid
                gap-4
                sm:grid-cols-2
            ">

            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <p class="rm-label-soft">
                    País
                </p>

                <p class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{
    $usuario->pais_telefono
    ?: 'No registrado'
                    }}
                </p>
            </div>


            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <p class="rm-label-soft">
                    Teléfono
                </p>

                <p class="
                        mt-1
                        text-base
                        font-bold
                        text-titulo
                    ">
                    {{ $telefonoVisible }}
                </p>
            </div>

        </div>


        {{-- Edición --}}
        <div x-cloak x-show="editandoContacto" x-transition.opacity.duration.150ms class="mt-5">

            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                    sm:p-5
                ">

                <div class="
                        mb-4
                        flex
                        items-start
                        gap-3
                    ">
                    <div class="
                            flex
                            h-9
                            w-9
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-fondo-cardSuave
                            text-boton-acento
                        ">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </div>

                    <div>
                        <p class="
                                text-sm
                                font-black
                                text-titulo
                            ">
                            Editar teléfono
                        </p>

                        <p class="
                                mt-0.5
                                text-xs
                                font-medium
                                text-meta
                            ">
                            El código internacional se determina
                            automáticamente según el país.
                        </p>
                    </div>
                </div>


                <div class="
                        grid
                        gap-4
                        sm:grid-cols-[1fr_0.55fr_1.25fr]
                    ">

                    {{-- País --}}
                    <div>

                        <label for="pais_telefono" class="rm-label">
                            País
                        </label>


                        <select id="pais_telefono" wire:model="state.pais_telefono" class="rm-select">
                            <option value="">
                                Seleccionar país
                            </option>

                            <option value="Bolivia">
                                Bolivia
                            </option>

                            <option value="Argentina">
                                Argentina
                            </option>

                            <option value="Brasil">
                                Brasil
                            </option>

                            <option value="Chile">
                                Chile
                            </option>

                            <option value="Perú">
                                Perú
                            </option>

                            <option value="Paraguay">
                                Paraguay
                            </option>

                            <option value="Uruguay">
                                Uruguay
                            </option>

                            <option value="Colombia">
                                Colombia
                            </option>

                            <option value="Ecuador">
                                Ecuador
                            </option>

                            <option value="Venezuela">
                                Venezuela
                            </option>

                            <option value="México">
                                México
                            </option>

                            <option value="España">
                                España
                            </option>

                            <option value="Estados Unidos">
                                Estados Unidos
                            </option>

                            <option value="Otro">
                                Otro
                            </option>
                        </select>


                        <x-input-error for="pais_telefono" class="mt-2" />

                    </div>


                    {{-- Código --}}
                    <div>

                        <label class="rm-label">
                            Código
                        </label>


                        <div class="
                                rm-input
                                flex
                                cursor-not-allowed
                                items-center
                                font-bold
                                text-meta
                                opacity-80
                            " aria-label="Código telefónico internacional">
                            <span x-text="
                                    codigoPais(
                                        $wire.state.pais_telefono
                                    ) || '—'
                                "></span>
                        </div>

                    </div>


                    {{-- Número --}}
                    <div>

                        <label for="telefono" class="rm-label">
                            Teléfono
                        </label>


                        <input id="telefono" type="tel" inputmode="numeric" autocomplete="tel" maxlength="20"
                            wire:model="state.telefono" class="rm-input" placeholder="Ej. 71543210">


                        <x-input-error for="telefono" class="mt-2" />

                    </div>

                </div>


                <div class="
                        mt-5
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    ">

                    <button type="button" @click="cancelarContacto()" class="rm-btn-secondary">
                        <i class="ph-bold ph-x"></i>

                        Cancelar
                    </button>


                    <button type="submit" wire:loading.attr="disabled" wire:target="updateProfileInformation"
                        class="rm-btn-accent">
                        <span wire:loading.remove wire:target="updateProfileInformation"
                            class="inline-flex items-center gap-2">
                            <i class="ph-bold ph-floppy-disk"></i>

                            Guardar contacto
                        </span>

                        <span wire:loading wire:target="updateProfileInformation" class="items-center gap-2">
                            <i class="
                                    ph-bold
                                    ph-circle-notch
                                    animate-spin
                                "></i>

                            Guardando...
                        </span>
                    </button>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
    DOMICILIO
    ========================================================== --}}
    <section class="
            rounded-[1.25rem]
            border border-borde-suave
            bg-fondo-cardSuave
            p-5
        ">

        <div class="
                flex
                flex-wrap
                items-start
                justify-between
                gap-3
            ">

            <div>

                <h3 class="
                        flex
                        items-center
                        gap-2
                        font-outfit
                        text-lg
                        font-extrabold
                        text-titulo
                    ">
                    <i class="
                            ph-bold
                            ph-map-pin
                            text-boton-acento
                        "></i>

                    Domicilio
                </h3>


                <p class="
                        mt-1
                        text-sm
                        font-medium
                        text-meta
                    ">
                    Dirección actualmente registrada
                    en tu ficha institucional.
                </p>

            </div>


            <span class="
                    inline-flex
                    items-center
                    gap-1.5
                    rounded-full
                    border border-borde-suave
                    bg-fondo-card
                    px-3
                    py-1.5
                    text-xs
                    font-black
                    text-apoyo
                ">
                <i class="ph-bold ph-eye"></i>

                Consulta
            </span>

        </div>


        <div class="
                mt-5
                rounded-2xl
                border border-borde-suave
                bg-fondo-card
                p-4
            ">

            <div class="
                    flex
                    items-start
                    gap-3
                ">
                <div class="
                        flex
                        h-10
                        w-10
                        shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        bg-fondo-cardSuave
                        text-boton-acento
                    ">
                    <i class="ph-bold ph-map-trifold"></i>
                </div>


                <div class="min-w-0">

                    <p class="rm-label-soft">
                        Dirección registrada
                    </p>

                    <p class="
                            mt-1
                            font-bold
                            leading-6
                            text-titulo
                        ">
                        {{ $direccionResumida }}
                    </p>

                </div>
            </div>

        </div>


        <dl class="
                mt-4
                grid
                gap-4
                sm:grid-cols-2
                lg:grid-cols-4
            ">

            {{-- Calle --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <dt class="rm-label-soft">
                    Calle / avenida
                </dt>

                <dd class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{ $usuario->calle ?: 'No registrada' }}
                </dd>
            </div>


            {{-- Número --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <dt class="rm-label-soft">
                    N.º domicilio
                </dt>

                <dd class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{ $usuario->nro_domicilio ?: 'No registrado' }}
                </dd>
            </div>


            {{-- Zona --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <dt class="rm-label-soft">
                    Zona / barrio
                </dt>

                <dd class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{ $usuario->zona ?: 'No registrada' }}
                </dd>
            </div>


            {{-- Ciudad --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">
                <dt class="rm-label-soft">
                    Ciudad
                </dt>

                <dd class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{ $usuario->ciudad ?: 'No registrada' }}
                </dd>
            </div>

        </dl>


        <div class="
                mt-4
                flex
                items-start
                gap-2
                rounded-xl
                border border-borde-suave
                bg-fondo-card
                p-3
                text-xs
                font-semibold
                leading-5
                text-meta
            ">
            <i class="
                    ph-bold
                    ph-info
                    mt-0.5
                    shrink-0
                    text-boton-acento
                "></i>

            <p>
                El domicilio se mantiene en modo consulta para preservar
                la estructura territorial registrada por Administración.
            </p>
        </div>

    </section>


    {{-- =========================================================
    CONTACTO DE EMERGENCIA
    ========================================================== --}}
    <section class="
            rounded-[1.25rem]
            border border-borde-suave
            bg-fondo-cardSuave
            p-5
        ">

        <div class="
                flex
                flex-wrap
                items-start
                justify-between
                gap-3
            ">

            <div>

                <h3 class="
                        flex
                        items-center
                        gap-2
                        font-outfit
                        text-lg
                        font-extrabold
                        text-titulo
                    ">
                    <i class="
                            ph-bold
                            ph-users-three
                            text-boton-acento
                        "></i>

                    Contacto de emergencia
                </h3>


                <p class="
                        mt-1
                        text-sm
                        font-medium
                        text-meta
                    ">
                    Persona que puede ser contactada
                    ante una situación de emergencia.
                </p>

            </div>


            <button x-cloak x-show="!editandoEmergencia" type="button" @click="editarEmergencia()"
                class="rm-btn-secondary">
                <i class="ph-bold ph-pencil-simple"></i>

                Editar
            </button>

        </div>


        {{-- Consulta --}}
        <div x-show="!editandoEmergencia" class="
                mt-5
                grid
                gap-4
                sm:grid-cols-3
            ">

            {{-- Nombre --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">

                <div class="
                        mb-2
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        bg-fondo-cardSuave
                        text-boton-acento
                    ">
                    <i class="ph-bold ph-user"></i>
                </div>


                <p class="rm-label-soft">
                    Nombre completo
                </p>

                <p class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{
    $nombreEmergencia
    ?: 'No registrado'
                    }}
                </p>

            </div>


            {{-- Parentesco --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">

                <div class="
                        mb-2
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        bg-fondo-cardSuave
                        text-boton-acento
                    ">
                    <i class="ph-bold ph-heart"></i>
                </div>


                <p class="rm-label-soft">
                    Parentesco
                </p>

                <p class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{
    $usuario->parentesco_emergencia
    ?: 'No registrado'
                    }}
                </p>

            </div>


            {{-- Celular --}}
            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                ">

                <div class="
                        mb-2
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        bg-fondo-cardSuave
                        text-boton-acento
                    ">
                    <i class="ph-bold ph-phone"></i>
                </div>


                <p class="rm-label-soft">
                    Celular
                </p>

                <p class="
                        mt-1
                        font-bold
                        text-titulo
                    ">
                    {{
    $usuario->celular_emergencia
    ?: 'No registrado'
                    }}
                </p>

            </div>

        </div>


        {{-- Edición --}}
        <div x-cloak x-show="editandoEmergencia" x-transition.opacity.duration.150ms class="mt-5">

            <div class="
                    rounded-2xl
                    border border-borde-suave
                    bg-fondo-card
                    p-4
                    sm:p-5
                ">

                <div class="
                        mb-4
                        flex
                        items-start
                        gap-3
                    ">
                    <div class="
                            flex
                            h-9
                            w-9
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-fondo-cardSuave
                            text-boton-acento
                        ">
                        <i class="ph-bold ph-pencil-simple"></i>
                    </div>


                    <div>

                        <p class="
                                text-sm
                                font-black
                                text-titulo
                            ">
                            Editar contacto de emergencia
                        </p>

                        <p class="
                                mt-0.5
                                text-xs
                                font-medium
                                text-meta
                            ">
                            Registra información de una persona
                            que pueda ser localizada cuando sea necesario.
                        </p>

                    </div>
                </div>


                <div class="
                        grid
                        gap-4
                        md:grid-cols-3
                    ">

                    {{-- Nombres --}}
                    <div>

                        <label for="contacto_emergencia" class="rm-label">
                            Nombres
                        </label>


                        <input id="contacto_emergencia" type="text" maxlength="150" autocomplete="off"
                            wire:model="state.contacto_emergencia" class="rm-input" placeholder="Ej. María Elena">


                        <x-input-error for="contacto_emergencia" class="mt-2" />

                    </div>


                    {{-- Paterno --}}
                    <div>

                        <label for="ap_paterno_emergencia" class="rm-label">
                            Apellido paterno
                        </label>


                        <input id="ap_paterno_emergencia" type="text" maxlength="80" autocomplete="off"
                            wire:model="state.ap_paterno_emergencia" class="rm-input" placeholder="Ej. Vargas">


                        <x-input-error for="ap_paterno_emergencia" class="mt-2" />

                    </div>


                    {{-- Materno --}}
                    <div>

                        <label for="ap_materno_emergencia" class="rm-label">
                            Apellido materno
                        </label>


                        <input id="ap_materno_emergencia" type="text" maxlength="80" autocomplete="off"
                            wire:model="state.ap_materno_emergencia" class="rm-input" placeholder="Ej. Flores">


                        <x-input-error for="ap_materno_emergencia" class="mt-2" />

                    </div>


                    {{-- Parentesco --}}
                    <div>

                        <label for="parentesco_emergencia" class="rm-label">
                            Parentesco / vínculo
                        </label>


                        <input id="parentesco_emergencia" type="text" list="lista-parentescos" maxlength="100"
                            autocomplete="off" wire:model="state.parentesco_emergencia" class="rm-input"
                            placeholder="Ej. Madre">


                        <datalist id="lista-parentescos">
                            <option value="MADRE"></option>
                            <option value="PADRE"></option>
                            <option value="ESPOSO/A"></option>
                            <option value="PAREJA"></option>
                            <option value="HERMANO/A"></option>
                            <option value="HIJO/A"></option>
                            <option value="TÍO/A"></option>
                            <option value="PRIMO/A"></option>
                            <option value="AMIGO/A"></option>
                            <option value="OTRO"></option>
                        </datalist>


                        <x-input-error for="parentesco_emergencia" class="mt-2" />

                    </div>


                    {{-- Celular --}}
                    <div>

                        <label for="celular_emergencia" class="rm-label">
                            Celular
                        </label>


                        <input id="celular_emergencia" type="tel" inputmode="numeric" maxlength="20" autocomplete="tel"
                            wire:model="state.celular_emergencia" class="rm-input" placeholder="Ej. 72567890">


                        <x-input-error for="celular_emergencia" class="mt-2" />

                    </div>

                </div>


                <div class="
                        mt-4
                        flex
                        items-start
                        gap-2
                        rounded-xl
                        border border-borde-suave
                        bg-fondo-cardSuave
                        p-3
                        text-xs
                        font-semibold
                        leading-5
                        text-meta
                    ">
                    <i class="
                            ph-bold
                            ph-info
                            mt-0.5
                            shrink-0
                            text-boton-acento
                        "></i>

                    <p>
                        Debe registrarse al menos un apellido
                        y el celular de emergencia debe ser diferente
                        de tu teléfono personal.
                    </p>
                </div>


                <div class="
                        mt-5
                        flex
                        flex-wrap
                        justify-end
                        gap-2
                    ">

                    <button type="button" @click="cancelarEmergencia()" class="rm-btn-secondary">
                        <i class="ph-bold ph-x"></i>

                        Cancelar
                    </button>


                    <button type="submit" wire:loading.attr="disabled" wire:target="updateProfileInformation"
                        class="rm-btn-accent">

                        <span wire:loading.remove wire:target="updateProfileInformation"
                            class="inline-flex items-center gap-2">
                            <i class="ph-bold ph-floppy-disk"></i>

                            Guardar emergencia
                        </span>


                        <span wire:loading wire:target="updateProfileInformation" class="items-center gap-2">
                            <i class="
                                    ph-bold
                                    ph-circle-notch
                                    animate-spin
                                "></i>

                            Guardando...
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
    MENSAJE DE GUARDADO
    ========================================================== --}}
    <x-action-message class="
            text-sm
            font-bold
            text-estado-exito
        " on="saved">
        <div class="
                flex
                items-center
                gap-2
                rounded-xl
                border border-estado-exitoBorde
                bg-estado-exitoBg
                px-4
                py-3
            ">
            <i class="ph-bold ph-check-circle"></i>

            Información actualizada correctamente.
        </div>
    </x-action-message>


    {{-- =========================================================
    MODAL DE FOTOGRAFÍA
    ========================================================== --}}
    <div x-cloak x-show="modalFoto" x-transition.opacity class="
            fixed
            inset-0
            z-[100]
            flex
            items-center
            justify-center
            p-4
        " role="dialog" aria-modal="true" aria-labelledby="titulo-modal-foto">

        {{-- Fondo --}}
        <button type="button" tabindex="-1" aria-label="Cerrar" @click="cancelarFoto()" class="
                absolute
                inset-0
                cursor-default
                bg-black/40
                backdrop-blur-sm
            "></button>


        {{-- Modal --}}
        <div x-show="modalFoto" x-transition.scale.origin.center class="
                relative
                z-10
                w-full
                max-w-lg
                overflow-hidden
                rounded-[1.7rem]
                border border-borde-suave
                bg-fondo-card
                shadow-2xl
            ">

            {{-- Header --}}
            <div class="
                    flex
                    items-start
                    justify-between
                    gap-4
                    border-b
                    border-borde-suave
                    p-5
                ">

                <div class="
                        flex
                        items-start
                        gap-3
                    ">

                    <div class="
                            flex
                            h-10
                            w-10
                            shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            bg-fondo-cardSuave
                            text-boton-acento
                        ">
                        <i class="ph-bold ph-camera"></i>
                    </div>


                    <div>

                        <h3 id="titulo-modal-foto" class="
                                font-outfit
                                text-lg
                                font-extrabold
                                text-titulo
                            ">
                            Fotografía de perfil
                        </h3>


                        <p class="
                                mt-1
                                text-xs
                                font-medium
                                text-meta
                            ">
                            Selecciona una nueva imagen
                            o elimina la fotografía actual.
                        </p>

                    </div>

                </div>


                <button id="cerrar-modal-foto" type="button" @click="cancelarFoto()" class="
                        flex
                        h-9
                        w-9
                        items-center
                        justify-center
                        rounded-xl
                        text-meta
                        transition
                        hover:bg-fondo-cardSuave
                        hover:text-titulo
                    " aria-label="Cerrar">
                    <i class="ph-bold ph-x"></i>
                </button>

            </div>


            {{-- Contenido --}}
            <div class="p-5">

                {{-- Input real --}}
                <input id="photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    wire:model.live="photo" x-ref="photoInput" @change="seleccionarFoto($event)" class="hidden">


                {{-- Preview --}}
                <div class="
                        flex
                        flex-col
                        items-center
                        text-center
                    ">

                    {{-- Vista nueva --}}
                    <template x-if="photoPreview && !eliminarFoto">
                        <img :src="photoPreview" alt="Vista previa de la nueva fotografía" class="
                                h-40
                                w-40
                                rounded-[1.8rem]
                                border-4
                                border-fondo-cardSuave
                                object-cover
                                shadow-card
                            ">
                    </template>


                    {{-- Actual --}}
                    <template x-if="!photoPreview && !eliminarFoto">
                        <img src="{{ $usuario->profile_photo_url }}" alt="Fotografía actual" class="
                                h-40
                                w-40
                                rounded-[1.8rem]
                                border-4
                                border-fondo-cardSuave
                                object-cover
                                shadow-card
                            ">
                    </template>


                    {{-- Eliminación --}}
                    <template x-if="eliminarFoto">
                        <div class="
                                flex
                                h-40
                                w-40
                                flex-col
                                items-center
                                justify-center
                                rounded-[1.8rem]
                                border-2
                                border-dashed
                                border-borde-suave
                                bg-fondo-cardSuave
                                text-meta
                            ">
                            <i class="
                                    ph-bold
                                    ph-user-circle
                                    text-5xl
                                "></i>

                            <span class="
                                    mt-2
                                    text-xs
                                    font-bold
                                ">
                                Sin fotografía
                            </span>
                        </div>
                    </template>


                    <p x-show="photoName && !eliminarFoto" class="
                            mt-3
                            max-w-full
                            truncate
                            text-xs
                            font-bold
                            text-apoyo
                        " x-text="photoName"></p>


                    <x-input-error for="photo" class="mt-3" />


                    <div class="
                            mt-5
                            flex
                            flex-wrap
                            justify-center
                            gap-2
                        ">

                        <button type="button" @click="$refs.photoInput.click()" class="rm-btn-secondary">
                            <i class="ph-bold ph-upload-simple"></i>

                            Seleccionar imagen
                        </button>


                        @if ($usuario->foto_de_perfil)
                            <button type="button" x-show="!eliminarFoto" @click="marcarEliminarFoto()"
                                class="rm-btn-danger">
                                <i class="ph-bold ph-trash"></i>

                                Quitar fotografía
                            </button>
                        @endif

                    </div>


                    <p class="
                            mt-4
                            text-[11px]
                            font-semibold
                            leading-5
                            text-meta
                        ">
                        Formatos permitidos:
                        JPG, JPEG, PNG y WEBP.
                        Tamaño máximo: 4 MB.
                    </p>

                </div>

            </div>


            {{-- Footer --}}
            <div class="
                    flex
                    flex-wrap
                    justify-end
                    gap-2
                    border-t
                    border-borde-suave
                    bg-fondo-cardSuave
                    p-4
                ">

                <button type="button" @click="cancelarFoto()" class="rm-btn-secondary">
                    Cancelar
                </button>


                <button type="submit" :disabled="!photoPreview && !eliminarFoto" wire:loading.attr="disabled"
                    wire:target="updateProfileInformation,photo" class="
                        rm-btn-accent
                        disabled:cursor-not-allowed
                        disabled:opacity-50
                    ">

                    <span wire:loading.remove wire:target="updateProfileInformation,photo"
                        class="inline-flex items-center gap-2">
                        <i class="ph-bold ph-check"></i>

                        Guardar fotografía
                    </span>


                    <span wire:loading wire:target="updateProfileInformation,photo" class="items-center gap-2">
                        <i class="
                                ph-bold
                                ph-circle-notch
                                animate-spin
                            "></i>

                        Procesando...
                    </span>

                </button>

            </div>

        </div>

    </div>

</form>