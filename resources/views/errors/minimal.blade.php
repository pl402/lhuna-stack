<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') - Lhuna Stack</title>

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&display=swap" rel="stylesheet">

        <!-- Tailwind CSS (via official modern CDN for the premium standalone style) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: {
                                400: '#818cf8',
                                500: '#6366f1',
                                600: '#4f46e5',
                            },
                            dark: {
                                base: '#0b0f19',
                                surface: '#111827',
                                elevated: '#1f2937',
                                border: '#374151',
                            }
                        }
                    }
                }
            }
        </script>

        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
            .glow-glow {
                animation: glow 6s infinite alternate;
            }
            @keyframes glow {
                0% { opacity: 0.2; transform: scale(1); }
                100% { opacity: 0.4; transform: scale(1.15); }
            }
        </style>
    </head>
    <body class="antialiased bg-dark-base text-slate-100 min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">
        
        <!-- Glowing Backdrop -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full bg-brand-500/10 blur-[130px] pointer-events-none glow-glow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full bg-indigo-500/10 blur-[130px] pointer-events-none glow-glow" style="animation-delay: -3s;"></div>
        
        <!-- Interactive Space Grid Overlay -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>

        <!-- Glassmorphism Card Wrapper -->
        <div class="relative z-10 w-full max-w-xl bg-dark-surface/50 border border-slate-800/80 rounded-3xl p-8 md:p-12 shadow-2xl backdrop-blur-xl text-center">
            
            <!-- Icon Indicator -->
            <div class="inline-flex items-center justify-center mb-6 relative">
                <div class="absolute inset-0 rounded-full bg-brand-500/30 blur-lg animate-pulse"></div>
                <div class="relative w-20 h-20 rounded-full bg-dark-elevated border border-slate-700/50 flex items-center justify-center shadow-lg">
                    <span class="text-3xl filter drop-shadow-md select-none">🚧</span>
                </div>
            </div>

            <!-- Title Status Code -->
            <h1 class="text-7xl md:text-8xl font-black bg-gradient-to-r from-brand-400 to-indigo-400 bg-clip-text text-transparent filter drop-shadow-sm select-none tracking-tight mb-2">
                @yield('code')
            </h1>

            <p class="text-xs uppercase font-extrabold tracking-widest text-slate-500 mb-6">
                Error de Servidor detectado
            </p>

            <h2 class="text-xl md:text-2xl font-bold text-slate-200 mb-4 tracking-tight">
                @yield('message')
            </h2>

            <p class="text-sm text-slate-400 leading-relaxed mb-8 max-w-md mx-auto">
                No pudimos procesar tu solicitud a través del canal de datos estándar. Este es el motor de renderizado de error fallback de Lhuna Stack.
            </p>

            <!-- Action Button -->
            <div>
                <a 
                    href="/escritorio" 
                    class="inline-flex items-center justify-center px-6 py-3 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-bold rounded-xl text-sm transition-all duration-200 shadow-lg shadow-brand-500/25 border border-brand-400/20"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Volver al Escritorio
                </a>
            </div>

        </div>

        <!-- Watermark -->
        <p class="mt-8 text-[10px] text-slate-600 select-none tracking-widest font-mono uppercase">
            Lhuna Stack Fallback Error Layout
        </p>

    </body>
</html>
