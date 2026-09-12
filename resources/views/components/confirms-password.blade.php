@props([
    /*
    |--------------------------------------------------------------------------
    | Contexto de la operación
    |--------------------------------------------------------------------------
    */

    'title' => 'Confirmar identidad',

    'content' =>
        'Por tu seguridad, confirma tu contraseña para continuar.',

    'button' => 'Confirmar',

    /*
    |--------------------------------------------------------------------------
    | Apariencia de operación sensible/destructiva
    |--------------------------------------------------------------------------
    */

    'danger' => false,
])


@php
    /*
    |--------------------------------------------------------------------------
    | Identificador único de confirmación
    |--------------------------------------------------------------------------
    |
    | Jetstream utiliza este identificador para relacionar
    | la confirmación de contraseña con la acción que la solicitó.
    |
    */

    $confirmableId = md5(
        (string) $attributes->wire('then')
    );
@endphp


{{-- ========================================================================
DISPARADOR DE LA OPERACIÓN
========================================================================= --}}
<span {{ $attributes->wire('then') }} x-data x-ref="span" data-confirm-title="{{ $title }}"
    data-confirm-content="{{ $content }}" data-confirm-button="{{ $button }}"
    data-confirm-danger="{{ $danger ? '1' : '0' }}" x-on:click="
        $dispatch(
            'remembermind-confirm-password-context',
            {
                title:
                    $el.dataset.confirmTitle,

                content:
                    $el.dataset.confirmContent,

                button:
                    $el.dataset.confirmButton,

                danger:
                    $el.dataset.confirmDanger === '1'
            }
        );

        $wire.startConfirmingPassword(
            '{{ $confirmableId }}'
        );
    " x-on:password-confirmed.window="
        setTimeout(
            () =>
                $event.detail.id === '{{ $confirmableId }}'
                &&
                $refs.span.dispatchEvent(
                    new CustomEvent(
                        'then',
                        {
                            bubbles: false
                        }
                    )
                ),
            250
        )
    ">
    {{ $slot }}
</span>


