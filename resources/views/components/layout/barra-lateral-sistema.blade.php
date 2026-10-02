<aside id="navegacion-principal" class="rm-sidebar" :class="sidebarOpen ? 'is-open' : ''" :inert="compact && !sidebarOpen" :aria-hidden="(compact && !sidebarOpen).toString()" aria-label="Navegación principal">
    <div class="rm-sidebar-brand">
        <a href="{{ route('dashboard') }}" class="rm-brand-link" @click="sidebarOpen = false">
            <span class="rm-brand-mark" aria-hidden="true"><i class="ph ph-heartbeat"></i></span>
            <span><strong>RememberMind</strong><small>Los Almendros</small></span>
        </a>
        <button type="button" x-ref="navClose" class="rm-btn-icon rm-sidebar-close" @click="sidebarOpen = false; $nextTick(() => $refs.menuTrigger?.focus())" aria-label="Cerrar navegación"><i class="ph ph-x" aria-hidden="true"></i></button>
    </div>
    <nav class="rm-sidebar-nav" aria-label="Secciones">
        <p class="rm-nav-group">Inicio</p>
        <a href="{{ route('dashboard') }}" class="rm-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif @click="sidebarOpen = false">
            <i class="ph ph-squares-four" aria-hidden="true"></i><span>Panel institucional</span>
        </a>
        @if(auth()->user()->can('viewAny', App\Models\Residente::class) || auth()->user()->can('preadmisiones.ver'))
            <p class="rm-nav-group">Residentes e ingreso</p>
            @can('viewAny', App\Models\Residente::class)
                <a href="{{ route('admin.residentes.index') }}" class="rm-nav-link {{ request()->routeIs('admin.residentes.*') ? 'is-active' : '' }}" @if(request()->routeIs('admin.residentes.*')) aria-current="page" @endif @click="sidebarOpen = false">
                    <i class="ph ph-users-three" aria-hidden="true"></i><span>Residentes</span>
                </a>
            @endcan
            @can('preadmisiones.ver')
                <a href="{{ route('admin.preadmisiones.index') }}" class="rm-nav-link {{ request()->routeIs('admin.preadmisiones.*') ? 'is-active' : '' }}" @if(request()->routeIs('admin.preadmisiones.*')) aria-current="page" @endif @click="sidebarOpen = false">
                    <i class="ph ph-clipboard-text" aria-hidden="true"></i><span>Preadmisiones</span>
                </a>
            @endcan
        @endif
        @can('alertas.ver')
            <p class="rm-nav-group">Seguimiento</p>
            <a href="{{ route('dashboard') }}#alertas" class="rm-nav-link" @click="sidebarOpen = false">
                <i class="ph ph-bell" aria-hidden="true"></i><span>Alertas abiertas</span>
            </a>
        @endcan
    </nav>
    <div class="rm-sidebar-footer">
        <span class="rm-sidebar-user-name">{{ auth()->user()->name }}</span>
        <span class="rm-sidebar-user-role">{{ auth()->user()->getRoleNames()->first() ?? 'Usuario' }}</span>
    </div>
</aside>
