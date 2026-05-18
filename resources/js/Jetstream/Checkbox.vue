<script setup>
import { computed } from "vue";

const emit = defineEmits(["update:checked"]);

const props = defineProps({
  checked: {
    type: [Array, Boolean],
    default: false,
  },
  value: {
    type: String,
    default: null,
  },
});

const proxyChecked = computed({
  get() {
    return props.checked;
  },

  set(val) {
    emit("update:checked", val);
  },
});
</script>


<template>
  <input
    v-model="proxyChecked"
    type="checkbox"
    :value="value"
    class="
      rounded
      border-dark-border
      text-brand-500 bg-dark-surface
      shadow-sm shadow-black/20
      focus:border-dark-border
      focus:ring-[1.5px]
      focus:ring-brand-500
      focus:ring-opacity-50
    "
  />
</template>
