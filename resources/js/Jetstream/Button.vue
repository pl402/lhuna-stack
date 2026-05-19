<script setup>
defineProps({
    type: {
        type: String,
        default: "submit",
    },
    colors: {
        type: Array,
        default: {
            bg: "bg-brand-500",
            hover: "hover:bg-brand-400",
            active: "active:bg-brand-600",
        },
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring disabled:opacity-25 shadow-md shadow-brand-500/20 hover:shadow-lg hover:shadow-brand-500/30 focus:shadow-xl focus:shadow-brand-500/40 active:ring-2 active:ring-brand-500 transition focus:border-brand-500 focus:ring-brand-500"
        :class="`${colors.bg} ${colors.hover} ${colors.active}`"
    >
        <slot />
    </button>
</template>
