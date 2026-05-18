import os
import re

components = [
    'resources/js/Jetstream/Input.vue',
    'resources/js/Components/Select.vue',
    'resources/js/Components/Textarea.vue',
    'resources/js/Components/NumberInput.vue',
    'resources/js/Components/DecimalInput.vue',
    'resources/js/Components/CurrencyInput.vue',
    'resources/js/Components/Tags.vue',
    'resources/js/Components/Autocomplete.vue'
]

def fix_file(filepath):
    if not os.path.exists(filepath):
        return
    
    with open(filepath, 'r') as f:
        content = f.read()

    original = content

    # 1. Remove the old focus classes
    content = content.replace('focus:border-dark-border', 'focus:border-brand-500')
    content = content.replace('focus:ring-slate-400', 'focus:ring-brand-500')
    content = content.replace('focus:ring-opacity-50', '')
    
    # Clean up double spaces that might be left
    content = content.replace('  ', ' ')

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in components:
    fix_file(filepath)
    
