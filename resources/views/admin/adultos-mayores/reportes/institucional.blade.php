<x-app-layout>
    <div class="p-6">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-black text-titulo uppercase tracking-tighter">Reporte de Gestión e Impacto Institucional</h1>
            <p class="mt-2 text-lg font-bold text-apoyo">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS — RememberMind System</p>
            <div class="mx-auto mt-4 h-1.5 w-32 rounded-full bg-gradient-to-r from-[#E27D60] to-[#5B5F97]"></div>
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            {{-- Sección de Métricas de Impacto --}}
            <div class="space-y-6">
                <h2 class="text-xl font-black text-titulo border-b border-borde pb-2 uppercase tracking-widest flex items-center gap-2">
                    <i class="ph-bold ph-chart-pie-slice text-boton-acento"></i> Resumen de Operaciones
                </h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-3xl border border-borde bg-fondo-card p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase text-apoyo">Atenciones Médicas</p>
                        <h3 class="mt-2 text-3xl font-black text-parrafo">{{ $stats['impacto_salud'] }}</h3>
                        <p class="text-xs font-bold text-apoyo">Servicios brindados</p>
                    </div>
                    <div class="rounded-3xl border border-borde bg-fondo-card p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase text-apoyo">Actividades Realizadas</p>
                        <h3 class="mt-2 text-3xl font-black text-parrafo">{{ $stats['impacto_social'] }}</h3>
                        <p class="text-xs font-bold text-apoyo">Talleres y terapias</p>
                    </div>
                    <div class="rounded-3xl border border-borde bg-fondo-card p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase text-apoyo">Monitoreo Cognitivo</p>
                        <h3 class="mt-2 text-3xl font-black text-parrafo">{{ $stats['seguimiento_cognitivo'] }}</h3>
                        <p class="text-xs font-bold text-apoyo">Evaluaciones MoCA/MMSE</p>
                    </div>
                    <div class="rounded-3xl border border-borde bg-fondo-card p-6 shadow-sm">
                        <p class="text-[10px] font-black uppercase text-apoyo">Índice de Confianza</p>
                        <h3 class="mt-2 text-3xl font-black text-boton-acento">{{ $stats['efectividad'] }}%</h3>
                        <p class="text-xs font-bold text-apoyo">Satisfacción familiar</p>
                    </div>
                </div>
            </div>

            {{-- Sección de Visualización de Crecimiento --}}
            <div class="rounded-[2.5rem] border border-borde bg-boton-principal p-8 text-inverso shadow-xl">
                <h2 class="text-xl font-black text-inverso mb-6 uppercase tracking-widest flex items-center gap-2">
                    <i class="ph-bold ph-trend-up text-boton-acento"></i> Crecimiento Institucional
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-xs font-black uppercase">Población Atendida</span>
                            <span class="text-xs font-black">{{ $stats['poblacion'] }} residentes</span>
                        </div>
                        <div class="h-3 w-full rounded-full bg-fondo-card/10 overflow-hidden">
                            <div class="h-full rounded-full bg-boton-acento" style="width: {{ min(100, ($stats['poblacion']/50)*100) }}%"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-fondo-card/5 p-5 border border-white/10">
                        <h4 class="text-sm font-black mb-3">Misión Cumplida</h4>
                        <p class="text-sm font-medium text-inverso/70 leading-relaxed">
                            "CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS" se consolida como el referente en cuidado cognitivo regional, utilizando RememberMind para garantizar que ningún adulto mayor pierda su identidad sin un acompañamiento profesional.
                        </p>
                    </div>
                </div>

                <div class="mt-10 flex justify-center">
                    <button onclick="window.print()" class="rounded-full bg-fondo-card px-8 py-3 text-sm font-black text-titulo shadow-lg transition hover:scale-105 active:scale-95">
                        <i class="ph-bold ph-printer mr-2"></i> Descargar Reporte PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-apoyo">Documento Informativo Institucional — {{ date('Y') }}</p>
        </div>
    </div>

    <style>
        @media print {
            body { background: white !important; }
            aside, nav { display: none !important; }
            .p-6 { padding: 0 !important; }
            .shadow-xl, .shadow-sm { shadow: none !important; }
            .rounded-[2.5rem] { border-radius: 0 !important; }
            .bg-boton-principal { background-color: #f8f8f8 !important; color: black !important; border: 1px solid #ccc !important; }
            .text-inverso { color: black !important; }
        }
    </style>
</x-app-layout>
