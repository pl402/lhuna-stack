<template>
  <input
    ref="inputRef"
    type="text"
    class=" bg-dark-surface border-dark-border text-slate-100 focus:ring-brand-500 focus:ring-opacity-30 focus:border-brand-500 focus:ring focus:ring-brand-500 focus:ring-opacity-30 rounded-md disabled:text-slate-400 shadow-sm shadow-black/20"
    :placeholder="placeholder"
  />
</template>

<script>
import { useCurrencyInput } from "vue-currency-input";

export default {
  name: "NumberInput",
  props: {
    modelValue: Number,
    placeholder: String,
  },
  setup(props) {
    const { inputRef } = useCurrencyInput({
      currency: "MXN",
      precision: 0,
      currencyDisplay: "hidden",
    });

    return { inputRef };
  },
};
</script>
