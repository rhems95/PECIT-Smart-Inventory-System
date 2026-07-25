import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'pecit-blue': {
                    50: '#E8EEF8',
                    100: '#C5D4ED',
                    200: '#9EB7E0',
                    300: '#779AD3',
                    400: '#5984C9',
                    500: '#3B6EBF',
                    600: '#0B3C91',
                    700: '#093074',
                    800: '#072457',
                    900: '#05183A',
                    DEFAULT: '#0B3C91',
                },
                'pecit-gold': {
                    50: '#FEF9E7',
                    100: '#FDF0C3',
                    200: '#FBE69B',
                    300: '#F9DC73',
                    400: '#F7D455',
                    500: '#F4B400',
                    600: '#D99E00',
                    700: '#B8860B',
                    800: '#966E09',
                    900: '#745607',
                    DEFAULT: '#F4B400',
                },
            },
            boxShadow: {
                'soft': '0 2px 15px -3px rgba(11, 60, 145, 0.08), 0 4px 6px -4px rgba(11, 60, 145, 0.05)',
                'soft-lg': '0 10px 40px -10px rgba(11, 60, 145, 0.15), 0 4px 12px -4px rgba(11, 60, 145, 0.08)',
            },
        },
    },

    plugins: [forms],
};
