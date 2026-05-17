@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de páginas" class="w-full">
        <div class="flex gap-2 items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C]/50 bg-[#E6DDD3]/50 border border-[#C7B5A3] cursor-not-allowed rounded-xl">
                    Anterior
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C] bg-[#D5C7B9] hover:bg-[#C7B5A3] border border-[#C7B5A3] rounded-xl transition">
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C] bg-[#D5C7B9] hover:bg-[#C7B5A3] border border-[#C7B5A3] rounded-xl transition">
                    Siguiente
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C]/50 bg-[#E6DDD3]/50 border border-[#C7B5A3] cursor-not-allowed rounded-xl">
                    Siguiente
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between bg-[#E6DDD3]/90 p-4 rounded-2xl border border-[#C7B5A3] shadow-sm">
            <div>
                <p class="text-xs font-bold text-[#2F3E5C]">
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
                <span class="inline-flex rounded-xl shadow-sm overflow-hidden border border-[#C7B5A3]">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true">
                            <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-[#2F3E5C]/40 bg-[#E6DDD3] cursor-not-allowed">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-bold text-[#2F3E5C] bg-[#D5C7B9] hover:bg-[#C7B5A3] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C]/50 bg-[#E6DDD3] cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-4 py-2 text-xs font-black text-white bg-[#2F3E5C] cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="inline-flex items-center px-4 py-2 text-xs font-black text-[#2F3E5C] bg-[#D5C7B9] hover:bg-[#C7B5A3] transition">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center px-3 py-2 text-sm font-bold text-[#2F3E5C] bg-[#D5C7B9] hover:bg-[#C7B5A3] transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                        </a>
                    @else
                        <span aria-disabled="true">
                            <span class="inline-flex items-center px-3 py-2 text-sm font-bold text-[#2F3E5C]/40 bg-[#E6DDD3] cursor-not-allowed">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
