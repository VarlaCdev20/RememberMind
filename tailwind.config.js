import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import animated from 'tailwindcss-animated';

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
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                outfit: ['Outfit', 'sans-serif'],
            },

            colors: {
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

                    voluntarios: 'var(--color-modulo-voluntarios)',
                    voluntariosTexto: 'var(--color-modulo-voluntarios-texto)',
                    voluntariosFondo: 'var(--color-modulo-voluntarios-fondo)',
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

                    voluntarios: 'var(--color-kpi-voluntarios-texto)',
                    voluntariosBg: 'var(--color-kpi-voluntarios-bg)',
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
