<script setup>
import { ref, computed, onMounted, toRef } from "vue";
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

if (import.meta.env.PROD) {
  var modelValue = toRef(props, 'modelValue')
  let selected = ref(modelValue);
} else {
  var modelValue = ref(props.modelValue)
  let selected = ref(props.modelValue)
}

if (!props.modelValue) {
  props.modelValue.value = "";
  props.modelValue.name = "";
}

const emit = defineEmits(["update:modelValue"]);

const autocomplete = ref(null);
let query = ref("");

let filteredOpcion = computed(() =>
  query.value === ""
    ? props.options
    : props.options.filter((opcion) =>
      opcion.name
        .toLowerCase()
        .replace(/\s+/g, "")
        .includes(query.value.toLowerCase().replace(/\s+/g, ""))
    )
);

const onChange = () => {
  if (import.meta.env.PROD) {
    emit('update:modelValue', modelValue.value);
  } else {
    emit('update:modelValue', props.modelValue);
  }
}


defineExpose({ focus: () => autocomplete.value.focus() });
</script>
<template>
  <Combobox v-model="modelValue" :value="modelValue" ref="autocomplete" @update:modelValue="onChange">
    <div class="relative mt-1">
      <ComboboxInput class="
          border-gray-400
          focus:border-slate-400
          focus:ring
          focus:ring-slate-400
          focus:ring-opacity-50
          rounded-md
          shadow-sm
          w-full
          disabled:text-gray-500
        " :displayValue="(opcion) => opcion.name" :class="(opcion) => (opcion.name === '' ? 'text-gray-400' : '')"
        @change="query = $event.target.value" :placeholder="props.placeholder" :disabled="props.disabled" />
      <ComboboxButton class="absolute inset-y-0 top-5 right-0 flex items-center pr-2" :disabled="props.disabled">
        <SelectorIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
      </ComboboxButton>

      <transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
        <ComboboxOptions class="
            absolute
            mt-1
            max-h-60
            w-full
            overflow-auto
            rounded-md
            bg-white
            py-1
            text-base
            shadow-lg
            ring-1 ring-black ring-opacity-5
            focus:outline-none
            sm:text-sm
            z-50
          ">
          <div v-if="filteredOpcion.length === 0 && query !== ''"
            class="relative cursor-default select-none py-2 px-4 text-gray-400">
            Sin resultados
          </div>
          <ComboboxOption v-for="opcion in filteredOpcion" as="template" :key="opcion.value" :value="opcion"
            v-slot="{ selected, active }" :disabled="opcion.disabled || props.disabled">
            <li class=" relative cursor-default select-none py-2 pl-10 pr-4" :class="{
              'bg-slate-600 text-white': active,
              'text-gray-900': !active,
            }">
              <span class="block truncate" :class="{
                'font-medium': selected,
                'font-normal': !selected,
              }">
                {{ opcion.name }}
              </span>
              <span v-if="selected" class="absolute inset-y-0 left-0 flex items-center pl-3"
                :class="{ 'text-white': active, 'text-slate-600': !active }">
                <CheckIcon class="h-5 w-5" aria-hidden="true" />
              </span>
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </transition>
    </div>
  </Combobox>
</template>
