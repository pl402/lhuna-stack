<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 404
    }
});

// Humorous error definitions matching theme with FontAwesome icons
const errorDetails = computed(() => {
    const database = {
        400: {
            title: 'Sintaxis del Espacio Exterior',
            subtitle: 'Error 400 - Bad Request',
            joke: '¡El servidor intentó descifrar tu petición pero cruzó los cables! ¿Escribiste en binario marciano?',
            suggestion: 'Verifica los parámetros o la dirección y no envíes jeroglíficos la próxima vez.',
            faIcon: ['fas', 'exclamation-triangle'],
            color: 'from-amber-500 to-yellow-500',
            bgGlow: 'bg-amber-500/10'
        },
        401: {
            title: '¡Alto ahí, Intrépido Viajero!',
            subtitle: 'Error 401 - Unauthorized',
            joke: 'No traes la llave mágica o tus credenciales expiraron hace tres lunas. ¡El perro del servidor no te deja pasar!',
            suggestion: 'Por favor, inicia sesión con credenciales válidas antes de cruzar esta puerta.',
            faIcon: ['fas', 'id-card'],
            color: 'from-blue-500 to-indigo-500',
            bgGlow: 'bg-blue-500/10'
        },
        403: {
            title: 'Acceso Prohibido en esta Galaxia',
            subtitle: 'Error 403 - Forbidden',
            joke: 'El club secreto de Lhuna Stack no admite curiosos en este directorio. ¡A menos que conozcas el saludo secreto!',
            suggestion: 'No tienes los permisos necesarios para ver esto. Regresa antes de que sonemos las alarmas.',
            faIcon: ['fas', 'times'],
            color: 'from-red-500 to-pink-500',
            bgGlow: 'bg-red-500/10'
        },
        404: {
            title: '¡La Página se ha Esfumado!',
            subtitle: 'Error 404 - Not Found',
            joke: 'Buscamos bajo cada piedra y en los rincones del disco duro, pero esta URL se evaporó por completo.',
            suggestion: 'Es posible que el enlace esté mal escrito o que la página se haya mudado a otra dimensión.',
            faIcon: ['fas', 'magnifying-glass'],
            color: 'from-brand-500 to-indigo-500',
            bgGlow: 'bg-brand-500/10'
        },
        405: {
            title: 'Método no Aceptado en el Templo',
            subtitle: 'Error 405 - Method Not Allowed',
            joke: 'Estás intentando abrir la puerta empujando cuando claramente dice "JALE". El servidor rechaza este método.',
            suggestion: 'El navegador usó una acción (POST/GET) que esta ruta no tolera. Prueba de otra manera.',
            faIcon: ['fas', 'triangle-exclamation'],
            color: 'from-orange-500 to-amber-500',
            bgGlow: 'bg-orange-500/10'
        },
        408: {
            title: '¡Caracoles en la Red!',
            subtitle: 'Error 408 - Request Timeout',
            joke: 'El navegador tardó tanto en enviar la petición que el servidor preparó un café, leyó el periódico y se fue a dormir.',
            suggestion: 'Tu conexión de red podría estar experimentando latencia pesada. Revisa el módem e intenta de nuevo.',
            faIcon: ['fas', 'clock'],
            color: 'from-cyan-500 to-teal-500',
            bgGlow: 'bg-cyan-500/10'
        },
        419: {
            title: 'Sesión Caducada por Inactividad',
            subtitle: 'Error 419 - Page Expired',
            joke: 'El token de seguridad de tu formulario se venció mientras ibas a la cocina por bocadillos.',
            suggestion: 'Simplemente refresca la página para renovar tu sesión y vuelve a enviar el formulario.',
            faIcon: ['fas', 'clock'],
            color: 'from-purple-500 to-violet-500',
            bgGlow: 'bg-purple-500/10'
        },
        429: {
            title: '¡Relaja los Dedos, Velocista!',
            subtitle: 'Error 429 - Too Many Requests',
            joke: 'Estás enviando peticiones más rápido que un procesador cuántico. ¡Nuestras APIs están sudando frío!',
            suggestion: 'Toma un respiro, prepara un té y vuelve a presionar el botón en un minuto.',
            faIcon: ['fas', 'gauge-high'],
            color: 'from-yellow-500 to-amber-500',
            bgGlow: 'bg-yellow-500/10'
        },
        500: {
            title: '¡El Servidor ha Explotado Internamente!',
            subtitle: 'Error 500 - Internal Server Error',
            joke: 'Algo salió terriblemente mal en nuestras entrañas de código. Nuestros ingenieros ya están apagando el fuego.',
            suggestion: 'No es tu culpa. Es un fallo general del código de la página. Lo arreglaremos pronto.',
            faIcon: ['fas', 'gears'],
            color: 'from-red-500 to-orange-500',
            bgGlow: 'bg-red-500/10'
        },
        502: {
            title: 'Intermediario en Huelga',
            subtitle: 'Error 502 - Bad Gateway',
            joke: 'El servidor de enlace y el principal se están peleando por el último pedazo de pizza y no se hablan.',
            suggestion: 'La puerta de enlace recibió una respuesta inválida de otro servidor de red. Reintenta en unos instantes.',
            faIcon: ['fas', 'wifi'],
            color: 'from-emerald-500 to-teal-500',
            bgGlow: 'bg-emerald-500/10'
        },
        503: {
            title: 'Servidor Fuera de Servicio por Siesta',
            subtitle: 'Error 503 - Service Unavailable',
            joke: 'Estamos barriendo el polvo, puliendo el CSS de Lhuna o el servidor colapsó por exceso de popularidad.',
            suggestion: 'El servidor está saturado por exceso de visitas o en mantenimiento preventivo. ¡Vuelve pronto!',
            faIcon: ['fas', 'screwdriver-wrench'],
            color: 'from-amber-600 to-red-500',
            bgGlow: 'bg-amber-600/10'
        },
        504: {
            title: 'La Puerta de Enlace se Cansó de Esperar',
            subtitle: 'Error 504 - Gateway Timeout',
            joke: 'El servidor principal le preguntó algo a su jefe en el backend superior, pero este se fue a almorzar sin responder.',
            suggestion: 'El tiempo límite de respuesta se agotó. Por favor, refresca la página para ver si ya despertó.',
            faIcon: ['fas', 'clock'],
            color: 'from-rose-500 to-pink-600',
            bgGlow: 'bg-rose-500/10'
        }
    };

    return database[props.status] || {
        title: 'Error Desconocido detectado',
        subtitle: `Código de Error ${props.status}`,
        joke: 'Algo extraño sucedió en el tejido del espacio-tiempo de Lhuna Stack.',
        suggestion: 'Por favor reporta este comportamiento inusual al administrador del sistema.',
        faIcon: ['fas', 'exclamation-triangle'],
        color: 'from-brand-500 to-indigo-500',
        bgGlow: 'bg-brand-500/10'
    };
});
</script>

