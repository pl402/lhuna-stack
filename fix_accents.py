import os
import re

def fix_checkbox():
    path = 'resources/js/Jetstream/Checkbox.vue'
    if os.path.exists(path):
        with open(path, 'r') as f:
            c = f.read()
        c = c.replace('text-slate-600', 'text-brand-500 bg-dark-surface')
        c = c.replace('focus:ring-slate-200', 'focus:ring-brand-500')
        with open(path, 'w') as f:
            f.write(c)
        print("Fixed Checkbox.vue")

def fix_secondary_button():
    path = 'resources/js/Jetstream/SecondaryButton.vue'
    if os.path.exists(path):
        with open(path, 'r') as f:
            c = f.read()
        c = c.replace('focus:border-blue-300 focus:ring focus:ring-blue-200', 'focus:border-brand-500 focus:ring focus:ring-brand-500/50')
        with open(path, 'w') as f:
            f.write(c)
        print("Fixed SecondaryButton.vue")

def fix_button():
    path = 'resources/js/Jetstream/Button.vue'
    if os.path.exists(path):
        with open(path, 'r') as f:
            c = f.read()
        c = c.replace('focus:border-slate-900/70 focus:ring-slate-300', 'focus:border-brand-500 focus:ring-brand-500/50')
        c = c.replace('bg-slate-800', 'bg-brand-500')
        c = c.replace('hover:bg-slate-700', 'hover:bg-brand-400')
        c = c.replace('active:bg-slate-900', 'active:bg-brand-600')
        with open(path, 'w') as f:
            f.write(c)
        print("Fixed Button.vue")

fix_checkbox()
fix_secondary_button()
fix_button()
