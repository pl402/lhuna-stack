<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
 modelValue: String,
 options: Array,
 value: String,
});

defineEmits(["update:modelValue"]);

const select = ref(null);

onMounted(() => {
 if (select.value.hasAttribute("autofocus")) {
  select.value.focus();
 }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
 <select ref="select" class="
    bg-dark-surface border-dark-border text-slate-100 transition-colors
   focus:border-brand-500 focus:bg-brand-50 dark:focus:bg-brand-950
   focus:ring-[1.5px] focus:ring-brand-500 focus:ring-opacity-30
   
   rounded-md
   shadow-sm shadow-black/20
   disabled:text-slate-400
  " :class="value === '' ? 'text-slate-400' : ''" :value="modelValue"
  @change="$emit('update:modelValue', $event.target.value)">
  <option selected value="" disabled>Seleccione una Opción</option>
  <option v-for="option in options" :disabled="option.disabled" :key="option.value"
   :value="option.value ? option.value : option.name" v-html="option.name" />
 </select>
</template>
