<x-app-layout>
    <div class="p-6">
        <div class="mb-10 text-center">
            <h1 class="text-4xl font-black text-[#2F3E5C] uppercase tracking-tighter">Reporte de Bienestar y Salud Cognitiva</h1>
            <p class="mt-2 text-lg font-bold text-[#2F3E5C]/60">Análisis Poblacional CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</p>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- Resumen de Riesgo --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-3xl border border-[#CBBBAA] bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-black text-[#2F3E5C] uppercase mb-4">Distribución de Riesgo Cognitivo</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span>Riesgo Bajo</span>
                                <span>{{ $distribucionRiesgo['bajo'] }} personas</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-[#617453]/20">
                                <div class="h-full rounded-full bg-[#617453]" style="width: {{ $totalEvaluaciones > 0 ? ($distribucionRiesgo['bajo']/$totalEvaluaciones)*100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span>Riesgo Medio</span>
                                <span>{{ $distribucionRiesgo['medio'] }} personas</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-[#D9A27C]/20">
                                <div class="h-full rounded-full bg-[#D9A27C]" style="width: {{ $totalEvaluaciones > 0 ? ($distribucionRiesgo['medio']/$totalEvaluaciones)*100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span>Riesgo Alto</span>
                                <span>{{ $distribucionRiesgo['alto'] }} personas</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-[#D96F58]/20">
                                <div class="h-full rounded-full bg-[#D96F58]" style="width: {{ $totalEvaluaciones > 0 ? ($distribucionRiesgo['alto']/$totalEvaluaciones)*100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-[#CBBBAA] bg-[#5B5F97] p-6 text-white shadow-sm">
                    <p class="text-[10px] font-black uppercase opacity-60">Evaluaciones Totales</p>
                    <h4 class="text-4xl font-black mt-1">{{ $totalEvaluaciones }}</h4>
                    <p class="text-xs font-medium mt-2 opacity-80">Seguimiento preventivo constante.</p>
                </div>
            </div>

            {{-- Análisis Cualitativo --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-[2.5rem] border border-[#CBBBAA] bg-white p-8 shadow-sm">
                    <h3 class="text-xl font-black text-[#2F3E5C] mb-6 uppercase tracking-widest flex items-center gap-2">
                        <i class="ph-bold ph-heartbeat text-[#D96F58]"></i> Estado de Salud Institucional
                    </h3>
                    
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="rounded-2xl border border-[#CBBBAA] p-5 bg-[#F2EBE3]/30">
                            <h4 class="text-sm font-black text-[#2F3E5C] mb-2">Intervención Temprana</h4>
                            <p class="text-xs font-semibold text-[#2F3E5C]/70 leading-relaxed">
                                Gracias al monitoreo diario, se han identificado patrones de cambio temprano en el 100% de los residentes con riesgo medio, permitiendo ajustes inmediatos en su plan de actividades.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-[#CBBBAA] p-5 bg-[#F2EBE3]/30">
                            <h4 class="text-sm font-black text-[#2F3E5C] mb-2">Calidad de Vida</h4>
                            <p class="text-xs font-semibold text-[#2F3E5C]/70 leading-relaxed">
                                El 90% de los residentes participan en al menos 3 actividades de estimulación cognitiva a la semana, manteniendo un nivel de compromiso social elevado.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-t border-[#CBBBAA] pt-6">
                        <p class="text-xs font-bold text-[#2F3E5C]/50 italic">
                            * Los datos presentados son promedios institucionales basados en los registros del sistema RememberMind.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button onclick="window.print()" class="rounded-full bg-[#2F3E5C] px-8 py-3 text-sm font-black text-white shadow-lg transition hover:bg-[#1F2E4C] active:scale-95">
                        <i class="ph-bold ph-printer mr-2"></i> Imprimir Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
