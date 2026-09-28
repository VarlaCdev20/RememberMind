import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import animated from 'tailwindcss-animated';

const rmColor = (channels) => `rgb(${channels} / <alpha-value>)`;

const rmMint = {
    50: rmColor('226 245 229'), 100: rmColor('203 239 208'), 200: rmColor('173 235 178'),
    300: rmColor('139 212 149'), 400: rmColor('109 182 122'), 500: rmColor('79 137 94'),
    600: rmColor('62 111 76'), 700: rmColor('49 88 62'), 800: rmColor('39 74 51'),
    900: rmColor('32 62 43'), 950: rmColor('24 48 33'),
};

const rmBlue = {
    50: rmColor('231 239 249'), 100: rmColor('220 232 247'), 200: rmColor('162 194 236'),
    300: rmColor('126 162 205'), 400: rmColor('111 146 188'), 500: rmColor('82 125 170'),
    600: rmColor('53 93 134'), 700: rmColor('41 76 112'), 800: rmColor('34 63 94'),
    900: rmColor('29 53 80'), 950: rmColor('22 42 65'),
};

const rmCoral = {
    50: rmColor('251 238 236'), 100: rmColor('247 213 209'), 200: rmColor('245 178 170'),
    300: rmColor('245 140 129'), 400: rmColor('243 111 99'), 500: rmColor('201 79 69'),
    600: rmColor('183 66 57'), 700: rmColor('159 53 46'), 800: rmColor('132 43 38'),
    900: rmColor('109 36 31'), 950: rmColor('79 25 22'),
};

const rmWarm = {
    50: rmColor('240 231 222'), 100: rmColor('231 221 211'), 200: rmColor('220 207 195'),
    300: rmColor('201 186 172'), 400: rmColor('189 175 162'), 500: rmColor('168 151 137'),
    600: rmColor('138 122 112'), 700: rmColor('102 92 85'), 800: rmColor('80 71 65'),
    900: rmColor('52 46 42'), 950: rmColor('39 34 31'),
};

const rmWarning = {
    50: rmColor('247 240 233'), 100: rmColor('239 222 207'), 200: rmColor('230 199 174'),
    300: rmColor('211 163 128'), 400: rmColor('190 130 87'), 500: rmColor('169 104 67'),
    600: rmColor('147 82 50'), 700: rmColor('121 65 42'), 800: rmColor('99 55 39'),
    900: rmColor('81 47 35'), 950: rmColor('53 29 22'),
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
                sans: ['"Nunito Sans"', 'sans-serif', ...defaultTheme.fontFamily.sans],
                outfit: ['"Nunito Sans"', 'sans-serif'],
                nunito: ['"Nunito Sans"', 'sans-serif'],
            },

            colors: {
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