{{-- ========================================================================
UN ÚNICO MODAL GLOBAL
========================================================================= --}}
@once

    <div x-data="{
                /*
                |--------------------------------------------------------------------------
                | Contexto dinámico
                |--------------------------------------------------------------------------
                */

                titulo:
                    'Confirmar identidad',

                contenido:
                    'Por tu seguridad, confirma tu contraseña para continuar.',

                textoBoton:
                    'Confirmar',

                operacionPeligrosa:
                    false,


                /*
                |--------------------------------------------------------------------------
                | Estado local
                |--------------------------------------------------------------------------
                */

                mostrarPassword:
                    false,

                capsLock:
                    false
            }" x-on:remembermind-confirm-password-context.window="
                titulo =
                    $event.detail.title
                    || 'Confirmar identidad';

                contenido =
                    $event.detail.content
                    || 'Por tu seguridad, confirma tu contraseña para continuar.';

                textoBoton =
                    $event.detail.button
                    || 'Confirmar';

                operacionPeligrosa =
                    Boolean(
                        $event.detail.danger
                    );

                mostrarPassword =
                    false;

                capsLock =
                    false;
            " x-on:confirming-password.window="
                mostrarPassword =
                    false;

                capsLock =
                    false;

                setTimeout(
                    () =>
                        $refs.confirmable_password
                        &&
                        $refs.confirmable_password.focus(),
                    250
                )
            " x-on:password-confirmed.window="
                mostrarPassword =
                    false;

                capsLock =
                    false;
            ">

        <x-dialog-modal wire:model.live="confirmingPassword" maxWidth="md">

            {{-- =============================================================
            TÍTULO
            ============================================================== --}}
            <x-slot name="title">

                <div class="
                            flex
                            items-start
                            gap-3
                        ">

                    {{-- Icono --}}
                    <div class="
                                flex
                                h-11
                                w-11
                                shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                transition-colors
                                duration-200
                            " :class="
                                operacionPeligrosa
                                    ? 'bg-estado-peligroBg text-estado-peligro'
                                    : 'bg-fondo-cardSuave text-boton-acento'
                            ">

                        <i class="
                                    ph-bold
                                    text-xl
                                " :class="
                                    operacionPeligrosa
                                        ? 'ph-warning'
                                        : 'ph-lock-key'
                                "></i>

                    </div>


                    {{-- Encabezado --}}
                    <div class="min-w-0">

                        <div class="
                                    flex
                                    flex-wrap
                                    items-center
                                    gap-2
                                ">

                            <h3 class="
                                        font-outfit
                                        text-lg
                                        font-extrabold
                                        text-titulo
                                    " x-text="titulo"></h3>


                            <span class="
                                        inline-flex
                                        items-center
                                        gap-1.5
                                        rounded-full
                                        border
                                        px-2.5
                                        py-1
                                        text-[10px]
                                        font-black
                                        uppercase
                                        tracking-wider
                                        transition-colors
                                    " :class="
                                        operacionPeligrosa
                                            ? 'border-estado-peligroBorde bg-estado-peligroBg text-estado-peligro'
                                            : 'border-borde-suave bg-fondo-cardSuave text-meta'
                                    ">

                                <i class="ph-bold" :class="
                                            operacionPeligrosa
                                                ? 'ph-warning-circle'
                                                : 'ph-shield-check'
                                        "></i>


                                <span x-text="
                                            operacionPeligrosa
                                                ? 'Acción sensible'
                                                : 'Verificación requerida'
                                        "></span>

                            </span>

                        </div>


                        <p class="
                                    mt-1
                                    text-xs
                                    font-medium
                                    text-meta
                                ">
                            RememberMind · Confirmación de seguridad
                        </p>

                    </div>

                </div>

            </x-slot>


            {{-- =============================================================
            CONTENIDO
            ============================================================== --}}
            <x-slot name="content">

                <div class="space-y-5">

                    {{-- Explicación de la acción --}}
                    <div class="
                                flex
                                items-start
                                gap-3
                                rounded-2xl
                                border
                                p-4
                                transition-colors
                                duration-200
                            " :class="
                                operacionPeligrosa
                                    ? 'border-estado-advertenciaBorde bg-estado-advertenciaBg'
                                    : 'border-borde-suave bg-fondo-cardSuave'
                            ">

                        <i class="
                                    ph-bold
                                    mt-0.5
                                    shrink-0
                                    text-lg
                                " :class="
                                    operacionPeligrosa
                                        ? 'ph-warning-circle text-estado-advertencia'
                                        : 'ph-shield-check text-boton-acento'
                                "></i>


                        <div>

                            <p class="
                                        text-sm
                                        font-bold
                                        leading-6
                                        text-titulo
                                    " x-text="contenido"></p>


                            <p class="
                                        mt-1
                                        text-xs
                                        font-medium
                                        leading-5
                                        text-meta
                                    ">
                                Esta comprobación impide que otra persona
                                realice una operación sensible simplemente
                                porque encontró tu sesión abierta.
                            </p>

                        </div>

                    </div>


                    {{-- =====================================================
                    CONTRASEÑA
                    ====================================================== --}}
                    <div>

                        <label for="confirmable_password" class="rm-label">
                            Contraseña actual

                            <span class="text-estado-peligro" aria-hidden="true">
                                *
                            </span>
                        </label>


                        <div class="relative mt-1">

                            <input id="confirmable_password" :type="
                                        mostrarPassword
                                            ? 'text'
                                            : 'password'
                                    " class="
                                        rm-input
                                        !mt-0
                                        pr-12
                                    " placeholder="Ingresa tu contraseña" autocomplete="current-password"
                                x-ref="confirmable_password" wire:model="confirmablePassword"
                                wire:keydown.enter="confirmPassword" x-on:keydown="
                                        capsLock =
                                            typeof $event.getModifierState === 'function'
                                            &&
                                            $event.getModifierState('CapsLock')
                                    " x-on:keyup="
                                        capsLock =
                                            typeof $event.getModifierState === 'function'
                                            &&
                                            $event.getModifierState('CapsLock')
                                    " x-on:blur="
                                        capsLock = false
                                    ">


                            {{-- Mostrar / ocultar --}}
                            <button type="button" x-on:click="
                                        mostrarPassword =
                                            !mostrarPassword
                                    " class="
                                        absolute
                                        inset-y-0
                                        right-0
                                        flex
                                        w-11
                                        items-center
                                        justify-center
                                        rounded-r-xl
                                        text-meta
                                        transition-colors
                                        duration-150
                                        hover:text-boton-acento
                                        focus:outline-none
                                    " :aria-label="
                                        mostrarPassword
                                            ? 'Ocultar contraseña'
                                            : 'Mostrar contraseña'
                                    " :title="
                                        mostrarPassword
                                            ? 'Ocultar contraseña'
                                            : 'Mostrar contraseña'
                                    ">

                                <i class="ph-bold" :class="
                                            mostrarPassword
                                                ? 'ph-eye-slash'
                                                : 'ph-eye'
                                        "></i>

                            </button>

                        </div>


                        {{-- Error real Livewire --}}
                        <x-input-error for="confirmable_password" class="mt-2" />


                        {{-- Caps Lock --}}
                        <div x-cloak x-show="capsLock" x-transition:enter="
                                    transition
                                    ease-out
                                    duration-150
                                " x-transition:enter-start="
                                    opacity-0
                                    -translate-y-1
                                " x-transition:enter-end="
                                    opacity-100
                                    translate-y-0
                                " x-transition:leave="
                                    transition
                                    ease-in
                                    duration-100
                                " x-transition:leave-start="
                                    opacity-100
                                " x-transition:leave-end="
                                    opacity-0
                                " class="
                                    mt-2
                                    flex
                                    items-center
                                    gap-2
                                    rounded-lg
                                    border
                                    border-estado-advertenciaBorde
                                    bg-estado-advertenciaBg
                                    px-3
                                    py-2
                                    text-xs
                                    font-bold
                                    text-estado-advertencia
                                " role="status" aria-live="polite">

                            <i class="
                                        ph-bold
                                        ph-arrow-fat-line-up
                                    "></i>

                            Bloq Mayús está activado.

                        </div>


                        {{-- Nota de privacidad --}}
                        <div class="
                                    mt-3
                                    flex
                                    items-start
                                    gap-2
                                    text-[11px]
                                    font-medium
                                    leading-5
                                    text-meta
                                ">

                            <i class="
                                        ph-bold
                                        ph-lock
                                        mt-0.5
                                        shrink-0
                                        text-boton-acento
                                    "></i>


                            <p>
                                Tu contraseña se utiliza únicamente
                                para confirmar tu identidad.
                                RememberMind no mostrará tu contraseña
                                ni la incluirá en registros de actividad.
                            </p>

                        </div>

                    </div>


                    {{-- =====================================================
                    ALERTA DE OPERACIÓN DESTRUCTIVA
                    ====================================================== --}}
                    <div x-cloak x-show="operacionPeligrosa" x-transition:enter="
                                transition
                                ease-out
                                duration-150
                            " x-transition:enter-start="
                                opacity-0
                                translate-y-1
                            " x-transition:enter-end="
                                opacity-100
                                translate-y-0
                            " class="
                                flex
                                items-start
                                gap-2
                                rounded-xl
                                border
                                border-estado-peligroBorde
                                bg-estado-peligroBg
                                p-3
                            ">

                        <i class="
                                    ph-bold
                                    ph-warning
                                    mt-0.5
                                    shrink-0
                                    text-estado-peligro
                                "></i>


                        <p class="
                                    text-xs
                                    font-semibold
                                    leading-5
                                    text-estado-peligro
                                ">
                            Revisa la operación antes de confirmar.
                            Una vez validada tu contraseña,
                            se ejecutará la acción solicitada.
                        </p>

                    </div>

                </div>

            </x-slot>


            {{-- =============================================================
            PIE
            ============================================================== --}}
            <x-slot name="footer">

                <div class="
                            flex
                            w-full
                            flex-col-reverse
                            gap-3
                            sm:flex-row
                            sm:items-center
                            sm:justify-between
                        ">

                    {{-- Indicador --}}
                    <div class="
                                flex
                                items-center
                                gap-2
                                text-[11px]
                                font-semibold
                                text-meta
                            ">

                        <i class="
                                    ph-bold
                                    ph-shield-check
                                    text-boton-acento
                                "></i>

                        Operación protegida

                    </div>


                    {{-- Acciones --}}
                    <div class="
                                flex
                                flex-wrap
                                justify-end
                                gap-2
                            ">

                        {{-- Cancelar --}}
                        <button type="button" x-on:click="
                                    mostrarPassword = false;
                                    capsLock = false;
                                " wire:click="stopConfirmingPassword" wire:loading.attr="disabled"
                            wire:target="confirmPassword" class="
                                    rm-btn-secondary
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                ">

                            <i class="
                                        ph-bold
                                        ph-x
                                    "></i>

                            Cancelar

                        </button>


                        {{-- Confirmar --}}
                        <button type="button" dusk="confirm-password-button" wire:click="confirmPassword"
                            wire:loading.attr="disabled" wire:target="confirmPassword" class="
                                    disabled:cursor-not-allowed
                                    disabled:opacity-50
                                " :class="
                                    operacionPeligrosa
                                        ? 'rm-btn-danger'
                                        : 'rm-btn-accent'
                                ">

                            {{-- Estado normal --}}
                            <span wire:loading.remove wire:target="confirmPassword" class="
                                        inline-flex
                                        items-center
                                        gap-2
                                    ">

                                <i class="ph-bold" :class="
                                            operacionPeligrosa
                                                ? 'ph-warning'
                                                : 'ph-lock-key-open'
                                        "></i>


                                <span x-text="textoBoton"></span>

                            </span>


                            {{-- Cargando --}}
                            <span wire:loading wire:target="confirmPassword" class="
                                        items-center
                                        gap-2
                                    ">

                                <i class="
                                            ph-bold
                                            ph-circle-notch
                                            animate-spin
                                        "></i>

                                Verificando...

                            </span>

                        </button>

                    </div>

                </div>

            </x-slot>

        </x-dialog-modal>

    </div>

@endonce