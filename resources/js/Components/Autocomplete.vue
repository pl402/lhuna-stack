<script setup>
import { ref, computed, onMounted, toRef, watch } from "vue";
import {
  Combobox,
  ComboboxInput,
  ComboboxButton,
  ComboboxOptions,
  ComboboxOption,
  TransitionRoot,
} from "@headlessui/vue";
import { CheckIcon, SelectorIcon } from "@heroicons/vue/solid";

const props = defineProps({
  modelValue: Object,
  options: Array,
  placeholder: String,
  disabled: Boolean,
});

var internalValue = ref(props.modelValue);
var options = ref(props.options);
var selected = ref(props.modelValue);

if (!internalValue.value) {
  // Agrega un valor vacio para que se pueda seleccionar
  options.value.unshift({ name: "", value: "", disabled: true });

  // Selecciona el valor vacio
  internalValue.value = options.value[0];
}

const emit = defineEmits(["update:modelValue"]);

const autocomplete = ref(null);
let query = ref("");

let filteredOpcion = computed(() =>
  query.value === ""
    ? options.value
    : options.value.filter((opcion) =>
       opcion.name
         .toLowerCase()
         .replace(/\s+/g, "")
         .includes(query.value.toLowerCase().replace(/\s+/g, ""))
     )
);

const onChange = (selectedVal) => {
  emit("update:modelValue", selectedVal);
};

watch(() => props.modelValue, (newVal) => {
  internalValue.value = newVal;
}, { deep: true });

defineExpose({ focus: () => autocomplete.value.focus() });
</script>
<template>
  <Combobox v-model="internalValue" @update:modelValue="onChange">
    <div class="relative mt-1">
      <ComboboxInput
        class="border-dark-border focus:border-brand-500 focus:ring focus:ring-brand-500 focus:ring-opacity-30 rounded-md shadow-sm shadow-black/20 w-full disabled:text-slate-400 bg-dark-surface"
        :displayValue="(opcion) => opcion ? opcion.name : ''"
        :class="
          (opcion) => (!opcion || opcion.name === '' ? 'text-slate-400' : '')
        "
        @change="query = $event.target.value"
        :placeholder="props.placeholder"
        :disabled="props.disabled"
      />
      <ComboboxButton
        class="absolute inset-y-0 top-0 right-0 flex items-center pr-2"
        :disabled="props.disabled"
      >
        <SelectorIcon
          class="h-5 w-5 text-slate-400"
          aria-hidden="true"
        />
      </ComboboxButton>

      <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <ComboboxOptions
          class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-dark-surface py-1 text-base shadow-lg shadow-black/40 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm z-50"
        >
          <div
            v-if="filteredOpcion.length === 0 && query !== ''"
            class="relative cursor-default select-none py-2 px-4 text-slate-400"
          >
            Sin resultados
          </div>
          <ComboboxOption
            v-for="opcion in filteredOpcion"
            as="template"
            :key="opcion.value"
            :value="opcion"
            v-slot="{ selected, active }"
            :disabled="opcion.disabled || props.disabled"
          >
            <li
              class="relative cursor-default select-none py-2 pl-10 pr-4"
              :class="{
                'bg-slate-600 text-white': active,
                'text-slate-100': !active,
              }"
            >
              <span
                class="block truncate"
                :class="{
                  'font-medium': selected,
                  'font-normal': !selected,
                }"
              >
                {{ opcion.name }}
              </span>
              <span
                v-if="selected"
                class="absolute inset-y-0 left-0 flex items-center pl-3"
                :class="{
                  'text-white': active,
                  'text-slate-600': !active,
                }"
              >
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </transition>
    </div>
  </Combobox>
</template>
