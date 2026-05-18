import os

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
                    base: 'var(--color-bg-base, #020617)',
                    surface: 'var(--color-bg-surface, #0F172A)',
                    elevated: 'var(--color-bg-elevated, #1E293B)',
                    border: 'var(--color-border, rgba(255, 255, 255, 0.1))',
                },
                slate: {
                    50: 'var(--color-text-50, #f8fafc)',
                    100: 'var(--color-text-100, #f1f5f9)',
                    200: 'var(--color-text-200, #e2e8f0)',
                    300: 'var(--color-text-300, #cbd5e1)',
                    400: 'var(--color-text-400, #94a3b8)',
                    500: 'var(--color-text-500, #64748b)',
                    600: 'var(--color-text-600, #475569)',
                    700: 'var(--color-text-700, #334155)',
                    800: 'var(--color-text-800, #1e293b)',
                    900: 'var(--color-text-900, #0f172a)',
                }
            },
"""

with open('tailwind.config.js', 'r') as f:
    content = f.read()

# Replace colors block
import re
content = re.sub(r'colors: \{.*?(?=\n            \},?\n            backgroundImage:)', tailwind_config_replacements.strip(), content, flags=re.DOTALL)

with open('tailwind.config.js', 'w') as f:
    f.write(content)

app_css_additions = """
@layer base {
  :root {
    /* LIGHT THEME (default) */
    --color-bg-base: #f8fafc;
    --color-bg-surface: #ffffff;
    --color-bg-elevated: #f1f5f9;
    --color-border: rgba(0, 0, 0, 0.1);
    
    /* Inverted text colors for light mode (100 means dark now because we replaced 900 -> 100) */
    --color-text-100: #0f172a;
    --color-text-200: #1e293b;
    --color-text-300: #334155;
    --color-text-400: #475569;
    --color-text-500: #64748b;
  }

  .dark {
    /* DARK THEME */
    --color-bg-base: #020617;
    --color-bg-surface: #0F172A;
    --color-bg-elevated: #1E293B;
    --color-border: rgba(255, 255, 255, 0.1);
    
    --color-text-100: #f1f5f9;
    --color-text-200: #e2e8f0;
    --color-text-300: #cbd5e1;
    --color-text-400: #94a3b8;
    --color-text-500: #64748b;
  }
}
"""

with open('resources/css/app.css', 'r') as f:
    css_content = f.read()

if '@layer base' not in css_content:
    with open('resources/css/app.css', 'a') as f:
        f.write("\n" + app_css_additions)

print("Theme variables updated.")
