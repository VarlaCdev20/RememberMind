/**
 * Geriátrico Jardín de los Recuerdos — Módulo de tema
 * Gestiona modo claro / oscuro con persistencia en localStorage
 */

const THEME_KEY = 'jardin_recuerdos_theme';

function getPreferredTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    if (stored === 'dark' || stored === 'light') {
        return stored;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    const root = document.documentElement;

    // Añadir clase de transición suave
    root.classList.add('theme-transitioning');

    root.classList.toggle('dark', theme === 'dark');
    root.setAttribute('data-theme', theme);
    localStorage.setItem(THEME_KEY, theme);

    // Actualizar etiquetas de texto en botones de toggle
    document.querySelectorAll('[data-theme-label]').forEach((el) => {
        el.textContent = theme === 'dark' ? 'Modo claro' : 'Modo oscuro';
    });

    // Actualizar iconos si existen
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
        el.className = theme === 'dark'
            ? el.getAttribute('data-icon-light') || 'ph-bold ph-sun text-base'
            : el.getAttribute('data-icon-dark')  || 'ph-bold ph-moon text-base';
    });

    window.dispatchEvent(new CustomEvent('theme:changed', { detail: { theme } }));

    // Quitar la clase de transición después de aplicar el tema
    setTimeout(() => root.classList.remove('theme-transitioning'), 250);
}

function toggleTheme() {
    const current = document.documentElement.getAttribute('data-theme') || getPreferredTheme();
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

// Aplicar tema de inmediato al cargar (antes de DOMContentLoaded para evitar flash)
applyTheme(getPreferredTheme());

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', toggleTheme);
    });
});

// Escuchar cambios del sistema operativo
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem(THEME_KEY)) {
        applyTheme(e.matches ? 'dark' : 'light');
    }
});

// API global
window.JardinRecuerdosTheme = { applyTheme, toggleTheme, getPreferredTheme };
