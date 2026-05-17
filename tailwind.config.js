import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import animated from 'tailwindcss-animated';

/** @type {import('tailwindcss').Config} */
export default {
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
                'terracota': '#E97A5F',
                'terracota-dark': '#D96B52',
                'naranja': '#F4A261',
                'durazno': '#F7B7A3',
                'rosa-crema': '#FADCD3',
                'fondo-calido': '#FDF2EC',
                'verde-salud': '#2A9D8F',
                'verde-suave': '#6FBFB3',
                'fondo-salud': '#D8F3EE',
                'azul-clinico': '#8FB7D9',
                'azul-tec': '#5E81AC',
                'azul-profundo': '#2F3E5C',
                'morado-cog': '#9B8AC7',
                'morado-tec': '#7B6FB3',
                'fondo-cog': '#E6E1F5',
                'crema-base': '#F8F6F3',
                'gris-calido': '#EAE7E3',
                'texto-secundario': '#6B7280',
                'texto-principal': '#2C2C2C',
            }
        },
    },

    plugins: [forms, typography, animated],
};
