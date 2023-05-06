<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
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
const buscando = ref(false);
const titulo = ref(
    props.titulo
        ? "Buscar " + props.titulo + "..."
        : "Buscar " + ruta.value + "..."
);
const params = ref(props.params ? props.params : {});
const orderByObject = ref(
    props.orderByObject ? props.orderByObject : { field: "id", sort: "asc" }
);
const realizarBusqueda = () => {
    buscando.value = true;
    if (buscar.value.length > 0) {
        router.get(
            route(ruta.value + ".search", {
                filtro: buscar.value,
                orderBy: orderByObject.value,
                ...params.value,
            })
        );
    } else {
        router.get(
            route(ruta.value + ".index", {
                orderBy: orderByObject.value,
                ...params.value,
            }),
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
    <div
        class="flex flex-wrap border-b border-slate-300 bg-slate-200 bg-gradient-to-b from-slate-100 to-slate-200 relative rounded-md shadow-sm rounded-b-none"
    >
        <div
            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"
        >
            <font-awesome-icon
                v-if="!buscando"
                icon="magnifying-glass"
                class="text-slate-400"
            />
            <font-awesome-icon
                v-if="buscando"
                icon="spinner"
                class="text-slate-400 animate-spin"
            />
        </div>
        <JetInput
            ref="input"
            class="m-1 block w-full pl-10"
            :placeholder="titulo"
            v-model="buscar"
            type="text"
            autofocus
            @keyup.enter="realizarBusqueda"
            :disabled="buscando"
        />
    </div>
</template>
