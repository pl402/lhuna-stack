<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        required: true,
    },
    tabs: {
        type: Array,
        required: true, // array of { id: string, label: string, icon?: string | array }
    },
});

const emit = defineEmits(['update:modelValue']);

const activeIndex = computed(() => {
    return props.tabs.findIndex(tab => tab.id === props.modelValue);
});

const sliderStyle = computed(() => {
    if (activeIndex.value === -1) return { display: 'none' };
    const count = props.tabs.length;
    return {
        width: `calc((100% - 8px) / ${count})`,
        left: `calc(4px + (100% - 8px) / ${count} * ${activeIndex.value})`,
    };
});

const selectTab = (id) => {
    emit('update:modelValue', id);
};
</script>

<template>
    <div class="relative flex p-1 bg-dark-elevated/40 border border-dark-border rounded-xl mb-6 backdrop-blur-md">
        
        <!-- Sliding Highlight Inset Capsule -->
        <div 
            class="absolute top-1 bottom-1 bg-brand-500 rounded-lg transition-all duration-300 ease-out shadow shadow-brand-500/10"
            :style="sliderStyle"
        ></div>

        <!-- Tab Action Buttons -->
        <button
            v-for="(tab, index) in tabs"
            :key="tab.id"
            type="button"
            @click="selectTab(tab.id)"
            class="relative z-10 flex-1 py-2 text-center text-xs font-semibold rounded-lg transition-colors duration-300 focus:outline-none flex items-center justify-center gap-1.5 cursor-pointer select-none"
            :class="modelValue === tab.id 
                ? 'text-white font-bold' 
                : 'text-slate-400 dark:text-slate-400 hover:text-slate-200'"
        >
            <font-awesome-icon v-if="tab.icon" :icon="tab.icon" class="w-3.5 h-3.5 transition-transform duration-300" :class="modelValue === tab.id ? 'scale-110' : ''" />
            <span>{{ tab.label }}</span>
        </button>

    </div>
</template>