<template>
    <Head :title="errorDetails.subtitle" />

    <div class="min-h-screen bg-dark-base text-slate-100 flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
        
        <!-- Ambient Glowing Background Orbs -->
        <div class="absolute top-1/4 left-1/4 w-80 h-80 rounded-full blur-[130px] pointer-events-none transition-all duration-1000 animate-pulse" :class="errorDetails.bgGlow"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 rounded-full blur-[130px] pointer-events-none transition-all duration-1000 animate-pulse bg-indigo-500/5"></div>
        
        <!-- Retro Space Grid Background Animation -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b12_1px,transparent_1px),linear-gradient(to_bottom,#1e293b12_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none"></div>

        <!-- Main Card Container -->
        <div class="relative z-10 w-full max-w-2xl bg-dark-surface/60 backdrop-blur-xl border border-dark-border rounded-2xl p-8 md:p-12 shadow-2xl shadow-black/80 text-center animate-fade-in">
            
            <!-- Glowing Error Code Badge -->
            <div class="inline-flex items-center justify-center mb-6 relative group">
                <!-- Glowing Aura -->
                <div class="absolute inset-0 rounded-full blur-xl opacity-30 animate-pulse bg-gradient-to-r" :class="errorDetails.color"></div>
                <!-- Circle Frame -->
                <div class="relative w-24 h-24 rounded-full bg-dark-elevated border border-zinc-800 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                    <font-awesome-icon :icon="errorDetails.faIcon" class="text-3xl text-slate-300 filter drop-shadow-md select-none animate-bounce" />
                </div>
            </div>

            <!-- Error Code Title -->
            <h1 class="text-7xl md:text-8xl font-black tracking-tight mb-2 bg-gradient-to-r bg-clip-text text-transparent filter drop-shadow-sm select-none" :class="errorDetails.color">
                {{ status }}
            </h1>
            
            <p class="text-xs uppercase font-extrabold tracking-widest text-slate-500 mb-6">
                {{ errorDetails.subtitle }}
            </p>

            <!-- Humorous Title & Explanation -->
            <h2 class="text-2xl font-bold text-slate-200 mb-4 tracking-tight">
                {{ errorDetails.title }}
            </h2>

            <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-md mx-auto">
                {{ errorDetails.joke }}
            </p>

            <!-- Action suggestion callout -->
            <div class="p-4 rounded-xl bg-dark-elevated/60 border border-slate-800/80 mb-8 max-w-lg mx-auto text-xs text-slate-300/90 leading-relaxed flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-brand-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-left font-medium">{{ errorDetails.suggestion }}</span>
            </div>

            <!-- Fully Styled Interactive Control Buttons -->
            <div class="flex justify-center items-center">
                <Link 
                    href="/" 
                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-bold rounded-lg text-sm transition-all duration-200 shadow-lg shadow-brand-500/25 border border-brand-400/20"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Volver al Escritorio
                </Link>
            </div>

        </div>

        <!-- Footer watermark -->
        <p class="mt-8 text-[10px] text-slate-600 select-none tracking-widest font-mono uppercase">
            Lhuna Stack Premium Error Engine
        </p>

    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
