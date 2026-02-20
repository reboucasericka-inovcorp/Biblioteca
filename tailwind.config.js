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
                'electric-blue': '#2563EB',
                'night-blue': '#0F172A',
                'steel-gray': '#CBD5E1',
                'neon-green': '#22D3EE',
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
                    primary: '#2563EB',
                    'primary-content': '#FFFFFF',
                    secondary: '#22D3EE',
                    'secondary-content': '#0F172A',
                    accent: '#22D3EE',
                    'accent-content': '#0F172A',
                    neutral: '#0F172A',
                    'neutral-content': '#FFFFFF',
                    'base-100': '#FFFFFF',
                    'base-200': '#CBD5E1',
                    'base-300': '#94A3B8',
                    'base-content': '#0F172A',
                },
            },
        ],
    },
};
