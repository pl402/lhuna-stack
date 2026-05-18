import os
import glob

def fix_borders():
    files = glob.glob('resources/js/**/*.vue', recursive=True)
    for file in files:
        with open(file, 'r') as f:
            content = f.read()
        
        if 'border-slate-100 dark:border-zinc-800' in content:
            content = content.replace('border-slate-100 dark:border-zinc-800', 'border-dark-border')
            with open(file, 'w') as f:
                f.write(content)
            print(f"Fixed {file}")

if __name__ == '__main__':
    fix_borders()
