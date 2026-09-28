<aside id="sidebar" @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="[
        sidebarOpen ?
        'translate-x-0' :
        '-translate-x-full lg:translate-x-0',

        sidebarCollapsed ?
        'lg:w-[76px]' :
        'lg:w-[248px]'
    ]"
    class="sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[248px] flex-col

        border-r
        border-[var(--rm-border-soft)]

        shadow-[6px_0_24px_rgba(47,40,36,0.06)]

        transition-all
        duration-300
        ease-in-out
    "
    aria-label="Barra lateral de navegación">

    {{-- ============================================================
         BOTÓN COLAPSAR
         ============================================================ --}}
    <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
        class="
            absolute
            -right-4
            top-7
            z-20

            hidden
            h-8
            w-8
            items-center
            justify-center

            rounded-full

            border
            border-[var(--rm-border-soft)]

            bg-[var(--rm-surface-raised)]

            text-[var(--rm-text-secondary)]

            shadow-[var(--rm-shadow-sm)]

            transition-all
            duration-200

            hover:border-[var(--rm-eucalyptus-500)]
            hover:bg-[var(--rm-eucalyptus-100)]
            hover:text-[var(--rm-coffee-950)]

            lg:flex
        "
        aria-label="Contraer o expandir menú lateral">
        <i class="
                ph-bold
                ph-caret-left
                text-sm
                transition-transform
                duration-300
            "
            :class="sidebarCollapsed ? 'rotate-180' : ''"></i>
    </button>


    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="shrink-0 px-3 pb-2 pt-4">

        <div class="sidebar-brand-glass
                flex
                min-h-[64px]
                items-center

                rounded-2xl

                border
                border-[var(--rm-border-soft)]

                px-3
                py-2.5

                shadow-[0_2px_8px_rgba(47,40,36,0.045)]

                transition-all
                duration-300
            "
            :class="sidebarCollapsed
                ?
                'justify-center px-2' :
                'justify-between'">

            <div class="flex min-w-0 items-center gap-3">

                <div
                    class="
                        flex
                        h-10
                        w-10
                        shrink-0
                        items-center
                        justify-center

                        rounded-xl

                        bg-[var(--rm-surface-raised)]

                        shadow-[0_1px_4px_rgba(47,40,36,0.06)]
                    ">
                    <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="CENTRO GERIÁTRICO LOS ALMENDROS"
                        class="
                            h-8
                            w-8
                            rounded-lg
                            object-contain

                            transition-transform
                            duration-300

                            hover:scale-105
                        ">
                </div>


                <div x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms class="min-w-0">

                    <h2
                        class="
                            max-w-[185px]

                            text-[11px]
                            font-extrabold
                            uppercase

                            leading-[1.12]
                            tracking-[0.035em]

                            text-[var(--rm-coffee-950)]
                        ">
                        CENTRO GERIÁTRICO<br>
                        LOS ALMENDROS
                    </h2>

                    <p
                        class="
                            mt-1
                            truncate

                            text-[9.5px]
                            font-extrabold
                            uppercase

                            tracking-[0.14em]

                            text-[var(--rm-eucalyptus-700)]
                        ">
                        RememberMind
                    </p>

                </div>

            </div>


            {{-- Cerrar sidebar móvil --}}
            <button type="button" @click="sidebarOpen = false"
                class="
                    flex
                    h-8
                    w-8
                    items-center
                    justify-center

                    rounded-lg

                    text-[var(--rm-text-secondary)]

                    transition

                    hover:bg-[var(--rm-earth-100)]
                    hover:text-[var(--rm-text-primary)]

                    lg:hidden
                "
                aria-label="Cerrar menú lateral">
                <i class="ph-bold ph-x"></i>
            </button>

        </div>

    </div>


    {{-- ============================================================
         MENÚ
         ============================================================ --}}
    <div
        class="
            mt-1
            flex-1
            overflow-y-auto

            px-3
            pb-4

            [scrollbar-width:thin]
            [scrollbar-color:var(--rm-taupe-400)_transparent]
        ">

        @php
            $safeUrl = function (?string $route, string $fallback = 'javascript:void(0)') {
                return $route && Route::has($route) ? route($route) : $fallback;
            };

            $isDisabled = function (?string $route) {
                return !$route || !Route::has($route);
            };

            $isActiveItem = function (array $item) {
                if (isset($item['route']) && $item['route']) {
                    if (request()->routeIs($item['route'])) {
                        return true;
                    }

                    $base = preg_replace('/\.index$/', '.*', $item['route']);

                    if ($base !== $item['route'] && request()->routeIs($base)) {
                        return true;
                    }
                }

                return false;
            };

            $isActiveSection = function (array $section) use ($isActiveItem) {
                if (isset($section['items'])) {
                    foreach ($section['items'] as $item) {
                        if ($isActiveItem($item)) {
                            return true;
                        }
                    }
                }

                return false;
            };
        @endphp


        @inject('sidebarService', 'App\Backend\Modulos\Identidad\Servicios\SidebarService')


        @php
            $sections = $sidebarService->getSidebar();

            $initialOpen = null;

            foreach ($sections as $idx => $sec) {
                if (!isset($sec['route']) && !empty($sec['items'])) {
                    $sActive = false;

                    foreach ($sec['items'] as $it) {
                        if ($isActiveItem($it)) {
                            $sActive = true;
                            break;
                        }
                    }

                    if ($sActive) {
                        $initialOpen = $idx;
                        break;
                    }

                    if (!empty($sec['default_expanded']) && $initialOpen === null) {
                        $initialOpen = $idx;
                    }
                }
            }
        @endphp


        <nav x-data="{
            openSection: {{ $initialOpen !== null ? $initialOpen : 'null' }},
            search: ''
        }" class="space-y-1">

            {{-- ====================================================
                 BUSCADOR
                 ==================================================== --}}
            <div x-show="!sidebarCollapsed" class="mb-4 px-1 pt-1">

                <div class="relative flex items-center">

                    <i
                        class="
                            ph-bold
                            ph-magnifying-glass

                            pointer-events-none

                            absolute
                            left-3

                            text-sm

                            text-[var(--rm-text-muted)]
                        "></i>


                    <input type="text" x-model="search" placeholder="Buscar módulo..."
                        class="
                            h-10
                            w-full

                            rounded-xl

                            border
                            border-[var(--rm-border-soft)]

                            bg-[var(--rm-input-bg)]

                            pl-9
                            pr-8

                            text-[12px]
                            font-semibold

                            text-[var(--rm-text-body)]

                            outline-none

                            transition-all
                            duration-200

                            placeholder:font-medium
                            placeholder:text-[var(--rm-text-muted)]

                            hover:border-[var(--rm-border)]

                            focus:border-[var(--rm-eucalyptus-600)]
                            focus:bg-[var(--rm-surface-raised)]
                            focus:ring-4
                            focus:ring-[rgba(119,134,109,0.10)]
                        ">


                    <button x-show="search.length > 0" @click="search = ''" type="button"
                        class="
                            absolute
                            right-2

                            flex
                            h-6
                            w-6
                            items-center
                            justify-center

                            rounded-md

                            text-[var(--rm-text-muted)]

                            transition

                            hover:bg-[var(--rm-earth-100)]
                            hover:text-[var(--rm-text-primary)]
                        "
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>

                </div>

            </div>


            {{-- ====================================================
                 SECCIONES
                 ==================================================== --}}
            @foreach ($sections as $section)
                @php
                    $isDirect = isset($section['route']) && !empty($section['route']);

                    $isSectionActive = $isDirect
                        ? request()->routeIs($section['route']) ||
                            (preg_replace('/\.index$/', '.*', $section['route']) !== $section['route'] &&
                                request()->routeIs(preg_replace('/\.index$/', '.*', $section['route'])))
                        : $isActiveSection($section);

                    $url = $isDirect ? $safeUrl($section['route']) : '#';

                    $group = $section['group'] ?? null;

                    $prevGroup =
                        $loop->index > 0 && isset($sections[$loop->index - 1]['group'])
                            ? $sections[$loop->index - 1]['group']
                            : null;

                    $itemsJson = json_encode(strtolower(implode(' ', array_column($section['items'] ?? [], 'label'))));

                    $titleJson = json_encode(strtolower($section['title']));
                @endphp


                {{-- ================================================
                     GRUPO / CATEGORÍA
                     ================================================ --}}
                @if (!empty($group) && $group !== $prevGroup)
                    <div class="
                            px-3
                            pb-1.5
                            pt-4
                        "
                        x-show="
                            !sidebarCollapsed
                            && search === ''
                        ">

                        <span
                            class="
                                select-none

                                text-[9.5px]
                                font-extrabold
                                uppercase

                                tracking-[0.17em]

                                text-[var(--rm-text-secondary)]
                            ">
                            {{ $group }}
                        </span>

                    </div>
                @endif


                {{-- ================================================
                     SECCIÓN CON LINK DIRECTO
                     ================================================ --}}
                @if ($isDirect)
                    <div class="
                            group/section
                            relative
                        "
                        x-show="
                            search === ''
                            || {{ $titleJson }}.includes(
                                search.toLowerCase()
                            )
                        ">

                        <a wire:navigate href="{{ $url }}"
                            class="
                                flex
                                min-h-[44px]
                                items-center
                                justify-between

                                rounded-xl

                                border

                                px-3
                                py-2.5

                                text-sm

                                transition-all
                                duration-200

                                {{ $isSectionActive
                                    ? '
                                                                        border-[var(--rm-nav-selected-border)]
                                                                        bg-[var(--rm-nav-selected)]
                                                                        text-[var(--rm-nav-selected-text)]
                                                                        shadow-[0_4px_12px_rgba(86,99,79,0.14)]
                                                                      '
                                    : '
                                                                        border-transparent
                                                                        text-[var(--rm-nav-text)]
                                                                        hover:bg-[var(--rm-nav-hover)]
                                                                        hover:text-[var(--rm-nav-text-hover)]
                                                                      ' }}
                            "
                            :class="sidebarCollapsed
                                ?
                                'justify-center px-0' :
                                ''"
                            title="{{ $section['title'] }}">

                            <div
                                class="
                                    flex
                                    min-w-0
                                    items-center
                                    gap-3
                                ">

                                <i
                                    class="
                                        ph-bold
                                        {{ $section['icon'] }}

                                        shrink-0

                                        text-[19px]

                                        transition-all
                                        duration-200

                                        {{ $isSectionActive
                                            ? 'text-[var(--rm-nav-selected-icon)]'
                                            : 'text-[var(--rm-text-secondary)] group-hover/section:text-[var(--rm-coffee-950)]' }}
                                    "></i>


                                <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms
                                    class="
                                        truncate

                                        text-[12px]
                                        font-extrabold
                                        uppercase

                                        tracking-[0.055em]
                                    ">
                                    {{ $section['title'] }}
                                </span>

                            </div>


                            @if (!empty($section['badge']))
                                <span x-show="!sidebarCollapsed"
                                    class="
                                        inline-flex
                                        min-w-5
                                        shrink-0
                                        items-center
                                        justify-center

                                        rounded-full

                                        border
                                        border-[var(--rm-coral-200)]

                                        bg-[var(--rm-danger-soft)]

                                        px-1.5
                                        py-0.5

                                        text-[9px]
                                        font-extrabold

                                        text-[var(--rm-danger)]
                                    ">
                                    {{ $section['badge'] }}
                                </span>
                            @endif

                        </a>


                        {{-- DIVISOR EN MODO COLAPSADO --}}
                        <div class="
                                mx-auto
                                my-2

                                h-px
                                w-8

                                bg-[var(--rm-border-soft)]
                            "
                            x-show="sidebarCollapsed"></div>


                        {{-- TOOLTIP MODO COLAPSADO --}}
                        <div x-show="sidebarCollapsed"
                            class="
                                pointer-events-none

                                absolute
                                left-full
                                top-1/2
                                z-50

                                ml-3

                                hidden

                                -translate-y-1/2

                                whitespace-nowrap

                                rounded-xl

                                bg-[var(--rm-coffee-900)]

                                px-3
                                py-2

                                text-[11px]
                                font-bold

                                text-white

                                shadow-[var(--rm-shadow-md)]

                                group-hover/section:block
                            ">

                            {{ $section['title'] }}


                            @if (!empty($section['badge']))
                                <span
                                    class="
                                        ml-1.5

                                        rounded-full

                                        bg-[var(--rm-danger)]

                                        px-1.5

                                        text-[9px]
                                        text-white
                                    ">
                                    {{ $section['badge'] }}
                                </span>
                            @endif

                        </div>

                    </div>
                @else
                    {{-- ================================================
                         SECCIÓN ACORDEÓN
                         ================================================ --}}
                    <div class="
                            group/section
                            relative
                        "
                        x-show="
                            search === ''
                            || {{ $titleJson }}.includes(
                                search.toLowerCase()
                            )
                            || {{ $itemsJson }}.includes(
                                search.toLowerCase()
                            )
                        ">

                        {{-- HEADER DE SECCIÓN --}}
                        <button type="button"
                            @click="
                                openSection =
                                    (
                                        openSection === {{ $loop->index }}
                                    )
                                        ? null
                                        : {{ $loop->index }}
                            "
                            class="
                                flex
                                min-h-[44px]
                                w-full

                                items-center
                                justify-between

                                rounded-xl

                                border

                                px-3
                                py-2.5

                                text-sm

                                transition-all
                                duration-200

                                {{ $isSectionActive
                                    ? '
                                                                        border-[rgba(101,116,93,0.30)]
                                                                        bg-[rgba(145,160,131,0.22)]
                                                                        text-[var(--rm-coffee-950)]
                                                                      '
                                    : '
                                                                        border-transparent
                                                                        text-[var(--rm-nav-text)]
                                                                        hover:bg-[var(--rm-nav-hover)]
                                                                        hover:text-[var(--rm-nav-text-hover)]
                                                                      ' }}
                            "
                            :class="sidebarCollapsed
                                ?
                                'justify-center px-0' :
                                ''">

                            <div
                                class="
                                    flex
                                    min-w-0
                                    items-center
                                    gap-3
                                ">

                                <i
                                    class="
                                        ph-bold
                                        {{ $section['icon'] }}

                                        shrink-0

                                        text-[19px]

                                        transition-colors
                                        duration-200

                                        {{ $isSectionActive
                                            ? 'text-[var(--rm-eucalyptus-700)]'
                                            : 'text-[var(--rm-text-secondary)] group-hover/section:text-[var(--rm-coffee-950)]' }}
                                    "></i>


                                <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms
                                    class="
                                        truncate

                                        text-[12px]
                                        font-extrabold
                                        uppercase

                                        tracking-[0.055em]
                                    ">
                                    {{ $section['title'] }}
                                </span>

                            </div>


                            <div class="
                                    flex
                                    items-center
                                    gap-2
                                "
                                x-show="!sidebarCollapsed">

                                @if (!empty($section['badge']))
                                    <span
                                        class="
                                            inline-flex
                                            min-w-5
                                            shrink-0
                                            items-center
                                            justify-center

                                            rounded-full

                                            border
                                            border-[var(--rm-coral-200)]

                                            bg-[var(--rm-danger-soft)]

                                            px-1.5
                                            py-0.5

                                            text-[9px]
                                            font-extrabold

                                            text-[var(--rm-danger)]
                                        ">
                                        {{ $section['badge'] }}
                                    </span>
                                @endif


                                <i class="
                                        ph-bold
                                        ph-caret-down

                                        text-[10px]

                                        text-[var(--rm-text-muted)]

                                        transition-transform
                                        duration-300
                                    "
                                    :class="(
                                        openSection === {{ $loop->index }} ||
                                        (
                                            search !== '' &&
                                            {{ $itemsJson }}.includes(
                                                search.toLowerCase()
                                            )
                                        )
                                    ) ?
                                    'rotate-180' :
                                    ''"></i>

                            </div>

                        </button>


                        {{-- ============================================
                             SUBITEMS
                             ============================================ --}}
                        <div x-show="
                                (
                                    openSection === {{ $loop->index }}
                                    || (
                                        search !== ''
                                        && {{ $itemsJson }}.includes(
                                            search.toLowerCase()
                                        )
                                    )
                                )
                                && !sidebarCollapsed
                            "
                            x-transition:enter="
                                transition
                                ease-out
                                duration-200
                            "
                            x-transition:enter-start="
                                opacity-0
                                -translate-y-1
                            "
                            x-transition:enter-end="
                                opacity-100
                                translate-y-0
                            "
                            class="
                                mt-1
                                space-y-1
                                pl-4
                            ">

                            @foreach ($section['items'] as $item)
                                @php
                                    $active = $isActiveItem($item);

                                    $disabled = $isDisabled($item['route']);

                                    $url = $safeUrl($item['route']);

                                    $labelJson = json_encode(strtolower($item['label']));
                                @endphp


                                <a wire:navigate href="{{ $url }}"
                                    x-show="
                                        search === ''
                                        || {{ $labelJson }}.includes(
                                            search.toLowerCase()
                                        )
                                        || {{ $titleJson }}.includes(
                                            search.toLowerCase()
                                        )
                                    "
                                    @if ($disabled) title="Próximamente"
                                    @else
                                        title="{{ $item['label'] }}" @endif
                                    class="
                                        group/item
                                        relative

                                        flex
                                        min-h-[38px]
                                        items-center
                                        justify-between
                                        gap-2.5

                                        rounded-xl

                                        border

                                        px-3
                                        py-2

                                        transition-all
                                        duration-200

                                        {{ $active
                                            ? '
                                                                                        border-[var(--rm-nav-selected-border)]
                                                                                        bg-[var(--rm-nav-selected)]
                                                                                        text-[var(--rm-nav-selected-text)]
                                                                                        shadow-[0_3px_10px_rgba(86,99,79,0.12)]
                                                                                      '
                                            : '
                                                                                        border-transparent
                                                                                        text-[var(--rm-text-secondary)]
                                                                                        hover:bg-[rgba(255,255,255,0.18)]
                                                                                        hover:text-[var(--rm-coffee-950)]
                                                                                      ' }}

                                        {{ $disabled
                                            ? '
                                                                                        cursor-not-allowed
                                                                                        opacity-40
                                                                                        grayscale
                                                                                      '
                                            : '' }}
                                    ">

                                    <div
                                        class="
                                            flex
                                            min-w-0
                                            items-center
                                            gap-2
                                        ">

                                        <span
                                            class="
                                                h-1.5
                                                w-1.5
                                                shrink-0

                                                rounded-full

                                                {{ $active ? 'bg-[var(--rm-coffee-950)] opacity-70' : 'bg-[var(--rm-taupe-500)] opacity-40' }}
                                            "></span>


                                        <span
                                            class="
                                                truncate

                                                text-[11.5px]
                                                font-bold
                                                uppercase

                                                tracking-[0.055em]
                                            ">
                                            {{ $item['label'] }}
                                        </span>

                                    </div>


                                    @if (!empty($item['badge']))
                                        <span
                                            class="
                                                inline-flex
                                                min-w-5
                                                shrink-0
                                                items-center
                                                justify-center

                                                rounded-full

                                                border
                                                border-[var(--rm-coral-200)]

                                                bg-[var(--rm-danger-soft)]

                                                px-1.5

                                                text-[9px]
                                                font-extrabold

                                                text-[var(--rm-danger)]
                                            ">
                                            {{ $item['badge'] }}
                                        </span>
                                    @endif

                                </a>
                            @endforeach

                        </div>


                        {{-- DIVISOR MODO COLAPSADO --}}
                        <div class="
                                mx-auto
                                my-2

                                h-px
                                w-8

                                bg-[var(--rm-border-soft)]
                            "
                            x-show="sidebarCollapsed"></div>


                        {{-- ============================================
                             FLYOUT COLAPSADO
                             ============================================ --}}
                        <div x-show="sidebarCollapsed"
                            class="
                                pointer-events-none

                                absolute
                                left-full
                                top-0
                                z-50

                                ml-3

                                hidden
                                min-w-[220px]

                                rounded-2xl

                                border
                                border-[var(--rm-border-soft)]

                                bg-[var(--rm-surface-raised)]

                                p-3

                                shadow-[var(--rm-shadow-lg)]

                                group-hover/section:block
                                group-hover/section:pointer-events-auto
                            ">

                            <div
                                class="
                                    mb-2

                                    flex
                                    items-center
                                    gap-2

                                    border-b
                                    border-[var(--rm-border-soft)]

                                    pb-2
                                ">

                                <i
                                    class="
                                        ph-bold
                                        {{ $section['icon'] }}

                                        text-base

                                        text-[var(--rm-eucalyptus-700)]
                                    "></i>


                                <span
                                    class="
                                        text-[11px]
                                        font-extrabold
                                        uppercase

                                        tracking-[0.07em]

                                        text-[var(--rm-coffee-950)]
                                    ">
                                    {{ $section['title'] }}
                                </span>

                            </div>


                            <div class="space-y-1">

                                @foreach ($section['items'] as $item)
                                    @php
                                        $active = $isActiveItem($item);

                                        $url = $safeUrl($item['route']);
                                    @endphp


                                    <a wire:navigate href="{{ $url }}"
                                        class="
                                            flex
                                            min-h-[36px]
                                            items-center
                                            justify-between

                                            rounded-lg

                                            px-2.5
                                            py-1.5

                                            transition

                                            {{ $active
                                                ? '
                                                                                                bg-[var(--rm-nav-selected)]
                                                                                                text-[var(--rm-nav-selected-text)]
                                                                                              '
                                                : '
                                                                                                text-[var(--rm-text-secondary)]
                                                                                                hover:bg-[var(--rm-earth-100)]
                                                                                                hover:text-[var(--rm-text-primary)]
                                                                                              ' }}
                                        ">

                                        <span
                                            class="
                                                truncate

                                                text-[11px]
                                                font-bold
                                                uppercase

                                                tracking-[0.05em]
                                            ">
                                            {{ $item['label'] }}
                                        </span>


                                        @if (!empty($item['badge']))
                                            <span
                                                class="
                                                    rounded-full

                                                    bg-[var(--rm-danger-soft)]

                                                    px-1.5

                                                    text-[9px]
                                                    font-bold

                                                    text-[var(--rm-danger)]
                                                ">
                                                {{ $item['badge'] }}
                                            </span>
                                        @endif

                                    </a>
                                @endforeach

                            </div>

                        </div>

                    </div>
                @endif
            @endforeach

        </nav>

    </div>


    {{-- ============================================================
         FOOTER
         ============================================================ --}}
    <div
        class="
            shrink-0
            space-y-2

            border-t
            border-[rgba(94,80,73,0.13)]

            p-3
        ">

        @php
            $currentUser = auth()->user();

            $userRole = $currentUser ? $currentUser->getRoleNames()->first() ?? 'USUARIO' : null;
        @endphp


        {{-- ================================================
             TARJETA DEL USUARIO
             ================================================ --}}
        @if ($currentUser)
            <div class="px-1" x-show="!sidebarCollapsed">

                <div
                    class="
                        flex
                        items-center
                        gap-2.5

                        rounded-xl

                        border
                        border-[rgba(94,80,73,0.16)]

                        bg-[rgba(247,243,240,0.46)]

                        p-2.5
                    ">

                    <div
                        class="
                            flex
                            h-8
                            w-8
                            shrink-0
                            items-center
                            justify-center

                            rounded-full

                            bg-[var(--rm-eucalyptus-300)]

                            text-[12px]
                            font-extrabold

                            text-[var(--rm-coffee-950)]
                        ">
                        {{ strtoupper(substr($currentUser->nombres ?? 'U', 0, 1)) }}
                    </div>


                    <div class="min-w-0 flex-1">

                        <p
                            class="
                                truncate

                                text-[11.5px]
                                font-extrabold
                                uppercase

                                leading-tight

                                text-[var(--rm-coffee-950)]
                            ">
                            {{ $currentUser->nombres }}
                        </p>


                        <span
                            class="
                                mt-1

                                inline-flex
                                items-center
                                gap-1.5

                                text-[8.5px]
                                font-extrabold
                                uppercase

                                tracking-[0.08em]

                                text-[var(--rm-green-700)]
                            ">

                            <span
                                class="
                                    h-1.5
                                    w-1.5

                                    rounded-full

                                    bg-[var(--rm-success)]
                                "></span>

                            {{ $userRole ?? 'ACTIVO' }}

                        </span>

                    </div>

                </div>

            </div>
        @endif


        {{-- ================================================
             CENTRO DE AYUDA
             ================================================ --}}
        <button type="button"
            class="
                group

                flex
                min-h-[42px]
                w-full
                items-center
                gap-3

                rounded-xl

                border
                border-transparent

                px-3
                py-2

                text-[11px]
                font-extrabold
                uppercase

                tracking-[0.05em]

                text-[var(--rm-text-secondary)]

                transition-all
                duration-200

                hover:border-[rgba(101,116,93,0.20)]
                hover:bg-[var(--rm-eucalyptus-200)]
                hover:text-[var(--rm-coffee-950)]

                active:scale-[0.99]
            "
            :class="sidebarCollapsed
                ?
                'justify-center px-0' :
                ''">

            <i
                class="
                    ph-bold
                    ph-question

                    shrink-0

                    text-base

                    transition-transform

                    group-hover:rotate-6
                "></i>


            <span x-show="!sidebarCollapsed" x-transition.opacity.duration.200ms>
                Centro de ayuda
            </span>

        </button>

    </div>

</aside>
