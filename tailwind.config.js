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
            borderRadius: {
                'sm': 'var(--border-radius-sm, 0.125rem)',
                'DEFAULT': 'var(--border-radius-default, 0.25rem)',
                'md': 'var(--border-radius-md, 0.375rem)',
                'lg': 'var(--border-radius-lg, 0.5rem)',
                'xl': 'var(--border-radius-xl, 0.75rem)',
                '2xl': 'var(--border-radius-2xl, 1rem)',
                '3xl': 'var(--border-radius-3xl, 1.5rem)',
            },
            boxShadow: {
                'sm': 'var(--shadow-sm, 0 1px 2px 0 rgb(0 0 0 / 0.05))',
                'DEFAULT': 'var(--shadow-default, 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1))',
                'md': 'var(--shadow-md, 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1))',
                'lg': 'var(--shadow-lg, 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1))',
                'xl': 'var(--shadow-xl, 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1))',
                '2xl': 'var(--shadow-2xl, 0 25px 50px -12px rgb(0 0 0 / 0.25))',
            },
            colors: {
                brand: {
                    50: 'rgb(var(--color-brand-50-rgb, 240 253 244) / <alpha-value>)',
                    100: 'rgb(var(--color-brand-100-rgb, 220 252 231) / <alpha-value>)',
                    200: 'rgb(var(--color-brand-200-rgb, 187 247 208) / <alpha-value>)',
                    300: 'rgb(var(--color-brand-300-rgb, 134 239 172) / <alpha-value>)',
                    400: 'rgb(var(--color-brand-400-rgb, 74 222 128) / <alpha-value>)',
                    500: 'rgb(var(--color-brand-500-rgb, 34 197 94) / <alpha-value>)',
                    600: 'rgb(var(--color-brand-600-rgb, 22 163 74) / <alpha-value>)',
                    700: 'rgb(var(--color-brand-700-rgb, 21 128 61) / <alpha-value>)',
                    800: 'rgb(var(--color-brand-800-rgb, 22 101 52) / <alpha-value>)',
                    900: 'rgb(var(--color-brand-900-rgb, 20 83 45) / <alpha-value>)',
                    950: 'rgb(var(--color-brand-950-rgb, 5 46 22) / <alpha-value>)',
                },
                dark: {
                    base: 'rgb(var(--color-bg-base) / <alpha-value>)',
                    surface: 'rgb(var(--color-bg-surface) / <alpha-value>)',
                    elevated: 'rgb(var(--color-bg-elevated) / <alpha-value>)',
                    border: 'rgb(var(--color-border) / 0.15)',
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
