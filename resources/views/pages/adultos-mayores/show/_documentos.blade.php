{{-- TAB RESUMEN DOCUMENTAL --}}
<section
 x-show="tab === 'documentos'"
 style="display: none;"
 x-transition.opacity.duration.250ms
 class="space-y-6"
>
 <!-- HEADER BLOCK -->
 <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-borde pb-5">
 <div class="flex items-center gap-3">
 <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-fondo-panel text-titulo">
 <i class="ph-fill ph-folder-open text-3xl"></i>
 </div>
 <div>
 <h2 class="text-2xl font-black tracking-tight text-titulo">Resumen Documental</h2>
 <p class="text-sm font-semibold text-apoyo">Documentos y archivos anexos al expediente.</p>
 </div>
 </div>
 
 <div class="flex items-center gap-2">
 <button type="button" @click="abrir('documento')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde px-4 py-2.5 text-xs font-bold text-titulo shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-[0.98]">
 <i class="ph-bold ph-upload-simple text-lg"></i>
 Subir Documento
 </button>
 <a href="{{ route('admin.adultos-mayores.documentos.index', $idAdulto) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:-translate-y-0.5 hover:opacity-90 active:scale-[0.98]">
 Ver Todos los Documentos <i class="ph-bold ph-arrow-right"></i>
 </a>
 </div>
 </div>

 <!-- METRICS GRID -->
 <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Total Documentos</p>
 <p class="mt-2 text-3xl font-black text-titulo">{{ collect($documentosActivos)->count() }}</p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Archivos Activos</p>
 <p class="mt-2 text-3xl font-black text-emerald-600">
 {{ collect($documentosActivos)->count() }}
 </p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Pendientes/Expirados</p>
 <p class="mt-2 text-3xl font-black text-amber-600">0</p>
 </div>

 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm flex flex-col justify-center text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-apoyo">Último Cargado</p>
 <p class="mt-2 text-sm font-bold text-titulo">
 @if(collect($documentosActivos)->count() > 0)
 @php $ultimoDoc = collect($documentosActivos)->sortByDesc('fecha_subida')->first(); @endphp
 <span class="block truncate max-w-[150px] mx-auto" title="{{ $ultimoDoc->titulo }}">{{ $ultimoDoc->titulo }}</span>
 <span class="block mt-1 text-xs text-apoyo font-normal">{{ \Carbon\Carbon::parse($ultimoDoc->fecha_subida)->format('d/m/Y') }}</span>
 @else
 --
 @endif
 </p>
 </div>
 </div>

 <!-- ÚLTIMOS DOCUMENTOS CARD -->
 <div class="rounded-[24px] border border-borde bg-fondo-card/95 p-6 shadow-sm">
 <h3 class="text-lg font-extrabold text-titulo border-b border-borde pb-4 mb-4">Documentos Recientes</h3>
 
 @if(collect($documentosActivos)->count() > 0)
 <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
 @foreach(collect($documentosActivos)->sortByDesc('fecha_subida')->take(3) as $doc)
 <div class="rounded-xl border border-borde bg-fondo-panel p-4 flex items-start gap-3 transition hover:border-borde-fuerte hover:bg-fondo-panel">
 <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-fondo-panel text-titulo shrink-0">
 @if(in_array(strtolower(pathinfo($doc->ruta_archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
 <i class="ph-bold ph-image text-xl"></i>
 @else
 <i class="ph-bold ph-file-pdf text-xl"></i>
 @endif
 </div>
 <div class="overflow-hidden">
 <p class="text-sm font-bold text-titulo truncate" title="{{ $doc->titulo }}">{{ $doc->titulo }}</p>
 <p class="text-[10px] font-bold text-apoyo mt-1 uppercase tracking-widest">{{ $doc->tipo_documento }}</p>
 <p class="text-[10px] font-bold text-apoyo mt-1">{{ \Carbon\Carbon::parse($doc->fecha_subida)->format('d/m/Y') }}</p>
 <a href="{{ route('admin.adultos-mayores.documentos.archivo', [$doc->cod_am, $doc->cod_doc_am]) }}" class="inline-block mt-2 text-xs font-bold text-parrafo hover:underline">Ver / Descargar</a>
 </div>
 </div>
 @endforeach
 </div>
 <div class="mt-5 text-center">
 <button type="button" @click="abrir('documento')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-card border border-borde-fuerte px-6 py-2.5 text-xs font-bold uppercase tracking-wider text-titulo shadow-sm transition hover:bg-fondo-panel active:scale-[0.98]">
 <i class="ph-bold ph-upload-simple"></i> Cargar Nuevo Archivo
 </button>
 </div>
 @else
 <div class="py-8 text-center">
 <i class="ph-fill ph-folder-open text-4xl text-meta mb-3 block"></i>
 <p class="text-sm font-bold text-apoyo">El expediente no cuenta con documentos adjuntos.</p>
 <button type="button" @click="abrir('documento')" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold text-inverso shadow-sm transition hover:-translate-y-0.5 hover:bg-fondo-panel">
 <i class="ph-bold ph-upload-simple"></i> Subir Primer Documento
 </button>
 </div>
 @endif
 </div>
</section>
