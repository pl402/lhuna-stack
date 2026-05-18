import os
import glob

replacements = [
    # Backgrounds
    ('bg-white', 'bg-dark-surface'),
    ('bg-gray-800', 'bg-brand-500'),
    ('hover:bg-gray-700', 'hover:bg-brand-400'),
    ('active:bg-gray-900', 'active:bg-brand-600'),
    ('focus:border-gray-900', 'focus:border-brand-500'),
    ('focus:ring-gray-300', 'focus:ring-brand-500/50'),
    ('bg-gray-50', 'bg-dark-elevated/30'),
    ('bg-gray-100', 'bg-dark-elevated'),
    ('bg-gray-200', 'bg-dark-elevated/80'),
    ('bg-slate-50', 'bg-dark-elevated/30'),
    ('bg-slate-100', 'bg-dark-elevated/50'),
    ('bg-slate-200', 'bg-dark-surface/50'),
    ('bg-slate-300', 'bg-dark-elevated'),
    
    # Text colors
    ('text-gray-900', 'text-slate-100'),
    ('text-gray-800', 'text-slate-200'),
    ('text-gray-700', 'text-slate-300'),
    ('text-gray-600', 'text-slate-400'),
    ('text-gray-500', 'text-slate-400'),
    ('text-slate-900', 'text-slate-100'),
    ('text-slate-800', 'text-slate-200'),
    ('text-slate-700', 'text-slate-300'),
    ('text-slate-500', 'text-slate-400'),
    
    # Borders
    ('border-gray-200', 'border-dark-border'),
    ('border-gray-300', 'border-dark-border'),
    ('border-slate-200', 'border-dark-border'),
    ('border-slate-300', 'border-dark-border'),
    ('border-slate-400', 'border-dark-border'),
    
    # Shadows
    ('shadow-sm', 'shadow-sm shadow-black/20'),
    ('shadow', 'shadow shadow-black/30'),
    ('shadow-md', 'shadow-md shadow-black/40'),
    
    # Gradients
    ('bg-gradient-to-b from-slate-100 to-slate-200', 'bg-glass-gradient backdrop-blur-md'),
    
    # Rings
    ('focus:ring-indigo-200', 'focus:ring-brand-500/30'),
    ('focus:border-indigo-300', 'focus:border-brand-500'),
    ('text-indigo-600', 'text-brand-500'),
]

directories = [
    'resources/js/Components',
    'resources/js/Jetstream',
    'resources/js/Pages'
]

for directory in directories:
    for filepath in glob.glob(directory + '/**/*.vue', recursive=True):
        if 'AppLayout.vue' in filepath:
            continue
        with open(filepath, 'r') as f:
            content = f.read()
            
        original_content = content
        for old, new in replacements:
            content = content.replace(old, new)
            
        if content != original_content:
            with open(filepath, 'w') as f:
                f.write(content)
            print(f"Updated {filepath}")

print("Global color replacements done.")
