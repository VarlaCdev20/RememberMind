<x-guest-layout>
    <style>
        .auth-noise {
            background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.85%22 numOctaves=%223%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');
        }

        .auth-dots {
            background-image: radial-gradient(#2F3E5C 2px, transparent 2px);
            background-size: 32px 32px;
        }

        .text-shadow-deep {
            text-shadow: 4px 8px 24px rgba(0,0,0,0.35), 2px 4px 8px rgba(0,0,0,0.20);
        }

        .text-shadow-title {
            text-shadow: 2px 4px 12px rgba(0,0,0,0.20);
        }

        @keyframes floatAuth {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-16px) rotate(5deg); }
        }

        .float-auth {
            animation: floatAuth 7s ease-in-out infinite;
        }
    </style>

    <div
        x-data="{
            panel: 'login',
            showPassword: false,
            isSubmitting: false,
            correo: @js(old('correo')),
            password: '',
            correoTouched: false,
            passwordTouched: false,
            toasts: [],

            addToast(message, type = 'error') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => this.removeToast(id), 4800);
            },

            removeToast(id) {
                this.toasts = this.toasts.filter(toast => toast.id !== id);
            },

            get correoError() {
                if (!this.correoTouched) return '';
                if (!this.correo.trim()) return 'Ingresa tu correo institucional.';
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!regex.test(this.correo.trim())) return 'El correo debe tener un formato válido.';
                if (this.correo.length > 120) return 'El correo no debe superar los 120 caracteres.';
                return '';
            },

            get passwordError() {
                if (!this.passwordTouched) return '';
                if (!this.password) return 'Ingresa tu contraseña.';
                if (this.password.length < 8) return 'La contraseña debe tener al menos 8 caracteres.';
                return '';
            },

            submitLogin(event) {
                this.correoTouched = true;
                this.passwordTouched = true;
                this.correo = this.correo.trim();

                if (this.correoError || this.passwordError) {
                    event.preventDefault();

                    if (this.correoError) {
                        this.addToast(this.correoError, 'error');
                    }

                    if (this.passwordError) {
                        this.addToast(this.passwordError, 'error');
                    }

                    return;
                }

                this.isSubmitting = true;
            }
        }"
        x-init="
            @if ($errors->any())
                addToast('Verifica tu correo o contraseña e inténtalo nuevamente.', 'error');
            @endif

            @if (session('status'))
                addToast(@js(session('status')), 'success');
            @endif
        "
        class="relative min-h-screen overflow-hidden bg-[#D5C7B9] font-outfit text-azul-profundo"
    >
        <div class="auth-noise pointer-events-none fixed inset-0 z-[60] opacity-[0.25] mix-blend-overlay"></div>
        <div class="auth-dots pointer-events-none fixed inset-0 z-0 opacity-[0.035]"></div>

        <div class="absolute left-[-12rem] top-[-14rem] h-[42rem] w-[42rem] rounded-full bg-[#967B66]/25 blur-[130px]"></div>
        <div class="absolute right-[-14rem] bottom-[-14rem] h-[44rem] w-[44rem] rounded-full bg-[#E97A5F]/20 blur-[130px]"></div>
        <div class="absolute right-[22%] top-[10%] h-[32rem] w-[32rem] rounded-full bg-[#8DA280]/18 blur-[120px]"></div>

        {{-- Mensajes emergentes --}}
        <div class="fixed right-4 top-5 z-[9999] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6">
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-x-8 opacity-0 scale-95"
                    x-transition:enter-end="translate-x-0 opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-x-0 opacity-100 scale-100"
                    x-transition:leave-end="translate-x-8 opacity-0 scale-95"
                    class="flex items-start gap-3 rounded-[2rem] border p-4 shadow-2xl backdrop-blur-xl"
                    :class="{
                        'border-terracota/30 bg-[#E6DDD3]/95 text-terracota': toast.type === 'error',
                        'border-[#8DA280]/40 bg-[#E6DDD3]/95 text-[#5F7555]': toast.type === 'success'
                    }"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl"
                         :class="toast.type === 'success' ? 'bg-[#8DA280]/15' : 'bg-terracota/10'">
                        <i class="ph-bold text-2xl"
                           :class="toast.type === 'success' ? 'ph-check-circle' : 'ph-warning-circle'"></i>
                    </div>

                    <p class="pt-1 text-sm font-black leading-5" x-text="toast.message"></p>

                    <button type="button" @click="removeToast(toast.id)" class="ml-auto rounded-full p-1 opacity-60 transition hover:bg-white/50 hover:opacity-100">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
            </template>
        </div>

        <main class="relative z-10 flex min-h-screen items-start justify-center px-4 pt-8 pb-8 lg:pt-10">
            <section class="relative grid w-full max-w-7xl overflow-hidden rounded-[3.8rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 shadow-[0_45px_100px_rgba(47,62,92,0.28)] backdrop-blur-xl lg:grid-cols-[1.08fr_0.92fr]">

                {{-- Lado visual --}}
                <aside class="relative hidden min-h-[680px] overflow-hidden bg-[#C7B5A3] p-10 lg:block">
                    <div class="absolute -left-32 top-20 h-[36rem] w-[36rem] rounded-full bg-[#E6DDD3]/70 blur-[80px]"></div>
                    <div class="absolute -right-52 top-0 h-full w-[75%] rounded-l-[60%] bg-[#E6DDD3]"></div>
                    <div class="absolute bottom-[-8rem] right-16 h-[26rem] w-[26rem] rounded-full bg-[#8DA280]/25 blur-[90px]"></div>

                    <div class="relative z-10 flex items-center gap-4">
                        <img src="{{ asset('storage/images/LOGO.png') }}"
                             alt="CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS"
                             class="h-16 w-auto object-contain drop-shadow-lg">
                        <div>
                            <h1 class="text-xl font-black leading-tight text-azul-profundo">CENTRO GERIÁTRICO<br>JARDÍN DE LOS RECUERDOS</h1>
                            <p class="mt-1 text-xs font-black uppercase tracking-[0.25em] text-[#3F7D5A]">Portal Institucional</p>
                        </div>
                    </div>

                    <div class="relative z-10 mt-14">
                        <span class="inline-flex items-center gap-2 rounded-full border border-terracota/25 bg-[#D5C7B9]/80 px-4 py-2 text-xs font-black uppercase tracking-wider text-terracota">
                            <span class="h-2 w-2 rounded-full bg-terracota"></span>
                            Portal institucional
                        </span>

                        <h2 class="mt-7 max-w-2xl text-6xl font-black leading-[1.02] tracking-tight text-azul-profundo text-shadow-deep">
                            Cuidado,
                            <span class="text-terracota">memoria</span>
                            y dignidad.
                        </h2>

                        <p class="mt-6 max-w-xl text-xl font-bold leading-8 text-azul-profundo/75">
                            Un espacio seguro para acompañar el bienestar, registrar el seguimiento y proteger la memoria de nuestros adultos mayores.
                        </p>
                    </div>

                    <div class="relative z-10 mt-12 grid grid-cols-2 gap-5">
                        <div class="rounded-[2.4rem] border border-[#C7B5A3] bg-[#D5C7B9]/85 p-6 shadow-lg transition duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <i class="ph-fill ph-heartbeat mb-4 block text-4xl text-terracota"></i>
                            <h3 class="text-xl font-black text-azul-profundo">Bienestar</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-azul-profundo/65">Atención cálida, cercana y humana.</p>
                        </div>

                        <div class="rounded-[2.4rem] border border-[#C7B5A3] bg-[#D5C7B9]/85 p-6 shadow-lg transition duration-500 hover:-translate-y-2 hover:shadow-2xl">
                            <i class="ph-fill ph-brain mb-4 block text-4xl text-[#8DA280]"></i>
                            <h3 class="text-xl font-black text-azul-profundo">Memoria</h3>
                            <p class="mt-2 text-sm font-bold leading-6 text-azul-profundo/65">Seguimiento cognitivo preventivo.</p>
                        </div>
                    </div>

                    <div class="relative z-10 mt-8 h-60 overflow-hidden rounded-[3rem] border-8 border-[#D5C7B9] shadow-[0_28px_60px_rgba(47,62,92,0.25)] group">
                        <img
                            src="{{ asset('storage/images/adultos-mayores.jpg') }}"
                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1581579438747-104c53d7fbc4?auto=format&fit=crop&w=1200&q=80';"
                            alt="Adultos mayores"
                            class="h-full w-full object-cover transition duration-1000 group-hover:scale-110"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-azul-profundo/35 to-transparent"></div>
                    </div>

                    <i class="ph-fill ph-leaf float-auth absolute bottom-10 left-10 text-7xl text-[#8DA280]/30"></i>
                    <i class="ph-fill ph-heart float-auth absolute right-24 top-[42%] text-5xl text-terracota/30"></i>
                    <i class="ph-fill ph-flower float-auth absolute bottom-28 right-32 text-6xl text-[#967B66]/25"></i>
                </aside>

                {{-- Lado formulario --}}
                <section class="relative min-h-[680px] overflow-hidden bg-[#D5C7B9] px-6 py-8 sm:px-10 lg:px-14">
                    <div class="absolute -right-28 -top-28 h-72 w-72 rounded-full bg-terracota/15 blur-[85px]"></div>
                    <div class="absolute -bottom-28 left-0 h-72 w-72 rounded-full bg-[#8DA280]/20 blur-[85px]"></div>

                    <div class="relative z-10 flex h-full min-h-[610px] items-start pt-8 lg:pt-10">
                        <div class="relative w-full">

                            {{-- Login --}}
                            <div
                                x-show="panel === 'login'"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 translate-x-10 scale-[0.98]"
                                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                                x-transition:leave="transition ease-in duration-300 absolute inset-0"
                                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-x-10 scale-[0.98]"
                                class="w-full"
                            >
                                <div class="mb-8">
                                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-[1.6rem] bg-terracota text-white shadow-[0_18px_35px_rgba(233,122,95,0.35)]">
                                        <i class="ph-bold ph-lock-key text-4xl"></i>
                                    </div>

                                    <h2 class="text-5xl font-black tracking-tight text-azul-profundo text-shadow-title">
                                        Iniciar sesión
                                    </h2>

                                    <p class="mt-3 max-w-md text-base font-bold leading-7 text-azul-profundo/65">
                                        Accede al portal institucional de seguimiento y cuidado.
                                    </p>
                                </div>

                                @if ($errors->any())
                                    <div class="mb-5 rounded-[2rem] border border-terracota/30 bg-terracota/10 p-4 text-sm font-black text-terracota">
                                        Verifica tu correo o contraseña e inténtalo nuevamente.
                                    </div>
                                @endif

                                @if (session('status'))
                                    <div class="mb-5 rounded-[2rem] border border-[#8DA280]/40 bg-[#8DA280]/10 p-4 text-sm font-black text-[#5F7555]">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}" class="space-y-5" @submit="submitLogin($event)">
                                    @csrf

                                    <div>
                                        <label for="correo" class="mb-2 block text-lg font-black text-azul-profundo">Correo institucional</label>

                                        <div class="relative">
                                            <input
                                                id="correo"
                                                type="email"
                                                name="correo"
                                                x-model="correo"
                                                @input="correoTouched = true; correo = correo.trimStart()"
                                                @blur="correoTouched = true"
                                                required
                                                maxlength="120"
                                                autofocus
                                                autocomplete="username"
                                                placeholder="admin@jardindelosrecuerdos.org"
                                                class="w-full rounded-full border-2 bg-[#E6DDD3] px-6 py-4 pr-14 text-base font-extrabold text-azul-profundo placeholder:text-azul-profundo/35 outline-none shadow-inner transition duration-300 focus:ring-4"
                                                :class="{
                                                    'border-terracota focus:ring-terracota/20': correoError,
                                                    'border-[#8DA280] focus:ring-[#8DA280]/25': correoTouched && correo && !correoError,
                                                    'border-transparent focus:border-azul-clinico focus:ring-azul-clinico/20': !correoTouched || (!correo && !correoError)
                                                }"
                                            >

                                            <span class="absolute inset-y-0 right-5 flex items-center" x-show="correoTouched && correo">
                                                <i class="ph-bold text-xl" :class="correoError ? 'ph-x-circle text-terracota' : 'ph-check-circle text-[#8DA280]'"></i>
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm font-black text-terracota" x-show="correoError" x-text="correoError"></p>
                                    </div>

                                    <div>
                                        <label for="password" class="mb-2 block text-lg font-black text-azul-profundo">Contraseña</label>

                                        <div class="relative">
                                            <input
                                                id="password"
                                                :type="showPassword ? 'text' : 'password'"
                                                name="password"
                                                x-model="password"
                                                @input="passwordTouched = true"
                                                @blur="passwordTouched = true"
                                                required
                                                minlength="8"
                                                maxlength="255"
                                                autocomplete="current-password"
                                                placeholder="Ingresa tu contraseña"
                                                class="w-full rounded-full border-2 bg-[#E6DDD3] px-6 py-4 pr-24 text-base font-extrabold text-azul-profundo placeholder:text-azul-profundo/35 outline-none shadow-inner transition duration-300 focus:ring-4"
                                                :class="{
                                                    'border-terracota focus:ring-terracota/20': passwordError,
                                                    'border-[#8DA280] focus:ring-[#8DA280]/25': passwordTouched && password && !passwordError,
                                                    'border-transparent focus:border-azul-clinico focus:ring-azul-clinico/20': !passwordTouched || (!password && !passwordError)
                                                }"
                                            >

                                            <div class="absolute inset-y-0 right-5 flex items-center gap-2">
                                                <span x-show="passwordTouched && password">
                                                    <i class="ph-bold text-xl" :class="passwordError ? 'ph-x-circle text-terracota' : 'ph-check-circle text-[#8DA280]'"></i>
                                                </span>

                                                <button type="button" @click="showPassword = !showPassword" class="rounded-full p-1 text-azul-profundo/45 transition hover:bg-[#D5C7B9]/70 hover:text-terracota">
                                                    <i class="ph-bold text-xl" :class="showPassword ? 'ph-eye-closed' : 'ph-eye'"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <p class="mt-2 text-sm font-black text-terracota" x-show="passwordError" x-text="passwordError"></p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded border-[#C7B5A3] bg-[#E6DDD3] text-terracota focus:ring-terracota/30">
                                            <span class="text-sm font-black text-azul-profundo/65">Recordarme</span>
                                        </label>

                                        <button type="button" @click="panel = 'recover'" class="text-sm font-black text-terracota transition hover:text-azul-profundo hover:underline">
                                            ¿Olvidaste tu contraseña?
                                        </button>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="isSubmitting"
                                        class="group relative w-full overflow-hidden rounded-full bg-terracota px-6 py-4 text-lg font-black text-white shadow-[0_15px_35px_rgba(233,122,95,0.35)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_40px_rgba(233,122,95,0.45)] active:scale-[0.98] disabled:opacity-70"
                                    >
                                        <span class="relative z-10 flex items-center justify-center gap-3" x-show="!isSubmitting">
                                            Entrar al Portal
                                            <i class="ph-bold ph-arrow-right transition group-hover:translate-x-1"></i>
                                        </span>

                                        <span class="relative z-10 flex items-center justify-center gap-3" x-show="isSubmitting">
                                            <i class="ph-bold ph-circle-notch animate-spin"></i>
                                            Ingresando...
                                        </span>

                                        <div class="absolute inset-0 translate-y-full bg-azul-profundo transition duration-500 group-hover:translate-y-0"></div>
                                    </button>
                                </form>
                            </div>

                            {{-- Recuperación --}}
                            <div
                                x-show="panel === 'recover'"
                                x-transition:enter="transition ease-out duration-500"
                                x-transition:enter-start="opacity-0 translate-x-10 scale-[0.98]"
                                x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                                x-transition:leave="transition ease-in duration-300 absolute inset-0"
                                x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-x-10 scale-[0.98]"
                                style="display:none;"
                                class="w-full"
                            >
                                <div class="mb-8">
                                    <button
                                        type="button"
                                        @click="panel = 'login'"
                                        class="mb-7 inline-flex items-center gap-2 rounded-full bg-[#E6DDD3] px-4 py-2 text-sm font-black text-azul-profundo shadow-md transition hover:-translate-x-1 hover:text-terracota"
                                    >
                                        <i class="ph-bold ph-arrow-left"></i>
                                        Volver
                                    </button>

                                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-[1.6rem] bg-azul-profundo text-white shadow-[0_18px_35px_rgba(47,62,92,0.25)]">
                                        <i class="ph-bold ph-key-return text-4xl"></i>
                                    </div>

                                    <h2 class="text-5xl font-black tracking-tight text-azul-profundo text-shadow-title">
                                        Recuperar acceso
                                    </h2>

                                    <p class="mt-3 max-w-md text-base font-bold leading-7 text-azul-profundo/65">
                                        Te enviaremos instrucciones para restablecer tu contraseña institucional.
                                    </p>
                                </div>

                                <div class="mb-6 rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/70 p-5 shadow-md">
                                    <div class="flex gap-4">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#8DA280]/15 text-[#8DA280]">
                                            <i class="ph-bold ph-shield-check text-2xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-black text-azul-profundo">Recuperación segura</h3>
                                            <p class="mt-1 text-sm font-bold leading-6 text-azul-profundo/60">
                                                Usa el correo registrado por administración. Si no recuerdas tu correo, comunícate con el administrador.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                                    @csrf

                                    <div>
                                        <label for="recover_correo" class="mb-2 block text-lg font-black text-azul-profundo">
                                            Correo institucional
                                        </label>

                                        <input
                                            id="recover_correo"
                                            type="email"
                                            name="correo"
                                            :value="correo"
                                            required
                                            maxlength="120"
                                            autocomplete="username"
                                            placeholder="Ingresa tu correo"
                                            class="w-full rounded-full border-2 border-transparent bg-[#E6DDD3] px-6 py-4 text-base font-extrabold text-azul-profundo placeholder:text-azul-profundo/35 outline-none shadow-inner transition duration-300 focus:border-azul-clinico focus:ring-4 focus:ring-azul-clinico/20"
                                        >
                                    </div>

                                    <button
                                        type="submit"
                                        class="w-full rounded-full bg-azul-profundo px-6 py-4 text-lg font-black text-white shadow-[0_15px_35px_rgba(47,62,92,0.28)] transition duration-300 hover:-translate-y-1 hover:bg-terracota active:scale-[0.98]"
                                    >
                                        Enviar instrucciones
                                    </button>
                                </form>

                                <p class="mt-7 text-center text-sm font-bold leading-6 text-azul-profundo/60">
                                    Por seguridad, no confirmaremos si el correo existe. Revisa tu bandeja principal y spam.
                                </p>
                            </div>

                        </div>
                    </div>
                </section>
            </section>
        </main>
    </div>
</x-guest-layout>
