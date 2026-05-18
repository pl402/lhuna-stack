<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? "inline-flex items-center px-1 pt-1 border-b-2 border-brand-500 text-sm font-medium leading-5 text-slate-100 focus:outline-none focus:border-brand-400 transition-all duration-300"
        : "inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-400 hover:text-slate-200 hover:border-dark-border focus:outline-none focus:text-slate-200 focus:border-dark-border transition-all duration-300";
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
