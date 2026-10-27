<x-guest-layout>
    <main class="rm-shell rm-auth-page" data-auth-access
        x-data="rmAcceso(@js(['correo' => old('correo', ''), 'errores' => $errors->messages(), 'recuperacion' => (bool)session('recovery_requested'), 'completado' => (bool)session('password_reset_completed'), 'status' => session('status')]))"
        x-on:pageshow.window="restaurarFormulario()">
        <x-auth.access-card label="Formulario de acceso">

                <div class="rm-auth-tab-panels">
                <div id="panel-login" class="rm-auth-tab-panel" role="region" aria-labelledby="acceso-titulo" x-show="panel === 'login'"
                    x-transition:enter="rm-auth-panel-enter" x-transition:enter-start="rm-auth-panel-offset" x-transition:enter-end="rm-auth-panel-ready"
                    x-transition:leave="rm-auth-panel-leave" x-transition:leave-start="rm-auth-panel-ready" x-transition:leave-end="rm-auth-panel-offset">
                    <header class="rm-auth-heading">
                        <h1 id="acceso-titulo">Bienvenido de nuevo</h1>
                        <p>Ingresa tus datos para continuar.</p>
                    </header>
                    @if(session('password_reset_completed'))
                        <div class="rm-auth-feedback rm-auth-feedback--success" role="status" tabindex="-1" x-ref="confirmacion" data-password-reset-confirmation><i class="ph ph-check-circle" aria-hidden="true"></i><div><strong>Contraseña actualizada</strong><p>{{ session('status') }}</p></div></div>
                    @endif
                    <div x-show="errorServidor" x-cloak class="rm-auth-feedback rm-auth-feedback--error" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><div><strong>Revisa tus datos de acceso</strong><p x-text="errorServidor"></p></div></div>
                    <form method="POST" action="{{ route('login') }}" class="rm-auth-form" x-on:submit="submitLogin($event)" novalidate>
                        @csrf
                        <div>
                            <label for="correo" class="rm-label">Correo de tu cuenta</label>
                            <div class="rm-auth-control">
                                <i class="ph ph-envelope-simple rm-auth-control__icon" aria-hidden="true"></i>
                                <input id="correo" name="correo" type="email" class="rm-input" placeholder="nombre@correo.com" required maxlength="120" autocomplete="username" x-model="correo" x-ref="loginCorreo" x-on:blur="correoTouched = true" x-on:input="errorServidor = ''" x-bind:aria-invalid="Boolean(correoError)" aria-describedby="login-correo-error">
                            </div>
                            <p id="login-correo-error" class="rm-error" x-show="correoError" x-text="correoError" x-cloak role="alert"></p>
                        </div>
                        <div>
                            <label for="password" class="rm-label">Contraseña</label>
                            <div class="rm-auth-control rm-auth-control--password">
                                <i class="ph ph-lock-simple rm-auth-control__icon" aria-hidden="true"></i>
                                <input id="password" name="password" type="password" class="rm-input" placeholder="Tu contraseña" required autocomplete="current-password" x-model="password" x-ref="loginPassword" x-bind:type="showPassword ? 'text' : 'password'" x-on:blur="passwordTouched = true" x-on:input="errorServidor = ''" x-bind:aria-invalid="Boolean(passwordError)" aria-describedby="login-password-error">
                                <button type="button" class="rm-auth-control__toggle" x-on:click="showPassword = !showPassword" x-bind:aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" x-bind:aria-pressed="showPassword" aria-controls="password"><i class="ph" x-bind:class="showPassword ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
                            </div>
                            <p id="login-password-error" class="rm-error" x-show="passwordError" x-text="passwordError" x-cloak role="alert"></p>
                        </div>
                        <div class="rm-auth-options">
                            @if(config('auth.remember_enabled'))
                                <label for="remember_me"><input id="remember_me" type="checkbox" name="remember" value="1"> Recordarme</label>
                            @endif
                            <button type="button" class="rm-auth-link" x-on:click="abrirRecuperacion()" x-bind:disabled="isSubmitting || recoverSubmitting" aria-controls="panel-recuperacion"><i class="ph ph-key" aria-hidden="true"></i> ¿Olvidaste tu contraseña?</button>
                        </div>
                        <button type="submit" class="rm-btn rm-btn-primary w-full" x-bind:disabled="isSubmitting || recoverSubmitting" x-bind:aria-busy="isSubmitting"><i class="ph ph-spinner-gap rm-auth-spinner" x-show="isSubmitting" x-cloak aria-hidden="true"></i><span x-show="!isSubmitting">Entrar al Portal</span><span x-show="isSubmitting" x-cloak>Iniciando sesión…</span><i class="ph ph-arrow-right rm-auth-action-arrow" x-show="!isSubmitting" aria-hidden="true"></i></button>
                    </form>
                </div>

                <div id="panel-recuperacion" class="rm-auth-tab-panel" role="region" aria-labelledby="recuperacion-titulo" x-show="panel === 'recover'" x-cloak
                    x-transition:enter="rm-auth-panel-enter" x-transition:enter-start="rm-auth-panel-offset" x-transition:enter-end="rm-auth-panel-ready"
                    x-transition:leave="rm-auth-panel-leave" x-transition:leave-start="rm-auth-panel-ready" x-transition:leave-end="rm-auth-panel-offset">
                    <button type="button" class="rm-auth-link rm-auth-back" x-on:click="volverLogin()" x-bind:disabled="recoverSubmitting" aria-controls="panel-login"><i class="ph ph-arrow-left" aria-hidden="true"></i> Volver al inicio de sesión</button>
                    <header class="rm-auth-heading">
                        <h2 id="recuperacion-titulo">Recuperar acceso</h2>
                        <p>Escribe tu correo y solicita las instrucciones aquí mismo.</p>
                    </header>
                    <div x-show="recoveryError" x-cloak class="rm-auth-feedback rm-auth-feedback--error" role="alert" data-recovery-error><i class="ph ph-warning-circle" aria-hidden="true"></i><div><strong>No pudimos procesar la solicitud</strong><p x-text="recoveryError"></p></div></div>
                    <div x-show="recoverySent" x-cloak class="rm-auth-feedback rm-auth-feedback--success" role="status" data-recovery-status><i class="ph ph-envelope-open" aria-hidden="true"></i><div><p class="font-semibold">Solicitud recibida</p><p>Si el correo corresponde a una cuenta institucional, recibirás las instrucciones. Revisa también Spam.</p></div></div>
                    <form method="POST" action="{{ route('password.email') }}" class="rm-auth-form" x-on:submit="submitRecovery($event)" novalidate>
                        @csrf
                        <div>
                            <label for="recover_correo" class="rm-label">Correo de tu cuenta</label>
                            <div class="rm-auth-control">
                                <i class="ph ph-envelope-simple rm-auth-control__icon" aria-hidden="true"></i>
                                <input id="recover_correo" name="correo" type="email" class="rm-input" placeholder="nombre@correo.com" required maxlength="120" autocomplete="username" x-model="recoverCorreo" x-ref="recoverCorreo" x-on:input="recoveryError = ''; recoverySent = false" x-on:blur="recoverTouched = true" x-bind:aria-invalid="Boolean(recoverCorreoError)" aria-describedby="recover-correo-ayuda recover-correo-error">
                            </div>
                            <p id="recover-correo-ayuda" class="rm-help">El mismo correo con el que inicias sesión.</p>
                            <p id="recover-correo-error" class="rm-error" x-show="recoverCorreoError" x-text="recoverCorreoError" x-cloak role="alert"></p>
                        </div>
                        <button type="submit" class="rm-btn rm-btn-primary w-full" x-bind:disabled="recoverSubmitting" x-bind:aria-busy="recoverSubmitting"><i class="ph ph-paper-plane-tilt" x-bind:class="recoverSubmitting ? 'ph-spinner-gap rm-auth-spinner' : 'ph-paper-plane-tilt'" aria-hidden="true"></i><span x-show="!recoverSubmitting">Enviar instrucciones</span><span x-show="recoverSubmitting" x-cloak>Procesando solicitud…</span></button>
                    </form>
                    <details class="rm-auth-help"><summary>¿No recibes el correo?</summary><ul><li>Revisa tu bandeja de entrada y Spam.</li><li>Espera al menos {{ config('auth.passwords.'.config('fortify.passwords').'.throttle', 60) }} segundos antes de volver a solicitarlo.</li><li>Utiliza el enlace más reciente; dura {{ config('auth.passwords.'.config('fortify.passwords').'.expire', 60) }} minutos.</li><li>Si no recuerdas el correo registrado, consulta con Administración.</li></ul></details>
                    <p class="rm-auth-note"><i class="ph ph-lock-key" aria-hidden="true"></i><span>Por seguridad, no confirmamos si un correo tiene una cuenta.</span></p>
                </div>
                </div>
                <div class="rm-auth-footer"><i class="ph ph-lock-key" aria-hidden="true"></i><p>¿Necesitas ayuda? Consulta con Administración.</p></div>
        </x-auth.access-card>
    </main>
</x-guest-layout>
