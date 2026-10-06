@inject('sidebarService', 'App\Backend\Modulos\Identidad\Servicios\SidebarService')
@php
    $sections = $sidebarService->getSidebar();
    $sidebarUser = auth()->user();
    $sidebarName = trim((string) ($sidebarUser?->nombres ?: $sidebarUser?->name ?: 'Usuario'));
    $sidebarNameParts = preg_split('/\s+/u', $sidebarName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $sidebarInitials = mb_strtoupper(mb_substr($sidebarNameParts[0] ?? 'U', 0, 1).mb_substr($sidebarNameParts[1] ?? '', 0, 1));
    $previewRole = app(\App\Backend\Modulos\Identidad\Servicios\RolePreviewService::class)->activeRole(auth()->user());
    $sidebarRole = $previewRole
        ?? (request()->routeIs('admin.enfermeria.*') && $sidebarUser?->hasRole('ENFERMEROS')
            ? 'ENFERMEROS'
            : ($sidebarUser?->getRoleNames()->first() ?? 'Usuario'));
    $sidebarRoleLabel = mb_convert_case(str_replace('_', ' ', $sidebarRole), MB_CASE_TITLE, 'UTF-8');
    $isAdministracion = $previewRole === 'ADMINISTRADOR' || ($previewRole === null && auth()->user()?->hasRole('ADMINISTRADOR') && ! auth()->user()?->hasRole('SUPERADMINISTRADOR'));
    $sidebarStorageKey = 'remembermind-sidebar-view-'.hash('sha256', (string) $sidebarUser?->getAuthIdentifier().'|'.$sidebarRole);
    $currentRoute = request()->route()?->getName() ?? '';
    $routeActive = static function (?string $name) use ($currentRoute): bool {
        if (!$name) return false;
        if ($currentRoute === $name || str_starts_with($currentRoute, $name . '.')) return true;
        return str_ends_with($name, '.index') && str_starts_with($currentRoute, substr($name, 0, -6) . '.');
    };
    $activeLocation = null;
    $bestMatch = -1;
    foreach ($sections as $sectionIndex => $section) {
        $candidates = [[null, $section['route'] ?? null]];
        foreach ($section['items'] ?? [] as $itemIndex => $item) {
            $candidates[] = [$itemIndex, $item['route'] ?? null];
        }
        foreach ($candidates as [$itemIndex, $routeName]) {
            if (! $routeActive($routeName)) continue;
            $score = ($currentRoute === $routeName ? 10000 : 0) + strlen($routeName);
            if ($score > $bestMatch) {
                $bestMatch = $score;
                $activeLocation = ['section' => $sectionIndex, 'item' => $itemIndex];
            }
        }
    }
    $initialOpen = $activeLocation !== null && $activeLocation['item'] !== null ? $activeLocation['section'] : null;
    $activeNursingSection = $sidebarRole === 'ENFERMEROS' && $initialOpen !== null ? $sections[$initialOpen]['title'] : null;
    $inicioAdministracion = $isAdministracion && app(\App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion::class)->puedeVerRuta('admin.administracion.dashboard');
    $brandRoute = request()->routeIs('admin.enfermeria.*') ? 'admin.enfermeria.dashboard' : ($inicioAdministracion ? 'admin.administracion.dashboard' : 'dashboard');
@endphp

<aside id="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria' : 'sidebar' }}" class="rm-sidebar" wire:transition="rm-sidebar" aria-label="Navegación principal"
    :class="{ 'is-mobile-open': sidebarOpen }"
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    x-data="{
        storageKey: @js($sidebarStorageKey),
        sectionKeys: @js(array_map(static fn ($section) => $section['title'], $sections)),
        activeSectionKey: @js($activeNursingSection),
        saveSidebarView() {
            const view = { path: window.location.pathname, open: this.openSection === null ? null : this.sectionKeys[this.openSection], scroll: this.$refs.navigation.scrollTop };
            try { sessionStorage.setItem(this.storageKey, JSON.stringify(view)); }
            catch (error) { this.savedView = view; }
        },
        restoreSidebarView() {
            let view = null;
            try { view = JSON.parse(sessionStorage.getItem(this.storageKey) || 'null'); }
            catch (error) { view = null; }
            if (view) {
                if (this.activeSectionKey) {
                    this.openSection = this.sectionKeys.indexOf(this.activeSectionKey);
                } else if (view.path === window.location.pathname || this.openSection === null) {
                    const index = this.sectionKeys.indexOf(view.open);
                    this.openSection = index >= 0 ? index : null;
                }
                this.$nextTick(() => { this.$refs.navigation.scrollTop = Number(view.scroll) || 0; });
            }
        },
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
        hoverFlyout(index, element) {
            if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
            this.showFlyout(index, element);
        },
        showFlyout(index, element) {
            if (!sidebarCollapsed || window.innerWidth < 1024) return;
            this.flyoutIndex = index;
            this.$nextTick(() => {
                const flyout = document.getElementById('rm-sidebar-flyout-' + index);
                const height = flyout?.getBoundingClientRect().height ?? 280;
                this.flyoutTop = Math.max(12, Math.min(element.getBoundingClientRect().top, window.innerHeight - height - 12));
            });
        }
    }"
    x-on:livewire:navigating.window="saveSidebarView(); closeSidebarForNavigation()"
    x-init="restoreSidebarView(); $watch('openSection', () => saveSidebarView()); $watch('sidebarOpen', open => { if (window.innerWidth >= 1024) return; if (open) $nextTick(() => $refs.mobileClose.focus()); else if ($el.contains(document.activeElement)) document.getElementById('sidebar-mobile-trigger')?.focus(); })"
    @keydown.tab="if (sidebarOpen && window.innerWidth < 1024) { const controls = [...$el.querySelectorAll('a[href], button:not([disabled]), summary')].filter(control => control.getClientRects().length); const first = controls[0], last = controls[controls.length - 1]; if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); } else if (!$event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); } }"
    @keydown.escape.window="if (flyoutIndex !== null && document.activeElement?.closest('.rm-sidebar__flyout')) document.getElementById('rm-sidebar-trigger-' + flyoutIndex)?.focus(); flyoutIndex = null; tooltipLabel = null; if (sidebarOpen) sidebarOpen = false">
    <div class="rm-sidebar__header">
        <a href="{{ route($brandRoute) }}" class="rm-sidebar__brand" wire:navigate @click="sidebarOpen = false" aria-label="RememberMind, inicio">
            <img src="{{ asset('storage/imagenes/LOGO.png') }}" alt="" class="rm-sidebar__logo">
            <span class="rm-sidebar__brand-copy">
                <strong class="rm-brand">RememberMind</strong>
                <small>Cuidado geriátrico</small>
            </span>
        </a>
        <button type="button" class="rm-sidebar__header-toggle" @click="toggleSidebarCollapse(); flyoutIndex = null; tooltipLabel = null"
            :aria-label="sidebarCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'"
            :title="sidebarCollapsed ? 'Expandir menú lateral' : 'Contraer menú lateral'"
            :aria-expanded="sidebarCollapsed ? 'false' : 'true'"
            aria-controls="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria-nav' : 'sidebar-nav' }}">
            <i class="ph-bold ph-caret-double-left" aria-hidden="true" :class="{ 'is-collapsed': sidebarCollapsed }"></i>
        </button>
        <button type="button" class="rm-sidebar__mobile-close" x-ref="mobileClose" @click="sidebarOpen = false" aria-label="Cerrar menú lateral">
            <i class="ph-bold ph-x" aria-hidden="true"></i>
        </button>
    </div>

    <nav id="{{ request()->routeIs('admin.enfermeria.*') ? 'sidebar-enfermeria-nav' : 'sidebar-nav' }}" class="rm-sidebar__nav" x-ref="navigation" @scroll.debounce.100ms="saveSidebarView()" aria-label="Secciones del sistema" @mouseleave="if (!$event.relatedTarget?.closest('.rm-sidebar__flyout')) flyoutIndex = null; tooltipLabel = null">
        @foreach ($sections as $index => $section)
            @php
                $items = $section['items'] ?? [];
                $hasChildren = count($items) > 0;
                $currentSection = $activeLocation !== null && $activeLocation['section'] === $index;
                $active = $currentSection && $activeLocation['item'] === null;
                $group = $section['group'] ?? null;
                $previousGroup = $index > 0 ? ($sections[$index - 1]['group'] ?? null) : null;
            @endphp
            @if($group && $group !== $previousGroup)
                <p class="rm-sidebar__group rm-nav-section">{{ $group }}</p>
            @endif
            <div class="rm-sidebar__entry">
                @if($hasChildren)
                    <button type="button" id="rm-sidebar-trigger-{{ $index }}" class="rm-sidebar__item {{ $currentSection ? 'is-current-section' : '' }}"
                        title="{{ $section['title'] }}" aria-label="{{ $section['title'] }}{{ !empty($section['badge']) ? ', '.$section['badge'] : '' }}" data-label="{{ mb_strtoupper($section['title']) }}"
                        :aria-expanded="((!sidebarCollapsed || window.innerWidth < 1024) && openSection === {{ $index }}) || (sidebarCollapsed && flyoutIndex === {{ $index }}) ? 'true' : 'false'"
                        :aria-controls="sidebarCollapsed && window.innerWidth >= 1024 ? 'rm-sidebar-flyout-{{ $index }}' : 'rm-sidebar-submenu-{{ $index }}'"
                        @click="if (sidebarCollapsed && window.innerWidth >= 1024) { showFlyout({{ $index }}, $el); if ($event.detail === 0) $nextTick(() => document.querySelector('#rm-sidebar-flyout-{{ $index }} a')?.focus()); } else openSection = openSection === {{ $index }} ? null : {{ $index }}"
                        @mouseenter="showTooltip(@js($section['title']), $el); hoverFlyout({{ $index }}, $el)" @focus="showTooltip(@js($section['title']), $el)" @blur="tooltipLabel = null"
                        @mouseleave="if (!$event.relatedTarget?.closest('.rm-sidebar__flyout')) flyoutIndex = null">
                        <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                        <i class="ph-bold {{ $section['icon'] }} rm-sidebar__icon" aria-hidden="true"></i>
                        <span class="rm-sidebar__label rm-nav-group">{{ $section['title'] }}</span>
                        @if(!empty($section['badge']))
                            <span class="rm-sidebar__badge" aria-hidden="true">
                                <span class="rm-sidebar__badge-value">{{ $section['badge'] }}</span>
                                <span class="rm-sidebar__badge-compact">{{ is_numeric($section['badge']) && (int) $section['badge'] > 99 ? '99+' : $section['badge'] }}</span>
                            </span>
                        @endif
                        <i class="ph-bold ph-caret-down rm-sidebar__chevron" aria-hidden="true" :class="{ 'is-open': openSection === {{ $index }} }"></i>
                    </button>
                    <div id="rm-sidebar-submenu-{{ $index }}" class="rm-sidebar__submenu"
                        x-show="openSection === {{ $index }} && (!sidebarCollapsed || window.innerWidth < 1024)"
                        x-collapse.duration.200ms
                        style="display: none;">
                        <div class="rm-sidebar__submenu-content" :class="{ 'is-visible': openSection === {{ $index }} }">
                        @foreach($items as $itemIndex => $item)
                            @php $childActive = $currentSection && $activeLocation['item'] === $itemIndex; @endphp
                            @if($item['disabled'] ?? false)
                                <span class="rm-sidebar__subitem rm-sidebar__subitem--pending rm-nav-item" aria-disabled="true" title="Vista todavía no disponible">
                                    <span class="rm-sidebar__subindicator" aria-hidden="true"></span>
                                    <span>{{ $item['label'] }}</span>
                                </span>
                            @else
                                <a href="{{ route($item['route']) }}" wire:navigate @click="sidebarOpen = false"
                                    class="rm-sidebar__subitem rm-nav-item {{ $childActive ? 'is-active' : '' }}" data-label="{{ mb_strtoupper($item['label']) }}"
                                    @if($childActive) aria-current="page" @endif>
                                    <span class="rm-sidebar__subindicator" aria-hidden="true"></span>
                                    <span>{{ $item['label'] }}</span>
                                    @if(!empty($item['badge']))<span @class(['rm-sidebar__badge', 'rm-sidebar__badge--alert' => str_contains($item['route'], '.alertas')])>{{ $item['badge'] }}</span>@endif
                                </a>
                            @endif
                        @endforeach
                        </div>
                    </div>
                @else
                    <a href="{{ route($section['route']) }}" wire:navigate @click="sidebarOpen = false; flyoutIndex = null; tooltipLabel = null"
                        class="rm-sidebar__item {{ $active ? 'is-active' : '' }}" title="{{ $section['title'] }}" aria-label="{{ $section['title'] }}{{ !empty($section['badge']) ? ', '.$section['badge'] : '' }}" data-label="{{ mb_strtoupper($section['title']) }}"
                        @mouseenter="showTooltip(@js($section['title']), $el)" @focus="showTooltip(@js($section['title']), $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null"
                        @if($active) aria-current="page" @endif>
                        <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                        <i class="ph-bold {{ $section['icon'] }} rm-sidebar__icon" aria-hidden="true"></i>
                        <span class="rm-sidebar__label rm-nav-group">{{ $section['title'] }}</span>
                        @if(!empty($section['badge']))
                            <span @class(['rm-sidebar__badge', 'rm-sidebar__badge--alert' => str_contains($section['route'] ?? '', '.alertas')]) aria-hidden="true">
                                <span class="rm-sidebar__badge-value">{{ $section['badge'] }}</span>
                                <span class="rm-sidebar__badge-compact">{{ is_numeric($section['badge']) && (int) $section['badge'] > 99 ? '99+' : $section['badge'] }}</span>
                            </span>
                        @endif
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
                x-transition:enter="rm-sidebar__flyout-enter" x-transition:enter-start="rm-sidebar__flyout-start" x-transition:enter-end="rm-sidebar__flyout-end"
                x-transition:leave="rm-sidebar__flyout-leave" x-transition:leave-start="rm-sidebar__flyout-end" x-transition:leave-end="rm-sidebar__flyout-start" x-cloak>
                <strong class="rm-sidebar__flyout-title rm-nav-group">{{ $section['title'] }}</strong>
                @foreach($section['items'] as $itemIndex => $item)
                    @php $flyoutActive = $activeLocation !== null && $activeLocation['section'] === $index && $activeLocation['item'] === $itemIndex; @endphp
                    @if($item['disabled'] ?? false)
                        <span class="rm-sidebar__flyout-link rm-sidebar__flyout-link--pending rm-nav-item" aria-disabled="true" title="Vista todavía no disponible">{{ $item['label'] }}</span>
                    @else
                        <a href="{{ route($item['route']) }}" wire:navigate @click="flyoutIndex = null; sidebarOpen = false"
                            class="rm-sidebar__flyout-link rm-nav-item {{ $flyoutActive ? 'is-active' : '' }}"
                            @if($flyoutActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                    @endif
                @endforeach
            </div>
        @endif
    @endforeach

    <div class="rm-sidebar__footer">
        <details class="rm-sidebar__account" @click.outside="$el.removeAttribute('open')"
            @keydown.escape="if ($el.open) { $event.stopPropagation(); $el.open = false; $el.querySelector('summary').focus(); }">
            <summary class="rm-sidebar__account-trigger" aria-label="Información de la cuenta de {{ $sidebarName }}"
                @mouseenter="showTooltip(@js($sidebarName), $el)" @focus="showTooltip(@js($sidebarName), $el)"
                @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null">
                <span class="rm-sidebar__avatar" aria-hidden="true">
                    {{ $sidebarInitials }}
                    @if($sidebarUser?->estado === 'ACTIVO')<span class="rm-sidebar__presence"></span>@endif
                </span>
                <span class="rm-sidebar__account-copy">
                    <strong>{{ $sidebarName }}</strong>
                    <small>{{ $sidebarRoleLabel }}</small>
                </span>
                <i class="ph-bold ph-dots-three-vertical rm-sidebar__account-more" aria-hidden="true"></i>
            </summary>
            <div class="rm-sidebar__account-panel">
                <p class="rm-sidebar__account-name">{{ $sidebarName }}</p>
                <p class="rm-sidebar__account-role">{{ $sidebarRoleLabel }}</p>
                @if($isAdministracion)
                    <a href="{{ route('profile.show') }}" wire:navigate @click="sidebarOpen = false; tooltipLabel = null"
                        @mouseenter="showTooltip('Mi perfil', $el)" @focus="showTooltip('Mi perfil', $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null"
                        class="rm-sidebar__item rm-sidebar__profile {{ $routeActive('profile.show') ? 'is-active' : '' }}" title="Mi perfil" aria-label="Mi perfil" @if($routeActive('profile.show')) aria-current="page" @endif>
                        <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                        <i class="ph-bold ph-user-circle rm-sidebar__icon" aria-hidden="true"></i>
                        <span class="rm-sidebar__label rm-nav-group">Mi perfil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="rm-sidebar__logout">
                        @csrf
                        <button type="submit" class="rm-sidebar__item" title="Cerrar sesión" aria-label="Cerrar sesión"
                            @mouseenter="showTooltip('Cerrar sesión', $el)" @focus="showTooltip('Cerrar sesión', $el)" @mouseleave="tooltipLabel = null" @blur="tooltipLabel = null">
                            <span class="rm-sidebar__indicator" aria-hidden="true"></span>
                            <i class="ph-bold ph-sign-out rm-sidebar__icon" aria-hidden="true"></i>
                            <span class="rm-sidebar__label rm-nav-group">Cerrar sesión</span>
                        </button>
                    </form>
                @endif
            </div>
        </details>
    </div>
</aside>
