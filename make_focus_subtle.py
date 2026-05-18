import os

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

    # Replace solid ring with 30% opacity ring
    content = content.replace('focus:ring-brand-500 ', 'focus:ring-brand-500/30 ')
    content = content.replace('focus:ring-brand-500\n', 'focus:ring-brand-500/30\n')
    
    # Just in case there are multiple replacements, be careful not to make it /30/30
    content = content.replace('/30/30', '/30')
    content = content.replace('/50/30', '/50') # protect existing ones if any

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in components:
    fix_file(filepath)
    
