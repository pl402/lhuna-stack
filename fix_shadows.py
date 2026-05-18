import os
import re

def fix_file(filepath):
    try:
        with open(filepath, 'r') as f:
            content = f.read()

        original = content

        # Fix broken shadows
        content = re.sub(r'shadow shadow-black/30-sm shadow shadow-black/30-black/20', 'shadow-sm shadow-black/20', content)
        content = re.sub(r'shadow shadow-black/30-xl', 'shadow-xl shadow-black/50 ring-1 ring-white/5', content) # Added subtle ring to pop modals
        content = re.sub(r'shadow shadow-black/30-2xl', 'shadow-2xl shadow-black/50 ring-1 ring-white/5', content)
        content = re.sub(r'shadow shadow-black/30-lg', 'shadow-lg shadow-black/40', content)
        content = re.sub(r'shadow shadow-black/30-md', 'shadow-md shadow-black/30', content)
        content = re.sub(r'shadow shadow-black/30-sm', 'shadow-sm shadow-black/20', content)
        content = re.sub(r'shadow shadow-black/30-outline-slate', 'ring-2 ring-slate-400', content)
        content = re.sub(r'shadow shadow-black/30-outline', 'shadow-[0_0_0_2px_rgba(34,197,94,0.5)]', content)
        content = re.sub(r'shadow shadow-black/30-inner', 'shadow-inner shadow-black/50', content)
        content = re.sub(r'shadow shadow-black/30', 'shadow shadow-black/30', content)

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

