<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex items-center px-4 py-2 bg-dark-surface border border-dark-border rounded-md font-semibold text-xs text-slate-300 uppercase tracking-widest shadow-sm shadow-black/20 hover:text-slate-400 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 active:text-slate-200 active:bg-dark-elevated/30 disabled:opacity-25 transition">
        <slot />
    </button>
</template>
