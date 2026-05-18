import re

app_css = """
@layer base {
  :root {
    /* LIGHT THEME (default) - raw RGB values */
    --color-bg-base: 248 250 252;
    --color-bg-surface: 255 255 255;
    --color-bg-elevated: 241 245 249;
    --color-border: 0 0 0;
    
    --color-text-100: 15 23 42;
    --color-text-200: 30 41 59;
    --color-text-300: 51 65 85;
    --color-text-400: 71 85 105;
    --color-text-500: 100 116 139;
  }

  .dark {
    /* DARK THEME - raw RGB values */
    --color-bg-base: 2 6 23;
    --color-bg-surface: 15 23 42;
    --color-bg-elevated: 30 41 59;
    --color-border: 255 255 255;
    
    --color-text-100: 248 250 252;
    --color-text-200: 226 232 240;
    --color-text-300: 203 213 225;
    --color-text-400: 148 163 184;
    --color-text-500: 100 116 139;
  }
}
"""

with open('resources/css/app.css', 'r') as f:
    css_content = f.read()

css_content = re.sub(r'@layer base\s*\{.*\}', app_css.strip(), css_content, flags=re.DOTALL)

with open('resources/css/app.css', 'w') as f:
    f.write(css_content)

tailwind_config_replacements = """
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
"""

with open('tailwind.config.js', 'r') as f:
    config_content = f.read()

config_content = re.sub(r'colors: \{.*?(?=\n            backgroundImage:)', tailwind_config_replacements.strip() + '\n', config_content, flags=re.DOTALL)

with open('tailwind.config.js', 'w') as f:
    f.write(config_content)

print("Fixed CSS variables and tailwind config for perfect transparency support.")
