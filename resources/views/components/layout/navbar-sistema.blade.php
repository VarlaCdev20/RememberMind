<header class="rm-topbar">
    <div class="rm-topbar-context">
        <button type="button" x-ref="menuTrigger" class="rm-btn-icon rm-menu-trigger" @click="sidebarOpen = true; $nextTick(() => $refs.navClose?.focus())" aria-label="Abrir navegación" aria-controls="navegacion-principal" :aria-expanded="sidebarOpen.toString()">
            <i class="ph ph-list" aria-hidden="true"></i>
        </button>
        <div>
            <span class="rm-topbar-eyebrow">RememberMind</span>
            <p class="rm-topbar-title">{{ request()->routeIs('admin.residentes.*') ? 'Residentes' : (request()->routeIs('admin.preadmisiones.*') ? 'Preadmisiones' : 'Panel institucional') }}</p>
        </div>
    </div>
    <div class="rm-topbar-actions">
        <a href="{{ route('profile.show') }}" class="rm-profile-link" aria-label="Abrir mi perfil: {{ auth()->user()->name }}">
            <span class="rm-profile-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="rm-profile-name">{{ auth()->user()->name }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="rm-btn rm-btn-ghost rm-logout-button">Salir</button></form>
    </div>
</header>
