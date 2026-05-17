{{-- TAB RESUMEN --}}
          
<section
    x-show="tab === 'resumen'"
    x-transition.opacity.duration.250ms
    class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]"
>
    {{-- PANEL PRINCIPAL --}}
    <div class="space-y-4">
        {{-- Datos personales --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-5 py-4">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#E27D60]">
                        Información base
                    </span>
                    <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                        Datos personales del adulto mayor
                    </h2>
                </div>

                <div class="hidden h-11 w-11 items-center justify-center rounded-2xl bg-[#E27D60]/12 text-[#E27D60] sm:flex">
                    <i class="ph-bold ph-identification-card text-xl"></i>
                </div>
            </div>

            <div class="grid gap-3 p-5 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Nombre completo
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ $nombreCompleto ?: 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Carnet de identidad
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->ci ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Edad
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ $edad ? $edad . ' años' : 'No disponible' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Fecha de nacimiento
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ $fechaNacimientoFormateada ?? optional($adultoObj)->fecha_nac ?? 'No registrada' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Género
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->genero ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Estado civil
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->estado_civil ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Nivel educativo
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->nivel_educat ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Teléfono
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->telefono ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Estado institucional
                    </p>
                    <p class="mt-2 text-sm font-black
                        {{ ($estadoTexto ?? 'ACTIVO') === 'ACTIVO' ? 'text-[#617453]' : 'text-[#7A5C49]' }}">
                        {{ ucfirst(strtolower($estadoTexto ?? 'activo')) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Información institucional --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-5 py-4">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#D9A27C]">
                        Administración
                    </span>
                    <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                        Ingreso y permanencia institucional
                    </h2>
                </div>

                <div class="hidden h-11 w-11 items-center justify-center rounded-2xl bg-[#D9A27C]/16 text-[#9B6D4C] sm:flex">
                    <i class="ph-bold ph-buildings text-xl"></i>
                </div>
            </div>

            <div class="grid gap-3 p-5 md:grid-cols-3">
                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Fecha de ingreso
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ $fechaIngresoFormateada ?? optional($adultoObj)->fecha_ing ?? 'No registrada' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Tipo de ingreso
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->tipo_ing ?? 'No registrado' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Permanencia
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->permanencia ?? 'No registrada' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Observaciones iniciales --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="flex flex-col gap-3 border-b border-[#D5C7B9] px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#9A7B60]">
                        Notas generales
                    </span>
                    <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                        Observaciones iniciales
                    </h2>
                </div>

                <button type="button"
                        @click="abrir('observacion')"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#6873A6] px-4 py-2.5 text-xs font-black text-white transition hover:-translate-y-0.5 hover:bg-[#586393] active:scale-[0.98]">
                    <i class="ph-bold ph-note-pencil"></i>
                    Agregar observación
                </button>
            </div>

            <div class="p-5">
                <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-5">
                    <p class="text-sm font-semibold leading-7 text-[#2F3E5C]/70">
                        {{ optional($adultoObj)->observaciones ?? 'No existen observaciones generales registradas para este adulto mayor.' }}
                    </p>
                </div>
            </div>
        </section>
    </div>

    {{-- PANEL LATERAL --}}
    <aside class="space-y-4">
        {{-- Dirección --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="border-b border-[#D5C7B9] px-5 py-4">
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#8EA17D]">
                    Ubicación
                </span>
                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Dirección referencial
                </h2>
            </div>

            <div class="space-y-3 p-5">
                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                    <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#8EA17D]/16 text-[#617453]">
                        <i class="ph-bold ph-map-pin text-lg"></i>
                    </div>

                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Zona
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->zona ?? 'No registrada' }}
                    </p>
                </div>

                <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                    <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#D9A27C]/18 text-[#9B6D4C]">
                        <i class="ph-bold ph-road-horizon text-lg"></i>
                    </div>

                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                        Calle / Avenida
                    </p>
                    <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                        {{ optional($adultoObj)->calle ?? 'No registrada' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Estado de completitud --}}
        <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="border-b border-[#D5C7B9] px-5 py-4">
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#6873A6]">
                    Control
                </span>
                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Estado de ficha
                </h2>
            </div>

            <div class="space-y-3 p-5">
                @php
                    $camposBase = [
                        optional($adultoObj)->nombres,
                        optional($adultoObj)->ap_paterno,
                        optional($adultoObj)->ci,
                        optional($adultoObj)->fecha_nac,
                        optional($adultoObj)->genero,
                        optional($adultoObj)->fecha_ing,
                        optional($adultoObj)->cod_est_adul,
                    ];

                    $camposCompletos = collect($camposBase)->filter(fn($valor) => filled($valor))->count();
                    $porcentajeFicha = round(($camposCompletos / count($camposBase)) * 100);
                @endphp

                <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4">
                    <div class="mb-2 flex items-center justify-between">
                        <p class="text-xs font-black text-[#2F3E5C]">
                            Completitud del registro
                        </p>

                        <span class="text-xs font-black text-[#E27D60]">
                            {{ $porcentajeFicha }}%
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-[#D5C7B9]">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8EA17D]"
                             style="width: {{ $porcentajeFicha }}%;">
                        </div>
                    </div>

                    <p class="mt-3 text-xs font-semibold leading-5 text-[#2F3E5C]/58">
                        Este indicador ayuda a verificar si la ficha cuenta con la información mínima necesaria para seguimiento institucional.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="tab = 'familiares'"
                            class="rounded-2xl bg-[#8EA17D]/16 px-3 py-3 text-xs font-black text-[#617453] transition hover:-translate-y-0.5 hover:bg-[#8EA17D] hover:text-white active:scale-[0.98]">
                        Familiares
                    </button>

                    <button type="button"
                            @click="tab = 'seguimiento'"
                            class="rounded-2xl bg-[#6873A6]/16 px-3 py-3 text-xs font-black text-[#566189] transition hover:-translate-y-0.5 hover:bg-[#6873A6] hover:text-white active:scale-[0.98]">
                        Seguimiento
                    </button>
                </div>
            </div>
        </section>
    </aside>
</section>
            