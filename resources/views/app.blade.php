<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">

    <!-- Theme Script -->
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <!-- Scripts -->
    @routes
    <script src="{{ mix('js/app.js') }}" defer></script>
    @inertiaHead

    <?php
        if (!function_exists('hex2rgb')) {
            function hex2rgb($hex) {
                $hex = str_replace("#", "", $hex);
                if (strlen($hex) == 3) {
                    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
                } else {
                    $r = hexdec(substr($hex,0,2));
                    $g = hexdec(substr($hex,2,2));
                    $b = hexdec(substr($hex,4,2));
                }
                return "$r $g $b";
            }
        }

        $colorTema = 'indigo';
        $redondez = 'md';
        $sombra = 'lg';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('configuraciones')) {
                $tema = \App\Models\Configuracion::where('clave', 'Color del Tema')->first();
                if ($tema && $tema->valor) {
                    $colorTema = strtolower($tema->valor);
                }
                $radiusConfig = \App\Models\Configuracion::where('clave', 'Redondez de Esquinas')->first();
                if ($radiusConfig && $radiusConfig->valor) {
                    $redondez = strtolower($radiusConfig->valor);
                }
                $shadowConfig = \App\Models\Configuracion::where('clave', 'Tamaño de Sombra')->first();
                if ($shadowConfig && $shadowConfig->valor) {
                    $sombra = strtolower($shadowConfig->valor);
                }
            }
        } catch (\Exception $e) {}

        $radiusStyles = [
            'none' => [
                'sm' => '0px',
                'default' => '0px',
                'md' => '0px',
                'lg' => '0px',
                'xl' => '0px',
                '2xl' => '0px',
                '3xl' => '0px',
            ],
            'sm' => [
                'sm' => '2px',
                'default' => '4px',
                'md' => '4px',
                'lg' => '6px',
                'xl' => '8px',
                '2xl' => '12px',
                '3xl' => '16px',
            ],
            'md' => [
                'sm' => '0.125rem',
                'default' => '0.25rem',
                'md' => '0.375rem',
                'lg' => '0.5rem',
                'xl' => '0.75rem',
                '2xl' => '1rem',
                '3xl' => '1.5rem',
            ],
            'lg' => [
                'sm' => '4px',
                'default' => '8px',
                'md' => '10px',
                'lg' => '12px',
                'xl' => '16px',
                '2xl' => '24px',
                '3xl' => '32px',
            ],
            'xl' => [
                'sm' => '6px',
                'default' => '12px',
                'md' => '16px',
                'lg' => '20px',
                'xl' => '28px',
                '2xl' => '36px',
                '3xl' => '48px',
            ],
        ];
        
        $esquinas = $radiusStyles[$redondez] ?? $radiusStyles['md'];
        
        $shadowStyles = [
            'none' => [
                'sm' => '0 0 #0000',
                'default' => '0 0 #0000',
                'md' => '0 0 #0000',
                'lg' => '0 0 #0000',
                'xl' => '0 0 #0000',
                '2xl' => '0 0 #0000',
            ],
            'sm' => [
                'sm' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'default' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'md' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'lg' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'xl' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                '2xl' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
            ],
            'md' => [
                'sm' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'default' => '0 1px 3px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 1px 2px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'md' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'lg' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'xl' => '0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -4px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                '2xl' => '0 20px 25px -5px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 8px 10px -6px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
            ],
            'lg' => [
                'sm' => '0 1px 2px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.05))',
                'default' => '0 1px 3px 0 var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 1px 2px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'md' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'lg' => '0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -4px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'xl' => '0 20px 25px -5px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 8px 10px -6px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                '2xl' => '0 25px 50px -12px var(--tw-shadow-color, rgb(0 0 0 / 0.25))',
            ],
            'xl' => [
                'sm' => '0 4px 6px -1px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 2px 4px -2px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'default' => '0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -4px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'md' => '0 10px 15px -3px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 4px 6px -4px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'lg' => '0 20px 25px -5px var(--tw-shadow-color, rgb(0 0 0 / 0.1)), 0 8px 10px -6px var(--tw-shadow-color, rgb(0 0 0 / 0.1))',
                'xl' => '0 25px 50px -12px var(--tw-shadow-color, rgb(0 0 0 / 0.25))',
                '2xl' => '0 25px 50px -12px var(--tw-shadow-color, rgb(0 0 0 / 0.25))',
            ],
        ];

        $sombras = $shadowStyles[$sombra] ?? $shadowStyles['lg'];

        $colores = [
            'slate' => ['50' => '#f8fafc', '100' => '#f1f5f9', '200' => '#e2e8f0', '300' => '#cbd5e1', '400' => '#94a3b8', '500' => '#64748b', '600' => '#475569', '700' => '#334155', '800' => '#1e293b', '900' => '#0f172a', '950' => '#020617'],
            'gray' => ['50' => '#f9fafb', '100' => '#f3f4f6', '200' => '#e5e7eb', '300' => '#d1d5db', '400' => '#9ca3af', '500' => '#6b7280', '600' => '#4b5563', '700' => '#374151', '800' => '#1f2937', '900' => '#111827', '950' => '#030712'],
            'zinc' => ['50' => '#fafafa', '100' => '#f4f4f5', '200' => '#e4e4e7', '300' => '#d4d4d8', '400' => '#a1a1aa', '500' => '#71717a', '600' => '#52525b', '700' => '#3f3f46', '800' => '#27272a', '900' => '#18181b', '950' => '#09090b'],
            'neutral' => ['50' => '#fafafa', '100' => '#f5f5f5', '200' => '#e5e5e5', '300' => '#d4d4d4', '400' => '#a3a3a3', '500' => '#737373', '600' => '#525252', '700' => '#404040', '800' => '#262626', '900' => '#171717', '950' => '#0a0a0a'],
            'stone' => ['50' => '#fafaf9', '100' => '#f5f5f4', '200' => '#e7e5e4', '300' => '#d6d3d1', '400' => '#a8a29e', '500' => '#78716c', '600' => '#57534e', '700' => '#44403c', '800' => '#292524', '900' => '#1c1917', '950' => '#0c0a09'],
            'red' => ['50' => '#fef2f2', '100' => '#fee2e2', '200' => '#fecaca', '300' => '#fca5a5', '400' => '#f87171', '500' => '#ef4444', '600' => '#dc2626', '700' => '#b91c1c', '800' => '#991b1b', '900' => '#7f1d1d', '950' => '#450a0a'],
            'orange' => ['50' => '#fff7ed', '100' => '#ffedd5', '200' => '#fed7aa', '300' => '#fdba74', '400' => '#fb923c', '500' => '#f97316', '600' => '#ea580c', '700' => '#c2410c', '800' => '#9a3412', '900' => '#7c2d12', '950' => '#431407'],
            'amber' => ['50' => '#fffbeb', '100' => '#fef3c7', '200' => '#fde68a', '300' => '#fcd34d', '400' => '#fbbf24', '500' => '#f59e0b', '600' => '#d97706', '700' => '#b45309', '800' => '#92400e', '900' => '#78350f', '950' => '#451a03'],
            'yellow' => ['50' => '#fefce8', '100' => '#fef9c3', '200' => '#fef08a', '300' => '#fde047', '400' => '#facc15', '500' => '#eab308', '600' => '#ca8a04', '700' => '#a16207', '800' => '#854d0e', '900' => '#713f12', '950' => '#422006'],
            'lime' => ['50' => '#f7fee7', '100' => '#ecfccb', '200' => '#d9f99d', '300' => '#bef264', '400' => '#a3e635', '500' => '#84cc16', '600' => '#65a30d', '700' => '#4d7c0f', '800' => '#3f6212', '900' => '#365314', '950' => '#1a2e05'],
            'green' => ['50' => '#f0fdf4', '100' => '#dcfce7', '200' => '#bbf7d0', '300' => '#86efac', '400' => '#4ade80', '500' => '#22c55e', '600' => '#16a34a', '700' => '#15803d', '800' => '#166534', '900' => '#14532d', '950' => '#052e16'],
            'emerald' => ['50' => '#ecfdf5', '100' => '#d1fae5', '200' => '#a7f3d0', '300' => '#6ee7b7', '400' => '#34d399', '500' => '#10b981', '600' => '#059669', '700' => '#047857', '800' => '#065f46', '900' => '#064e3b', '950' => '#022c22'],
            'teal' => ['50' => '#f0fdfa', '100' => '#ccfbf1', '200' => '#99f6e4', '300' => '#5eead4', '400' => '#2dd4bf', '500' => '#14b8a6', '600' => '#0d9488', '700' => '#0f766e', '800' => '#115e59', '900' => '#134e4a', '950' => '#042f2e'],
            'cyan' => ['50' => '#ecfeff', '100' => '#cffafe', '200' => '#a5f3fc', '300' => '#67e8f9', '400' => '#22d3ee', '500' => '#06b6d4', '600' => '#0891b2', '700' => '#0e7490', '800' => '#155e75', '900' => '#164e63', '950' => '#083344'],
            'sky' => ['50' => '#f0f9ff', '100' => '#e0f2fe', '200' => '#bae6fd', '300' => '#7dd3fc', '400' => '#38bdf8', '500' => '#0ea5e9', '600' => '#0284c7', '700' => '#0369a1', '800' => '#075985', '900' => '#0c4a6e', '950' => '#082f49'],
            'blue' => ['50' => '#eff6ff', '100' => '#dbeafe', '200' => '#bfdbfe', '300' => '#93c5fd', '400' => '#60a5fa', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8', '800' => '#1e40af', '900' => '#1e3a8a', '950' => '#172554'],
            'indigo' => ['50' => '#eef2ff', '100' => '#e0e7ff', '200' => '#c7d2fe', '300' => '#a5b4fc', '400' => '#818cf8', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca', '800' => '#3730a3', '900' => '#312e81', '950' => '#1e1b4b'],
            'violet' => ['50' => '#f5f3ff', '100' => '#ede9fe', '200' => '#ddd6fe', '300' => '#c4b5fd', '400' => '#a78bfa', '500' => '#8b5cf6', '600' => '#7c3aed', '700' => '#6d28d9', '800' => '#5b21b6', '900' => '#4c1d95', '950' => '#2e1065'],
            'purple' => ['50' => '#faf5ff', '100' => '#f3e8ff', '200' => '#e9d5ff', '300' => '#d8b4fe', '400' => '#c084fc', '500' => '#a855f7', '600' => '#9333ea', '700' => '#7e22ce', '800' => '#6b21a8', '900' => '#581c87', '950' => '#3b0764'],
            'fuchsia' => ['50' => '#fdf4ff', '100' => '#fae8ff', '200' => '#f5d0fe', '300' => '#f0abfc', '400' => '#e879f9', '500' => '#d946ef', '600' => '#c026d3', '700' => '#a21caf', '800' => '#86198f', '900' => '#701a75', '950' => '#4a044e'],
            'pink' => ['50' => '#fdf2f8', '100' => '#fce7f3', '200' => '#fbcfe8', '300' => '#f9a8d4', '400' => '#f472b6', '500' => '#ec4899', '600' => '#db2777', '700' => '#be185d', '800' => '#9d174d', '900' => '#831843', '950' => '#500724'],
            'rose' => ['50' => '#fff1f2', '100' => '#ffe4e6', '200' => '#fecdd3', '300' => '#fda4af', '400' => '#fb7185', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c', '800' => '#9f1239', '900' => '#881337', '950' => '#4c0519'],
            'black' => ['50' => '#f9fafb', '100' => '#f3f4f6', '200' => '#e5e7eb', '300' => '#d1d5db', '400' => '#9ca3af', '500' => '#000000', '600' => '#111827', '700' => '#1f2937', '800' => '#374151', '900' => '#4b5563', '950' => '#030712'],
        ];
        
        $paleta = $colores[$colorTema] ?? $colores['indigo'];
    ?>
    <style>
        :root {
            --color-brand-50: {{ $paleta['50'] }};
            --color-brand-100: {{ $paleta['100'] }};
            --color-brand-200: {{ $paleta['200'] }};
            --color-brand-300: {{ $paleta['300'] }};
            --color-brand-400: {{ $paleta['400'] }};
            --color-brand-500: {{ $paleta['500'] }};
            --color-brand-600: {{ $paleta['600'] }};
            --color-brand-700: {{ $paleta['700'] }};
            --color-brand-800: {{ $paleta['800'] }};
            --color-brand-900: {{ $paleta['900'] }};
            --color-brand-950: {{ $paleta['950'] }};

            --color-brand-50-rgb: {{ hex2rgb($paleta['50']) }};
            --color-brand-100-rgb: {{ hex2rgb($paleta['100']) }};
            --color-brand-200-rgb: {{ hex2rgb($paleta['200']) }};
            --color-brand-300-rgb: {{ hex2rgb($paleta['300']) }};
            --color-brand-400-rgb: {{ hex2rgb($paleta['400']) }};
            --color-brand-500-rgb: {{ hex2rgb($paleta['500']) }};
            --color-brand-600-rgb: {{ hex2rgb($paleta['600']) }};
            --color-brand-700-rgb: {{ hex2rgb($paleta['700']) }};
            --color-brand-800-rgb: {{ hex2rgb($paleta['800']) }};
            --color-brand-900-rgb: {{ hex2rgb($paleta['900']) }};
            --color-brand-950-rgb: {{ hex2rgb($paleta['950']) }};

            /* Dynamic border radius */
            --border-radius-sm: {{ $esquinas['sm'] }};
            --border-radius-default: {{ $esquinas['default'] }};
            --border-radius-md: {{ $esquinas['md'] }};
            --border-radius-lg: {{ $esquinas['lg'] }};
            --border-radius-xl: {{ $esquinas['xl'] }};
            --border-radius-2xl: {{ $esquinas['2xl'] }};
            --border-radius-3xl: {{ $esquinas['3xl'] }};

            /* Dynamic shadows */
            --shadow-sm: {{ $sombras['sm'] }};
            --shadow-default: {{ $sombras['default'] }};
            --shadow-md: {{ $sombras['md'] }};
            --shadow-lg: {{ $sombras['lg'] }};
            --shadow-xl: {{ $sombras['xl'] }};
            --shadow-2xl: {{ $sombras['2xl'] }};
        }
        @if($colorTema === 'black')
        html.dark {
            --color-brand-50: #09090b;
            --color-brand-100: #18181b;
            --color-brand-200: #27272a;
            --color-brand-300: #3f3f46;
            --color-brand-400: #a1a1aa;
            --color-brand-500: #ffffff;
            --color-brand-600: #e4e4e7;
            --color-brand-700: #f4f4f5;
            --color-brand-800: #fafafa;
            --color-brand-900: #ffffff;
            --color-brand-950: #ffffff;

            --color-brand-50-rgb: 9 9 11;
            --color-brand-100-rgb: 24 24 27;
            --color-brand-200-rgb: 39 39 42;
            --color-brand-300-rgb: 63 63 70;
            --color-brand-400-rgb: 161 161 170;
            --color-brand-500-rgb: 255 255 255;
            --color-brand-600-rgb: 228 228 231;
            --color-brand-700-rgb: 244 244 245;
            --color-brand-800-rgb: 250 250 250;
            --color-brand-900-rgb: 255 255 255;
            --color-brand-950-rgb: 255 255 255;
        }
        /* Custom overrides to make text readable when brand elements are white in dark mode */
        html.dark .bg-brand-500,
        html.dark .hover\:bg-brand-400:hover,
        html.dark .active\:bg-brand-600:active {
            color: #0f172a !important;
        }
        html.dark .bg-brand-500 *,
        html.dark .hover\:bg-brand-400:hover *,
        html.dark .active\:bg-brand-600:active * {
            color: #0f172a !important;
            --tw-text-opacity: 1 !important;
            stroke: #0f172a !important;
        }
        html.dark .bg-brand-500 ~ button.text-white,
        html.dark .bg-brand-500 ~ button.text-white * {
            color: #0f172a !important;
        }
        html.dark ::selection {
            background-color: #ffffff !important;
            color: #0f172a !important;
        }
        @endif
    </style>
</head>

<body class="font-sans antialiased bg-dark-base text-slate-200 selection:bg-brand-500 selection:text-white transition-colors duration-300">
    @inertia

    @env('local')
    <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
    @endenv
</body>

</html>
