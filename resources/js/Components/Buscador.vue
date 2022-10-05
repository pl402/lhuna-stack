<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import JetInput from "@/Jetstream/Input.vue";

const props = defineProps({
  modelValue: Object,
  ruta: String,
  orderByObject: Object,
  params: Object,
  titulo: String,
});

const buscar = ref(props.modelValue ? props.modelValue : "");
const ruta = ref(props.ruta ? props.ruta : "");
const titulo = ref(props.titulo ? "Buscar " + props.titulo + "..." : "Buscar " + ruta.value + "...");
const params = ref(props.params ? props.params : {});
const orderByObject = ref(props.orderByObject ? props.orderByObject : { field: "id", sort: "asc" });
const realizarBusqueda = () => {
  if (buscar.value.length > 0) {
    Inertia.get(route(ruta.value + ".search", { filtro: buscar.value, orderBy: orderByObject.value, ...params.value }));
  } else {
    Inertia.get(
      route(ruta.value + ".index", { orderBy: orderByObject.value, ...params.value }),
      { preserveState: true }
    );
  }
};

defineEmits(["update:modelValue"]);

const input = ref(null);

</script>
<script>
export default {};
</script>


<template>
  <div class="flex flex-wrap border-b border-slate-300 bg-slate-200 bg-gradient-to-b from-slate-100 to-slate-200">
    <JetInput ref="input" class="m-1 block w-full" :placeholder="titulo" v-model="buscar" type="text" autofocus
      @keyup.enter="realizarBusqueda" />
  </div>
</template>
