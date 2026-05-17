@use('Carbon\Carbon')
<x-app-layout>
    <div class="bg-white p-4 sm:p-8 min-h-screen font-outfit text-[#2F3E5C]">
        {{-- Encabezado de Impresión --}}
        <div class="mb-8 flex flex-col sm:flex-row items-center justify-between border-b-2 border-[#2F3E5C] pb-6">
            <div class="flex items-center gap-4">
                <div class="h-20 w-20 bg-[#2F3E5C] flex items-center justify-center rounded-2xl">
                    <i class="ph-bold ph-brain text-white text-4xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tighter">RememberMind</h1>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2F3E5C]/60">Casa del Adulto Mayor "Casa Amandita"</p>
                </div>
            </div>
            <div class="text-right mt-4 sm:mt-0">
                <h2 class="text-xl font-black uppercase">Ficha Técnica Individual</h2>
                <p class="text-sm font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Código: {{ $adulto->cod_am }}</p>
                <div class="mt-2 no-print flex flex-wrap gap-2 justify-end">
                    <button onclick="window.print()" class="rounded-full bg-[#2F3E5C] px-6 py-2 text-xs font-black text-white shadow-lg transition hover:bg-[#1F2E4C] active:scale-95">
                        <i class="ph-bold ph-printer mr-2"></i> IMPRIMIR
                    </button>
                    <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $adulto->cod_am, 'format' => 'pdf']) }}" class="rounded-full bg-[#D96F58] px-6 py-2 text-xs font-black text-white shadow-lg transition hover:opacity-90 active:scale-95">
                        <i class="ph-bold ph-file-pdf mr-2"></i> PDF
                    </a>
                    <a href="{{ route('admin.adultos-mayores.reporte-individual', ['adulto_mayor' => $adulto->cod_am, 'format' => 'word']) }}" class="rounded-full bg-[#4E5D8A] px-6 py-2 text-xs font-black text-white shadow-lg transition hover:opacity-90 active:scale-95">
                        <i class="ph-bold ph-file-doc mr-2"></i> WORD
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- SECCIÓN A: DATOS PERSONALES Y ESTADO --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="rounded-3xl border border-[#CBBBAA] bg-[#F2EBE3]/30 p-6">
                    <div class="flex flex-col items-center mb-6">
                        <div class="h-40 w-40 overflow-hidden rounded-[2.5rem] border-4 border-white shadow-xl">
                            @if($adulto->foto)
                                <img src="{{ Storage::url($adulto->foto) }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-[#E7DDD2] text-[#2F3E5C]/20">
                                    <i class="ph-bold ph-user text-7xl"></i>
                                </div>
                            @endif
                        </div>
                        <h3 class="mt-4 text-2xl font-black text-center">{{ $adulto->nombres }}<br>{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</h3>
                        @php $estAdul = $adulto->estado->estado ?? 'ACTIVO'; @endphp
                        <span class="mt-2 inline-flex items-center rounded-full px-4 py-1 text-xs font-black uppercase tracking-widest
                            {{ $estAdul === 'ACTIVO' ? 'bg-[#8EA17D]/20 text-[#617453]' : 'bg-[#D96F58]/20 text-[#D96F58]' }}">
                            {{ $estAdul }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">C.I.</span>
                            <span class="text-sm font-black">{{ $adulto->ci }} {{ $adulto->expedicion_ci }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">Edad</span>
                            <span class="text-sm font-black">{{ $adulto->edad }} años</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">Género</span>
                            <span class="text-sm font-black">{{ $adulto->genero }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">Fecha Nac.</span>
                            <span class="text-sm font-black">{{ $adulto->fecha_nac?->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">Fecha Ingreso</span>
                            <span class="text-sm font-black">{{ $adulto->fecha_ing?->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center border-b border-[#D5C7B9] pb-2">
                            <span class="text-[10px] font-black uppercase text-[#2F3E5C]/40">Permanencia</span>
                            <span class="text-sm font-black">{{ $adulto->permanencia }}</span>
                        </div>
                    </div>
                </div>

                {{-- Familiares --}}
                <div class="rounded-3xl border border-[#CBBBAA] bg-white p-6 shadow-sm">
                    <h4 class="mb-4 text-xs font-black uppercase tracking-[0.2em] text-[#2F3E5C]/50 border-b border-[#F2EBE3] pb-2">Contactos de Referencia</h4>
                    <div class="space-y-4">
                        @forelse($familiares as $fam)
                            <div class="flex items-start gap-3">
                                <div class="h-8 w-8 rounded-lg bg-[#8EA17D]/10 flex items-center justify-center text-[#617453]">
                                    <i class="ph-bold ph-heart"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-black leading-tight">{{ $fam->usuario->name ?? 'Familiar' }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/50">{{ $fam->pivot->parentesco_vinculo }} · {{ $fam->usuario->telefono ?? 'S/T' }}</p>
                                    @if($fam->pivot->es_responsable)
                                        <span class="mt-1 inline-block rounded bg-[#E27D60]/10 px-1.5 py-0.5 text-[8px] font-black text-[#E27D60] uppercase">Responsable Principal</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-xs font-bold text-[#2F3E5C]/30 py-2">Sin familiares vinculados.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Resumen Final --}}
                <div class="rounded-3xl border border-[#CBBBAA] bg-[#2F3E5C] p-6 text-white shadow-xl">
                    <h4 class="mb-4 text-xs font-black uppercase tracking-[0.2em] opacity-50">Resumen Operativo</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase opacity-60">Atenciones</p>
                            <p class="text-2xl font-black">{{ $resumen['total_atenciones'] }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[10px] font-black uppercase opacity-60">Evaluaciones</p>
                            <p class="text-2xl font-black">{{ $resumen['total_evaluaciones'] }}</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/10 text-center">
                        <p class="text-[10px] font-black uppercase opacity-60">Último Seguimiento</p>
                        @php $ultSeg = $resumen['ultima_fecha_seguimiento'] ?? 'N/D'; @endphp
                        <p class="text-sm font-bold">{{ is_string($ultSeg) ? $ultSeg : (is_object($ultSeg) ? $ultSeg->format('d/m/Y') : $ultSeg) }}</p>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN B: HISTORIAL CLÍNICO-ADMINISTRATIVO --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Evaluaciones Cognitivas --}}
                <div class="rounded-[2.5rem] border border-[#CBBBAA] bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-black uppercase tracking-widest flex items-center gap-3">
                            <i class="ph-bold ph-brain text-[#5B5F97]"></i> Perfil Cognitivo Institucional
                        </h3>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($evaluaciones as $eval)
                            <div class="rounded-2xl border border-[#D5C7B9] p-5 bg-[#F2EBE3]/20">
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <h4 class="text-base font-black text-[#2F3E5C]">{{ $eval->tipoEvaluacion->nombre }} — {{ $eval->puntaje_total }} pts.</h4>
                                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 uppercase tracking-widest">Eval. por: {{ $eval->personalSalud->usuario->name ?? 'Personal Autorizado' }}</p>
                                    </div>
                                    <div class="flex gap-3">
                                        <span class="rounded-full bg-white px-4 py-1 text-[10px] font-black shadow-sm">{{ $eval->fecha_eval->format('d/m/Y') }}</span>
                                        <span class="rounded-full px-4 py-1 text-[10px] font-black {{ $eval->nivel_riesgo === 'BAJO' ? 'bg-[#617453] text-white' : 'bg-[#D96F58] text-white' }}">RIESGO {{ $eval->nivel_riesgo }}</span>
                                    </div>
                                </div>
                                <div class="mt-4 text-sm font-semibold leading-relaxed text-[#2F3E5C]/80">
                                    <span class="font-black text-[#2F3E5C]">Interpretación:</span> {{ $eval->resultado_interpretacion }}. 
                                    @if($eval->observaciones)
                                        <span class="block mt-1 font-medium italic text-[#2F3E5C]/60">Obs: {{ $eval->observaciones }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center border-2 border-dashed border-[#D5C7B9] rounded-2xl">
                                <p class="text-sm font-bold text-[#2F3E5C]/40 italic">No se han registrado evaluaciones cognitivas en este expediente.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Atenciones y Seguimiento --}}
                <div class="rounded-[2.5rem] border border-[#CBBBAA] bg-white p-8 shadow-sm">
                    <h3 class="text-xl font-black uppercase tracking-widest flex items-center gap-3 mb-6">
                        <i class="ph-bold ph-stethoscope text-[#E27D60]"></i> Historial de Atenciones
                    </h3>
                    
                    <div class="space-y-3">
                        @forelse($atenciones->take(10) as $aten)
                            <div class="flex gap-4 p-4 rounded-2xl border border-[#F2EBE3] hover:bg-[#F2EBE3]/10">
                                <div class="text-center min-w-[60px]">
                                    <p class="text-xs font-black text-[#E27D60]">{{ $aten->fecha->format('d/m') }}</p>
                                    <p class="text-[10px] font-bold text-[#2F3E5C]/40">{{ $aten->hora }}</p>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black uppercase tracking-wide">{{ $aten->tipoAtencion->tipo ?? 'Atención General' }}</h4>
                                    <p class="text-xs font-semibold text-[#2F3E5C]/70 mt-1 line-clamp-2">{{ $aten->obs ?? $aten->descripcion }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-[#2F3E5C]/5 text-[#2F3E5C]/60">{{ $aten->estado }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-sm font-bold text-[#2F3E5C]/30 italic py-4">Sin atenciones registradas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="rounded-[2.5rem] border border-[#CBBBAA] bg-white p-8 shadow-sm">
                    <h3 class="text-xl font-black uppercase tracking-widest flex items-center gap-3 mb-6">
                        <i class="ph-bold ph-note-pencil text-[#6873A6]"></i> Bitácora de Observaciones
                    </h3>
                    <div class="space-y-4">
                        @forelse($observaciones->take(8) as $obs)
                            <div class="relative pl-6 pb-4 border-l-2 border-[#D5C7B9] last:pb-0">
                                <div class="absolute -left-1.5 top-0 h-3 w-3 rounded-full bg-[#6873A6]"></div>
                                <p class="text-[10px] font-black text-[#6873A6] uppercase tracking-widest mb-1">{{ \Carbon\Carbon::parse($obs->fecha)->format('d/m/Y') }} · {{ $obs->tipo_obs }}</p>
                                <p class="text-sm font-semibold text-[#2F3E5C]/80 leading-relaxed">{{ $obs->descripcion }}</p>
                            </div>
                        @empty
                            <p class="text-center text-sm font-bold text-[#2F3E5C]/30 italic py-4">Sin observaciones registradas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Historial de Actividad (Bitácora Auditoría) --}}
                <div class="rounded-[2.5rem] border border-[#CBBBAA] bg-[#F2EBE3]/30 p-8">
                    <h3 class="text-sm font-black uppercase tracking-widest flex items-center gap-3 mb-6 opacity-60">
                        <i class="ph-bold ph-clock-counter-clockwise"></i> Trazabilidad del Expediente
                    </h3>
                    <div class="space-y-3">
                        @foreach($bitacora->take(10) as $log)
                            <div class="flex items-center justify-between text-xs font-bold text-[#2F3E5C]/60">
                                <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                <span class="uppercase tracking-tighter">{{ $log->description }}</span>
                                <span class="italic text-[10px]">{{ $log->causer->name ?? 'Sistema' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Firmas e Institucional --}}
        <div class="mt-16 pt-16 border-t-2 border-[#2F3E5C]/10">
            <div class="flex flex-col sm:flex-row justify-around items-center gap-12 sm:gap-4">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-0.5 w-64 bg-[#2F3E5C]/20"></div>
                    <p class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]">Sello / Firma Personal de Salud</p>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/40 mt-1 uppercase">Validación de Perfil Cognitivo</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto mb-4 h-0.5 w-64 bg-[#2F3E5C]/20"></div>
                    <p class="text-xs font-black uppercase tracking-widest text-[#2F3E5C]">Sello / Firma Dirección Administrativa</p>
                    <p class="text-[10px] font-bold text-[#2F3E5C]/40 mt-1 uppercase">Autorización de Expediente</p>
                </div>
            </div>
            
            <div class="mt-20 text-center space-y-2">
                <p class="text-[10px] font-black text-[#2F3E5C]/50 uppercase tracking-[0.3em]">REPORTE GENERADO INSTITUCIONALMENTE POR EL SISTEMA REMEMBERMIND</p>
                <p class="text-[9px] font-bold text-[#2F3E5C]/30 italic">* Este reporte es para fines administrativos y de seguimiento interno. No constituye un diagnóstico médico final.</p>
            </div>
        </div>

        {{-- ANEXO: REGISTROS ANULADOS / HISTORIAL DE BAJAS --}}
        @if($familiaresInactivos->count() > 0 || $observacionesAnuladas->count() > 0 || $atencionesAnuladas->count() > 0 || $actividadesAnuladas->count() > 0 || $evaluacionesAnuladas->count() > 0 || $documentosArchivados->count() > 0)
        <div class="mt-16 pt-16 border-t-2 border-dashed border-[#D5C7B9] page-break-before">
            <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3 mb-8">
                <i class="ph-bold ph-archive"></i> Anexo: Historial de Registros Anulados y Archivados
            </h3>
            <p class="mb-6 text-[11px] font-bold text-[#2F3E5C]/60 italic">
                * El siguiente anexo contiene información que ha sido retirada de la ficha activa por motivos administrativos o médicos, pero que se conserva institucionalmente para fines de auditoría y trazabilidad histórica.
            </p>
            
            <div class="space-y-8">
                {{-- Observaciones --}}
                @if($observacionesAnuladas->count() > 0)
                <div class="opacity-80 grayscale-[50%]">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-3 border-l-4 border-terracota pl-2">Observaciones Anuladas</h4>
                    <table class="w-full text-[10px] text-left border-collapse border border-[#D5C7B9]">
                        <thead class="bg-[#F2EBE3]">
                            <tr>
                                <th class="p-2 border border-[#D5C7B9]">Fecha Anul.</th>
                                <th class="p-2 border border-[#D5C7B9]">Tipo</th>
                                <th class="p-2 border border-[#D5C7B9]">Descripción Original</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($observacionesAnuladas as $obsAnu)
                            <tr>
                                <td class="p-2 border border-[#D5C7B9] font-black">{{ $obsAnu->deleted_at->format('d/m/Y') }}</td>
                                <td class="p-2 border border-[#D5C7B9] font-bold uppercase">{{ $obsAnu->tipo_obs }}</td>
                                <td class="p-2 border border-[#D5C7B9] italic text-[#2F3E5C]/70">{{ $obsAnu->descripcion }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Atenciones --}}
                @if($atencionesAnuladas->count() > 0)
                <div class="opacity-80 grayscale-[50%]">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-3 border-l-4 border-[#9A7B60] pl-2">Atenciones Médicas Anuladas</h4>
                    <table class="w-full text-[10px] text-left border-collapse border border-[#D5C7B9]">
                        <thead class="bg-[#F2EBE3]">
                            <tr>
                                <th class="p-2 border border-[#D5C7B9]">Fecha Anul.</th>
                                <th class="p-2 border border-[#D5C7B9]">Tipo de Atención</th>
                                <th class="p-2 border border-[#D5C7B9]">Motivo Original</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($atencionesAnuladas as $ateAnu)
                            <tr>
                                <td class="p-2 border border-[#D5C7B9] font-black">{{ $ateAnu->deleted_at->format('d/m/Y') }}</td>
                                <td class="p-2 border border-[#D5C7B9] font-bold uppercase">{{ $ateAnu->tipoAtencion->nombre ?? 'N/D' }}</td>
                                <td class="p-2 border border-[#D5C7B9] italic text-[#2F3E5C]/70">{{ $ateAnu->motivo_consulta }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Evaluaciones --}}
                @if($evaluacionesAnuladas->count() > 0)
                <div class="opacity-80 grayscale-[50%]">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50 mb-3 border-l-4 border-[#4E5D8A] pl-2">Evaluaciones Cognitivas Anuladas</h4>
                    <table class="w-full text-[10px] text-left border-collapse border border-[#D5C7B9]">
                        <thead class="bg-[#F2EBE3]">
                            <tr>
                                <th class="p-2 border border-[#D5C7B9]">Fecha Anul.</th>
                                <th class="p-2 border border-[#D5C7B9]">Evaluación</th>
                                <th class="p-2 border border-[#D5C7B9]">Puntaje Original</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluacionesAnuladas as $evalAnu)
                            <tr>
                                <td class="p-2 border border-[#D5C7B9] font-black">{{ $evalAnu->deleted_at->format('d/m/Y') }}</td>
                                <td class="p-2 border border-[#D5C7B9] font-bold uppercase">{{ $evalAnu->tipoEvaluacion->nombre ?? 'N/D' }}</td>
                                <td class="p-2 border border-[#D5C7B9] italic text-[#2F3E5C]/70">{{ $evalAnu->puntaje_total }} / {{ $evalAnu->puntaje_maximo }} pts</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <style>
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 400;
            src: url(https://fonts.gstatic.com/s/outfit/v11/Q_k790bb99nUx296-K_g6WfN.woff2) format('woff2');
        }
        @media print {
            body { background: white !important; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            aside, nav { display: none !important; }
            .sm\:p-8 { padding: 0 !important; }
            .shadow-xl, .shadow-lg, .shadow-md, .shadow-sm { box-shadow: none !important; }
            .rounded-3xl, .rounded-\[2\.5rem\], .rounded-2xl { border-radius: 0.5rem !important; }
            .bg-\[\#F2EBE3\]\/30 { background-color: rgba(242, 235, 227, 0.2) !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</x-app-layout>
