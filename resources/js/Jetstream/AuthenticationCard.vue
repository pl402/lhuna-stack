<script setup>
import GridBackground from "@/Components/GridBackground.vue";
import GlowCard from "@/Components/GlowCard.vue";

defineProps({
    shake: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <div 
        class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-dark-base relative overflow-hidden font-sans"
    >
        <!-- Reusable Grid Background -->
        <GridBackground v-if="$page.props.themeGrid !== false" :fixed="true" />

        <div class="relative z-10 flex flex-col items-center w-full px-4">
            <div class="hover:scale-105 transition-transform duration-500">
                <slot name="logo" />
            </div>

            <GlowCard 
                orb-size="w-56 h-56"
                orb-opacity="group-hover:opacity-15"
                orb-blur="blur-[80px]"
                :class="[
                    'w-full sm:max-w-md mt-8 p-6 rounded-xl',
                    { 'animate-shake border-red-500/50 shadow-red-950/20': shake }
                ]"
            >
                <slot />
            </GlowCard>
        </div>
    </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  15%, 45%, 75% { transform: translateX(-6px); }
  30%, 60%, 90% { transform: translateX(6px); }
}
.animate-shake {
  animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
}
</style>
