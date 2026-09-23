
<x-sistema-layout>
<x-validation-errors />
 @php
 $nombreCompleto = trim("{$adulto_mayor->nombres} {$adulto_mayor->ap_paterno} {$adulto_mayor->ap_materno}");
 $documentosActivos = $adulto_mayor->documentos->where('estado', '!=', 'ARCHIVADO');
 $documentosArchivados = $adulto_mayor->documentos->where('estado', 'ARCHIVADO');
 @endphp

 <div
 x-data="documentosAdulto(@js(today()->format('Y-m-d')))"
 class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 space-y-6"
 >
 {{-- CABECERA GESTIÓN DOCUMENTAL --}}
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
 <div>
 <h1 class="text-2xl font-black text-titulo">Gestión Documental</h1>
 <p class="text-sm font-bold text-apoyo uppercase tracking-widest mt-1">
 Expediente de: <span class="text-boton-acento">{{ $nombreCompleto }}</span>
 </p>
 </div>
 <div class="flex items-center gap-2">
 <a href="{{ route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_am, 'tab' => 'documentos']) }}" class="inline-flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2 text-xs font-bold text-titulo transition hover:bg-fondo-panel">
 <i class="ph-bold ph-arrow-left"></i> Volver a Ficha
 </a>
 <button type="button" @click="abrirRegistro()" class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel">
 <i class="ph-bold ph-upload-simple text-base"></i> Registrar Documento
 </button>
 </div>
 </div>

 {{-- MÉTRICAS RESUMEN --}}
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
 <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Total Documentos</p>
 <p class="text-2xl font-black text-titulo mt-1">{{ $adulto_mayor->documentos->count() }}</p>
 </div>
 <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Archivos Activos</p>
 <p class="text-2xl font-black text-parrafo mt-1">{{ $documentosActivos->count() }}</p>
 </div>
 <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
 <p class="text-xs font-bold uppercase tracking-wide text-apoyo">Archivados</p>
 <p class="text-2xl font-black text-amber-600 mt-1">{{ $documentosArchivados->count() }}</p>
 </div>
 </div>

 {{-- TABLA DE DOCUMENTOS ACTIVOS --}}
 <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
 <div class="px-6 py-4 border-b border-borde bg-fondo-panel">
 <h2 class="text-sm font-bold text-titulo uppercase tracking-widest">Documentos del Expediente</h2>
 </div>
 
 <div class="overflow-x-auto">
 <table class="w-full text-left text-sm text-titulo">
 <thead class="bg-fondo-panel text-xs font-bold uppercase tracking-wide text-apoyo">
 <tr>
 <th class="px-6 py-4">Documento</th>
 <th class="px-6 py-4">Tipo</th>
 <th class="px-6 py-4">Fecha Subida</th>
 <th class="px-6 py-4">Estado</th>
 <th class="px-6 py-4 text-right">Acciones</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#CBBBAA]/20">
 @forelse($adulto_mayor->documentos as $doc)
 <tr class="transition hover:bg-fondo-panel {{ ($doc->estado === 'ARCHIVADO') ? 'opacity-60 bg-fondo-panel' : '' }}">
 <td class="px-6 py-4">
 <div class="flex items-center gap-3">
 <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-fondo-panel text-titulo">
 @if(in_array(strtolower(pathinfo($doc->ruta_archivo, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']))
 <i class="ph-bold ph-image text-xl"></i>
 @else
 <i class="ph-bold ph-file-pdf text-xl"></i>
 @endif
 </div>
 <div>
 <p class="font-black text-titulo">{{ $doc->titulo ?? $doc->nom_doc }}</p>
 <p class="text-xs text-apoyo truncate max-w-xs">{{ $doc->observaciones ?? 'Sin observación' }}</p>
 </div>
 </div>
 </td>
 <td class="px-6 py-4">
 <span class="inline-flex rounded-md bg-fondo-panel px-2 py-1 text-xs font-bold uppercase tracking-wide text-titulo">
 {{ $doc->tipo_documento ?? $doc->tipo_doc ?? 'Documento' }}
 </span>
 </td>
 <td class="px-6 py-4">
 <p class="font-bold">{{ $doc->created_at->format('d/m/Y') }}</p>
 </td>
 <td class="px-6 py-4">
 @if(($doc->estado === 'ARCHIVADO'))
 <span class="text-xs font-bold text-amber-600">ARCHIVADO</span>
 @else
 <span class="text-xs font-bold text-parrafo">ACTIVO</span>
 @endif
 </td>
 <td class="px-6 py-4 text-right">
 <div class="flex justify-end gap-2">
 @if(($doc->estado === 'ARCHIVADO'))
 <form action="{{ route('admin.adultos-mayores.documentos.restore', ['adulto_mayor' => $adulto_mayor->cod_am, 'documento' => $doc->cod_doc_am]) }}" method="POST" class="inline">
 @csrf @method('PATCH')
 <button type="submit" class="rounded-lg p-2 text-amber-600 hover:bg-amber-50" title="Restaurar Documento">
 <i class="ph-bold ph-arrow-u-up-left text-lg"></i>
 </button>
 </form>
 @else
 <a href="{{ route('admin.adultos-mayores.documentos.archivo', [$adulto_mayor->cod_am, $doc->cod_doc_am]) }}" target="_blank" class="rounded-lg p-2 text-apoyo hover:bg-fondo-panel hover:text-titulo" title="Ver/Descargar">
 <i class="ph-bold ph-download-simple text-lg"></i>
 </a>
 <button type="button" @click='abrirEdicion(@json($doc))' class="rounded-lg p-2 text-apoyo hover:bg-fondo-panel hover:text-titulo" title="Editar Información">
 <i class="ph-bold ph-pencil-simple text-lg"></i>
 </button>
 <form action="{{ route('admin.adultos-mayores.documentos.destroy', ['adulto_mayor' => $adulto_mayor->cod_am, 'documento' => $doc->cod_doc_am]) }}" method="POST" class="inline" onsubmit="return confirm('¿Archivar este documento?');">
 @csrf @method('DELETE')
 <button type="submit" class="rounded-lg p-2 text-red-500 hover:bg-red-50" title="Archivar">
 <i class="ph-bold ph-archive text-lg"></i>
 </button>
 </form>
 @endif
 </div>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-12 text-center">
 <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-fondo-panel text-apoyo mb-3">
 <i class="ph-bold ph-folder-open text-3xl"></i>
 </div>
 <p class="text-sm font-bold text-apoyo">No hay documentos registrados para este adulto mayor.</p>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 {{-- MODAL DE REGISTRO / EDICIÓN --}}
 @include('pages.adultos-mayores.documentos.partials.formulario')
 </div>
</x-sistema-layout>
