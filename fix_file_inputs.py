import os

components = [
    'resources/js/Jetstream/Input.vue',
    'resources/js/Pages/Configuraciones.vue'
]

def fix_file(filepath):
    if not os.path.exists(filepath):
        return
    
    with open(filepath, 'r') as f:
        content = f.read()

    original = content

    content = content.replace('focus:file:border-gray-900', 'focus:file:border-brand-500')
    content = content.replace('focus:file:ring-gray-300', 'focus:file:ring-brand-500/50')

    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for filepath in components:
    fix_file(filepath)
    
