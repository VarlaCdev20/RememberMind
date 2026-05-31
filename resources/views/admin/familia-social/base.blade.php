<x-sistema-layout>
    <section class="min-h-[calc(100vh-7rem)] bg-fondo-panel px-4 py-5 text-titulo sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-5">
            <div class="overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
                <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-borde-focus bg-estado-peligroBg px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-boton-acento">
                            <i class="ph-bold ph-house-line text-sm"></i>
                            Familia y Social
                        </span>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo sm:text-3xl">{{ $titulo }}</h1>
                        <p class="mt-1 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">{{ $descripcion }}</p>
                    </div>

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-fondo-panel text-boton-acento shadow-sm">
                        <i class="ph-bold {{ $icono }} text-2xl"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[1.35rem] border border-dashed border-borde-suave bg-fondo-panel p-8 text-center shadow-sm backdrop-blur-xl">
                <i class="ph-bold ph-wrench text-4xl text-apoyo"></i>
                <h2 class="mt-3 text-base font-black text-titulo">Vista base preparada</h2>
                <p class="mx-auto mt-1 max-w-2xl text-sm font-bold leading-relaxed text-apoyo">
                    Esta entrada queda lista para una proxima fase funcional. La reorganizacion actual solo ajusta la navegacion del modulo.
                </p>
            </div>
        </div>
    </section>
</x-sistema-layout>
