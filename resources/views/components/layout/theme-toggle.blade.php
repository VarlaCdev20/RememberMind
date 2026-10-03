@props(['compact' => false])
<button type="button" data-theme-toggle
        @click.stop="toggleDarkMode()"
        :aria-pressed="darkMode.toString()"
        :title="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
        :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
        @class(['rm-topbar__action', 'rm-topbar__theme-toggle' => !$compact])>
    <i class="ph-bold text-lg" :class="darkMode ? 'ph-sun' : 'ph-moon'" aria-hidden="true"></i>
</button>
