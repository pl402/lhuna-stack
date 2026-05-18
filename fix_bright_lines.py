import os
import re

def fix_file(filepath):
    try:
        with open(filepath, 'r') as f:
            content = f.read()

        original = content

        # Fix bright borders
        content = re.sub(r'border-dark-border/9[0-9]', 'border-dark-border', content)
        # Fix weird bg-dark-surface/50/50
        content = re.sub(r'bg-dark-surface/50/50', 'bg-dark-surface/50', content)
        # Also let's fix any border-white just in case
        content = re.sub(r'border-white', 'border-dark-border', content)

        if content != original:
            with open(filepath, 'w') as f:
                f.write(content)
            print(f"Fixed {filepath}")
    except Exception as e:
        pass

for root, dirs, files in os.walk('resources/js'):
    for file in files:
        if file.endswith('.vue'):
            fix_file(os.path.join(root, file))

