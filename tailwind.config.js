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
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                outfit: ['Outfit', 'sans-serif'],
            },
            colors: {
                'terracota': 'var(--rm-terracota)',
                'terracota-dark': 'var(--rm-terracota-hover)',
                'naranja': 'var(--rm-naranja)',
                'durazno': 'var(--rm-durazno)',
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
                'texto-secundario': 'var(--color-texto-apoyo)',
                'texto-principal': 'var(--color-parrafo)',

                titulo: 'var(--color-titulo)',
                subtitulo: 'var(--color-subtitulo)',
                parrafo: 'var(--color-parrafo)',
                apoyo: 'var(--color-texto-apoyo)',
                label: 'var(--color-label)',
                meta: 'var(--color-meta)',
                placeholder: 'var(--color-placeholder)',

                fondo: {
                    app: 'var(--color-fondo-app)',
                    layout: 'var(--color-fondo-layout)',
                    panel: 'var(--color-fondo-panel)',
                    panelAlt: 'var(--color-fondo-panel-alt)',
                    card: 'var(--color-fondo-card)',
                    cardSuave: 'var(--color-fondo-card-suave)',
                    cardCalido: 'var(--color-fondo-card-calido)',
                    input: 'var(--color-fondo-input)',
                    tabla: 'var(--color-fondo-tabla)',
                    hover: 'var(--color-fondo-hover)',
                },
                borde: {
                    DEFAULT: 'var(--color-borde-general)',
                    suave: 'var(--color-borde-suave)',
                    medio: 'var(--color-borde-medio)',
                    fuerte: 'var(--color-borde-fuerte)',
                    focus: 'var(--color-borde-focus)',
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
                },
                modulo: {
                    adultoMayor: 'var(--color-modulo-adulto-mayor)',
                    adultoMayorFondo: 'var(--color-modulo-adulto-mayor-fondo)',
                    salud: 'var(--color-modulo-salud)',
                    saludSuave: 'var(--color-modulo-salud-suave)',
                    saludFondo: 'var(--color-modulo-salud-fondo)',
                    cognitivo: 'var(--color-modulo-cognitivo)',
                    cognitivoTexto: 'var(--color-modulo-cognitivo-texto)',
                    cognitivoFondo: 'var(--color-modulo-cognitivo-fondo)',
                    reportes: 'var(--color-modulo-reportes)',
                    alertas: 'var(--color-modulo-alertas)',
                    alertasTexto: 'var(--color-modulo-alertas-texto)',
                    bitacora: 'var(--color-modulo-bitacora)',
                    voluntarios: 'var(--color-modulo-voluntarios)',
                    voluntariosTexto: 'var(--color-modulo-voluntarios-texto)',
                    voluntariosFondo: 'var(--color-modulo-voluntarios-fondo)',
                },
                estado: {
                    exito: {
                        bg: 'var(--color-estado-exito-bg)',
                        texto: 'var(--color-estado-exito-texto)',
                        borde: 'var(--color-estado-exito-borde)',
                    },
                    advertencia: {
                        bg: 'var(--color-estado-advertencia-bg)',
                        texto: 'var(--color-estado-advertencia-texto)',
                        borde: 'var(--color-estado-advertencia-borde)',
                    },
                    peligro: {
                        bg: 'var(--color-estado-peligro-bg)',
                        texto: 'var(--color-estado-peligro-texto)',
                        borde: 'var(--color-estado-peligro-borde)',
                    },
                    info: {
                        bg: 'var(--color-estado-info-bg)',
                        texto: 'var(--color-estado-info-texto)',
                        borde: 'var(--color-estado-info-borde)',
                    },
                    neutral: {
                        bg: 'var(--color-estado-neutral-bg)',
                        texto: 'var(--color-estado-neutral-texto)',
                        borde: 'var(--color-estado-neutral-borde)',
                    },
                },
            },
            boxShadow: {
                card: 'var(--sombra-card)',
                cardHover: 'var(--sombra-card-hover)',
                panel: 'var(--sombra-panel)',
                sidebar: 'var(--sombra-sidebar)',
                modal: 'var(--sombra-modal)',
                glow: 'var(--glow-acento)',
            },
            backgroundImage: {
                'gradiente-progreso': 'var(--gradiente-progreso)',
                'gradiente-salud': 'var(--gradiente-salud)',
                'gradiente-cognitivo': 'var(--gradiente-cognitivo)',
                'gradiente-acento': 'var(--gradiente-acento)',
            },
        },
    },

    plugins: [forms, typography, animated],
};
