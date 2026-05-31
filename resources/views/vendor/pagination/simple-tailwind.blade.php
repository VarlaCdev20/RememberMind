@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-apoyo bg-fondo-card border border-borde-suave cursor-not-allowed leading-5 rounded-md dark:text-apoyo dark:bg-fondo-panel dark:border-borde-suave">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-apoyo bg-fondo-card border border-borde-suave leading-5 rounded-md hover:text-apoyo focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-fondo-panel active:text-apoyo transition ease-in-out duration-150 dark:bg-fondo-panel dark:border-borde-suave dark:text-apoyo dark:focus:border-blue-700 dark:active:bg-fondo-panel dark:active:text-apoyo hover:bg-fondo-panel dark:hover:bg-fondo-panel dark:hover:text-apoyo">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-apoyo bg-fondo-card border border-borde-suave leading-5 rounded-md hover:text-apoyo focus:outline-none focus:ring ring-gray-300 focus:border-blue-300 active:bg-fondo-panel active:text-apoyo transition ease-in-out duration-150 dark:bg-fondo-panel dark:border-borde-suave dark:text-apoyo dark:focus:border-blue-700 dark:active:bg-fondo-panel dark:active:text-apoyo hover:bg-fondo-panel dark:hover:bg-fondo-panel dark:hover:text-apoyo">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-apoyo bg-fondo-card border border-borde-suave cursor-not-allowed leading-5 rounded-md dark:text-apoyo dark:bg-fondo-panel dark:border-borde-suave">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
