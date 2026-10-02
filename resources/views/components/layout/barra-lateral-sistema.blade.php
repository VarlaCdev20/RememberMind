@inject('sidebarService', 'App\Backend\Modulos\Identidad\Servicios\SidebarService')
@php
    $sections = $sidebarService->getSidebar();
    $previewRole = app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->activeRole(auth()->user());
    $isAdministracion = $previewRole === 'ADMINISTRADOR' || ($previewRole === null && auth()->user()?->hasRole('ADMINISTRADOR') && ! auth()->user()?->hasRole('SUPERADMINISTRADOR'));
    $currentRoute = request()->route()?->getName() ?? '';
    $routeActive = static function (?string $name) use ($currentRoute): bool {
        if (!$name) return false;
        if ($currentRoute === $name || str_starts_with($currentRoute, $name . '.')) return true;
        return str_ends_with($name, '.index') && str_starts_with($currentRoute, substr($name, 0, -6) . '.');
    };
    $initialOpen = null;
    foreach ($sections as $index => $section) {
        if (collect($section['items'] ?? [])->contains(fn ($item) => $routeActive($item['route'] ?? null))) {
            $initialOpen = $index;
            break;
        }
    }
    $inicioAdministracion = $isAdministracion && app(\App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion::class)->puedeVerRuta('admin.administracion.dashboard');
    $brandRoute = request()->routeIs('admin.enfermeria.*') ? 'admin.enfermeria.dashboard' : ($inicioAdministracion ? 'admin.administracion.dashboard' : 'dashboard');
@endphp

