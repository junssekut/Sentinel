import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Sentinel Brand Colors
                sentinel: {
                    blue: '#0066AE',
                    'blue-dark': '#004d82',
                    'blue-light': '#3388c4',
                },
                navy: {
                    DEFAULT: '#0B1F33',
                    50: '#1a3a5c',
                    100: '#152d47',
                    800: '#0B1F33',
                    900: '#081624',
                },
                slate: {
                    DEFAULT: '#5F6C7B',
                },
                light: {
                    DEFAULT: '#F4F6F8',
                    50: '#FFFFFF',
                    100: '#F4F6F8',
                    200: '#E8EBEE',
                },
                success: '#2FBF71',
                warning: '#F4A261',
                error: '#E63946',
            },
        },
    },

    plugins: [forms],
};
