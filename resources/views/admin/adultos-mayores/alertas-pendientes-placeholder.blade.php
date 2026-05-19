<x-sistema-layout>
    <div class="relative mx-auto max-w-7xl space-y-6 py-6">
        {{-- ENCABEZADO --}}
        <section class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-6 shadow-[0_14px_32px_rgba(47,62,92,0.08)] backdrop-blur-xl">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">
                        Casa Amandita • Seguimiento Prioritario
                    </span>
                    <h1 class="mt-1.5 text-2xl font-black text-[#2F3E5C]">Alertas y Pendientes</h1>
                    <p class="mt-1.5 text-sm font-semibold text-[#2F3E5C]/70">
                        Consola de alertas cognitivas, documentales y notificaciones críticas de adultos mayores.
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-[#5B5F97] active:scale-95">
                        <i class="ph-bold ph-arrow-left text-sm"></i> Volver al Centro
                    </a>
                </div>
            </div>
        </section>

        {{-- CONTENIDO DEL PLACEHOLDER --}}
        <section class="rounded-2xl border border-[#C7B5A3] bg-white p-12 text-center shadow-sm flex flex-col items-center justify-center min-h-[400px]">
            <div class="h-20 w-20 rounded-full bg-[#E27D60]/10 text-[#E27D60] border border-[#E27D60]/30 shadow-inner flex items-center justify-center mb-5 animate-pulse">
                <i class="ph-bold ph-bell-ringing text-4xl"></i>
            </div>
            
            <h2 class="text-2xl font-black text-[#2F3E5C] tracking-tight">Módulo en Desarrollo Posterior</h2>
            <p class="mt-3 max-w-xl text-sm font-semibold leading-relaxed text-[#2F3E5C]/60">
                La consola de <strong>Alertas y Pendientes</strong> se encuentra planificada como parte de la <strong>Fase 3</strong> de la reestructuración del módulo. 
            </p>
            <p class="mt-2 max-w-xl text-xs font-medium leading-relaxed text-[#2F3E5C]/45 bg-[#E6DDD3]/30 p-3 rounded-xl border border-[#C7B5A3]/25">
                Muy pronto, en este tablero consolidado se listarán en tiempo real las alertas de documentos faltantes (CI/Consentimiento), alertas de familiares responsables sin registrar y controles cognitivos (Mini-mental) con más de 6 meses de antigüedad, permitiendo disparar acciones correctivas con un solo clic.
            </p>

            <div class="mt-8 flex gap-3">
                <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E6DDD3] border border-[#C7B5A3] px-5 py-3 text-xs font-black text-[#2F3E5C] transition hover:bg-[#D5C7B9] active:scale-95">
                    <i class="ph-bold ph-users-three text-sm"></i> Ver Adultos Mayores
                </a>
            </div>
        </section>
    </div>
</x-sistema-layout>
