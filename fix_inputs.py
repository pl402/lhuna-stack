import os
import re

directories = [
    'resources/js/Jetstream',
    'resources/js/Components'
]

def add_classes_to_tag(content, tag_name, classes_to_add):
    pattern = rf'<{tag_name}[^>]*class="([^"]+)"'
    
    def replacer(match):
        existing_classes = match.group(1)
        # Avoid duplicating
        for cls in classes_to_add.split(' '):
            if cls not in existing_classes:
                existing_classes = f"{cls} {existing_classes}"
        
        # Replace white hardcoded backgrounds if any
        existing_classes = existing_classes.replace('bg-white', '')
        
        # We replace the whole class attribute
        return match.group(0).replace(f'class="{match.group(1)}"', f'class="{existing_classes.strip()}"')

    # Also handle the multiline class attributes or dynamically bound classes
    # Actually, let's just do a simple replacement if possible, or use a more robust regex
    return content

# More robust replacing just by adding `bg-dark-surface border-dark-border text-slate-100`
def fix_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    original = content
    # Look for class="... " in input, select, textarea
    # Jetstream/Input.vue has a multiline class attribute!
    if 'Input.vue' in filepath or 'Select.vue' in filepath or 'Textarea.vue' in filepath or 'NumberInput.vue' in filepath:
        # We can just inject it
        content = re.sub(r'(class="[\s\S]*?)border-gray-400', r'\1 bg-dark-surface border-dark-border text-slate-100 focus:ring-brand-500 ', content)
        content = re.sub(r'border-gray-300', 'border-dark-border', content)

    # Let's write back
    if content != original:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Fixed {filepath}")

for d in directories:
    for filename in os.listdir(d):
        if filename.endswith('.vue'):
            fix_file(os.path.join(d, filename))

