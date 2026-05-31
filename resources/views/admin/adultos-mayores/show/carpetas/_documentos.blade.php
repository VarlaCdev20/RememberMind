<div class="rounded-[24px] border border-[#CBBBAA]/60 bg-white/95 p-6 shadow-sm">
    <div class="mb-5 flex items-center justify-between border-b border-[#CBBBAA]/30 pb-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                <i class="ph-bold ph-folder-open text-xl"></i>
            </div>
            <div>
                <h2 class="text-lg font-black text-[#2F3E5C]">Expediente Documental</h2>
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide">Archivos adjuntos e identidad</p>
            </div>
        </div>
    </div>

    @if($documentosLista->count() > 0)
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 text-center">
                <div class="rounded-xl bg-[#F2EBE3]/50 p-3 border border-[#CBBBAA]/30">
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Total Documentos</span>
                    <p class="text-2xl font-black text-[#2F3E5C]">{{ $documentosLista->count() }}</p>
                </div>
                <div class="rounded-xl bg-[#F2EBE3]/50 p-3 border border-[#CBBBAA]/30">
                    <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-1">Último Ingresado</span>
                    <p class="text-sm font-black text-[#2F3E5C] mt-2">{{ $documentosLista->first()->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <div>
                <span class="block text-xs font-black uppercase tracking-wide text-[#2F3E5C]/50 mb-3">Listado Resumido</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($documentosLista->take(6) as $doc)
                        <div class="flex items-start gap-3 rounded-xl border border-[#D5C7B9]/50 bg-white p-3">
                            <i class="ph-fill ph-file-pdf text-2xl text-[#C45F4B]"></i>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $doc->titulo ?? $doc->nombre_original }}</p>
                                <p class="text-xs font-bold text-[#2F3E5C]/50 mt-0.5 uppercase tracking-wide">{{ $doc->tipo_documento ?? 'Documento' }} • {{ $doc->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($documentosLista->count() > 6)
                    <p class="text-xs text-center font-bold text-[#2F3E5C]/50 mt-4">+ {{ $documentosLista->count() - 6 }} documentos más</p>
                @endif
            </div>
            
            <div class="text-center mt-6 pt-4 border-t border-[#CBBBAA]/30">
                <p class="text-xs font-bold text-[#2F3E5C]/50 uppercase tracking-wide mb-3">Para registrar o actualizar documentos, utilice Gestión documental.</p>
                <a href="{{ route('admin.adultos-mayores.documentos.index', $idAdulto) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#1F2E4C] transition">
                    <i class="ph-bold ph-folder-notch-open text-lg"></i>
                    Gestionar Documentos
                </a>
            </div>
        </div>
    @else
        <div class="py-10 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#F2EBE3]/50 text-[#2F3E5C]/30 mb-4">
                <i class="ph-bold ph-folder-open text-3xl"></i>
            </div>
            <p class="text-sm font-bold text-[#2F3E5C]/60">No hay documentos registrados en el expediente.</p>
            <p class="mt-2 text-xs font-bold text-[#2F3E5C]/40 uppercase tracking-wide mb-4">Para registrar o actualizar documentos, utilice Gestión documental.</p>
            <a href="{{ route('admin.adultos-mayores.documentos.index', $idAdulto) }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#1F2E4C] transition">
                <i class="ph-bold ph-folder-notch-open text-lg"></i>
                Gestionar Documentos
            </a>
        </div>
    @endif
</div>
