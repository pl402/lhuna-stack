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
import {
  CheckIcon,
  SelectorIcon,
  PlusIcon,
  MinusIcon,
  XIcon,
} from "@heroicons/vue/solid";

const props = defineProps({
  modelValue: Object,
  options: Array,
  placeholder: String,
  disabled: Boolean,
});

if (!props.modelValue) {
  props.modelValue.value = "";
  props.modelValue.name = "";
}

const emit = defineEmits(["update:modelValue"]);

let internalValue = ref(props.modelValue);

const autocomplete = ref(null);
let query = ref("");

let filteredOpcion = computed(() => {
  let este_filtro =
    query.value === ""
      ? props.options
      : props.options.filter((opcion) =>
         opcion.name
           .toLowerCase()
           .replace(/\s+/g, "")
           .includes(query.value.toLowerCase().replace(/\s+/g, ""))
       );
  if(internalValue.value) {
    internalValue.value.forEach((element) => {
      este_filtro = este_filtro.filter(
        (opcion) => opcion.value !== element.value
      );
    });
  }
  return este_filtro;
});

defineExpose({ focus: () => autocomplete.value.focus() });

const onChange = () => {
  emit("update:modelValue", internalValue.value);
};

watch(() => props.modelValue, (newVal) => {
  internalValue.value = newVal;
}, { deep: true });

</script>
<template>
  <Combobox
    v-model="internalValue"
    ref="autocomplete"
    @update:modelValue="onChange"
    multiple
  >
    <div class="relative mt-1">
      <ComboboxInput
        class="border-gray-400 focus:border-brand-500 focus:ring focus:ring-brand-500 focus:ring-opacity-30 rounded-md shadow-sm shadow-black/20 w-full disabled:text-slate-400 bg-dark-surface"
        :displayValue="(opcion) => opcion.name"
        :class="(opcion) => (opcion.name === '' ? 'text-gray-400' : '')"
        @change="query = $event.target.value"
        :placeholder="props.placeholder"
        :disabled="props.disabled"
      />
      <ComboboxButton
        class="absolute inset-y-0 top-0 right-0 flex items-center pr-2"
        :disabled="props.disabled"
      >
        <SelectorIcon
          class="h-5 w-5 text-gray-400"
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
          class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-dark-surface/80 py-1 text-base shadow-lg shadow-black/40 ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm z-50"
          :disabled="props.disabled"
        >
          <div
            v-if="filteredOpcion.length === 0"
            class="relative cursor-default select-none py-2 px-4 text-gray-400 text-xs bg-dark-surface/80"
          >
            Lista vacía
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
              class="relative cursor-default select-none py-2 px-4"
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
                class="absolute inset-y-0 right-0 flex items-center pr-3"
                :class="{
                  'text-white': active,
                  'text-slate-600': !active,
                }"
              >
                <PlusIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </transition>
    </div>
  </Combobox>
  <transition
    enter-active-class="transition ease-out duration-200"
    enter-from-class="transform opacity-0 scale-95"
    enter-to-class="transform opacity-100 scale-100"
    leave-active-class="transition ease-in duration-75"
    leave-from-class="transform opacity-100 scale-100"
    leave-to-class="transform opacity-0 scale-95"
  >
    <div v-if="internalValue && internalValue.length > 0" class="block mt-2">
      <div
        v-for="opcion in internalValue"
        :key="opcion.id"
        class="bg-slate-600 inline-flex items-center text-sm mt-1 mr-1 rounded-full"
      >
        <span
          class="ml-2 mr-1 leading-relaxed truncate max-w-xs bg-slate-600 text-white"
          >{{ opcion.name }}</span
        >
        <button
          @click="internalValue.splice(internalValue.indexOf(opcion), 1); onChange()"
          class="w-6 h-6 inline-block align-middle bg-slate-600 hover:bg-slate-800 active:bg-slate-700 focus:outline-2 focus:outline-blue-600 text-white/60 hover:text-white rounded-full disabled:hover:bg-slate-600 disabled:hover:text-white/60"
          :disabled="props.disabled"
        >
          <XIcon class="h-6 w-6 p-1" aria-hidden="true" />
        </button>
      </div>
    </div>
  </transition>
</template>
