<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    },
    min: String,
    max: String
});

const emit = defineEmits(['update:modelValue']);
const inputRef = ref(null);

const focus = () => {
    inputRef.value.focus();
};

defineExpose({ focus });
</script>

<template>
    <div class="relative">
        <input
            ref="inputRef"
            type="date"
            :value="modelValue"
            :disabled="disabled"
            :min="min"
            :max="max"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full bg-slate-50 dark:bg-dark-surface border-slate-300 dark:border-dark-border text-slate-800 dark:text-slate-100 focus:ring-brand-500 focus:ring-opacity-30 focus:border-brand-500 focus:ring rounded-md shadow-sm shadow-black/5 dark:shadow-black/20 disabled:text-slate-400 dark:disabled:text-slate-500 disabled:bg-slate-100 dark:disabled:bg-dark-elevated disabled:cursor-not-allowed appearance-none"
        />
        <!-- Custom calendar icon can be positioned absolute here if needed, but native input handles it well usually -->
    </div>
</template>

<style scoped>
/* Optional: Style the native datepicker icon for webkit */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.2s;
}
input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}
.dark input[type="date"]::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.4;
}
.dark input[type="date"]::-webkit-calendar-picker-indicator:hover {
    opacity: 0.8;
}
</style>
