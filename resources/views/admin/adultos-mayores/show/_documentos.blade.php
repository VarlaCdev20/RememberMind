{{-- TAB DOCUMENTOS --}}
  {{-- TAB DOCUMENTOS MEJORADO --}}
<section
    x-show="tab === 'documentos'"
    x-transition.opacity.duration.250ms
    class="space-y-4"
>
    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]">
                    Expediente institucional
                </span>

                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">
                    Documentos asociados
                </h2>

                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/55">
                    Consulta y registra documentos administrativos, personales o médicos vinculados al adulto mayor.
                </p>
            </div>

            <button type="button"
                    @click="abrir('documento')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black text-white shadow-[0_10px_20px_rgba(47,62,92,0.18)] transition hover:-translate-y-0.5 hover:bg-[#566189] active:scale-[0.98]">
                <i class="ph-bold ph-upload-simple"></i>
                Subir documento
            </button>
        </div>

        {{-- Métricas --}}
        <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Documentos
                </p>
                <p class="mt-2 text-2xl font-black text-[#2F3E5C]">
                    {{ $totalDocumentos }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Estado expediente
                </p>
                <p class="mt-2 text-sm font-black {{ $totalDocumentos > 0 ? 'text-[#617453]' : 'text-[#D96F58]' }}">
                    {{ $totalDocumentos > 0 ? 'Con respaldo' : 'Sin documentos' }}
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Tipo principal
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    Administrativo / médico
                </p>
            </div>

            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-[#2F3E5C]/45">
                    Acción sugerida
                </p>
                <p class="mt-2 text-sm font-black text-[#2F3E5C]">
                    {{ $totalDocumentos > 0 ? 'Verificar vigencia' : 'Subir documento' }}
                </p>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2F3E5C]/15 text-[#2F3E5C]">
                    <i class="ph-bold ph-folder-open text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#2F3E5C]">Expediente Digital</h3>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Documentos y respaldos legales</p>
                </div>
            </div>
            <button type="button" @click="abrir('documento')" class="rounded-xl bg-[#2F3E5C] px-4 py-2 text-xs font-black text-white hover:bg-[#566189] transition">
                <i class="ph-bold ph-upload-simple mr-1"></i> Subir
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                    <tr>
                        <th class="px-6 py-3">Documento</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Observaciones</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse($documentosActivos as $documento)
                        @php
                            $documentoObj = is_object($documento) ? $documento : null;
                            $nombreDoc = optional($documentoObj)->nom_doc ?? 'Documento';
                            $tipoDoc = strtoupper(optional($documentoObj)->tipo_doc ?? 'GENERAL');
                            $fechaDoc = optional($documentoObj)->fecha_doc;
                        @endphp
                        <tr class="group transition hover:bg-[#F2EBE3]/40">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                                        <i class="ph-bold ph-file-text text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black">{{ $nombreDoc }}</p>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/45">{{ optional($documentoObj)->extension ?? 'PDF' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-[#2F3E5C]/10 px-2.5 py-1 text-[9px] font-black">{{ $tipoDoc }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-[#2F3E5C]/60">
                                {{ $fechaDoc ? \Carbon\Carbon::parse($fechaDoc)->format('d/m/Y') : 'N/D' }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-semibold text-[#2F3E5C]/65 line-clamp-1">{{ optional($documentoObj)->observaciones ?? 'Sin notas' }}</p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1.5">
                                    @if(optional($documentoObj)->ruta_archivo)
                                        <a href="{{ Storage::url($documentoObj->ruta_archivo) }}" target="_blank" class="rounded-lg bg-[#2F3E5C]/5 p-2 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition">
                                            <i class="ph-bold ph-eye"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.adultos-mayores.documentos.destroy', [$idAdulto, $documentoObj->cod_doc_am ?? '0']) }}" method="POST" onsubmit="confirmarAccion(event, 'Archivar documento', 'El documento y su archivo físico se conservarán en el servidor, marcados como anulados.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Archivar documento" class="rounded-lg bg-terracota/5 p-2 text-terracota hover:bg-terracota hover:text-white transition">
                                            <i class="ph-bold ph-archive-box"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-xs font-bold text-[#2F3E5C]/40">Sin documentos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Documentos Archivados --}}
        @if(count($documentosArchivados) > 0)
        <div class="mt-8 border-t border-[#D5C7B9]/30 pt-6 px-4">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/60 flex items-center gap-2">
                <i class="ph-bold ph-archive-box"></i> Repositorio de Documentos Archivados
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/50 opacity-60 grayscale-[50%] transition-all hover:grayscale-0 hover:opacity-100 bg-[#E7DDD2]/40 backdrop-blur-sm shadow-sm">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3">Fecha Arch.</th>
                            <th class="px-6 py-3">Documento / Tipo</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($documentosArchivados as $docAnu)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-3 text-xs font-black text-[#2F3E5C]/60">
                                    {{ $docAnu->deleted_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-[11px] font-black text-[#2F3E5C]/70 uppercase">{{ $docAnu->nom_doc }}</p>
                                    <p class="text-[10px] font-semibold text-[#2F3E5C]/50">Tipo: {{ $docAnu->tipo_doc }}</p>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        @if($docAnu->ruta_archivo)
                                            <a href="{{ Storage::url($docAnu->ruta_archivo) }}" target="_blank" class="rounded-lg bg-[#2F3E5C]/10 p-1.5 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition" title="Ver archivo">
                                                <i class="ph-bold ph-eye"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.adultos-mayores.documentos.restore', [$adulto->cod_am, $docAnu->cod_doc_am]) }}" method="POST" onsubmit="confirmarAccion(event, 'Restaurar documento', 'El documento volverá a estar visible en el expediente activo.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Restaurar" class="rounded-lg bg-[#8EA17D]/10 p-1.5 text-[#8EA17D] hover:bg-[#8EA17D] hover:text-white transition">
                                                <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            </button>
                                        </form>
                                        <span class="rounded-full bg-white/20 px-2 py-0.5 text-[8px] font-black uppercase text-[#2F3E5C]/40">ARCHIVADO</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </section>
</section>

                        
                 
            