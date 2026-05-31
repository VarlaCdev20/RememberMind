{{-- TAB REPORTES DEL ADULTO MAYOR (RESUMEN) --}}
<section
    x-show="tab === 'reportes'"
    style="display: none;"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#8C5A48]/10 text-[#8C5A48]">
                <i class="ph-fill ph-file-pdf text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Reportes del Adulto Mayor</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Centro de exportación y generación de reportes individuales.</p>
            </div>
        </div>
    </div>

    <!-- GRID DE REPORTES INDIVIDUALES -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        
        <!-- REPORTE 360 INTEGRAL -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-gradient-to-br from-[#2F3E5C] to-[#4A5D8A] p-6 shadow-sm flex flex-col justify-between text-white transition hover:-translate-y-1 hover:shadow-lg">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 text-white mb-4">
                    <i class="ph-bold ph-identification-card text-2xl"></i>
                </div>
                <h3 class="text-lg font-black">Expediente Integral 360°</h3>
                <p class="text-xs font-semibold text-white/70 mt-2">Documento consolidado con todos los datos institucionales, de salud, cognitivos y de participación del adulto mayor.</p>
            </div>
            <div class="mt-6">
                <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'format' => 'pdf']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white text-[#2F3E5C] px-4 py-2.5 text-xs font-black uppercase tracking-wider shadow-sm transition hover:bg-white/90 active:scale-95">
                    <i class="ph-bold ph-download-simple"></i> Generar Reporte Completo
                </a>
            </div>
        </div>

        <!-- REPORTE DE SALUD -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between transition hover:-translate-y-1 hover:shadow-lg hover:border-[#C45F4B]/40">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#C45F4B]/10 text-[#C45F4B] mb-4">
                    <i class="ph-bold ph-heartbeat text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-[#2F3E5C]">Reporte de Salud</h3>
                <p class="text-xs font-semibold text-[#2F3E5C]/60 mt-2">Historial médico, control de signos vitales, valoración funcional y esquema de medicación.</p>
            </div>
            <div class="mt-6 border-t border-[#CBBBAA]/20 pt-4">
                <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'tipo' => 'salud', 'format' => 'pdf']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-[#C45F4B]/30 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#C45F4B] shadow-sm transition hover:bg-[#C45F4B]/10 active:scale-95">
                    <i class="ph-bold ph-file-pdf"></i> Descargar Reporte
                </a>
            </div>
        </div>

        <!-- REPORTE COGNITIVO -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between transition hover:-translate-y-1 hover:shadow-lg hover:border-[#5B5F97]/40">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#5B5F97]/10 text-[#5B5F97] mb-4">
                    <i class="ph-bold ph-brain text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-[#2F3E5C]">Reporte Cognitivo</h3>
                <p class="text-xs font-semibold text-[#2F3E5C]/60 mt-2">Resultados de evaluaciones MoCA y MMSE, evolución y alertas de deterioro cognitivo.</p>
            </div>
            <div class="mt-6 border-t border-[#CBBBAA]/20 pt-4">
                <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'tipo' => 'cognitivo', 'format' => 'pdf']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-[#5B5F97]/30 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#5B5F97] shadow-sm transition hover:bg-[#5B5F97]/10 active:scale-95">
                    <i class="ph-bold ph-file-pdf"></i> Descargar Reporte
                </a>
            </div>
        </div>

        <!-- REPORTE DE ACTIVIDADES -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between transition hover:-translate-y-1 hover:shadow-lg hover:border-[#A86B3C]/40">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#A86B3C]/10 text-[#A86B3C] mb-4">
                    <i class="ph-bold ph-users-three text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-[#2F3E5C]">Reporte de Participación</h3>
                <p class="text-xs font-semibold text-[#2F3E5C]/60 mt-2">Historial de asistencia a actividades, atenciones recibidas y observaciones institucionales.</p>
            </div>
            <div class="mt-6 border-t border-[#CBBBAA]/20 pt-4">
                <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'tipo' => 'actividades', 'format' => 'pdf']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-[#A86B3C]/30 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#A86B3C] shadow-sm transition hover:bg-[#A86B3C]/10 active:scale-95">
                    <i class="ph-bold ph-file-pdf"></i> Descargar Reporte
                </a>
            </div>
        </div>

        <!-- REPORTE DOCUMENTAL -->
        <div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm flex flex-col justify-between transition hover:-translate-y-1 hover:shadow-lg hover:border-[#8EA17D]/40">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#8EA17D]/10 text-[#617453] mb-4">
                    <i class="ph-bold ph-folder-open text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-[#2F3E5C]">Reporte Documental</h3>
                <p class="text-xs font-semibold text-[#2F3E5C]/60 mt-2">Índice del expediente físico y digital con el registro de documentos activos adjuntos al perfil.</p>
            </div>
            <div class="mt-6 border-t border-[#CBBBAA]/20 pt-4">
                <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $idAdulto, 'tipo' => 'documentos', 'format' => 'pdf']) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-[#8EA17D]/30 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#617453] shadow-sm transition hover:bg-[#8EA17D]/10 active:scale-95">
                    <i class="ph-bold ph-file-pdf"></i> Descargar Reporte
                </a>
            </div>
        </div>
        
    </div>
</section>
