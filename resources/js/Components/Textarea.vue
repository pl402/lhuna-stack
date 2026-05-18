<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
  modelValue: String,
  options: Array,
  value: String,
});

defineEmits(["update:modelValue"]);

const textarea = ref(null);

onMounted(() => {
  if (textarea.value.hasAttribute("autofocus")) {
    textarea.value.focus();
  }
});

defineExpose({ focus: () => textarea.value.focus() });
</script>

<template>
  <textarea
    ref="textarea"
    class=" bg-dark-surface border-dark-border text-slate-100 focus:ring-brand-500 focus:ring-opacity-30 focus:border-brand-500 focus:ring focus:ring-brand-500 focus:ring-opacity-30 rounded-md shadow-sm shadow-black/20 disabled:text-slate-400"
    :class="value === '' ? 'text-slate-400' : ''"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
  />
</template>
