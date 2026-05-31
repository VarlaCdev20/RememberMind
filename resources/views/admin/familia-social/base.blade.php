<x-sistema-layout>
    <section class="min-h-[calc(100vh-7rem)] bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-5">
            <div class="overflow-hidden rounded-[1.45rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/72 shadow-[0_16px_46px_rgba(47,62,92,0.12)] backdrop-blur-xl">
                <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">
                            <i class="ph-bold ph-house-line text-sm"></i>
                            Familia y Social
                        </span>
                        <h1 class="mt-2 text-2xl font-black tracking-tight text-[#2F3E5C] sm:text-3xl">{{ $titulo }}</h1>
                        <p class="mt-1 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">{{ $descripcion }}</p>
                    </div>

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#F3ECE4]/78 text-[#E27D60] shadow-sm">
                        <i class="ph-bold {{ $icono }} text-2xl"></i>
                    </span>
                </div>
            </div>

            <div class="rounded-[1.35rem] border border-dashed border-[#C7B5A3]/70 bg-[#F3ECE4]/75 p-8 text-center shadow-sm backdrop-blur-xl">
                <i class="ph-bold ph-wrench text-4xl text-[#2F3E5C]/25"></i>
                <h2 class="mt-3 text-base font-black text-[#2F3E5C]">Vista base preparada</h2>
                <p class="mx-auto mt-1 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/58">
                    Esta entrada queda lista para una proxima fase funcional. La reorganizacion actual solo ajusta la navegacion del modulo.
                </p>
            </div>
        </div>
    </section>
</x-sistema-layout>
