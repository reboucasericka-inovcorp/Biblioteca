import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'electric-blue': '#513DD8',
                'night-blue': '#1A1D55',
                'steel-gray': '#E2E8EE',
                'neon-green': '#9DAEE1',
                'polar-border': '#D5D8DD',
            },
        },
    },

    plugins: [
        forms,
        typography,
        require('daisyui'),
    ],

    daisyui: {
        themes: [
            {
                biblioteca: {
                    primary: '#000020',
                    'primary-content': '#FFFFFF',
                    secondary: '#000020',
                    'secondary-content': '#FFFFFF',
                    accent: '#9DAEE1',
                    'accent-content': '#000020',
                    neutral: '#000020',
                    'neutral-content': '#FFFFFF',
                    'base-100': '#FFFFFF',
                    'base-200': '#E2E8EE',
                    'base-300': '#D5D8DD',
                    'base-content': '#1A1D55',
                },
            },
        ],
    },
};
