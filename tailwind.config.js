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
                sans: ['"Bricolage Grotesque"', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Bricolage Grotesque"', 'Inter', ...defaultTheme.fontFamily.sans],
                bricolage: ['"Bricolage Grotesque"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Sentinel Brand Colors
                sentinel: {
                    DEFAULT: '#0066AE',
                    blue: '#0066AE',
                    'blue-dark': '#004d82',
                    'blue-light': '#3388c4',
                    'blue-50': '#f0f7fc',
                    'blue-100': '#e0eff9',
                    'blue-200': '#b8dff2',
                    'blue-300': '#7dc3e8',
                    'blue-400': '#3ba2db',
                    'blue-500': '#0066AE', // Brand Primary
                    'blue-600': '#005899',
                    'blue-700': '#00477d',
                    'blue-800': '#003d69',
                    'blue-900': '#063254',
                },
                navy: {
                    DEFAULT: '#0B1F33',
                    50: '#f5f7fa',
                    100: '#eef1f5',
                    200: '#dce3eb',
                    300: '#c0ccd9',
                    400: '#9eadbf',
                    500: '#8392a6',
                    600: '#68778a',
                    700: '#546170',
                    800: '#0B1F33', // Brand Dark
                    900: '#081624',
                    950: '#03080d',
                },
                slate: {
                    DEFAULT: '#5F6C7B',
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                },
                light: {
                    DEFAULT: '#F4F6F8',
                    50: '#FFFFFF',
                    100: '#F4F6F8',
                    200: '#E8EBEE',
                    300: '#DDE2E7',
                },
                success: '#2FBF71',
                warning: '#F4A261',
                error: '#E63946',
            },
            boxShadow: {
                'bento': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 0 0 1px rgba(0, 0, 0, 0.03)',
                'bento-hover': '0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.03)',
                'card': '0 0 0 1px rgba(0, 0, 0, 0.03), 0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                'glow': '0 0 15px rgba(0, 102, 174, 0.3)',
                'glass': '0 4px 30px rgba(0, 0, 0, 0.1)',
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'hero-pattern': "url('/images/hero-pattern.svg')", // Placeholder if needed
                'glass': 'linear-gradient(135deg, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.1) 100%)',
                'glass-dark': 'linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%)',
                'sentinel-gradient': 'linear-gradient(135deg, #0066AE 0%, #004d82 100%)',
                'mesh': 'radial-gradient(at 0% 0%, hsla(211,100%,34%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(211,100%,34%,1) 0, transparent 50%)',
            },
            borderRadius: {
                'xl': '1rem',
                '2xl': '1.5rem',
                '3xl': '2rem',
            },
            animation: {
                'float': 'float 3s ease-in-out infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                }
            }
        },
    },

    plugins: [forms],
};
