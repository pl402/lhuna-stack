<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

const errors = computed(() => usePage().props.errors);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform -translate-y-2 opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform -translate-y-2 opacity-0"
    >
        <div v-if="hasErrors" class="p-4 mb-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 shadow-lg shadow-red-950/20">
            <div class="flex items-center gap-2.5 font-bold text-sm mb-2">
                <svg class="w-5 h-5 flex-shrink-0 animate-pulse text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>¡Oh no! Algo salió mal</span>
            </div>

            <ul class="list-disc list-inside text-xs space-y-1 opacity-90 leading-relaxed pl-1">
                <li v-for="(error, key) in errors" :key="key" class="text-left font-medium">
                    {{ error }}
                </li>
            </ul>
        </div>
    </Transition>
</template>
