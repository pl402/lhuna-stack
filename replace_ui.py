import os
import re

def replace_in_file(filepath, replacements):
    with open(filepath, 'r') as f:
        content = f.read()
    
    for old, new in replacements:
        content = content.replace(old, new)
        
    with open(filepath, 'w') as f:
        f.write(content)

# JetResponsiveNavLink
replace_in_file('resources/js/Jetstream/ResponsiveNavLink.vue', [
    ('"block pl-3 pr-4 py-2 border-l-4 border-slate-400 text-base font-medium text-slate-700 bg-slate-50 focus:outline-none focus:text-slate-800 focus:bg-slate-100 focus:border-slate-700 transition"', 
     '"block pl-3 pr-4 py-2 border-l-4 border-brand-500 text-base font-medium text-brand-400 bg-brand-500/10 focus:outline-none focus:text-brand-300 focus:bg-brand-500/20 focus:border-brand-400 transition-all duration-300"'),
    ('"block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition"',
     '"block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-400 hover:text-brand-400 hover:bg-dark-elevated hover:border-dark-border focus:outline-none focus:text-brand-400 focus:bg-dark-elevated focus:border-dark-border transition-all duration-300"')
])

# JetDropdownLink
replace_in_file('resources/js/Jetstream/DropdownLink.vue', [
    ('text-slate-700 hover:bg-slate-300 focus:outline-none focus:bg-slate-500 transition',
     'text-slate-300 hover:text-brand-400 hover:bg-dark-elevated focus:outline-none focus:bg-dark-elevated transition-all duration-300')
])

# JetNavLink
replace_in_file('resources/js/Jetstream/NavLink.vue', [
    ('"inline-flex items-center px-1 pt-1 border-b-2 border-slate-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-slate-700 transition"',
     '"inline-flex items-center px-1 pt-1 border-b-2 border-brand-500 text-sm font-medium leading-5 text-slate-100 focus:outline-none focus:border-brand-400 transition-all duration-300"'),
    ('"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition"',
     '"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-400 hover:text-slate-200 hover:border-dark-border focus:outline-none focus:text-slate-200 focus:border-dark-border transition-all duration-300"')
])

# AppLayout.vue
app_layout_replacements = [
    ('bg-gradient-to-b from-slate-50 to-slate-100', 'bg-dark-base text-slate-200 relative selection:bg-brand-500/30 selection:text-brand-200'),
    ('sm:hidden bg-white/80 border-b border-slate-100 fixed top-0 z-50 w-full backdrop-blur-sm shadow', 'sm:hidden bg-dark-surface/80 border-b border-dark-border fixed top-0 z-50 w-full backdrop-blur-md shadow-lg shadow-black/20'),
    ('max-w-full mx-auto px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-100 shadow', 'max-w-full mx-auto px-4 sm:px-6 lg:px-8 bg-dark-surface/90 border-b border-dark-border shadow-sm backdrop-blur-lg'),
    ('text-lg font-bold text-slate-900 flex my-auto truncate', 'text-lg font-bold text-slate-100 flex my-auto truncate tracking-tight'),
    ('text-slate-500 mx-2', 'text-slate-500 mx-2 opacity-50'),
    ('font-medium text-slate-800 truncate', 'font-medium text-brand-400 truncate'),
    ('text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition', 'text-slate-400 hover:text-brand-400 hover:bg-brand-500/10 focus:outline-none focus:bg-brand-500/20 focus:text-brand-400 transition-all duration-300'),
    ('bg-white border-r border-slate-200 shadow fixed transition-all duration-300 ease-in-out z-50', 'bg-dark-surface/95 backdrop-blur-xl border-r border-dark-border shadow-2xl fixed transition-all duration-300 ease-in-out z-50'),
    ('text-xl font-bold text-slate-900 flex-1 text-center', 'text-xl font-bold text-slate-100 flex-1 text-center tracking-tight'),
    ('border-t border-slate-200 my-[1px]', 'border-t border-dark-border my-[1px]'),
    ('font-medium text-sm text-slate-800', 'font-medium text-sm text-brand-400'),
    ('bg-white shadow-2xl absolute bottom-0 w-full border-t border-slate-100', 'bg-dark-surface/80 backdrop-blur-md absolute bottom-0 w-full border-t border-dark-border'),
    ('text-xs text-slate-400', 'text-xs text-slate-500'),
    ('bg-white/80 border-b border-slate-300/90 sticky sm:top-0 top-12 z-10 backdrop-blur-sm shadow hidden sm:flex', 'bg-dark-surface/80 border-b border-dark-border sticky sm:top-0 top-12 z-40 backdrop-blur-xl shadow-lg shadow-black/10 hidden sm:flex'),
    ('font-semibold text-xl text-slate-800 leading-tight', 'font-semibold text-xl text-slate-100 leading-tight flex items-center gap-2"><div class="w-2 h-6 bg-brand-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div><span'),
    ('{{ title }}\n                            </h2>', '{{ title }}</span>\n                            </h2>'),
    ('border border-transparent text-sm leading-4 font-medium rounded-md text-slate-500 bg-slate-100 hover:text-slate-700 focus:outline-none transition', 'border border-dark-border text-sm leading-4 font-medium rounded-xl text-slate-300 bg-dark-elevated/50 hover:text-brand-400 hover:bg-brand-500/10 hover:border-brand-500/30 focus:outline-none transition-all duration-300 shadow-sm'),
    ('border-t border-slate-300', 'border-t border-dark-border')
]
replace_in_file('resources/js/Layouts/AppLayout.vue', app_layout_replacements)

# Add glow element to AppLayout.vue
with open('resources/js/Layouts/AppLayout.vue', 'r') as f:
    content = f.read()

content = content.replace(
    '<div class="min-h-screen bg-dark-base text-slate-200 relative selection:bg-brand-500/30 selection:text-brand-200">',
    '<div class="min-h-screen bg-dark-base text-slate-200 relative selection:bg-brand-500/30 selection:text-brand-200">\n            <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-brand-500/10 blur-[120px] rounded-full pointer-events-none z-0"></div>'
)

with open('resources/js/Layouts/AppLayout.vue', 'w') as f:
    f.write(content)

print("UI Replacements Done.")
