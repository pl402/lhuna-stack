const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: 'var(--color-brand-50, #f0fdf4)',
                    100: 'var(--color-brand-100, #dcfce7)',
                    200: 'var(--color-brand-200, #bbf7d0)',
                    300: 'var(--color-brand-300, #86efac)',
                    400: 'var(--color-brand-400, #4ade80)',
                    500: 'var(--color-brand-500, #22c55e)',
                    600: 'var(--color-brand-600, #16a34a)',
                    700: 'var(--color-brand-700, #15803d)',
                    800: 'var(--color-brand-800, #166534)',
                    900: 'var(--color-brand-900, #14532d)',
                    950: 'var(--color-brand-950, #052e16)',
                },
                dark: {
                    base: 'rgb(var(--color-bg-base) / <alpha-value>)',
                    surface: 'rgb(var(--color-bg-surface) / <alpha-value>)',
                    elevated: 'rgb(var(--color-bg-elevated) / <alpha-value>)',
                    border: 'rgb(var(--color-border) / var(--tw-border-opacity, 0.1))',
                },
                slate: {
                    50: 'rgb(var(--color-text-50) / <alpha-value>)',
                    100: 'rgb(var(--color-text-100) / <alpha-value>)',
                    200: 'rgb(var(--color-text-200) / <alpha-value>)',
                    300: 'rgb(var(--color-text-300) / <alpha-value>)',
                    400: 'rgb(var(--color-text-400) / <alpha-value>)',
                    500: 'rgb(var(--color-text-500) / <alpha-value>)',
                    600: 'var(--color-text-600, #475569)',
                    700: 'var(--color-text-700, #334155)',
                    800: 'var(--color-text-800, #1e293b)',
                    900: 'var(--color-text-900, #0f172a)',
                }
            },

            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'glass-gradient': 'linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%)',
            },
            animation: {
                'fade-in': 'fadeIn 0.3s ease-out',
                'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                }
            }
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
