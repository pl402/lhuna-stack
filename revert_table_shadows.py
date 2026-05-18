import os
import re

def modify_file(filepath, changes):
    if not os.path.exists(filepath):
        return
    with open(filepath, 'r') as f:
        content = f.read()
    
    original = content
    for old, new in changes.items():
        content = content.replace(old, new)
        
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

# 1. Tabla.vue
modify_file('resources/js/Components/Tabla.vue', {
    'shadow-lg shadow-black/40': ''
})

# 2. Usuarios.vue and Configuraciones.vue
modify_file('resources/js/Pages/Usuarios.vue', {
    'shadow-xl shadow-black/50 ring-1 ring-white/5': ''
})
modify_file('resources/js/Pages/Configuraciones.vue', {
    'shadow-xl shadow-black/50 ring-1 ring-white/5': ''
})

# 3. Modal.vue
modify_file('resources/js/Jetstream/Modal.vue', {
    'shadow-xl shadow-black/50 ring-1 ring-white/5': 'shadow-2xl shadow-black/20 ring-1 ring-white/5'
})

# Clean up any double spaces that might be left over
for file in ['resources/js/Components/Tabla.vue', 'resources/js/Pages/Usuarios.vue', 'resources/js/Pages/Configuraciones.vue']:
    modify_file(file, {'  ': ' '})

