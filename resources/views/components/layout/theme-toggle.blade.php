@props(['compact' => false])
<button type="button" data-theme-toggle
        @click.stop="toggleDarkMode()"
        :aria-pressed="darkMode.toString()"
        :title="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
        :aria-label="darkMode ? 'Activar modo claro' : 'Activar modo oscuro'"
        @class(['rm-topbar__action', 'rm-topbar__theme-toggle' => !$compact])>
    <i class="ph-bold ph-moon text-lg rm-topbar__theme-icon--moon" aria-hidden="true"></i>
    <i class="ph-bold ph-sun text-lg rm-topbar__theme-icon--sun" aria-hidden="true"></i>
</button>
