<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import Select from "@/Components/Select.vue";
import NumberInput from "@/Components/NumberInput.vue";

const props = defineProps({
    modelValue: Object,
    ruta: String,
    orderByObject: Object,
    params: Object,
    titulo: String,
    filtros: Object,
});

const buscar = ref(props.modelValue ? props.modelValue : "");
const ruta = ref(props.ruta ? props.ruta : "");
const buscando = ref(false);
const titulo = ref(
    props.titulo
        ? "Buscar " + props.titulo + "..."
        : "Buscar " + ruta.value + "..."
);
const filtros = ref(props.filtros ? props.filtros : false);
const params = ref(props.params ? props.params : {});
const orderByObject = ref(
    props.orderByObject ? props.orderByObject : { field: "id", sort: "asc" }
);

const muestraFiltros = ref(false);

const rotaIcono = ref(false);

function abreFiltros() {
    rotaIcono.value = true;
    muestraFiltros.value = !muestraFiltros.value;
    // Limpia los filtros
    if (filtros.value) {
        Object.keys(filtros.value).forEach((key) => {
            filtros.value[key].value = "";
        });
    }
    setTimeout(() => {
        rotaIcono.value = false;
    }, 300);
}

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

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.25s ease-out;
}

.slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.slide-up-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}
</style>

<template>
    <div
        class="flex flex-wrap border-b border-slate-300 bg-slate-200 bg-gradient-to-b from-slate-100 to-slate-200 relative rounded-md shadow-sm rounded-b-none"
    >
        <div
            class="absolute inset-y-0 left-0 pl-4 p-3 items-center pointer-events-none"
        >
            <transition name="slide-up" mode="out-in">
                <font-awesome-icon
                    v-if="muestraFiltros"
                    icon="filter"
                    class="text-slate-500"
                />
                <font-awesome-icon
                    v-else-if="!buscando"
                    icon="magnifying-glass"
                    class="text-slate-400"
                />
                <font-awesome-icon
                    v-else-if="buscando"
                    icon="spinner"
                    class="text-slate-400 animate-spin"
                />
            </transition>
        </div>

        <transition name="slide-up" mode="out-in">
            <div
                v-if="muestraFiltros"
                class="m-1 block w-full pl-10 pr-10 pb-2"
            >
                <h4 class="text-slate-500 font-bold text-lg pt-[5px]">
                    Filtros
                </h4>

                <label v-for="(filtro, index) in filtros" class="block mb-1">
                    <template v-if="filtro.activo"> </template>
                </label>
                <div class="flex justify-between mt-4">
                    <div class="w-1/2">
                        <JetSecondaryButton
                            class="bg-opacity-95"
                            type="cancel"
                            @click="abreFiltros"
                            >Cancelar</JetSecondaryButton
                        >
                    </div>
                    <div class="w-1/2">
                        <JetButton
                            class="float-right"
                            :colors="{
                                bg: 'bg-blue-500',
                                hover: 'hover:bg-blue-600',
                                active: 'active:bg-blue-700',
                            }"
                        >
                            <font-awesome-icon icon="filter" class="mr-2" />
                            Filtrar
                        </JetButton>
                    </div>
                </div>
            </div>
            <JetInput
                v-else
                ref="input"
                class="m-1 block w-full pl-10 h-10"
                :class="{
                    'rounded-r-md': !filtros,
                    'mr-1 pr-12': filtros,
                }"
                :placeholder="titulo"
                v-model="buscar"
                type="text"
                autofocus
                @keyup.enter="realizarBusqueda"
                :disabled="buscando"
            />
        </transition>
        <button
            v-if="filtros"
            @click="abreFiltros"
            class="absolute w-10 inset-y-1 right-1 flex items-center rounded-md hover:bg-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-opacity-50 border-slate-400 border h-10 transition-all duration-200 ease-in-out"
            :class="{
                'bg-slate-200 rounded-l-none': !muestraFiltros,
                'bg-slate-300 shadow-inner': muestraFiltros,
            }"
            title="Mostrar filtros"
        >
            <font-awesome-icon
                icon="gear"
                class="text-slate-500 m-auto"
                :class="{
                    'animate-spin': rotaIcono,
                }"
            />
        </button>
    </div>
</template>
