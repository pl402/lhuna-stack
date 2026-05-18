<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Array, File, null],
        default: () => []
    },
    multiple: {
        type: Boolean,
        default: true
    },
    accept: {
        type: String,
        default: '*'
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);

const onDragOver = (e) => {
    if (props.disabled) return;
    e.preventDefault();
    isDragging.value = true;
};

const onDragLeave = (e) => {
    if (props.disabled) return;
    e.preventDefault();
    isDragging.value = false;
};

const onDrop = (e) => {
    if (props.disabled) return;
    e.preventDefault();
    isDragging.value = false;
    
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
        handleFiles(e.dataTransfer.files);
    }
};

const onFileChange = (e) => {
    if (e.target.files && e.target.files.length > 0) {
        handleFiles(e.target.files);
    }
};

const handleFiles = (fileList) => {
    let filesArray = Array.from(fileList);
    
    if (!props.multiple) {
        filesArray = [filesArray[0]];
        emit('update:modelValue', filesArray[0]);
    } else {
        const existing = Array.isArray(props.modelValue) ? props.modelValue : [];
        emit('update:modelValue', [...existing, ...filesArray]);
    }
};

const triggerInput = () => {
    if (!props.disabled) {
        fileInput.value.click();
    }
};
</script>

<template>
    <div 
        @dragover="onDragOver" 
        @dragleave="onDragLeave" 
        @drop="onDrop"
        @click="triggerInput"
        :class="[
            'relative flex flex-col items-center justify-center w-full h-40 px-4 py-6 border-2 border-dashed rounded-lg transition-colors cursor-pointer',
            isDragging ? 'border-brand-500 bg-brand-500/10' : 'border-dark-border bg-dark-surface hover:bg-dark-elevated/40',
            disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
    >
        <input 
            type="file" 
            ref="fileInput" 
            class="hidden" 
            :multiple="multiple" 
            :accept="accept" 
            @change="onFileChange" 
            :disabled="disabled"
        />
        
        <svg class="w-10 h-10 mb-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        <p class="mb-1 text-sm font-medium text-slate-600 dark:text-slate-300">
            <span class="text-brand-500 font-semibold">Haz clic para subir</span> o arrastra y suelta
        </p>
        <p class="text-xs text-slate-500 dark:text-slate-400">PNG, JPG, PDF (MAX. 10MB)</p>
    </div>
</template>
