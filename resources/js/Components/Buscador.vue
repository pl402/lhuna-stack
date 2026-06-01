<script setup>
import { ref, watch, onMounted, computed, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import Select from "@/Components/Select.vue";
import { notify } from "notiwind";
import NumberInput from "@/Components/NumberInput.vue";

const props = defineProps({
    modelValue: Object,
    ruta: String,
    orderByObject: Object,
    params: Object,
    titulo: String,
    campos: Object,
    filtros: Array,
});

const buscar = ref(props.modelValue ? props.modelValue : "");
const filtros = ref(props.filtros ? props.filtros : []);
const ruta = ref(props.ruta ? props.ruta : "");
const buscando = ref(false);
const titulo = ref(
    props.titulo
        ? "Buscar " + props.titulo + "..."
        : "Buscar " + ruta.value + "..."
);
const campos = ref(props.campos ? props.campos : false);
const params = ref(props.params ? props.params : {});
const orderByObject = ref(
    props.orderByObject ? props.orderByObject : { field: "id", sort: "asc" }
);

// select_filtros es un array con {name: "nombre", value: "valor"} de los campos
const select_filtros = campos.value
    ? Object.keys(campos.value).map((key) => {
          return { name: campos.value[key].label, value: key };
      })
    : [];

// select_condiciones es un array con {name: "nombre", value: "valor"} de las condiciones
const select_condiciones = [
    {
        name: "Igual",
        value: "=",
    },
    {
        name: "Mayor",
        value: ">",
    },
    {
        name: "Menor",
        value: "<",
    },
    {
        name: "Mayor o igual",
        value: ">=",
    },
    {
        name: "Menor o igual",
        value: "<=",
    },
    {
        name: "Diferente",
        value: "!=",
    },
    {
        name: "Como",
        value: "LIKE",
    },
    {
        name: "No como",
        value: "NOT LIKE",
    },
    {
        name: "Es nulo",
        value: "IS NULL",
    },
    {
        name: "No es nulo",
        value: "IS NOT NULL",
    },
];

// Valor para almacenar los campos agregados

const muestraFiltros = ref(false);
const rotaIcono = ref(false);

const campo = ref("");
const condicion = ref("");
const valor = ref("");
const conjuncion = ref("AND");
const editingIndex = ref(-1);
const isProgrammaticChange = ref(false);

// Smart inputs and condition pre-selection computed values
const isSelectField = computed(() => {
    if (!campo.value || !props.campos) return false;
    return props.campos[campo.value]?.type === 'select';
});

const getFieldOptions = computed(() => {
    if (!campo.value || !props.campos) return [];
    return props.campos[campo.value]?.options || [];
});

watch(campo, (newCampo) => {
    if (isProgrammaticChange.value) return;

    if (newCampo && props.campos && props.campos[newCampo]) {
        const fieldMeta = props.campos[newCampo];
        condicion.value = fieldMeta.defaultCondition || "=";
    } else {
        condicion.value = "=";
    }
    valor.value = "";
});

// Función para agregar campos
const agregarFiltro = () => {
    if (campo.value && condicion.value && (valor.value || condicion.value === 'IS NULL' || condicion.value === 'IS NOT NULL')) {
        // If condition doesn't need value, clear value
        if (condicion.value === 'IS NULL' || condicion.value === 'IS NOT NULL') {
            valor.value = "NULO";
        }
        
        filtros.value.push({
            campo: campo.value,
            nombre_campo: select_filtros.find(
                (filtro) => filtro.value == campo.value
            ).name,
            condicion: condicion.value,
            valor: valor.value,
            conjuncion: conjuncion.value,
        });
        campo.value = "";
        condicion.value = "";
        valor.value = "";
        conjuncion.value = "AND";
    } else {
        notify(
            {
                group: "error",
                title: "Error",
                text: "Debe llenar todos los campos",
            },
            3000
        );
    }
};

function abreFiltros() {
    rotaIcono.value = true;
    muestraFiltros.value = !muestraFiltros.value;
    setTimeout(() => {
        rotaIcono.value = false;
    }, 300);
}

const toggleConjuncion = (index) => {
    filtros.value[index].conjuncion = filtros.value[index].conjuncion === 'AND' ? 'OR' : 'AND';
    filtrar();
};

const eliminarFiltro = (index) => {
    if (editingIndex.value === index) {
        cancelarEdicion();
    } else if (editingIndex.value > index) {
        editingIndex.value--;
    }
    filtros.value.splice(index, 1);
    filtrar();
};

const limpiarTodosFiltros = () => {
    cancelarEdicion();
    filtros.value = [];
    filtrar();
};

const getDisplayValue = (filtro) => {
    if (filtro.condicion === 'IS NULL') return 'Es Nulo';
    if (filtro.condicion === 'IS NOT NULL') return 'No es Nulo';
    
    if (props.campos && props.campos[filtro.campo]) {
        const fieldMeta = props.campos[filtro.campo];
        if (fieldMeta.type === 'select' && fieldMeta.options) {
            const opt = fieldMeta.options.find(o => o.value == filtro.valor);
            if (opt) return opt.name;
        }
    }
    return filtro.valor;
};

// Función para editar campos (se mantiene por compatibilidad si se requiere, pero ahora con los chips es más rápido eliminar y crear)
const cancelarEdicion = () => {
    editingIndex.value = -1;
    campo.value = "";
    condicion.value = "";
    valor.value = "";
    conjuncion.value = "AND";
};

const guardarEdicion = () => {
    if (campo.value && condicion.value && (valor.value || condicion.value === 'IS NULL' || condicion.value === 'IS NOT NULL')) {
        let finalValor = valor.value;
        if (condicion.value === 'IS NULL' || condicion.value === 'IS NOT NULL') {
            finalValor = "NULO";
        }
        
        filtros.value[editingIndex.value] = {
            campo: campo.value,
            nombre_campo: select_filtros.find(
                (filtro) => filtro.value == campo.value
            ).name,
            condicion: condicion.value,
            valor: finalValor,
            conjuncion: conjuncion.value,
        };
        
        cancelarEdicion();
        filtrar();
    } else {
        notify(
            {
                group: "error",
                title: "Error",
                text: "Debe llenar todos los campos",
            },
            3000
        );
    }
};

const editaFiltro = (campo_1, condicion_1, valor_1, conjuncion_1, index) => {
    if (campo.value && condicion.value && (valor.value || condicion.value === 'IS NULL' || condicion.value === 'IS NOT NULL') && editingIndex.value !== index) {
        notify(
            {
                group: "error",
                title: "Error",
                text: "Debe guardar el filtro actual antes de editar otro",
            },
            3000
        );
        return;
    }

    isProgrammaticChange.value = true;
    editingIndex.value = index;
    campo.value = campo_1;
    condicion.value = condicion_1;
    valor.value = valor_1 === 'NULO' ? '' : valor_1;
    conjuncion.value = conjuncion_1;
    muestraFiltros.value = true;

    nextTick(() => {
        isProgrammaticChange.value = false;
    });
};

onMounted(() => {
    setTimeout(() => {
        if (input.value) {
            input.value.focus();
            const length = input.value.value.length;
            input.value.setSelectionRange(length, length);
        }
    }, 50);
});

const realizarBusqueda = () => {
    buscando.value = true;
    const options = {
        preserveState: true,
        onFinish: () => {
            buscando.value = false;
            setTimeout(() => {
                if (input.value) {
                    input.value.focus();
                    const length = input.value.value.length;
                    input.value.setSelectionRange(length, length);
                }
            }, 50);
        }
    };
    if (buscar.value.length > 0) {
        router.get(
            route(ruta.value + ".search", {
                filtro: buscar.value,
                orderBy: orderByObject.value,
                ...params.value,
            }),
            options
        );
    } else {
        router.get(
            route(ruta.value + ".index", {
                orderBy: orderByObject.value,
                ...params.value,
            }),
            options
        );
    }
};

const filtrar = () => {
    buscando.value = true;
    if (filtros.value.length > 0) {
        router.get(
            route(ruta.value + ".filter", {
                filtros: filtros.value,
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

.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}
</style>

<template>
    <div class="relative w-full flex flex-col border-b border-dark-border bg-dark-surface/90 bg-glass-gradient z-30">
        <!-- Fila del Buscador -->
        <div class="relative w-full flex items-center">
            <!-- Icono de Búsqueda o Spinner -->
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                <font-awesome-icon
                    v-if="buscando"
                    icon="spinner"
                    class="text-slate-400 animate-spin"
                />
                <font-awesome-icon
                    v-else
                    icon="magnifying-glass"
                    class="text-slate-400"
                />
            </div>

            <!-- Input de Búsqueda -->
            <input
                ref="input"
                class="block w-full pl-10 bg-transparent border-0 text-slate-200 placeholder-slate-500 focus:ring-0 focus:outline-none text-sm py-3 z-0"
                :class="{
                    'pr-12': campos,
                    'pr-4': !campos,
                }"
                :placeholder="titulo"
                v-model="buscar"
                type="text"
                autofocus
                @keyup.enter="realizarBusqueda"
            />

            <!-- Botón de Engrane/Filtros -->
            <button
                v-if="campos"
                @click="abreFiltros"
                class="absolute inset-y-0 right-0 w-12 flex items-center justify-center hover:bg-dark-elevated/50 focus:outline-none transition-colors duration-200 z-10"
                :class="{
                    'text-slate-400': !muestraFiltros,
                    'text-brand-400 bg-dark-elevated/40': muestraFiltros,
                }"
                title="Filtros avanzados"
            >
                <font-awesome-icon
                    icon="filter"
                    :class="{
                        'animate-pulse text-brand-400': filtros.length > 0,
                    }"
                />
            </button>
        </div>

        <!-- Chips de Filtros Activos -->
        <div v-if="campos && filtros.length > 0" class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-t border-dark-border">
            <div
                v-for="(filtro, index) in filtros"
                :key="index"
                class="inline-flex items-center gap-1.5 bg-brand-500/10 border border-brand-500/20 rounded-full px-3 py-0.5 text-xs text-brand-400 select-none shadow-sm hover:border-brand-500/40 transition"
            >
                <!-- Selector de Conjunción (Y / O) -->
                <span 
                    v-if="index > 0" 
                    @click="toggleConjuncion(index)"
                    class="cursor-pointer font-bold px-1.5 py-0.5 bg-dark-surface/50 border border-dark-border rounded text-[9px] hover:bg-brand-500 hover:text-white transition uppercase mr-0.5"
                    :title="filtro.conjuncion === 'AND' ? 'Click para cambiar a O' : 'Click para cambiar a Y'"
                >
                    {{ filtro.conjuncion === 'AND' ? 'Y' : 'O' }}
                </span>
                <span class="opacity-75 font-medium">{{ filtro.nombre_campo }}</span>
                <span class="text-slate-500 font-mono text-[9px]">{{ filtro.condicion }}</span>
                <span class="font-bold text-slate-300">{{ getDisplayValue(filtro) }}</span>
                <button
                    @click="eliminarFiltro(index)"
                    class="ml-1 hover:text-red-400 transition"
                    title="Eliminar filtro"
                >
                    <font-awesome-icon icon="times" class="w-2.5 h-2.5" />
                </button>
            </div>
            <!-- Botón Limpiar Todo -->
            <button
                v-if="filtros.length > 1"
                @click="limpiarTodosFiltros"
                class="text-xs text-red-500 hover:text-red-400 font-semibold px-2 py-0.5 rounded hover:bg-red-500/10 transition ml-auto"
            >
                Limpiar todos
            </button>
        </div>

        <!-- Popover Flotante de Filtros Avanzados -->
        <transition name="slide-fade">
            <div
                v-if="muestraFiltros"
                class="absolute right-2 top-[46px] w-[500px] max-w-[calc(100vw-1rem)] bg-dark-elevated backdrop-blur-xl border border-dark-border shadow-2xl rounded-xl p-4 z-50 flex flex-col gap-4 animate-fade-in"
            >
                <!-- Capa fija invisible para cerrar al hacer click fuera -->
                <div class="fixed inset-0 z-[-1]" @click="muestraFiltros = false"></div>

                <div class="flex items-center justify-between border-b border-dark-border pb-2">
                    <h4 class="text-slate-200 font-bold text-sm flex items-center gap-1.5">
                        <font-awesome-icon icon="filter" class="text-brand-400" />
                        Filtros Avanzados
                    </h4>
                    <button @click="muestraFiltros = false" class="text-slate-400 hover:text-slate-200 transition">
                        <font-awesome-icon icon="times" />
                    </button>
                </div>

                <!-- Formulario para agregar filtros -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <!-- Campo -->
                    <div class="flex flex-col gap-1">
                        <JetLabel for="campo" value="Campo" class="text-left" />
                        <Select
                            id="campo"
                            v-model="campo"
                            :options="select_filtros"
                            placeholder="Seleccione..."
                            class="block w-full mt-1"
                        />
                    </div>

                    <!-- Condición -->
                    <div class="flex flex-col gap-1">
                        <JetLabel for="condicion" value="Condición" class="text-left" />
                        <Select
                            id="condicion"
                            v-model="condicion"
                            :options="select_condiciones"
                            placeholder="Seleccione..."
                            class="block w-full mt-1"
                        />
                    </div>

                    <!-- Valor -->
                    <div class="flex flex-col gap-1">
                        <JetLabel for="valor" value="Valor" class="text-left" />
                        
                        <!-- Dropdown de valores inteligentes o Input de Texto -->
                        <Select
                            v-if="isSelectField"
                            id="valor"
                            v-model="valor"
                            :options="getFieldOptions"
                            placeholder="Seleccione..."
                            class="block w-full mt-1"
                        />
                        <JetInput
                            v-else
                            id="valor"
                            type="text"
                            v-model="valor"
                            placeholder="Ingrese valor"
                            class="block w-full mt-1"
                            @keyup.enter="agregarFiltro"
                            :disabled="condicion === 'IS NULL' || condicion === 'IS NOT NULL'"
                        />
                    </div>
                </div>

                <!-- Listado de Filtros Activos dentro del Popover -->
                <div v-if="filtros.length > 0" class="flex flex-col gap-2 border-t border-dark-border pt-3">
                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider text-left flex items-center gap-1.5 select-none">
                        <font-awesome-icon icon="list-check" class="text-brand-400" />
                        Filtros Aplicados
                    </h5>
                    <div class="flex flex-col gap-2 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                        <div 
                            v-for="(filtro, index) in filtros" 
                            :key="index" 
                            class="flex items-center justify-between border rounded-lg p-2 gap-2 text-xs transition-all duration-200"
                            :class="editingIndex === index ? 'bg-brand-500/10 border-brand-500/50 shadow-sm shadow-brand-500/10' : 'bg-dark-elevated/40 border-dark-border hover:border-brand-500/30'"
                        >
                            <div class="flex items-center gap-1.5 flex-1 min-w-0 text-left">
                                <!-- Conjunción editable/toggleable -->
                                <button 
                                    v-if="index > 0"
                                    @click="toggleConjuncion(index)"
                                    class="font-bold px-2 py-0.5 bg-dark-surface hover:bg-brand-500 hover:text-white border border-dark-border rounded text-[10px] text-brand-400 hover:border-brand-400 transition-all duration-200 uppercase shrink-0"
                                    :title="filtro.conjuncion === 'AND' ? 'Cambiar a O' : 'Cambiar a Y'"
                                >
                                    {{ filtro.conjuncion === 'AND' ? 'Y' : 'O' }}
                                </button>
                                <span v-else class="text-slate-500 font-bold shrink-0 uppercase text-[10px]">Donde</span>
                                
                                <span class="text-slate-200 font-medium truncate">{{ filtro.nombre_campo }}</span>
                                <span class="text-slate-400 font-mono text-[10px] shrink-0">{{ filtro.condicion }}</span>
                                <span class="text-brand-400 font-semibold truncate">{{ getDisplayValue(filtro) }}</span>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button 
                                    @click="editaFiltro(filtro.campo, filtro.condicion, filtro.valor, filtro.conjuncion, index)"
                                    class="p-1.5 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded transition-colors duration-200"
                                    title="Editar filtro"
                                >
                                    <font-awesome-icon icon="edit" class="w-3.5 h-3.5" />
                                </button>
                                <button 
                                    @click="eliminarFiltro(index)"
                                    class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded transition-colors duration-200"
                                    title="Eliminar filtro"
                                >
                                    <font-awesome-icon icon="trash" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botonera -->
                <div class="flex justify-end gap-2 border-t border-dark-border pt-3">
                    <button
                        v-if="editingIndex === -1"
                        @click="agregarFiltro"
                        type="button"
                        class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-brand-500/10"
                    >
                        <font-awesome-icon icon="plus" class="mr-1.5" />
                        Agregar
                    </button>
                    <div v-else class="flex gap-2">
                        <button
                            @click="cancelarEdicion"
                            type="button"
                            class="inline-flex items-center justify-center px-4 py-2 bg-dark-elevated hover:bg-dark-surface text-slate-300 border border-dark-border rounded font-bold text-xs uppercase tracking-widest transition duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="guardarEdicion"
                            type="button"
                            class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-brand-500/10"
                        >
                            <font-awesome-icon icon="check" class="mr-1.5" />
                            Guardar
                        </button>
                    </div>
                    <button
                        v-if="editingIndex === -1"
                        @click="filtrar"
                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-blue-500/10"
                    >
                        <font-awesome-icon icon="filter" class="mr-1.5" />
                        Filtrar
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>
