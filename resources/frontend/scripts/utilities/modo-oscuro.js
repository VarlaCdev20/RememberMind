/**
 * ============================================================
 * REMEMBERMIND / CASA AMANDITA
 * CONTROL DE MODO OSCURO
 * ------------------------------------------------------------
 * Archivo:
 * resources/frontend/scripts/utilities/modo-oscuro.js
 *
 * Objetivo:
 * Gestionar el cambio entre modo claro y modo oscuro usando:
 * - Clase .dark para compatibilidad con Tailwind darkMode: 'class'
 * - Atributo data-theme="dark" para compatibilidad con CSS semántico
 * - localStorage para recordar la preferencia del usuario
 *
 * Requiere que modo-oscuro.css use:
 * .dark,
 * [data-theme="dark"] { ... }
 * ============================================================
 */

const THEME_STORAGE_KEY = 'remembermind-theme';
const THEME_DARK = 'dark';
const THEME_LIGHT = 'light';
const THEME_SYSTEM = 'system';

const root = document.documentElement;

/**
 * Obtiene la preferencia del sistema operativo.
 */
function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches
        ? THEME_DARK
        : THEME_LIGHT;
}

/**
 * Obtiene el tema guardado.
 * Si no existe, por defecto usa modo claro para evitar cambios bruscos.
 */
function getSavedTheme() {
    return localStorage.getItem(THEME_STORAGE_KEY) || THEME_LIGHT;
}

/**
 * Resuelve el tema final cuando la preferencia es "system".
 */
function resolveTheme(theme) {
    if (theme === THEME_SYSTEM) {
        return getSystemTheme();
    }

    return theme === THEME_DARK ? THEME_DARK : THEME_LIGHT;
}

/**
 * Aplica el tema al documento.
 */
function applyTheme(theme, persist = true) {
    const resolvedTheme = resolveTheme(theme);
    const isDark = resolvedTheme === THEME_DARK;

    root.classList.toggle('dark', isDark);
    root.dataset.theme = resolvedTheme;

    root.style.colorScheme = resolvedTheme;

    if (persist) {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    }

    updateThemeControls(theme, resolvedTheme);

    window.dispatchEvent(
        new CustomEvent('remembermind:theme-changed', {
            detail: {
                theme,
                resolvedTheme,
                isDark,
            },
        })
    );
}

/**
 * Alterna claro/oscuro.
 * Si estaba en system, alterna según el tema actualmente resuelto.
 */
function toggleTheme() {
    const currentPreference = getSavedTheme();
    const currentResolvedTheme = resolveTheme(currentPreference);

    const nextTheme = currentResolvedTheme === THEME_DARK
        ? THEME_LIGHT
        : THEME_DARK;

    applyTheme(nextTheme);
}

/**
 * Actualiza botones, switches o selects asociados al tema.
 *
 * Soporta:
 * - [data-theme-toggle]
 * - [data-theme-option="light"]
 * - [data-theme-option="dark"]
 * - [data-theme-option="system"]
 * - [data-theme-icon]
 * - [data-theme-label]
 */
function updateThemeControls(preferenceTheme, resolvedTheme) {
    const isDark = resolvedTheme === THEME_DARK;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
        button.dataset.currentTheme = resolvedTheme;
    });

    document.querySelectorAll('[data-theme-option]').forEach((button) => {
        const option = button.dataset.themeOption;
        const isActive = option === preferenceTheme;

        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', String(isActive));
    });

    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        icon.dataset.currentTheme = resolvedTheme;
        if (icon.dataset.iconDark && icon.dataset.iconLight) {
            icon.className = isDark ? icon.dataset.iconLight : icon.dataset.iconDark;
            icon.textContent = '';
            return;
        }
        icon.textContent = isDark ? '☀️' : '🌙';
    });

    document.querySelectorAll('[data-theme-label]').forEach((label) => {
        if (preferenceTheme === THEME_SYSTEM) {
            label.textContent = isDark
                ? 'Modo sistema: oscuro'
                : 'Modo sistema: claro';
            return;
        }

        label.textContent = isDark ? 'Modo oscuro' : 'Modo claro';
    });

    document.querySelectorAll('select[data-theme-select]').forEach((select) => {
        select.value = preferenceTheme;
    });
}

/**
 * Inicializa listeners de botones y selects.
 */
function bindThemeControls() {
    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest('[data-theme-toggle]');
        const optionButton = event.target.closest('[data-theme-option]');

        if (toggleButton) {
            event.preventDefault();
            toggleTheme();
            return;
        }

        if (optionButton) {
            event.preventDefault();

            const selectedTheme = optionButton.dataset.themeOption;

            if (
                selectedTheme === THEME_LIGHT ||
                selectedTheme === THEME_DARK ||
                selectedTheme === THEME_SYSTEM
            ) {
                applyTheme(selectedTheme);
            }
        }
    });

    document.addEventListener('change', (event) => {
        const select = event.target.closest('select[data-theme-select]');

        if (!select) return;

        const selectedTheme = select.value;

        if (
            selectedTheme === THEME_LIGHT ||
            selectedTheme === THEME_DARK ||
            selectedTheme === THEME_SYSTEM
        ) {
            applyTheme(selectedTheme);
        }
    });
}

/**
 * Si el usuario eligió "system", se actualiza automáticamente
 * cuando cambia el tema del sistema operativo.
 */
function bindSystemThemeListener() {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    mediaQuery.addEventListener('change', () => {
        const savedTheme = getSavedTheme();

        if (savedTheme === THEME_SYSTEM) {
            applyTheme(THEME_SYSTEM, false);
        }
    });
}

/**
 * Evita parpadeo visual aplicando el tema lo antes posible.
 */
function initTheme() {
    const savedTheme = getSavedTheme();

    applyTheme(savedTheme, false);
    bindThemeControls();
    bindSystemThemeListener();
}

/**
 * Inicialización segura.
 */
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

/**
 * API global opcional para usar desde Alpine, Livewire o scripts Blade.
 */
window.RememberMindTheme = {
    apply: applyTheme,
    toggle: toggleTheme,
    getSaved: getSavedTheme,
    getSystem: getSystemTheme,
    resolve: resolveTheme,
    DARK: THEME_DARK,
    LIGHT: THEME_LIGHT,
    SYSTEM: THEME_SYSTEM,
};
