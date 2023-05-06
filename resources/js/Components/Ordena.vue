<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import JetInput from "@/Jetstream/Input.vue";

const props = defineProps({
    modelValue: Object,
    ruta: String,
    titulo: String,
    campo: String,
    buscar: String,
    params: Object,
});

const buscar = ref(props.buscar ? props.buscar : "");
const ruta = ref(props.ruta ? props.ruta : "");
const campo = ref(props.campo ? props.campo : "");
const titulo = ref(props.titulo ? props.titulo : "");
const params = ref(props.params ? props.params : {});
const orderByObject = ref(
    props.modelValue ? props.modelValue : { field: "id", sort: "asc" }
);

defineEmits(["update:modelValue"]);

const ordena = (key) => {
    if (orderByObject.value.field === key) {
        orderByObject.value.sort =
            orderByObject.value.sort === "asc" ? "desc" : "asc";
    } else {
        orderByObject.value.field = key;
        orderByObject.value.sort = "asc";
    }

    if (buscar.value.length > 0) {
        router.get(
            route(ruta.value + ".search", buscar.value),
            {
                orderBy: orderByObject.value,
            },
            { preserveState: true }
        );
    } else {
        router.get(
            route(ruta.value + ".index"),
            {
                orderBy: orderByObject.value,
            },
            { preserveState: true }
        );
    }
};
</script>
<script>
export default {};
</script>

<template>
    <span
        @click="ordena(campo)"
        class="hover:underline cursor-pointer"
        :class="orderByObject.field === campo ? 'text-gray-900' : ''"
        >{{ titulo }}
        <font-awesome-icon
            v-if="orderByObject.field === campo"
            :icon="'sort-' + orderByObject.sort"
        />
    </span>
</template>
