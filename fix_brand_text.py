import os
import re

files_to_fix = [
    'resources/js/Layouts/AppLayout.vue',
    'resources/js/Jetstream/ResponsiveNavLink.vue',
    'resources/js/Jetstream/DropdownLink.vue'
]

def fix_file(filepath):
    if not os.path.exists(filepath):
        return
        
    with open(filepath, 'r') as f:
        content = f.read()
    
    original = content
    
    # Order matters
    content = content.replace('hover:text-brand-400', 'hover:text-brand-700 dark:hover:text-brand-400')
    content = content.replace('focus:text-brand-400', 'focus:text-brand-700 dark:focus:text-brand-400')
    content = content.replace('focus:text-brand-300', 'focus:text-brand-800 dark:focus:text-brand-300')
    
    # Use regex for 'text-brand-400' not preceded by hover: or focus:
    content = re.sub(r'(?<![:\-])text-brand-400', 'text-brand-700 dark:text-brand-400', content)
    
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for path in files_to_fix:
    fix_file(path)
