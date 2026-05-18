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

    # Replace invalid opacity modifier with ring-opacity utility
    content = content.replace('focus:ring-brand-500/30', 'focus:ring-brand-500 focus:ring-opacity-30')
    content = content.replace('focus:file:ring-brand-500/50', 'focus:file:ring-brand-500 focus:file:ring-opacity-50')
    content = content.replace('focus:ring-brand-500/50', 'focus:ring-brand-500 focus:ring-opacity-50')

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in components:
    fix_file(filepath)

# Also check Configuraciones.vue for file:ring
with open('resources/js/Pages/Configuraciones.vue', 'r') as f:
    content = f.read()
if 'focus:file:ring-brand-500/50' in content:
    content = content.replace('focus:file:ring-brand-500/50', 'focus:file:ring-brand-500 focus:file:ring-opacity-50')
    with open('resources/js/Pages/Configuraciones.vue', 'w') as f:
        f.write(content)
    print("Fixed resources/js/Pages/Configuraciones.vue")

