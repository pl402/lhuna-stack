<script setup>
import { ref, onMounted, onUnmounted } from "vue";

defineProps({
    fixed: {
        type: Boolean,
        default: true
    }
});

const mouseX = ref("50%");
const mouseY = ref("50%");

const handleMouseMove = (event) => {
    const x = (event.clientX / window.innerWidth) * 100;
    const y = (event.clientY / window.innerHeight) * 100;
    mouseX.value = `${x}%`;
    mouseY.value = `${y}%`;
};

onMounted(() => {
    window.addEventListener("mousemove", handleMouseMove);
});

onUnmounted(() => {
    window.removeEventListener("mousemove", handleMouseMove);
});
</script>

<template>
    <div 
        :class="[
            'pointer-events-none overflow-hidden inset-0',
            fixed ? 'fixed z-0' : 'absolute'
        ]"
        :style="{ '--mouse-x': mouseX, '--mouse-y': mouseY }"
    >
        <!-- Glowing Ambient Backdrops (with slowly floating animation) -->
        <div 
            :class="[
                'rounded-full bg-brand-500/10 blur-[130px] pointer-events-none animate-float-1',
                fixed ? 'fixed top-1/4 left-1/4 w-96 h-96 z-0' : 'absolute top-1/4 left-1/4 w-96 h-96'
            ]"
        ></div>
        <div 
            :class="[
                'rounded-full bg-indigo-500/10 blur-[130px] pointer-events-none animate-float-2',
                fixed ? 'fixed bottom-1/4 right-1/4 w-96 h-96 z-0' : 'absolute bottom-1/4 right-1/4 w-96 h-96'
            ]"
            style="animation-delay: -5s;"
        ></div>
        
        <!-- Moving Retro Space Grid Overlay (Base subtle lines - 25% opacity) -->
        <div 
            :class="[
                'bg-[linear-gradient(to_right,#1f29370c_1px,transparent_1px),linear-gradient(to_bottom,#1f29370c_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] pointer-events-none',
                fixed ? 'fixed inset-0 z-0' : 'absolute inset-0'
            ]"
        ></div>

        <!-- Interactive Glowing Grid (Follows mouse, lights up with brand color) -->
        <div 
            :class="[
                'text-brand-500/20 bg-[linear-gradient(to_right,currentColor_1px,transparent_1px),linear-gradient(to_bottom,currentColor_1px,transparent_1px)] bg-[size:4rem_4rem] pointer-events-none',
                fixed ? 'fixed inset-0 z-0' : 'absolute inset-0'
            ]"
            style="mask-image: radial-gradient(280px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), black 15%, transparent 100%); -webkit-mask-image: radial-gradient(280px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), black 15%, transparent 100%);"
        ></div>
    </div>
</template>

<style scoped>
@keyframes float-orb-1 {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(24px, -24px) scale(1.1); }
}
.animate-float-1 {
  animation: float-orb-1 16s infinite ease-in-out;
}

@keyframes float-orb-2 {
  0%, 100% { transform: translate(0, 0) scale(1.1); }
  50% { transform: translate(-24px, 24px) scale(1); }
}
.animate-float-2 {
  animation: float-orb-2 16s infinite ease-in-out;
}

@keyframes move-grid {
  0% { background-position: 0 0; }
  100% { background-position: 4rem 4rem; }
}
.animate-grid {
  animation: move-grid 24s linear infinite;
}
</style>
