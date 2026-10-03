import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import animated from 'tailwindcss-animated';

// All colors come from the canonical CSS tokens, including opacity modifiers.
const rmColor = (value) => ({ opacityValue = 1 }) =>
    `color-mix(in srgb, ${value} calc(${opacityValue} * 100%), transparent)`;
const token = (name) => rmColor(`var(--rm-${name})`);
const palette = (name) => token(`palette-${name}`);
const mix = (a, percent, b) => rmColor(
    `color-mix(in srgb, var(--rm-palette-${a}) ${percent}%, var(--rm-palette-${b}))`
);

// Existing numeric classes remain available during the screen-by-screen migration.
const rmMint = {
    50: mix('primary-soft', 35, 'page'), 100: palette('sage-soft'), 200: palette('primary-soft'),
    300: palette('sage'), 400: mix('primary', 65, 'sage'), 500: palette('primary'),
    600: mix('primary', 90, 'ink'), 700: mix('primary', 80, 'ink'),
    800: mix('primary', 65, 'ink'), 900: mix('primary', 50, 'ink'), 950: palette('ink'),
};
const rmBlue = {
    50: mix('info-soft', 35, 'page'), 100: palette('info-soft'), 200: palette('info-soft'),
    300: palette('info'), 400: mix('info', 70, 'ink'), 500: mix('info', 35, 'ink'),
    600: mix('info', 30, 'ink'), 700: mix('info', 20, 'ink'), 800: mix('info', 15, 'ink'),
    900: mix('info', 10, 'ink'), 950: palette('ink'),
};
const rmCoral = {
    50: mix('danger', 12, 'page'), 100: mix('danger', 18, 'page'), 200: mix('danger', 35, 'page'),
    300: mix('danger', 55, 'page'), 400: palette('danger'), 500: mix('danger', 70, 'ink'),
    600: mix('danger', 65, 'ink'), 700: mix('danger', 60, 'ink'), 800: mix('danger', 45, 'ink'),
    900: mix('danger', 30, 'ink'), 950: palette('ink'),
};
const rmWarm = {
    50: palette('page'), 100: palette('surface'), 200: palette('surface-soft'),
    300: palette('surface-muted'), 400: mix('ink-secondary', 55, 'surface'),
    500: palette('ink-secondary'), 600: palette('ink-secondary'), 700: mix('ink', 65, 'ink-secondary'),
    800: mix('ink', 80, 'ink-secondary'), 900: palette('ink'), 950: palette('ink'),
};
const rmWarning = {
    50: mix('warning', 10, 'page'), 100: mix('warning', 16, 'page'), 200: mix('warning', 30, 'page'),
    300: mix('warning', 55, 'page'), 400: palette('warning'), 500: mix('warning', 30, 'ink'),
    600: mix('warning', 25, 'ink'), 700: mix('warning', 20, 'ink'), 800: mix('warning', 15, 'ink'),
    900: mix('warning', 10, 'ink'), 950: palette('ink'),
};

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/frontend/scripts/**/*.js',
        './resources/frontend/scripts/**/*.blade.php',
        './app/Frontend/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['var(--rm-font-body)', ...defaultTheme.fontFamily.sans],
                brand: ['var(--rm-font-brand)'],
                heading: ['var(--rm-font-heading)'],
                friendly: ['var(--rm-font-friendly)'],
                body: ['var(--rm-font-body)'],
                outfit: ['var(--rm-font-brand)'],
                nunito: ['var(--rm-font-friendly)'],
            },

            colors: {
                /* Semantic utilities: text-rm-primary is body ink;
                   bg-rm-primary-action is the institutional action color. */
                rm: {
                    DEFAULT: token('border'),
                    app: token('bg-app'),
                    sidebar: token('bg-sidebar'),
                    surface: {
                        DEFAULT: token('surface'), soft: token('surface-soft'), muted: token('surface-muted'),
                    },
                    primary: token('text-primary'),
                    secondary: token('text-secondary'),
                    muted: token('text-muted'),
                    icon: token('icon-default'),
                    earth: Object.fromEntries([300, 400, 500, 600, 700, 800, 900].map(level => [level, token(`earth-${level}`)])),
                    capuchino: palette('capuchino'),
                    selection: token('selected-bg'),
                    inverse: token('text-inverse'),
                    'primary-action': token('primary'),
                    'primary-ink': token('primary-ink'),
                    'primary-soft': token('primary-soft'),
                    sage: { DEFAULT: token('sage'), soft: token('sage-soft'), strong: token('success-strong') },
                    info: { DEFAULT: token('info'), soft: token('info-soft'), strong: token('info-strong') },
                    psychology: { DEFAULT: token('psychology'), soft: token('psychology-soft'), strong: token('psychology-strong') },
                    nutrition: { DEFAULT: token('nutrition'), soft: token('nutrition-soft'), strong: token('nutrition-strong') },
                    warning: { DEFAULT: token('warning'), soft: token('warning-soft'), strong: token('warning-strong') },
                    danger: { DEFAULT: token('danger'), soft: token('danger-soft'), strong: token('danger-strong') },
                    'border-strong': token('border-strong'),
                    focus: token('focus'),
                },

                /* Familias heredadas redirigidas al contrato institucional. */
                gray: rmWarm,
                slate: rmWarm,
                zinc: rmWarm,
                neutral: rmWarm,
                stone: rmWarm,
                green: rmMint,
                emerald: rmMint,
                teal: rmMint,
                blue: rmBlue,
                sky: rmBlue,
                cyan: rmBlue,
                indigo: rmBlue,
                violet: rmBlue,
                purple: rmBlue,
                red: rmCoral,
                rose: rmCoral,
                pink: rmCoral,
                orange: rmWarning,
                amber: rmWarning,
                yellow: rmWarning,

                /* Legacy temporal */
                terracota: 'var(--rm-terracota)',
                'terracota-dark': 'var(--rm-terracota-hover)',
                naranja: 'var(--rm-naranja)',
                durazno: 'var(--rm-durazno)',
                'rosa-crema': 'var(--rm-rosa-crema)',
                'fondo-calido': 'var(--rm-fondo-calido)',

                'verde-salud': 'var(--rm-verde-salud)',
                'verde-suave': 'var(--rm-verde-suave)',
                'fondo-salud': 'var(--rm-fondo-salud)',

                'azul-clinico': 'var(--rm-azul-clinico)',
                'azul-tec': 'var(--rm-azul-tecnico)',
                'azul-profundo': 'var(--rm-azul-profundo)',

                'morado-cog': 'var(--rm-morado-cognitivo)',
                'morado-tec': 'var(--rm-morado-tecnico)',
                'fondo-cog': 'var(--rm-fondo-cognitivo)',

                'crema-base': 'var(--rm-neutro-100)',
                'gris-calido': 'var(--rm-gris-calido)',
                'texto-principal': 'var(--color-parrafo)',
                'texto-secundario': 'var(--color-texto-apoyo)',

                /* Semánticos principales */
                titulo: 'var(--color-titulo)',
                subtitulo: 'var(--color-subtitulo)',
                parrafo: 'var(--color-parrafo)',
                apoyo: 'var(--color-texto-apoyo)',
                suave: 'var(--color-texto-suave)',
                muted: 'var(--color-texto-muted)',
                label: 'var(--color-label)',
                meta: 'var(--color-meta)',
                placeholder: 'var(--color-placeholder)',
                inverso: 'var(--color-texto-inverso)',

                fondo: {
                    app: 'var(--color-fondo-app)',
                    layout: 'var(--color-fondo-layout)',
                    panel: 'var(--color-fondo-panel)',
                    panelAlt: 'var(--color-fondo-panel-alt)',
                    panelFuerte: 'var(--color-fondo-panel-fuerte)',
                    card: 'var(--color-fondo-card)',
                    cardSuave: 'var(--color-fondo-card-suave)',
                    cardCalido: 'var(--color-fondo-card-calido)',
                    input: 'var(--color-fondo-input)',
                    tabla: 'var(--color-fondo-tabla)',
                    hover: 'var(--color-fondo-hover)',
                    empty: 'var(--color-fondo-empty)',
                },

                borde: {
                    DEFAULT: 'var(--color-borde-general)',
                    suave: 'var(--color-borde-suave)',
                    medio: 'var(--color-borde-medio)',
                    fuerte: 'var(--color-borde-fuerte)',
                    focus: 'var(--color-borde-focus)',
                    dashed: 'var(--color-borde-dashed)',
                    error: 'var(--color-borde-error)',
                    success: 'var(--color-borde-success)',
                    warning: 'var(--color-borde-warning)',
                    info: 'var(--color-borde-info)',
                },

                boton: {
                    principal: 'var(--color-boton-principal)',
                    principalHover: 'var(--color-boton-principal-hover)',
                    principalTexto: 'var(--color-boton-principal-texto)',

                    acento: 'var(--color-boton-acento)',
                    acentoHover: 'var(--color-boton-acento-hover)',
                    acentoTexto: 'var(--color-boton-acento-texto)',

                    secundario: 'var(--color-boton-secundario)',
                    secundarioHover: 'var(--color-boton-secundario-hover)',
                    secundarioTexto: 'var(--color-boton-secundario-texto)',

                    fantasma: 'var(--color-boton-fantasma-bg)',
                    fantasmaHover: 'var(--color-boton-fantasma-hover)',
                    fantasmaTexto: 'var(--color-boton-fantasma-texto)',
                    fantasmaBorde: 'var(--color-boton-fantasma-borde)',

                    icono: 'var(--color-boton-icono-bg)',
                    iconoHover: 'var(--color-boton-icono-hover)',
                    iconoTexto: 'var(--color-boton-icono-texto)',
                    iconoTextoHover: 'var(--color-boton-icono-texto-hover)',
                },

                sidebar: {
                    bg: 'var(--color-sidebar-bg)',
                    header: 'var(--color-sidebar-header-bg)',
                    texto: 'var(--color-sidebar-texto)',
                    hover: 'var(--color-sidebar-texto-hover)',
                    muted: 'var(--color-sidebar-texto-muted)',
                    activo: 'var(--color-sidebar-item-activo)',
                    activoBg: 'var(--color-sidebar-item-activo-bg)',
                    activoBorde: 'var(--color-sidebar-item-activo-borde)',
                    seccion: 'var(--color-sidebar-seccion-texto)',
                    seccionHover: 'var(--color-sidebar-seccion-hover-bg)',
                    seccionActiva: 'var(--color-sidebar-seccion-activa-texto)',
                    seccionActivaBg: 'var(--color-sidebar-seccion-activa-bg)',
                },

                header: {
                    bg: 'var(--color-header-bg)',
                    texto: 'var(--color-header-texto)',
                    borde: 'var(--color-header-borde)',
                },

                modulo: {
                    adulto: 'var(--color-modulo-adulto-mayor)',
                    adultoHover: 'var(--color-modulo-adulto-mayor-hover)',
                    adultoFondo: 'var(--color-modulo-adulto-mayor-fondo)',

                    salud: 'var(--color-modulo-salud)',
                    saludSuave: 'var(--color-modulo-salud-suave)',
                    saludFondo: 'var(--color-modulo-salud-fondo)',

                    cognitivo: 'var(--color-modulo-cognitivo)',
                    cognitivoHover: 'var(--color-modulo-cognitivo-hover)',
                    cognitivoTexto: 'var(--color-modulo-cognitivo-texto)',
                    cognitivoFondo: 'var(--color-modulo-cognitivo-fondo)',

                    reportes: 'var(--color-modulo-reportes)',
                    reportesFondo: 'var(--color-modulo-reportes-fondo)',

                    alertas: 'var(--color-modulo-alertas)',
                    alertasTexto: 'var(--color-modulo-alertas-texto)',
                    alertasFondo: 'var(--color-modulo-alertas-fondo)',

                    bitacora: 'var(--color-modulo-bitacora)',
                    bitacoraFondo: 'var(--color-modulo-bitacora-fondo)',

                    apoyo: 'var(--color-modulo-apoyo)',
                    apoyoTexto: 'var(--color-modulo-apoyo-texto)',
                    apoyoFondo: 'var(--color-modulo-apoyo-fondo)',
                },

                estado: {
                    exito: 'var(--color-estado-exito-texto)',
                    exitoBg: 'var(--color-estado-exito-bg)',
                    exitoBorde: 'var(--color-estado-exito-borde)',

                    advertencia: 'var(--color-estado-advertencia-texto)',
                    advertenciaBg: 'var(--color-estado-advertencia-bg)',
                    advertenciaBorde: 'var(--color-estado-advertencia-borde)',

                    peligro: 'var(--color-estado-peligro-texto)',
                    peligroBg: 'var(--color-estado-peligro-bg)',
                    peligroBorde: 'var(--color-estado-peligro-borde)',

                    info: 'var(--color-estado-info-texto)',
                    infoBg: 'var(--color-estado-info-bg)',
                    infoBorde: 'var(--color-estado-info-borde)',

                    neutral: 'var(--color-estado-neutral-texto)',
                    neutralBg: 'var(--color-estado-neutral-bg)',
                    neutralBorde: 'var(--color-estado-neutral-borde)',

                    restaurado: 'var(--color-estado-restaurado-texto)',
                    restauradoBg: 'var(--color-estado-restaurado-bg)',
                    restauradoBorde: 'var(--color-estado-restaurado-borde)',
                },

                signo: {
                    normal: 'var(--color-signo-normal-texto)',
                    normalBg: 'var(--color-signo-normal-bg)',
                    normalBorde: 'var(--color-signo-normal-borde)',

                    observacion: 'var(--color-signo-observacion-texto)',
                    observacionBg: 'var(--color-signo-observacion-bg)',
                    observacionBorde: 'var(--color-signo-observacion-borde)',

                    critico: 'var(--color-signo-critico-texto)',
                    criticoBg: 'var(--color-signo-critico-bg)',
                    criticoBorde: 'var(--color-signo-critico-borde)',

                    sinDatos: 'var(--color-signo-sin-datos-texto)',
                    sinDatosBg: 'var(--color-signo-sin-datos-bg)',
                    sinDatosBorde: 'var(--color-signo-sin-datos-borde)',
                },

                kpi: {
                    residentes: 'var(--color-kpi-residentes-texto)',
                    residentesBg: 'var(--color-kpi-residentes-bg)',

                    salud: 'var(--color-kpi-salud-texto)',
                    saludBg: 'var(--color-kpi-salud-bg)',

                    cognitivo: 'var(--color-kpi-cognitivo-texto)',
                    cognitivoBg: 'var(--color-kpi-cognitivo-bg)',

                    alertas: 'var(--color-kpi-alertas-texto)',
                    alertasBg: 'var(--color-kpi-alertas-bg)',

                    actividades: 'var(--color-kpi-actividades-texto)',
                    actividadesBg: 'var(--color-kpi-actividades-bg)',

                    apoyo: 'var(--color-kpi-apoyo-texto)',
                    apoyoBg: 'var(--color-kpi-apoyo-bg)',
                },

                tabla: {
                    header: 'var(--color-tabla-header-bg)',
                    headerTexto: 'var(--color-tabla-header-texto)',
                    headerBorde: 'var(--color-tabla-header-borde)',
                    rowBorde: 'var(--color-tabla-row-borde)',
                    rowHover: 'var(--color-tabla-row-hover)',
                    label: 'var(--color-tabla-cell-label)',
                    meta: 'var(--color-tabla-cell-meta)',
                },

                input: {
                    bg: 'var(--color-input-bg)',
                    disabled: 'var(--color-input-bg-disabled)',
                    texto: 'var(--color-input-texto)',
                    placeholder: 'var(--color-input-placeholder)',
                    borde: 'var(--color-input-borde)',
                    bordeHover: 'var(--color-input-borde-hover)',
                    bordeFocus: 'var(--color-input-borde-focus)',
                    ringFocus: 'var(--color-input-ring-focus)',
                },

                modal: {
                    overlay: 'var(--color-modal-overlay)',
                    bg: 'var(--color-modal-bg)',
                    borde: 'var(--color-modal-borde)',
                    headerBorde: 'var(--color-modal-header-borde)',
                    footerBorde: 'var(--color-modal-footer-borde)',
                    titulo: 'var(--color-modal-titulo)',
                    texto: 'var(--color-modal-texto)',
                },
            },

            boxShadow: {
                card: 'var(--sombra-card)',
                cardHover: 'var(--sombra-card-hover)',
                panel: 'var(--sombra-panel)',
                sidebar: 'var(--sombra-sidebar)',
                modal: 'var(--sombra-modal)',
                glow: 'var(--glow-acento)',
                glowHover: 'var(--glow-acento-hover)',
            },

            backgroundImage: {
                'gradiente-progreso': 'var(--gradiente-progreso)',
                'gradiente-salud': 'var(--gradiente-salud)',
                'gradiente-cognitivo': 'var(--gradiente-cognitivo)',
                'gradiente-acento': 'var(--gradiente-acento)',
                'gradiente-panel': 'var(--gradiente-panel)',
                'gradiente-card': 'var(--gradiente-card)',
                'gradiente-input': 'var(--gradiente-input)',
            },
        },
    },

    plugins: [forms, typography, animated],
};
