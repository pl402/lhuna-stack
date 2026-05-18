import re

def fix_tailwind():
    with open('tailwind.config.js', 'r') as f:
        content = f.read()

    replacements = {
        '50': '240 253 244',
        '100': '220 252 231',
        '200': '187 247 208',
        '300': '134 239 172',
        '400': '74 222 128',
        '500': '34 197 94',
        '600': '22 163 74',
        '700': '21 128 61',
        '800': '22 101 52',
        '900': '20 83 45',
        '950': '5 46 22',
    }

    brand_block = "                brand: {\n"
    for key, rgb in replacements.items():
        brand_block += f"                    {key}: 'rgb(var(--color-brand-{key}-rgb, {rgb}) / <alpha-value>)',\n"
    brand_block += "                },"

    content = re.sub(r'                brand: \{[\s\S]*?\},', brand_block, content)

    with open('tailwind.config.js', 'w') as f:
        f.write(content)


def fix_app_js():
    with open('resources/js/app.js', 'r') as f:
        content = f.read()

    js_code = """                                Object.keys(selectedColor).forEach(key => {
                                    root.style.setProperty(`--color-brand-${key}`, selectedColor[key]);
                                    const hex = selectedColor[key];
                                    const r = parseInt(hex.slice(1, 3), 16);
                                    const g = parseInt(hex.slice(3, 5), 16);
                                    const b = parseInt(hex.slice(5, 7), 16);
                                    root.style.setProperty(`--color-brand-${key}-rgb`, `${r} ${g} ${b}`);
                                });"""

    old_code = r"                                Object.keys\(selectedColor\)\.forEach\(key => \{\s*root\.style\.setProperty\(`--color-brand-\$\{key\}`\, selectedColor\[key\]\);\s*\}\);"

    content = re.sub(old_code, js_code, content)

    with open('resources/js/app.js', 'w') as f:
        f.write(content)

if __name__ == '__main__':
    fix_tailwind()
    fix_app_js()
    print("Fixed brand opacities!")
