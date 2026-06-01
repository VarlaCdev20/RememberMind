@if ($paginator->hasPages())
 <nav role="navigation" aria-label="Navegación de páginas" class="w-full">
 <div class="flex gap-2 items-center justify-between sm:hidden">
 @if ($paginator->onFirstPage())
 <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-apoyo bg-fondo-panel border border-borde-suave cursor-not-allowed rounded-xl">
 Anterior
 </span>
 @else
 <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-titulo bg-fondo-app hover:bg-fondo-panel border border-borde-suave rounded-xl transition">
 Anterior
 </a>
 @endif

 @if ($paginator->hasMorePages())
 <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-titulo bg-fondo-app hover:bg-fondo-panel border border-borde-suave rounded-xl transition">
 Siguiente
 </a>
 @else
 <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-apoyo bg-fondo-panel border border-borde-suave cursor-not-allowed rounded-xl">
 Siguiente
 </span>
 @endif
 </div>

 <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between bg-fondo-panel p-4 rounded-2xl border border-borde-suave shadow-sm">
 <div>
 <p class="text-xs font-bold text-titulo">
 Mostrando
 @if ($paginator->firstItem())
 <span class="font-black">{{ $paginator->firstItem() }}</span>
 a
 <span class="font-black">{{ $paginator->lastItem() }}</span>
 @else
 {{ $paginator->count() }}
 @endif
 de
 <span class="font-black">{{ $paginator->total() }}</span>
 resultados
 </p>
 </div>

 <div>
 <span class="inline-flex rounded-xl shadow-sm overflow-hidden border border-borde-suave">
 {{-- Previous Page Link --}}
 @if ($paginator->onFirstPage())
 <span aria-disabled="true">
 <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-apoyo bg-fondo-app cursor-not-allowed">
 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
 </span>
 </span>
 @else
 <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-bold text-titulo bg-fondo-app hover:bg-fondo-panel transition">
 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
 </a>
 @endif

 {{-- Pagination Elements --}}
 @foreach ($elements as $element)
 @if (is_string($element))
 <span aria-disabled="true">
 <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-apoyo bg-fondo-app cursor-default">{{ $element }}</span>
 </span>
 @endif

 @if (is_array($element))
 @foreach ($element as $page => $url)
 @if ($page == $paginator->currentPage())
 <span aria-current="page">
 <span class="inline-flex items-center px-4 py-2 text-xs font-bold text-inverso bg-boton-principal cursor-default">{{ $page }}</span>
 </span>
 @else
 <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-titulo bg-fondo-app hover:bg-fondo-panel transition">{{ $page }}</a>
 @endif
 @endforeach
 @endif
 @endforeach

 {{-- Next Page Link --}}
 @if ($paginator->hasMorePages())
 <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-bold text-titulo bg-fondo-app hover:bg-fondo-panel transition">
 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
 </a>
 @else
 <span aria-disabled="true">
 <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-apoyo bg-fondo-app cursor-not-allowed">
 <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
 </span>
 </span>
 @endif
 </span>
 </div>
 </div>
 </nav>
@endif
