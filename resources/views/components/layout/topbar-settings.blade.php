<div class="rm-topbar__settings" x-data="{ settingsOpen: false }"
     @click.outside="settingsOpen = false"
     @keydown.escape.stop="settingsOpen = false; $refs.settingsToggle.focus()"
     x-on:livewire:navigating.window="settingsOpen = false">
    <button type="button" class="rm-topbar__action" x-ref="settingsToggle"
            @click="settingsOpen = !settingsOpen" :aria-expanded="settingsOpen.toString()"
            aria-label="Configuración de apariencia" aria-controls="topbar-appearance-panel">
        <i class="ph-bold ph-gear text-xl" aria-hidden="true"></i>
    </button>
    <div id="topbar-appearance-panel" class="rm-topbar__settings-panel"
         x-show="settingsOpen" x-transition.opacity.duration.180ms x-cloak>
        <span class="rm-label">Apariencia</span>
        <x-layout.theme-toggle compact />
    </div>
</div>
