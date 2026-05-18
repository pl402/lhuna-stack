<script setup>
import { onMounted, ref } from "vue";

defineProps({
 modelValue: String,
});

defineEmits(["update:modelValue"]);

const input = ref(null);

onMounted(() => {
 if (input.value.hasAttribute("autofocus")) {
  input.value.focus();
 }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
 <input ref="input" class="
    bg-dark-surface border-dark-border text-slate-100 transition-colors
   focus:border-brand-500 focus:bg-brand-50 dark:focus:bg-brand-950
   focus:ring-[1.5px] focus:ring-brand-500 focus:ring-opacity-30
   
   rounded-md
   shadow-sm shadow-black/20
   disabled:text-slate-400
   
   file:border-solid
   file:inline-flex
   file:items-center
   file:px-4
   file:py-2
   file:bg-brand-500
   file:border
   file:border-transparent
   file:rounded-md
   file:font-semibold
   file:text-xs
   file:text-white
   file:uppercase
   file:tracking-widest
   hover:file:bg-gray-700
   active:file:bg-gray-900
   focus:file:outline-none
   focus:file:border-brand-500
   focus:file:ring
   focus:file:ring-brand-500 focus:file:ring-opacity-50
   file:disabled:opacity-25
   file:shadow-sm shadow-black/20
   hover:file:shadow-md shadow-black/30
   focus:file:shadow-lg shadow-black/40
   active:file:ring-2 active:file:ring-brand-500
   file:transition
  " :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />
</template>
