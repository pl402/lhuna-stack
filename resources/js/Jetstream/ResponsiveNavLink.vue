<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? "block pl-3 pr-4 py-2 border-l-4 border-brand-500 text-base font-medium text-brand-700 dark:text-brand-400 bg-brand-500/10 focus:outline-none focus:text-brand-800 dark:focus:text-brand-300 focus:bg-brand-500/20 focus:border-brand-400 transition-all duration-300"
        : "block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-slate-400 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-dark-elevated hover:border-dark-border focus:outline-none focus:text-brand-700 dark:focus:text-brand-400 focus:bg-dark-elevated focus:border-dark-border transition-all duration-300";
});
</script>

<template>
    <div>
        <button
            v-if="as === 'button'"
            :class="classes"
            class="w-full text-left"
        >
            <slot />
        </button>

        <Link v-else :href="href" :class="classes">
            <slot />
        </Link>
    </div>
</template>
