<x-guest-layout>
    <div class="min-h-screen bg-[linear-gradient(135deg,#f8f1ea_0%,#efe1d4_45%,#dbeaf5_100%)] relative overflow-hidden">

        <div class="absolute -top-24 -left-20 h-80 w-80 rounded-full bg-[#8fb7d9]/30 blur-3xl"></div>
        <div class="absolute top-1/3 -right-20 h-96 w-96 rounded-full bg-[#9b8ac7]/25 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-[#e27d60]/20 blur-3xl"></div>

        <div class="relative z-10 mx-auto flex min-h-screen max-w-7xl items-center px-6 py-10 lg:px-10">
            <div class="grid w-full items-center gap-10 lg:grid-cols-[0.95fr_1.05fr]">

                {{-- Lado visual --}}
                <div class="hidden lg:block">
                    <div class="overflow-hidden rounded-[2.7rem] bg-white/45 p-6 shadow-2xl backdrop-blur-md ring-1 ring-white/60">
                        <img
                            src="https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&w=1200&q=80"
                            alt="Adultos mayores en acompañamiento"
                            class="h-[620px] w-full rounded-[2rem] object-cover"
                        >

                        <div class="-mt-36 ml-6 max-w-md rounded-[2rem] bg-[#5a3e36]/80 p-6 text-white backdrop-blur-md">
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#e9c7bc]">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</p>
                            <h2 class="mt-3 text-4xl font-extrabold leading-tight">
                                Registro institucional para un cuidado más organizado
                            </h2>
                            <p class="mt-4 text-sm leading-7 text-white/85">
                                Crea una cuenta para acceder al seguimiento, registro y apoyo continuo de adultos mayores.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Formulario --}}
                <div class="w-full">
                    <div class="mx-auto max-w-xl rounded-[2.4rem] border border-white/70 bg-white/80 p-8 shadow-2xl backdrop-blur-md sm:p-10">

                        <div class="mb-8 text-center">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#5a3e36] text-white shadow-lg">
                                <span class="text-xl font-extrabold">+</span>
                            </div>

                            <h1 class="text-4xl font-extrabold tracking-tight text-[#463730]">
                                Crear cuenta
                            </h1>

                            <p class="mt-3 text-sm font-medium leading-7 text-[#7b6f68]">
                                Completa tus datos para registrarte en la plataforma RememberMind.
                            </p>
                        </div>

                        <x-validation-errors class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" />

                        <form method="POST" action="{{ route('register') }}" x-data="{ showPassword: false, showConfirm: false, loading: false }" x-on:submit="loading = true" class="space-y-5">
                            @csrf

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-label for="nombres" value="Nombres" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                    <x-input
                                        id="nombres"
                                        class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 text-[15px] font-semibold text-[#463730] transition focus:border-[#8fb7d9] focus:ring-[#8fb7d9]"
                                        type="text"
                                        name="nombres"
                                        :value="old('nombres')"
                                        required
                                        autofocus
                                        autocomplete="given-name"
                                        placeholder="Ej. Carla Valeria"
                                    />
                                </div>

                                <div>
                                    <x-label for="ap_paterno" value="Apellido paterno" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                    <x-input
                                        id="ap_paterno"
                                        class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 text-[15px] font-semibold text-[#463730] transition focus:border-[#8fb7d9] focus:ring-[#8fb7d9]"
                                        type="text"
                                        name="ap_paterno"
                                        :value="old('ap_paterno')"
                                        required
                                        autocomplete="family-name"
                                        placeholder="Ej. Encinas"
                                    />
                                </div>
                            </div>

                            <div>
                                <x-label for="ap_materno" value="Apellido materno opcional" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                <x-input
                                    id="ap_materno"
                                    class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 text-[15px] font-semibold text-[#463730] transition focus:border-[#8fb7d9] focus:ring-[#8fb7d9]"
                                    type="text"
                                    name="ap_materno"
                                    :value="old('ap_materno')"
                                    autocomplete="additional-name"
                                    placeholder="Ej. Cano"
                                />
                            </div>

                            <div>
                                <x-label for="correo" value="Correo electrónico" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                <x-input
                                    id="correo"
                                    class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 text-[15px] font-semibold text-[#463730] transition focus:border-[#8fb7d9] focus:ring-[#8fb7d9]"
                                    type="email"
                                    name="correo"
                                    :value="old('correo')"
                                    required
                                    autocomplete="username"
                                    placeholder="usuario@correo.com"
                                />
                                <p class="mt-2 text-xs font-medium text-[#8b776d]">
                                    Usa un correo válido para acceder posteriormente al sistema.
                                </p>
                            </div>

                            <div class="grid gap-5 md:grid-cols-2">
                                <div>
                                    <x-label for="password" value="Contraseña" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                    <div class="relative">
                                        <x-input
                                            id="password"
                                            class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 pr-16 text-[15px] font-semibold text-[#463730] transition focus:border-[#9b8ac7] focus:ring-[#9b8ac7]"
                                            x-bind:type="showPassword ? 'text' : 'password'"
                                            name="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Mínimo 8 caracteres"
                                        />

                                        <button type="button"
                                                x-on:click="showPassword = !showPassword"
                                                class="absolute inset-y-0 right-0 pr-4 text-sm font-bold text-[#8b776d] transition hover:text-[#5a3e36]">
                                            <span x-show="!showPassword">Ver</span>
                                            <span x-show="showPassword">Ocultar</span>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <x-label for="password_confirmation" value="Confirmar contraseña" class="mb-2 block text-sm font-extrabold text-[#5a4a42]" />
                                    <div class="relative">
                                        <x-input
                                            id="password_confirmation"
                                            class="block w-full rounded-2xl border-[#e7ddd4] bg-[#fcfaf8] py-3.5 pr-16 text-[15px] font-semibold text-[#463730] transition focus:border-[#9b8ac7] focus:ring-[#9b8ac7]"
                                            x-bind:type="showConfirm ? 'text' : 'password'"
                                            name="password_confirmation"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Repite la contraseña"
                                        />

                                        <button type="button"
                                                x-on:click="showConfirm = !showConfirm"
                                                class="absolute inset-y-0 right-0 pr-4 text-sm font-bold text-[#8b776d] transition hover:text-[#5a3e36]">
                                            <span x-show="!showConfirm">Ver</span>
                                            <span x-show="showConfirm">Ocultar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                <div class="rounded-2xl bg-[#f7f1ea] p-4">
                                    <label for="terms" class="flex items-start gap-3">
                                        <x-checkbox name="terms" id="terms" required class="mt-1 rounded border-[#d8ccc0] text-[#5a3e36] focus:ring-[#8fb7d9]" />

                                        <span class="text-sm font-medium leading-6 text-[#6f6761]">
                                            {!! __('Acepto los :terms_of_service y la :privacy_policy', [
                                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="font-bold text-[#e27d60] hover:text-[#c96b50]">Términos de Servicio</a>',
                                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="font-bold text-[#e27d60] hover:text-[#c96b50]">Política de Privacidad</a>',
                                            ]) !!}
                                        </span>
                                    </label>
                                </div>
                            @endif

                            <button
                                type="submit"
                                class="w-full rounded-full bg-[#5a3e36] px-6 py-3.5 text-base font-extrabold text-white shadow-lg transition duration-300 hover:-translate-y-0.5 hover:bg-[#4a322b] focus:outline-none focus:ring-2 focus:ring-[#8fb7d9] focus:ring-offset-2"
                                x-bind:disabled="loading"
                                x-bind:class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                            >
                                <span x-show="!loading">Crear cuenta</span>
                                <span x-show="loading">Registrando...</span>
                            </button>

                            <div class="text-center">
                                <p class="text-sm font-medium text-[#7b6f68]">
                                    ¿Ya tienes una cuenta?
                                    <a href="{{ route('login') }}" class="font-extrabold text-[#e27d60] transition hover:text-[#c96b50]">
                                        Inicia sesión
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-guest-layout>