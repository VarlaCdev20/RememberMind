{{-- TAB DOCUMENTOS --}}
<section
    x-show="tab === 'documentos'"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>
    {{-- Encabezado Principal del Expediente Digital --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="flex flex-col gap-4 border-b border-[#D5C7B9] px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.2em] text-[#2F3E5C]">
                    Expediente Digital
                </span>
                <h2 class="mt-1 text-xl font-black text-[#2F3E5C]">
                    Documentos
                </h2>
                <p class="mt-1 text-xs font-bold leading-5 text-[#2F3E5C]/60">
                    Expediente digital, archivos asociados y trazabilidad documental del adulto mayor.
                </p>
            </div>

            <button type="button"
                    @click="abrir('documento')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2F3E5C] px-5 py-3 text-xs font-black text-white shadow-[0_10px_20px_rgba(47,62,92,0.18)] transition hover:-translate-y-0.5 hover:bg-[#566189] active:scale-[0.98]">
                <i class="ph-bold ph-upload-simple text-sm"></i>
                Subir documento
            </button>
        </div>

        {{-- Panel de Indicadores Reales --}}
        <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Indicador 1: Total Activos --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition-all hover:bg-white/40 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C]">
                        <i class="ph-bold ph-folders text-xs"></i>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/50">
                        Total Expediente
                    </p>
                </div>
                <p class="mt-3 text-3xl font-black text-[#2F3E5C]">
                    {{ $totalDocumentos }}
                </p>
                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                    Documentos vigentes activos
                </p>
            </div>

            {{-- Indicador 2: Archivados/Anulados --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition-all hover:bg-white/40 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-terracota/10 text-terracota">
                        <i class="ph-bold ph-archive-box text-xs"></i>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/50">
                        Archivados
                    </p>
                </div>
                <p class="mt-3 text-3xl font-black text-terracota">
                    {{ count($documentosArchivados) }}
                </p>
                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                    Historial anulado protegido
                </p>
            </div>

            {{-- Indicador 3: Último Documento Cargado --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition-all hover:bg-white/40 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#617453]/10 text-[#617453]">
                        <i class="ph-bold ph-calendar-plus text-xs"></i>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/50">
                        Último Registro
                    </p>
                </div>
                @php
                    $ultimoDoc = $documentosLista->first();
                @endphp
                <p class="mt-3 text-lg font-black text-[#2F3E5C] truncate">
                    {{ $ultimoDoc && $ultimoDoc->fecha_doc ? \Carbon\Carbon::parse($ultimoDoc->fecha_doc)->format('d/m/Y') : 'Ninguno' }}
                </p>
                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45 truncate">
                    {{ $ultimoDoc ? Str::limit($ultimoDoc->nom_doc, 25) : 'Sin archivos' }}
                </p>
            </div>

            {{-- Indicador 4: Estado Ficha --}}
            <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 transition-all hover:bg-white/40 hover:shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg {{ $totalDocumentos > 0 ? 'bg-[#617453]/10 text-[#617453]' : 'bg-[#D96F58]/10 text-[#D96F58]' }}">
                        <i class="ph-bold ph-shield-check text-xs"></i>
                    </span>
                    <p class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/50">
                        Garantía Digital
                    </p>
                </div>
                <p class="mt-3 text-base font-black {{ $totalDocumentos > 0 ? 'text-[#617453]' : 'text-terracota' }}">
                    {{ $totalDocumentos > 0 ? 'Con Respaldo' : 'Sin Respaldos' }}
                </p>
                <p class="mt-1 text-[10px] font-bold text-[#2F3E5C]/45">
                    {{ $totalDocumentos > 0 ? 'Expediente institucional al día' : 'Requiere subir documentos' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Contenedor del Listado del Expediente --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)]">
        <div class="flex items-center justify-between gap-3 border-b border-[#D5C7B9] px-6 py-4 bg-[#F2EBE3]/30">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#2F3E5C]/15 text-[#2F3E5C]">
                    <i class="ph-bold ph-folder-open text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-[#2F3E5C]">Expediente Digital Vigente</h3>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Documentos legales y respaldos activos</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#2F3E5C]">
                <thead class="bg-[#D5C7B9]/40 text-[10px] uppercase tracking-widest text-[#2F3E5C]/60">
                    <tr>
                        <th class="px-6 py-4">Documento</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4">Fecha Emisión</th>
                        <th class="px-6 py-4">Observaciones y Respaldo</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#D5C7B9]/30">
                    @forelse($documentosActivos as $documento)
                        @php
                            $documentoObj = is_object($documento) ? $documento : null;
                            $nombreDoc = optional($documentoObj)->nom_doc ?? 'Documento sin título';
                            $tipoDoc = strtoupper(optional($documentoObj)->tipo_doc ?? 'GENERAL');
                            $fechaDoc = optional($documentoObj)->fecha_doc;
                            
                            // Colores de categorías dinámicas
                            $colorClase = match($tipoDoc) {
                                'HISTORIAL MÉDICO' => 'bg-[#6873A6]/10 text-[#6873A6] border-[#6873A6]/20',
                                'EXÁMENES' => 'bg-[#617453]/10 text-[#617453] border-[#617453]/20',
                                'RECETAS' => 'bg-[#D96F58]/10 text-[#D96F58] border-[#D96F58]/20',
                                'ADMINISTRATIVO' => 'bg-[#9A7B60]/10 text-[#9A7B60] border-[#9A7B60]/20',
                                'LEGAL' => 'bg-[#2F3E5C]/10 text-[#2F3E5C] border-[#2F3E5C]/20',
                                'CONSENTIMIENTO' => 'bg-[#8EA17D]/10 text-[#8EA17D] border-[#8EA17D]/20',
                                default => 'bg-[#D5C7B9]/15 text-[#2F3E5C]/60 border-[#D5C7B9]/30',
                            };

                            // Selector de íconos por extensión
                            $ext = strtolower(optional($documentoObj)->extension ?? 'pdf');
                            $iconClase = match($ext) {
                                'pdf' => 'ph-file-pdf text-rose-600 bg-rose-50',
                                'jpg', 'jpeg', 'png' => 'ph-file-image text-emerald-600 bg-emerald-50',
                                'doc', 'docx' => 'ph-file-doc text-blue-600 bg-blue-50',
                                default => 'ph-file text-slate-600 bg-slate-50',
                            };
                        @endphp
                        <tr class="group transition hover:bg-[#F2EBE3]/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-[#D5C7B9]/60 {{ explode(' ', $iconClase)[1] }} {{ explode(' ', $iconClase)[2] }}">
                                        <i class="ph-bold {{ explode(' ', $iconClase)[0] }} text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-[#2F3E5C] transition-colors group-hover:text-[#E27D60]">{{ $nombreDoc }}</p>
                                        <p class="text-[9px] font-black tracking-widest text-[#2F3E5C]/40 uppercase">{{ $ext }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-wider {{ $colorClase }}">
                                    {{ $tipoDoc }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-[#2F3E5C]/75">
                                {{ $fechaDoc ? \Carbon\Carbon::parse($fechaDoc)->format('d/m/Y') : 'Sin fecha registrada' }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-[11px] font-bold text-[#2F3E5C]/65 max-w-sm break-words line-clamp-2" title="{{ optional($documentoObj)->observaciones }}">
                                    {{ optional($documentoObj)->observaciones ?: 'Sin observaciones asociadas' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <div class="flex justify-end gap-1.5">
                                    {{-- Visualizar --}}
                                    @if(optional($documentoObj)->ruta_archivo)
                                        <a href="{{ Storage::url($documentoObj->ruta_archivo) }}" target="_blank" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2F3E5C]/5 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition shadow-sm" title="Ver documento">
                                            <i class="ph-bold ph-eye"></i>
                                        </a>
                                        {{-- Descargar --}}
                                        <a href="{{ Storage::url($documentoObj->ruta_archivo) }}" download class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#2F3E5C]/5 text-[#2F3E5C] hover:bg-[#6873A6] hover:text-white transition shadow-sm" title="Descargar documento">
                                            <i class="ph-bold ph-download-simple"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Editar metadatos --}}
                                    <button type="button" @click="abrir('documento', @js($documentoObj), true)" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#9A7B60]/10 text-[#9A7B60] hover:bg-[#9A7B60] hover:text-white transition shadow-sm" title="Editar Metadatos">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                    </button>

                                    {{-- Anular/Archivar --}}
                                    <form action="{{ route('admin.adultos-mayores.documentos.destroy', [$idAdulto, $documentoObj->cod_doc_am ?? '0']) }}" method="POST" onsubmit="confirmarAccion(event, '¿Desea anular este documento?', 'El documento dejará de estar visible en la lista activa, pero se conservará físicamente en el servidor para trazabilidad.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Anular o Archivar" class="flex h-8 w-8 items-center justify-center rounded-xl bg-terracota/10 text-terracota hover:bg-terracota hover:text-white transition shadow-sm">
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

        {{-- Repositorio de Documentos Anulados o Archivados --}}
        @if(count($documentosArchivados) > 0)
        <div class="border-t border-[#D5C7B9]/40 bg-[#D5C7B9]/10 pt-6 pb-6 px-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-terracota/85 flex items-center gap-2">
                <i class="ph-bold ph-archive-box text-sm"></i> Repositorio de Documentos Archivados
            </h4>
            <div class="overflow-hidden rounded-[24px] border border-[#D5C7B9]/60 bg-[#E7DDD2]/50 backdrop-blur-sm shadow-sm transition-all hover:border-[#C7B5A3]">
                <table class="w-full text-left text-sm text-[#2F3E5C]">
                    <thead class="bg-[#D5C7B9]/30 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                        <tr>
                            <th class="px-6 py-3.5">Fecha Archivo</th>
                            <th class="px-6 py-3.5">Documento / Tipo</th>
                            <th class="px-6 py-3.5">Observación</th>
                            <th class="px-6 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D5C7B9]/20">
                        @foreach($documentosArchivados as $docAnu)
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-black text-[#2F3E5C]/60">
                                    {{ $docAnu->deleted_at ? $docAnu->deleted_at->format('d/m/Y') : 'N/D' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-black text-[#2F3E5C]/80 uppercase">{{ $docAnu->nom_doc }}</p>
                                    <p class="text-[9px] font-semibold text-[#2F3E5C]/50">Tipo: {{ $docAnu->tipo_doc }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-[11px] font-bold text-[#2F3E5C]/55 line-clamp-1">
                                        {{ $docAnu->observaciones ?: 'Sin observaciones registradas' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex justify-end items-center gap-1.5">
                                        @if($docAnu->ruta_archivo)
                                            <a href="{{ Storage::url($docAnu->ruta_archivo) }}" target="_blank" class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#2F3E5C]/5 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition shadow-sm" title="Ver archivo">
                                                <i class="ph-bold ph-eye"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('admin.adultos-mayores.documentos.restore', [$adulto->cod_am, $docAnu->cod_doc_am]) }}" method="POST" onsubmit="confirmarAccion(event, '¿Desea restaurar este documento?', 'El documento volverá a estar disponible en el expediente digital activo.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Restaurar Documento" class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#617453]/10 text-[#617453] hover:bg-[#617453] hover:text-white transition shadow-sm">
                                                <i class="ph-bold ph-arrow-counter-clockwise"></i>
                                            </button>
                                        </form>
                                        <span class="rounded-full bg-white/30 px-2.5 py-0.5 text-[8px] font-black uppercase text-[#2F3E5C]/45 border border-[#D5C7B9]/50">ARCHIVADO</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="border-t border-[#D5C7B9]/40 bg-[#D5C7B9]/10 pt-4 pb-4 px-6 text-center text-xs font-bold text-[#2F3E5C]/40">
            Sin documentos anulados o archivados.
        </div>
        @endif
    </section>
</section>