<aside id="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria' : 'sidebar' }}" class="rm-sidebar" aria-label="Navegación principal"
    :class="{ 'is-mobile-open': sidebarOpen }"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    x-data="{
        openSection: {{ $initialOpen !== null ? $initialOpen : 'null' }},
        flyoutIndex: null,
        flyoutTop: 0,
        tooltipLabel: null,
        tooltipTop: 0,
        showTooltip(label, element) {
            if (!sidebarCollapsed || window.innerWidth < 1024) return;
            this.tooltipLabel = label;
            this.tooltipTop = element.getBoundingClientRect().top + 10;
        },
        showFlyout(index, element) {
            if (!sidebarCollapsed || window.innerWidth < 1024) return;
            this.flyoutIndex = index;
            this.flyoutTop = Math.max(12, Math.min(element.getBoundingClientRect().top, window.innerHeight - 280));
        }
    }"
    @keydown.escape.window="flyoutIndex = null; if (sidebarOpen) { sidebarOpen = false; $nextTick(() => document.getElementById('sidebar-mobile-trigger')?.focus()); }">
    <div class="rm-sidebar__header h-[60px] min-h-[60px] max-h-[60px] box-border border-b border-[var(--rm-border)] flex items-center justify-between" style="height: 60px; min-height: 60px; max-height: 60px; box-sizing: border-box;">
        <a href="{{ route($brandRoute) }}" class="rm-sidebar__brand" wire:navigate @click="sidebarOpen = false" aria-label="RememberMind, inicio">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="" class="rm-sidebar__logo">
            <span class="rm-sidebar__brand-copy">
                <strong>RememberMind</strong>
                <small>{{ request()->routeIs('admin.enfermeria.*') ? 'ENFERMERÍA' : ($isAdministracion ? 'ADMINISTRACIÓN' : 'Cuidado geriátrico') }}</small>
            </span>
        </a>
        <button type="button" class="rm-sidebar__header-toggle" @click="toggleSidebarCollapse(); flyoutIndex = null; tooltipLabel = null"
            :aria-label="sidebarCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'"
            :title="sidebarCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'"
            :aria-expanded="sidebarCollapsed ? 'false' : 'true'"
            aria-controls="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria-nav' : 'sidebar-nav' }}">
            <i class="ph-bold ph-caret-double-left" aria-hidden="true" :class="{ 'is-collapsed': sidebarCollapsed }"></i>
        </button>
        <button type="button" class="rm-sidebar__mobile-close" @click="sidebarOpen = false; $nextTick(() => document.getElementById('sidebar-mobile-trigger')?.focus())" aria-label="Cerrar menú lateral">
            <i class="ph-bold ph-x" aria-hidden="true"></i>
        </button>
    </div>

    <nav id="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria-nav' : 'sidebar-nav' }}" class="rm-sidebar__nav" aria-label="Secciones del sistema" @mouseleave="if (!$event.relatedTarget?.closest('.rm-sidebar__flyout')) flyoutIndex = null; tooltipLabel = null">
        @foreach ($sections as $index => $section)
            @php
                $items = $section['items'] ?? [];
                $hasChildren = count($items) > 0;
                $active = $routeActive($section['route'] ?? null) || collect($items)->contains(fn ($item) => $routeActive($item['route'] ?? null));
                $group = $section['group'] ?? null;
                $previousGroup = $index > 0 ? ($sections[$index - 1]['group'] ?? null) : null;
            @endphp
            @if($group && $group !== $previousGroup)
                <p class="rm-sidebar__group">{{ $group }}</p>
            @endif
            <div class="rm-sidebar__entry">
                @if($hasChildren)
                    <button type="button" class="rm-sidebar__item {{ $active ? 'is-active' : '' }}"
                        title="{{ $section['title'] }}" aria-label="{{ $section['title'] }}{{ !empty($section['badge']) ? ', '.$section['badge'].' pendientes' : '' }}" data-label="{{ mb_strtoupper($section['title']) }}"
                        :aria-expanded="((!sidebarCollapsed || window.innerWidth < 1024) && openSection === {{ $index }}) || (sidebarCollapsed && flyoutIndex === {{ $index }}) ? 'true' : 'false'"
                        :aria-controls="sidebarCollapsed && window.innerWidth >= 1024 ? 'rm-sidebar-flyout-{{ $index }}' : 'rm-sidebar-submenu-{{ $index }}'"
                        @click="sidebarCollapsed && window.innerWidth >= 1024 ? showFlyout({{ $index }}, $el) : (openSection = openSection === {{ $index }} ? null : {{ $index }})"
                        @mouseenter="showFlyout({{ $index }}, $el)" @focus="showFlyout({{ $index }}, $el)"
                        @mouseleave="if (!$event.relatedTarget?.closest('.rm-sidebar__flyout')) flyoutIndex = null">
                        <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                        <i class="ph-bold {{ $section['icon'] }} rm-sidebar__icon" aria-hidden="true"></i>
                        <span class="rm-sidebar__label">{{ $section['title'] }}</span>
                        @if(!empty($section['badge']))<span class="rm-sidebar__badge" aria-hidden="true">{{ $section['badge'] }}</span>@endif
                        <i class="ph-bold ph-caret-down rm-sidebar__chevron" aria-hidden="true" :class="{ 'is-open': openSection === {{ $index }} }"></i>
                    </button>
                    <div id="rm-sidebar-submenu-{{ $index }}" class="rm-sidebar__submenu"
                        x-show="openSection === {{ $index }} && (!sidebarCollapsed || window.innerWidth < 1024)"
                        x-transition:enter="rm-sidebar__submenu-enter"
                        x-transition:enter-start="rm-sidebar__submenu-start"
                        x-transition:enter-end="rm-sidebar__submenu-end"
                        x-transition:leave="rm-sidebar__submenu-enter"
                        x-transition:leave-start="rm-sidebar__submenu-end"
                        x-transition:leave-end="rm-sidebar__submenu-start"
                        style="display: none;">
                        @foreach($items as $item)
                            @php $childActive = $routeActive($item['route']); @endphp
                            <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false"
                                class="rm-sidebar__subitem {{ $childActive ? 'is-active' : '' }}" data-label="{{ mb_strtoupper($item['label']) }}"
                                @if($childActive) aria-current="page" @endif>
                                <span class="rm-sidebar__subindicator" aria-hidden="true"></span>
                                <span>{{ $item['label'] }}</span>
                                @if(!empty($item['badge']))<span class="rm-sidebar__badge">{{ $item['badge'] }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ route($section['route']) }}" wire:navigate @click="sidebarOpen = false; flyoutIndex = null; tooltipLabel = null"
                        class="rm-sidebar__item {{ $active ? 'is-active' : '' }}" title="{{ $section['title'] }}" data-label="{{ mb_strtoupper($section['title']) }}"
                        @mouseenter="showTooltip(@js($section['title']), $el)" @focus="showTooltip(@js($section['title']), $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null"
                        @if($active) aria-current="page" @endif>
                        <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                        <i class="ph-bold {{ $section['icon'] }} rm-sidebar__icon" aria-hidden="true"></i>
                        <span class="rm-sidebar__label">{{ $section['title'] }}</span>
                        @if(!empty($section['badge']))<span class="rm-sidebar__badge">{{ $section['badge'] }}</span>@endif
                    </a>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="rm-sidebar__tooltip" x-show="sidebarCollapsed && tooltipLabel && window.innerWidth >= 1024"
        x-transition:enter="rm-sidebar__tooltip-enter" x-transition:enter-start="rm-sidebar__tooltip-start" x-transition:enter-end="rm-sidebar__tooltip-end"
        :style="{ top: tooltipTop + 'px' }" x-text="tooltipLabel" role="tooltip" x-cloak></div>

    @foreach ($sections as $index => $section)
        @if(!empty($section['items']))
            <div id="rm-sidebar-flyout-{{ $index }}" class="rm-sidebar__flyout" x-show="sidebarCollapsed && flyoutIndex === {{ $index }} && window.innerWidth >= 1024"
                :style="{ top: flyoutTop + 'px' }" @mouseenter="flyoutIndex = {{ $index }}" @mouseleave="flyoutIndex = null"
                x-transition.opacity.duration.150ms x-cloak>
                <strong class="rm-sidebar__flyout-title">{{ $section['title'] }}</strong>
                @foreach($section['items'] as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate @click="flyoutIndex = null; sidebarOpen = false"
                        class="rm-sidebar__flyout-link {{ $routeActive($item['route']) ? 'is-active' : '' }}"
                        @if($routeActive($item['route'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </div>
        @endif
    @endforeach

    @if($isAdministracion)
        <a href="{{ route('profile.show') }}" wire:navigate @click="sidebarOpen = false; tooltipLabel = null"
            @mouseenter="showTooltip('Mi perfil', $el)" @focus="showTooltip('Mi perfil', $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null"
            class="rm-sidebar__item rm-sidebar__profile {{ $routeActive('profile.show') ? 'is-active' : '' }}" title="Mi perfil" aria-label="Mi perfil" @if($routeActive('profile.show')) aria-current="page" @endif>
            <span class="rm-sidebar__indicator" aria-hidden="true"></span>
            <i class="ph-bold ph-user-circle rm-sidebar__icon" aria-hidden="true"></i>
            <span class="rm-sidebar__label">Mi perfil</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="rm-sidebar__logout">
            @csrf
            <button type="submit" class="rm-sidebar__item" title="Cerrar sesión" aria-label="Cerrar sesión"
                @mouseenter="showTooltip('Cerrar sesión', $el)" @focus="showTooltip('Cerrar sesión', $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null">
                <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                <i class="ph-bold ph-sign-out rm-sidebar__icon" aria-hidden="true"></i>
                <span class="rm-sidebar__label">Cerrar sesión</span>
            </button>
        </form>
    @endif
</aside>
