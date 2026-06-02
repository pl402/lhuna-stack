<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import JetButton from '@/Jetstream/Button.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import Select from '@/Components/Select.vue';
import ButtonA from '@/Components/ButtonA.vue';
import Tags from '@/Components/Tags.vue';
import Autocomplete from '@/Components/Autocomplete.vue';
import Textarea from '@/Components/Textarea.vue';
import NumberInput from '@/Components/NumberInput.vue';
import Progress from '@/Components/Progress.vue';
import Toggle from '@/Components/Toggle.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import DatePicker from '@/Components/DatePicker.vue';
import Tabla from '@/Components/Tabla.vue';
import Tabs from '@/Components/Tabs.vue';
import DialogModal from '@/Jetstream/DialogModal.vue';
import { notify } from "notiwind";
import GlowCard from '@/Components/GlowCard.vue';
const colorThemes = [
  { value: 'slate', name: 'Pizarra', colorClass: 'bg-slate-500', bgClass: 'bg-slate-500', borderClass: 'border-slate-500', textClass: 'text-slate-500' },
  { value: 'gray', name: 'Gris', colorClass: 'bg-gray-500', bgClass: 'bg-gray-500', borderClass: 'border-gray-500', textClass: 'text-gray-500' },
  { value: 'zinc', name: 'Zinc', colorClass: 'bg-zinc-500', bgClass: 'bg-zinc-500', borderClass: 'border-zinc-500', textClass: 'text-zinc-500' },
  { value: 'neutral', name: 'Neutral', colorClass: 'bg-neutral-500', bgClass: 'bg-neutral-500', borderClass: 'border-neutral-500', textClass: 'text-neutral-500' },
  { value: 'stone', name: 'Piedra', colorClass: 'bg-stone-500', bgClass: 'bg-stone-500', borderClass: 'border-stone-500', textClass: 'text-stone-500' },
  { value: 'red', name: 'Rojo', colorClass: 'bg-red-500', bgClass: 'bg-red-500', borderClass: 'border-red-500', textClass: 'text-red-500' },
  { value: 'orange', name: 'Naranja', colorClass: 'bg-orange-500', bgClass: 'bg-orange-500', borderClass: 'border-orange-500', textClass: 'text-orange-500' },
  { value: 'amber', name: 'Ámbar', colorClass: 'bg-amber-500', bgClass: 'bg-amber-500', borderClass: 'border-amber-500', textClass: 'text-amber-500' },
  { value: 'yellow', name: 'Amarillo', colorClass: 'bg-yellow-500', bgClass: 'bg-yellow-500', borderClass: 'border-yellow-500', textClass: 'text-yellow-500' },
  { value: 'lime', name: 'Lima', colorClass: 'bg-lime-500', bgClass: 'bg-lime-500', borderClass: 'border-lime-500', textClass: 'text-lime-500' },
  { value: 'green', name: 'Verde', colorClass: 'bg-green-500', bgClass: 'bg-green-500', borderClass: 'border-green-500', textClass: 'text-green-500' },
  { value: 'emerald', name: 'Esmeralda', colorClass: 'bg-emerald-500', bgClass: 'bg-emerald-500', borderClass: 'border-emerald-500', textClass: 'text-emerald-500' },
  { value: 'teal', name: 'Verde Azulado', colorClass: 'bg-teal-500', bgClass: 'bg-teal-500', borderClass: 'border-teal-500', textClass: 'text-teal-500' },
  { value: 'cyan', name: 'Cian', colorClass: 'bg-cyan-500', bgClass: 'bg-cyan-500', borderClass: 'border-cyan-500', textClass: 'text-cyan-500' },
  { value: 'sky', name: 'Celeste', colorClass: 'bg-sky-500', bgClass: 'bg-sky-500', borderClass: 'border-sky-500', textClass: 'text-sky-500' },
  { value: 'blue', name: 'Azul', colorClass: 'bg-blue-500', bgClass: 'bg-blue-500', borderClass: 'border-blue-500', textClass: 'text-blue-500' },
  { value: 'indigo', name: 'Índigo', colorClass: 'bg-indigo-500', bgClass: 'bg-indigo-500', borderClass: 'border-indigo-500', textClass: 'text-indigo-500' },
  { value: 'violet', name: 'Violeta', colorClass: 'bg-violet-500', bgClass: 'bg-violet-500', borderClass: 'border-violet-500', textClass: 'text-violet-500' },
  { value: 'purple', name: 'Púrpura', colorClass: 'bg-purple-500', bgClass: 'bg-purple-500', borderClass: 'border-purple-500', textClass: 'text-purple-500' },
  { value: 'fuchsia', name: 'Fucsia', colorClass: 'bg-fuchsia-500', bgClass: 'bg-fuchsia-500', borderClass: 'border-fuchsia-500', textClass: 'text-fuchsia-500' },
  { value: 'pink', name: 'Rosa', colorClass: 'bg-pink-500', bgClass: 'bg-pink-500', borderClass: 'border-pink-500', textClass: 'text-pink-500' },
  { value: 'rose', name: 'Rosado', colorClass: 'bg-rose-500', bgClass: 'bg-rose-500', borderClass: 'border-rose-500', textClass: 'text-rose-500' },
  { value: 'black', name: 'Negro y Blanco', colorClass: 'bg-black dark:bg-white', bgClass: 'bg-black dark:bg-white', borderClass: 'border-black dark:border-white', textClass: 'text-black dark:text-white' }
];

const textValue = ref('');
const checkboxValue = ref(true);
const selectValue = ref('');
const tagsValue = ref([]);
const autocompleteValue = ref({ name: "", value: "" });
const textareaValue = ref('');
const numberValue = ref(100);
const toggleValue = ref(false);
const fileValue = ref([]);
const dateValue = ref('');
const activeTab = ref('basicos');
const formTabs = [
    { id: 'basicos', label: 'Básicos', icon: ['fas', 'list-check'] },
    { id: 'avanzados', label: 'Avanzados', icon: ['fas', 'gears'] },
    { id: 'media', label: 'Adicionales', icon: ['fas', 'plus-circle'] }
];
const showModal = ref(false);

const tableData = ref([
    { id: 1, name: 'Elias pl402', email: 'elias@lhuna.dev', role: 'Administrador', status: 'Activo', statusType: 'success' },
    { id: 2, name: 'María Gómez', email: 'maria@lhuna.dev', role: 'Editor', status: 'Pendiente', statusType: 'warning' },
    { id: 3, name: 'Carlos Ruíz', email: 'carlos@lhuna.dev', role: 'Espectador', status: 'Suspendido', statusType: 'danger' },
    { id: 4, name: 'Ana Beltrán', email: 'ana@lhuna.dev', role: 'Administrador', status: 'Activo', statusType: 'success' },
    { id: 5, name: 'Javier Ortega', email: 'javier@lhuna.dev', role: 'Usuario', status: 'Activo', statusType: 'success' },
    { id: 6, name: 'Sofía Méndez', email: 'sofia@lhuna.dev', role: 'Editor', status: 'Pendiente', statusType: 'warning' },
]);

const totalDemoUsers = ref(24);
const buscarDemo = ref('');
const searchQuery = ref('');
const buscandoDemo = ref(false);

// Advanced Filters Mock State
const filtrosDemo = ref([]);
const filtrosAplicadosDemo = ref([]);
const muestraFiltrosDemo = ref(false);
const campoDemo = ref("");
const condicionDemo = ref("");
const valorDemo = ref("");
const conjuncionDemo = ref("AND");
const editingIndexDemo = ref(-1);
const isProgrammaticChangeDemo = ref(false);

const camposDemo = {
  id: { label: "ID", type: "number", defaultCondition: "=" },
  name: { label: "Nombre", type: "text", defaultCondition: "LIKE" },
  email: { label: "Email", type: "text", defaultCondition: "LIKE" },
  role: { 
    label: "Rol", 
    type: "select", 
    defaultCondition: "=", 
    options: [
      { name: "Administrador", value: "Administrador" },
      { name: "Editor", value: "Editor" },
      { name: "Usuario", value: "Usuario" },
      { name: "Espectador", value: "Espectador" }
    ]
  },
  status: {
    label: "Estado",
    type: "select",
    defaultCondition: "=",
    options: [
      { name: "Activo", value: "Activo" },
      { name: "Pendiente", value: "Pendiente" },
      { name: "Suspendido", value: "Suspendido" }
    ]
  }
};

const selectCondicionesDemo = [
    { name: "Igual", value: "=" },
    { name: "Mayor", value: ">" },
    { name: "Menor", value: "<" },
    { name: "Mayor o igual", value: ">=" },
    { name: "Menor o igual", value: "<=" },
    { name: "Diferente", value: "!=" },
    { name: "Como", value: "LIKE" },
    { name: "No como", value: "NOT LIKE" },
    { name: "Es nulo", value: "IS NULL" },
    { name: "No es nulo", value: "IS NOT NULL" }
];

const isSelectFieldDemo = computed(() => {
    if (!campoDemo.value) return false;
    return camposDemo[campoDemo.value]?.type === 'select';
});

const getFieldOptionsDemo = computed(() => {
    if (!campoDemo.value) return [];
    return camposDemo[campoDemo.value]?.options || [];
});

watch(campoDemo, (newCampo) => {
    if (isProgrammaticChangeDemo.value) return;
    if (newCampo && camposDemo[newCampo]) {
        condicionDemo.value = camposDemo[newCampo].defaultCondition || "=";
    } else {
        condicionDemo.value = "=";
    }
    valorDemo.value = "";
}, { flush: 'sync' });

const getFieldLabelDemo = (key) => {
    return camposDemo[key]?.label || key;
};

const getDisplayValueDemo = (filtro) => {
    if (filtro.condicion === 'IS NULL') return 'Es Nulo';
    if (filtro.condicion === 'IS NOT NULL') return 'No es Nulo';
    if (camposDemo[filtro.campo]) {
        const fieldMeta = camposDemo[filtro.campo];
        if (fieldMeta.type === 'select' && fieldMeta.options) {
            const opt = fieldMeta.options.find(o => o.value == filtro.valor);
            if (opt) return opt.name;
        }
    }
    return filtro.valor;
};

const realizarBusquedaDemo = () => {
    buscandoDemo.value = true;
    setTimeout(() => {
        searchQuery.value = buscarDemo.value;
        buscandoDemo.value = false;
    }, 450);
};

const filtrarDemo = () => {
    buscandoDemo.value = true;
    muestraFiltrosDemo.value = false;
    setTimeout(() => {
        filtrosAplicadosDemo.value = JSON.parse(JSON.stringify(filtrosDemo.value));
        buscandoDemo.value = false;
    }, 450);
};

const agregarFiltroDemo = () => {
    if (campoDemo.value && condicionDemo.value && (valorDemo.value || condicionDemo.value === 'IS NULL' || condicionDemo.value === 'IS NOT NULL')) {
        let finalValor = valorDemo.value;
        if (condicionDemo.value === 'IS NULL' || condicionDemo.value === 'IS NOT NULL') {
            finalValor = "NULO";
        }
        filtrosDemo.value.push({
            campo: campoDemo.value,
            condicion: condicionDemo.value,
            valor: finalValor,
            conjuncion: conjuncionDemo.value,
            type: camposDemo[campoDemo.value]?.type || 'text'
        });
        campoDemo.value = "";
        condicionDemo.value = "";
        valorDemo.value = "";
        conjuncionDemo.value = "AND";
    } else {
        notify({
            group: "error",
            title: "Error",
            text: "Debe llenar todos los campos",
        }, 3000);
    }
};

const toggleConjuncionDemo = (index) => {
    filtrosDemo.value[index].conjuncion = filtrosDemo.value[index].conjuncion === 'AND' ? 'OR' : 'AND';
    filtrarDemo();
};

const eliminarFiltroDemo = (index) => {
    if (editingIndexDemo.value === index) {
        cancelarEdicionDemo();
    } else if (editingIndexDemo.value > index) {
        editingIndexDemo.value--;
    }
    filtrosDemo.value.splice(index, 1);
    filtrarDemo();
};

const limpiarTodosFiltrosDemo = () => {
    cancelarEdicionDemo();
    filtrosDemo.value = [];
    filtrarDemo();
};

const cancelarEdicionDemo = () => {
    editingIndexDemo.value = -1;
    campoDemo.value = "";
    condicionDemo.value = "";
    valorDemo.value = "";
    conjuncionDemo.value = "AND";
};

const guardarEdicionDemo = () => {
    if (campoDemo.value && condicionDemo.value && (valorDemo.value || condicionDemo.value === 'IS NULL' || condicionDemo.value === 'IS NOT NULL')) {
        let finalValor = valorDemo.value;
        if (condicionDemo.value === 'IS NULL' || condicionDemo.value === 'IS NOT NULL') {
            finalValor = "NULO";
        }
        filtrosDemo.value[editingIndexDemo.value] = {
            campo: campoDemo.value,
            condicion: condicionDemo.value,
            valor: finalValor,
            conjuncion: conjuncionDemo.value,
            type: camposDemo[campoDemo.value]?.type || 'text'
        };
        cancelarEdicionDemo();
        filtrarDemo();
    } else {
        notify({
            group: "error",
            title: "Error",
            text: "Debe llenar todos los campos",
        }, 3000);
    }
};

const editaFiltroDemo = (campo_1, condicion_1, valor_1, conjuncion_1, index) => {
    if (campoDemo.value && condicionDemo.value && (valorDemo.value || condicionDemo.value === 'IS NULL' || condicionDemo.value === 'IS NOT NULL') && editingIndexDemo.value !== index) {
        notify({
            group: "error",
            title: "Error",
            text: "Debe guardar el filtro actual antes de editar otro",
        }, 3000);
        return;
    }
    isProgrammaticChangeDemo.value = true;
    campoDemo.value = campo_1;
    condicionDemo.value = condicion_1;
    valorDemo.value = valor_1 === 'NULO' ? '' : valor_1;
    conjuncionDemo.value = conjuncion_1;
    editingIndexDemo.value = index;
    muestraFiltrosDemo.value = true;
    isProgrammaticChangeDemo.value = false;
};

const filteredTableData = computed(() => {
    let result = tableData.value;

    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(user => 
            user.name.toLowerCase().includes(query) || 
            user.email.toLowerCase().includes(query) || 
            user.role.toLowerCase().includes(query)
        );
    }
    
    if (filtrosAplicadosDemo.value.length > 0) {
        result = result.filter(user => {
            let match = true;
            for (let i = 0; i < filtrosAplicadosDemo.value.length; i++) {
                const f = filtrosAplicadosDemo.value[i];
                const valUser = user[f.campo];
                let fMatch = false;
                
                const valTarget = f.type === 'number' ? Number(f.valor) : f.valor;
                const valSource = f.type === 'number' ? Number(valUser) : valUser;
                
                if (f.condicion === '=') {
                    fMatch = String(valSource).toLowerCase() === String(valTarget).toLowerCase();
                } else if (f.condicion === 'LIKE') {
                    fMatch = String(valSource).toLowerCase().includes(String(valTarget).toLowerCase());
                } else if (f.condicion === 'NOT LIKE') {
                    fMatch = !String(valSource).toLowerCase().includes(String(valTarget).toLowerCase());
                } else if (f.condicion === '>') {
                    fMatch = valSource > valTarget;
                } else if (f.condicion === '<') {
                    fMatch = valSource < valTarget;
                } else if (f.condicion === '>=') {
                    fMatch = valSource >= valTarget;
                } else if (f.condicion === '<=') {
                    fMatch = valSource <= valTarget;
                } else if (f.condicion === '!=') {
                    fMatch = String(valSource).toLowerCase() !== String(valTarget).toLowerCase();
                } else if (f.condicion === 'IS NULL') {
                    fMatch = valUser === null || valUser === undefined;
                } else if (f.condicion === 'IS NOT NULL') {
                    fMatch = valUser !== null && valUser !== undefined;
                }
                
                if (i === 0) {
                    match = fMatch;
                } else {
                    if (f.conjuncion === 'AND') {
                        match = match && fMatch;
                    } else {
                        match = match || fMatch;
                    }
                }
            }
            return match;
        });
    }
    
    return result;
});

const openCreateUserModal = () => {
    const randomNames = ['Diego Flores', 'Valeria Rojas', 'Juan Castro', 'Gabriela Solis', 'Esteban Marín'];
    const randomName = randomNames[Math.floor(Math.random() * randomNames.length)] + ' (Demo)';
    const emailName = randomName.toLowerCase().replace(/\s+/g, '').replace('(demo)', '');
    const roles = ['Administrador', 'Editor', 'Usuario', 'Espectador'];
    const randomRole = roles[Math.floor(Math.random() * roles.length)];
    const statuses = ['Activo', 'Pendiente', 'Suspendido'];
    const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
    const statusType = randomStatus === 'Activo' ? 'success' : (randomStatus === 'Pendiente' ? 'warning' : 'danger');
    
    const nextId = tableData.value.length ? Math.max(...tableData.value.map(u => u.id)) + 1 : 1;
    tableData.value.push({
        id: nextId,
        name: randomName,
        email: `${emailName}@lhuna.dev`,
        role: randomRole,
        status: randomStatus,
        statusType
    });
    
    totalDemoUsers.value++;
    
    notify({
        group: 'main',
        title: 'Usuario Añadido (Demo)',
        text: `Se ha auto-creado a ${randomName} con rol ${randomRole}.`,
    }, 3500);
};

const openEditUserModal = (user) => {
    const roles = ['Administrador', 'Editor', 'Usuario', 'Espectador'];
    const currentRoleIndex = roles.indexOf(user.role);
    const nextRole = roles[(currentRoleIndex + 1) % roles.length];
    user.role = nextRole;
    
    notify({
        group: 'info',
        title: 'Rol Cambiado (Demo)',
        text: `Se editó a ${user.name}: nuevo rol ${nextRole}`,
    }, 3500);
};

const deleteUser = (userId) => {
    const user = tableData.value.find(u => u.id === userId);
    tableData.value = tableData.value.filter(u => u.id !== userId);
    totalDemoUsers.value--;
    notify({
        group: 'error',
        title: 'Usuario Eliminado',
        text: `${user ? user.name : 'El usuario'} ha sido removido de la tabla de demo.`,
    }, 3500);
};

const confirmModalDemo = () => {
    notify({
        group: 'main',
        title: 'Acción Completada',
        text: 'Has interactuado correctamente con el diálogo modal de demostración.',
    }, 3500);
    showModal.value = false;
};

const demoOptions = ref([
    { name: 'Vue.js', value: 'vue' },
    { name: 'React', value: 'react' },
    { name: 'Angular', value: 'angular' },
    { name: 'Svelte', value: 'svelte' },
    { name: 'Laravel', value: 'laravel' },
]);

const showNotification = (type) => {
    let title = "";
    let text = "";
    let group = "";

    switch(type) {
        case 'success':
            title = "¡Éxito!";
            text = "La operación se completó correctamente.";
            group = "main";
            break;
        case 'error':
            title = "¡Error!";
            text = "Ocurrió un problema al procesar la solicitud.";
            group = "error";
            break;
        case 'info':
            title = "Información";
            text = "Tienes nuevos mensajes sin leer en tu bandeja.";
            group = "info";
            break;
        case 'alert':
            title = "Advertencia";
            text = "Tu sesión está a punto de expirar en 5 minutos.";
            group = "alert";
            break;
    }

    notify({
        group: group,
        title: title,
        text: text
    }, 4000);
}

// Lógica de Scroll Infinito Simulado
const loadingMoreDemo = ref(false);
const hasMoreDemo = computed(() => tableData.value.length < totalDemoUsers.value);

const loadMoreDemo = () => {
    if (loadingMoreDemo.value || !hasMoreDemo.value) return;
    loadingMoreDemo.value = true;
    
    setTimeout(() => {
        const roles = ['Administrador', 'Editor', 'Usuario', 'Espectador'];
        const statuses = ['Activo', 'Pendiente', 'Suspendido'];
        const names = [
            'Andrés Mendoza', 'Lucía Peralta', 'Fernando Gómez', 'Camila Herrera', 
            'Ricardo Silva', 'Daniela Vargas', 'Mauricio Castro', 'Elena Rojas',
            'Gabriel Ortiz', 'Isabella Núñez', 'Hugo Romero', 'Natalia Méndez'
        ];
        
        // Generar 4 usuarios mock
        for (let i = 0; i < 4; i++) {
            if (tableData.value.length >= totalDemoUsers.value) break;
            
            const randomName = names[Math.floor(Math.random() * names.length)] + ' (Demo)';
            const emailName = randomName.toLowerCase().replace(/\s+/g, '').replace('(demo)', '');
            const randomRole = roles[Math.floor(Math.random() * roles.length)];
            const randomStatus = statuses[Math.floor(Math.random() * statuses.length)];
            const statusType = randomStatus === 'Activo' ? 'success' : (randomStatus === 'Pendiente' ? 'warning' : 'danger');
            const nextId = tableData.value.length ? Math.max(...tableData.value.map(u => u.id)) + 1 : 1;
            
            tableData.value.push({
                id: nextId,
                name: randomName,
                email: `${emailName}@lhuna.dev`,
                role: randomRole,
                status: randomStatus,
                statusType
            });
        }
        
        loadingMoreDemo.value = false;
    }, 1000);
};

const loadMoreDemoTrigger = ref(null);
let demoObserver = null;

const setupDemoObserver = () => {
    if (demoObserver) {
        demoObserver.disconnect();
    }
    
    demoObserver = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) {
                loadMoreDemo();
            }
        },
        {
            root: null,
            rootMargin: "100px",
        }
    );
    
    if (loadMoreDemoTrigger.value) {
        demoObserver.observe(loadMoreDemoTrigger.value);
    }
};

onMounted(() => {
    setupDemoObserver();
});

onUnmounted(() => {
    if (demoObserver) {
        demoObserver.disconnect();
    }
});
</script>

<template>
    <div class="w-full max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 animate-fade-in">
        
        <!-- Hero Section -->
        <GlowCard class="bg-gradient-to-br from-dark-surface to-dark-elevated p-10 mb-12" orb-size="w-96 h-96" orb-opacity="group-hover:opacity-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                <div class="md:col-span-2">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-slate-100 mb-4 tracking-tight">
                        Lhuna Stack<span class="align-super text-[10px] md:text-xs font-bold tracking-wider uppercase text-brand-700 dark:text-brand-400 bg-brand-500/10 border border-brand-500/20 px-1.5 py-0.5 rounded-full ml-1">v2</span>
                    </h1>
                    <p class="text-lg text-slate-400 mb-8 leading-relaxed">
                        Experimenta una paleta de componentes diseñados meticulosamente con obsesión por los detalles. Soporta personalización total de colores (23 temas curados), redondez de esquinas (5 estilos), nivel de sombras, modo claro/oscuro y orbes interactivos de seguimiento.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <ButtonA href="https://github.com/pl402/lhuna-stack/blob/main/README.md" target="_blank" class="justify-center">Explorar Documentación</ButtonA>
                        <a href="https://github.com/pl402/lhuna-stack" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-dark-surface border border-dark-border rounded-md font-semibold text-xs text-slate-300 uppercase tracking-widest shadow-sm shadow-black/20 hover:text-slate-400 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 active:text-slate-200 active:bg-dark-elevated/30 disabled:opacity-25 transition">Ver Repositorio</a>
                    </div>
                </div>
                <div class="flex justify-center md:col-span-1">
                    <div class="relative flex items-center justify-center w-48 h-48">
                        <!-- Concentric pulsing rings (Subtle 25% opacity borders) -->
                        <div class="absolute inset-0 rounded-full border border-dark-border/25 animate-ripple-1"></div>
                        <div class="absolute inset-5 rounded-full border border-dark-border/25 animate-ripple-2"></div>
                        <div class="absolute inset-10 rounded-full border border-dark-border/25 animate-ripple-3"></div>

                        <!-- Logo Image -->
                        <img :src="$page.props.logo" class="w-[120px] h-[120px] relative z-10 select-none pointer-events-none" />
                    </div>
                </div>
            </div>
        </GlowCard>

        <!-- Components Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Botones -->
            <GlowCard class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Botones de Acción</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex flex-col gap-3">
                        <JetButton class="justify-center w-full">Botón Principal</JetButton>
                        <JetSecondaryButton class="justify-center w-full">Botón Secundario</JetSecondaryButton>
                        <JetDangerButton class="justify-center w-full">Botón Destructivo</JetDangerButton>
                    </div>
                    <p class="text-xs text-slate-500 mt-4 text-center">Estados hover, active y focus nativos.</p>
                </div>
            </GlowCard>

            <!-- Formularios con Pestañas -->
            <GlowCard class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Formularios y Controles</h3>
                </div>

                <!-- Tabs Navigation -->
                <Tabs v-model="activeTab" :tabs="formTabs" />

                <!-- Tab Contents -->
                <div class="space-y-4 min-h-[300px]">
                    <!-- Tab 1: Básicos -->
                    <div v-show="activeTab === 'basicos'" class="space-y-4 animate-fade-in">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Entrada de Texto</label>
                            <JetInput type="text" class="w-full" placeholder="Ej. John Doe" v-model="textValue" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Selector Básico</label>
                            <Select v-model="selectValue" class="w-full">
                                <option value="" disabled>Seleccionar un rol...</option>
                                <option value="1">Administrador</option>
                                <option value="2">Editor</option>
                                <option value="3">Espectador</option>
                            </Select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Selector de Fecha</label>
                            <DatePicker v-model="dateValue" />
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-dark-border">
                            <label class="block text-sm font-medium text-slate-600 dark:text-slate-300">Activar Notificaciones</label>
                            <Toggle v-model="toggleValue" />
                        </div>
                    </div>

                    <!-- Tab 2: Avanzados -->
                    <div v-show="activeTab === 'avanzados'" class="space-y-4 animate-fade-in">
                        <div class="relative z-50">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Autocompletado (Buscable)</label>
                            <Autocomplete v-model="autocompleteValue" :options="demoOptions" placeholder="Busca una tecnología..." />
                        </div>
                        <div class="relative z-40">
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Etiquetas (Múltiple)</label>
                            <Tags v-model="tagsValue" :options="demoOptions" placeholder="Selecciona múltiples..." />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Entrada Numérica (Formateada)</label>
                            <NumberInput v-model="numberValue" placeholder="Monto" class="w-full" />
                        </div>
                    </div>

                    <!-- Tab 3: Adicionales -->
                    <div v-show="activeTab === 'media'" class="space-y-4 animate-fade-in">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Área de Texto</label>
                            <Textarea v-model="textareaValue" placeholder="Escribe tus comentarios aquí..." class="w-full" rows="3" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Barra de Progreso ({{ numberValue || 0 }}%)</label>
                            <Progress :value="Math.min(numberValue || 0, 100).toString()" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2">Subida de Archivos</label>
                            <FileDropzone v-model="fileValue" accept="image/*,application/pdf" />
                        </div>
                        <div class="pt-3 border-t border-dark-border">
                            <label class="flex items-center">
                                <JetCheckbox v-model="checkboxValue" />
                                <span class="ml-2 text-sm text-slate-600 dark:text-slate-300">Acepto los términos y condiciones</span>
                            </label>
                        </div>
                    </div>
                </div>
            </GlowCard>

            <!-- Tarjetas de Estadísticas -->
            <GlowCard class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Indicadores</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-dark-elevated border border-dark-border flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wider">Ingresos Mensuales</p>
                            <p class="text-2xl font-bold text-slate-100 mt-1">$45,231.00</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                        </div>
                    </div>

                    <div class="p-4 rounded-lg bg-brand-500 shadow-lg shadow-brand-500/30 text-white flex items-center justify-between relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/20 rounded-full blur-xl group-hover:bg-white/30 transition-colors"></div>
                        <div class="relative z-10">
                            <p class="text-xs font-medium uppercase tracking-wider text-white/80">Usuarios Activos</p>
                            <p class="text-2xl font-bold mt-1">1,204</p>
                        </div>
                        <div class="relative z-10">
                            <svg class="w-8 h-8 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                </div>
            </GlowCard>

            <!-- Alertas / Feedback -->
            <GlowCard class="p-6 md:col-span-1 lg:col-span-2">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Alertas y Badges</h3>
                </div>
                
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1 space-y-4">
                        <!-- Success Alert -->
                        <div class="flex p-4 rounded-lg bg-green-500/10 border border-green-500/20 text-green-700 dark:text-green-400">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h4 class="text-sm font-semibold mb-1">Operación Exitosa</h4>
                                <p class="text-xs opacity-90 leading-relaxed">Los cambios han sido guardados correctamente en la base de datos sin errores.</p>
                            </div>
                        </div>
                        
                        <!-- Warning Alert -->
                        <div class="flex p-4 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h4 class="text-sm font-semibold mb-1">Atención Requerida</h4>
                                <p class="text-xs opacity-90 leading-relaxed">Estás a punto de agotar tu cuota de almacenamiento mensual de tu plan actual.</p>
                            </div>
                        </div>

                        <!-- Notification Trigger Buttons -->
                        <div class="pt-4 mt-4 border-t border-dark-border">
                            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">Notificaciones Flotantes (Toasts)</p>
                            <div class="flex flex-wrap gap-2">
                                <button @click="showNotification('success')" class="px-3 py-1.5 rounded bg-green-500/10 dark:bg-green-500/20 text-green-700 dark:text-green-400 text-xs font-semibold hover:bg-green-500/20 dark:hover:bg-green-500/30 transition-colors">Success</button>
                                <button @click="showNotification('error')" class="px-3 py-1.5 rounded bg-red-500/10 dark:bg-red-500/20 text-red-700 dark:text-red-400 text-xs font-semibold hover:bg-red-500/20 dark:hover:bg-red-500/30 transition-colors">Error</button>
                                <button @click="showNotification('alert')" class="px-3 py-1.5 rounded bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 text-xs font-semibold hover:bg-amber-500/20 dark:hover:bg-amber-500/30 transition-colors">Warning</button>
                                <button @click="showNotification('info')" class="px-3 py-1.5 rounded bg-blue-500/10 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 text-xs font-semibold hover:bg-blue-500/20 dark:hover:bg-blue-500/30 transition-colors">Info</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-1 border-t md:border-t-0 md:border-l border-dark-border pt-6 md:pt-0 md:pl-6 flex flex-col justify-center gap-4">
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">Estados de Badges</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-brand-500/10 text-brand-700 dark:text-brand-400 border border-brand-500/20">Brand Default</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-green-500/10 text-green-700 dark:text-green-400 border border-green-500/20">Completado</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">En Progreso</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/20">Rechazado</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/20">Borrador</span>
                        </div>
                    </div>
                </div>
            </GlowCard>

            <!-- Animaciones Tailwind CSS -->
            <GlowCard class="p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Animaciones Nativas</h3>
                </div>

                <div class="space-y-4">
                    <!-- Spin Animation -->
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                        <div class="w-8 h-8 rounded-full border-4 border-slate-700 border-t-brand-500 animate-spin"></div>
                        <div>
                            <p class="text-xs font-semibold text-slate-200">Giro Infinito</p>
                            <code class="text-[10px] text-brand-400 font-mono">animate-spin</code>
                        </div>
                    </div>

                    <!-- Ping Animation -->
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                        <div class="relative flex h-3 w-3 ml-2.5 my-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-200">Señal de Radar</p>
                            <code class="text-[10px] text-brand-400 font-mono">animate-ping</code>
                        </div>
                    </div>

                    <!-- Pulse Animation -->
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                        <div class="flex-1 space-y-2 py-1 max-w-[80px] animate-pulse">
                            <div class="h-2 bg-slate-700 rounded"></div>
                            <div class="space-y-1">
                                <div class="h-2 bg-slate-700 rounded w-5/6"></div>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-200">Carga Gradual (Esqueleto)</p>
                            <code class="text-[10px] text-brand-400 font-mono">animate-pulse</code>
                        </div>
                    </div>

                    <!-- Bounce Animation -->
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-500 animate-bounce">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.07 6.07 0 00-1-3.59M9 17v1a3 3 0 006 0v-1m-6 0H9"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-200">Rebote de Atención</p>
                            <code class="text-[10px] text-brand-400 font-mono">animate-bounce</code>
                        </div>
                    </div>
                </div>
            </GlowCard>

        </div>

        <!-- Secciones Complejas: Tablas y Diálogos Modales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-12">
            
            <!-- Tabla Completa de Demostración -->
            <GlowCard class="lg:col-span-2">
                <div>
                    <!-- Buscador de Usuarios Mock -->
                    <div class="relative w-full flex flex-col border-b border-dark-border bg-dark-surface/90 bg-glass-gradient z-30">
                        <!-- Fila del Buscador -->
                        <div class="relative w-full flex items-center">
                            <!-- Icono de Búsqueda o Spinner -->
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <font-awesome-icon
                                    v-if="buscandoDemo"
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
                                class="block w-full pl-10 pr-12 bg-transparent border-0 text-slate-200 placeholder-slate-500 focus:ring-0 focus:outline-none text-sm py-3 z-0"
                                placeholder="Buscar usuarios..."
                                v-model="buscarDemo"
                                type="text"
                                @keyup.enter="realizarBusquedaDemo"
                            />

                            <!-- Botón de Filtros -->
                            <button
                                @click="muestraFiltrosDemo = !muestraFiltrosDemo"
                                class="absolute inset-y-0 right-0 w-12 flex items-center justify-center hover:bg-dark-elevated/50 focus:outline-none transition-colors duration-200 z-10"
                                :class="{
                                    'text-slate-400': !muestraFiltrosDemo,
                                    'text-brand-400 bg-dark-elevated/40': muestraFiltrosDemo,
                                }"
                                title="Filtros avanzados"
                            >
                                <font-awesome-icon
                                    icon="filter"
                                    :class="{
                                        'animate-pulse text-brand-400': filtrosDemo.length > 0,
                                    }"
                                />
                            </button>
                        </div>

                        <!-- Chips de Filtros Activos -->
                        <div v-if="filtrosDemo.length > 0" class="flex flex-wrap items-center gap-2 px-4 py-2.5 border-t border-dark-border select-none">
                            <div
                                v-for="(filtro, index) in filtrosDemo"
                                :key="index"
                                class="inline-flex items-center gap-1.5 bg-brand-500/10 border border-brand-500/20 rounded-full px-3 py-0.5 text-xs text-brand-400 select-none shadow-sm hover:border-brand-500/40 transition"
                            >
                                <!-- Selector de Conjunción (Y / O) -->
                                <span 
                                    v-if="index > 0" 
                                    @click="toggleConjuncionDemo(index)"
                                    class="cursor-pointer font-bold px-1.5 py-0.5 bg-dark-surface/50 border border-dark-border rounded text-[9px] hover:bg-brand-500 hover:text-white transition uppercase mr-0.5"
                                    :title="filtro.conjuncion === 'AND' ? 'Click para cambiar a O' : 'Click para cambiar a Y'"
                                >
                                    {{ filtro.conjuncion === 'AND' ? 'Y' : 'O' }}
                                </span>
                                <span class="opacity-75 font-medium">{{ getFieldLabelDemo(filtro.campo) }}</span>
                                <span class="text-slate-500 font-mono text-[9px]">{{ filtro.condicion }}</span>
                                <span class="font-bold text-slate-300">{{ getDisplayValueDemo(filtro) }}</span>
                                <button
                                    @click="eliminarFiltroDemo(index)"
                                    class="ml-1 hover:text-red-400 transition"
                                    title="Eliminar filtro"
                                >
                                    <font-awesome-icon icon="times" class="w-2.5 h-2.5" />
                                </button>
                            </div>
                            <!-- Botón Limpiar Todo -->
                            <button
                                v-if="filtrosDemo.length > 1"
                                @click="limpiarTodosFiltrosDemo"
                                class="text-xs text-red-500 hover:text-red-400 font-semibold px-2 py-0.5 rounded hover:bg-red-500/10 transition ml-auto"
                            >
                                Limpiar todos
                            </button>
                        </div>

                        <!-- Popover Flotante de Filtros Avanzados -->
                        <transition name="slide-up">
                            <div
                                v-if="muestraFiltrosDemo"
                                class="absolute right-2 top-[46px] w-[500px] max-w-[calc(100vw-1rem)] bg-dark-elevated backdrop-blur-xl border border-dark-border shadow-2xl rounded-xl p-4 z-50 flex flex-col gap-4 animate-fade-in"
                            >
                                <!-- Capa fija invisible para cerrar al hacer click fuera -->
                                <div class="fixed inset-0 z-[-1]" @click="muestraFiltrosDemo = false"></div>

                                <div class="flex items-center justify-between border-b border-dark-border pb-2">
                                    <h4 class="text-slate-200 font-bold text-sm flex items-center gap-1.5">
                                        <font-awesome-icon icon="filter" class="text-brand-400" />
                                        Filtros Avanzados
                                    </h4>
                                    <button @click="muestraFiltrosDemo = false" class="text-slate-400 hover:text-slate-200 transition">
                                        <font-awesome-icon icon="times" />
                                    </button>
                                </div>

                                <!-- Formulario para agregar filtros -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                    <!-- Campo -->
                                    <div class="flex flex-col gap-1">
                                        <JetLabel for="campoDemo" value="Campo" class="text-left" />
                                        <Select
                                            id="campoDemo"
                                            v-model="campoDemo"
                                            :options="Object.keys(camposDemo).map(key => ({ name: camposDemo[key].label, value: key }))"
                                            placeholder="Seleccione..."
                                            class="block w-full mt-1"
                                        />
                                    </div>

                                    <!-- Condición -->
                                    <div class="flex flex-col gap-1">
                                        <JetLabel for="condicionDemo" value="Condición" class="text-left" />
                                        <Select
                                            id="condicionDemo"
                                            v-model="condicionDemo"
                                            :options="selectCondicionesDemo"
                                            placeholder="Seleccione..."
                                            class="block w-full mt-1"
                                        />
                                    </div>

                                    <!-- Valor -->
                                    <div class="flex flex-col gap-1">
                                        <JetLabel for="valorDemo" value="Valor" class="text-left" />
                                        <Select
                                            v-if="isSelectFieldDemo"
                                            id="valorDemo"
                                            v-model="valorDemo"
                                            :options="getFieldOptionsDemo"
                                            placeholder="Seleccione..."
                                            class="block w-full mt-1"
                                        />
                                        <JetInput
                                            v-else
                                            id="valorDemo"
                                            type="text"
                                            v-model="valorDemo"
                                            placeholder="Ingrese valor"
                                            class="block w-full mt-1"
                                            @keyup.enter="editingIndexDemo === -1 ? agregarFiltroDemo() : guardarEdicionDemo()"
                                            :disabled="condicionDemo === 'IS NULL' || condicionDemo === 'IS NOT NULL'"
                                        />
                                    </div>
                                </div>

                                <!-- Listado de Filtros Activos dentro del Popover -->
                                <div v-if="filtrosDemo.length > 0" class="flex flex-col gap-2 border-t border-dark-border pt-3">
                                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider text-left flex items-center gap-1.5 select-none">
                                        <font-awesome-icon icon="list-check" class="text-brand-400" />
                                        Filtros Aplicados
                                    </h5>
                                    <div class="flex flex-col gap-2 max-h-40 overflow-y-auto custom-scrollbar pr-1">
                                        <div 
                                            v-for="(filtro, index) in filtrosDemo" 
                                            :key="index" 
                                            class="flex items-center justify-between border rounded-lg p-2 gap-2 text-xs transition-all duration-200"
                                            :class="editingIndexDemo === index ? 'bg-brand-500/10 border-brand-500/50 shadow-sm shadow-brand-500/10' : 'bg-dark-elevated/40 border-dark-border hover:border-brand-500/30'"
                                        >
                                            <div class="flex items-center gap-1.5 flex-1 min-w-0 text-left">
                                                <button 
                                                    v-if="index > 0"
                                                    @click="toggleConjuncionDemo(index)"
                                                    class="font-bold px-2 py-0.5 bg-dark-surface hover:bg-brand-500 hover:text-white border border-dark-border rounded text-[10px] text-brand-400 hover:border-brand-400 transition-all duration-200 uppercase shrink-0"
                                                    :title="filtro.conjuncion === 'AND' ? 'Cambiar a O' : 'Cambiar a Y'"
                                                >
                                                    {{ filtro.conjuncion === 'AND' ? 'Y' : 'O' }}
                                                </button>
                                                <span v-else class="text-slate-500 font-bold shrink-0 uppercase text-[10px]">Donde</span>
                                                
                                                <span class="text-slate-200 font-medium truncate">{{ getFieldLabelDemo(filtro.campo) }}</span>
                                                <span class="text-slate-400 font-mono text-[10px] shrink-0">{{ filtro.condicion }}</span>
                                                <span class="text-brand-400 font-semibold truncate">{{ getDisplayValueDemo(filtro) }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button 
                                                    @click="editaFiltroDemo(filtro.campo, filtro.condicion, filtro.valor, filtro.conjuncion, index)"
                                                    class="p-1.5 text-blue-400 hover:text-blue-300 hover:bg-blue-500/10 rounded transition-colors duration-200"
                                                    title="Editar filtro"
                                                >
                                                    <font-awesome-icon icon="edit" class="w-3.5 h-3.5" />
                                                </button>
                                                <button 
                                                    @click="eliminarFiltroDemo(index)"
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
                                        v-if="editingIndexDemo === -1"
                                        @click="agregarFiltroDemo"
                                        type="button"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-brand-500/10"
                                    >
                                        <font-awesome-icon icon="plus" class="mr-1.5" />
                                        Agregar
                                    </button>
                                    <div v-else class="flex gap-2">
                                        <button
                                            @click="cancelarEdicionDemo"
                                            type="button"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-dark-elevated hover:bg-dark-surface text-slate-300 border border-dark-border rounded font-bold text-xs uppercase tracking-widest transition duration-200"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            @click="guardarEdicionDemo"
                                            type="button"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-brand-500/10"
                                        >
                                            <font-awesome-icon icon="check" class="mr-1.5" />
                                            Guardar
                                        </button>
                                    </div>
                                    <button
                                        v-if="editingIndexDemo === -1"
                                        @click="filtrarDemo"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded font-bold text-xs uppercase tracking-widest transition shadow-md shadow-blue-500/10"
                                    >
                                        <font-awesome-icon icon="filter" class="mr-1.5" />
                                        Filtrar
                                    </button>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <div class="overflow-hidden">
                        <Tabla v-if="filteredTableData.length > 0">
                            <template #col>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">Rol</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                                <th class="px-4 py-3 w-5 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                                    <button 
                                        @click="openCreateUserModal" 
                                        class="inline-flex items-center justify-center px-3 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold transition duration-200 shadow-md shadow-brand-500/20 hover:shadow-lg hover:shadow-brand-500/30"
                                        title="Agregar Usuario"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </th>
                            </template>
                            <template #row>
                                <tr v-for="user in filteredTableData" :key="user.id" class="border-t border-dark-border hover:bg-dark-elevated/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-brand-500/20 flex items-center justify-center text-xs font-bold text-brand-500 uppercase">
                                                {{ user.name.slice(0, 2) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-200 text-left">{{ user.name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 text-left">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-left">{{ user.role }}</td>
                                    <td class="px-4 py-3 text-left">
                                        <span v-if="user.statusType === 'success'" class="px-2 py-0.5 rounded text-xs bg-green-500/10 text-green-700 dark:text-green-400 border border-green-500/20">
                                            {{ user.status }}
                                        </span>
                                        <span v-else-if="user.statusType === 'warning'" class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                            {{ user.status }}
                                        </span>
                                        <span v-else class="px-2 py-0.5 rounded text-xs bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/20">
                                            {{ user.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                                        <div class="inline-flex rounded shadow-sm">
                                            <button 
                                                @click="openEditUserModal(user)" 
                                                class="inline-flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded-l border-r border-blue-600/50 transition duration-200 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <button 
                                                @click="deleteUser(user.id)" 
                                                class="inline-flex items-center justify-center px-3 py-2 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white rounded-r transition duration-200 shadow-md shadow-red-500/20 hover:shadow-lg hover:shadow-red-500/30"
                                                title="Eliminar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Trigger para Scroll Infinito Demo -->
                                <tr ref="loadMoreDemoTrigger" class="opacity-0 h-1">
                                    <td colspan="4"></td>
                                </tr>
                            </template>
                            <template #pagination>
                              <div class="px-4 py-2.5 border-t border-dark-border text-xs font-semibold tracking-wide text-slate-500 uppercase bg-dark-surface/50 bg-glass-gradient backdrop-blur-md flex justify-between items-center select-none">
                                <span>Mostrando {{ filteredTableData.length }} de {{ totalDemoUsers }} usuarios</span>
                                <span v-if="loadingMoreDemo" class="flex items-center gap-1.5 text-brand-400">
                                  <font-awesome-icon icon="spinner" class="animate-spin" />
                                  Cargando más...
                                </span>
                                <span v-else-if="!hasMoreDemo && filteredTableData.length > 0" class="text-slate-600 dark:text-slate-500">
                                  Fin de la lista
                                </span>
                              </div>
                            </template>
                        </Tabla>
                        <div v-else class="text-center p-8 text-slate-400 text-sm">
                            No se encontraron usuarios
                        </div>
                    </div>
                </div>
            </GlowCard>

            <!-- Diálogos y Modales Showcase -->
            <GlowCard class="p-6">
                <div class="flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-200">Modales Interactivos</h3>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed mb-6">
                            Los diálogos de Lhuna Stack están diseñados para enfocar al usuario de manera limpia. Cuentan con un fondo traslúcido estilizado, transiciones fluidas de entrada/salida y total compatibilidad con dispositivos móviles.
                        </p>
                        
                        <div class="p-4 rounded-lg bg-brand-50 dark:bg-brand-950/40 border border-brand-200 dark:border-brand-900/60 text-slate-700 dark:text-slate-300 text-xs mb-4">
                            <p class="font-semibold text-brand-600 dark:text-brand-400 mb-1">Prueba Interactiva</p>
                            <p class="opacity-90">
                                Haz clic en el botón de abajo para abrir una ventana modal interactiva de ejemplo. Observa cómo mantiene el esquema de colores, el desenfoque de fondo y la alineación perfecta de los controles.
                            </p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-dark-border">
                        <button 
                            @click="showModal = true"
                            class="w-full inline-flex items-center justify-center px-4 py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold text-sm transition duration-300 shadow-lg shadow-brand-500/20 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-dark-base"
                        >
                            Abrir Modal de Ejemplo
                        </button>
                    </div>
                </div>
            </GlowCard>

        </div>

        <!-- Sistema de Diseño y Tokens de Colores -->
        <GlowCard class="p-8 mt-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Side: Tokens Showcase (col-span-2) -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-200">Sistema de Diseño y Tokens</h3>
                    </div>

                    <p class="text-sm text-slate-400 mb-8 leading-relaxed">
                        Lhuna Stack utiliza variables CSS nativas vinculadas en la base del layout. Esto permite cambiar paletas cromáticas al instante y mantener un control riguroso del contraste y la legibilidad en entornos tanto claros como oscuros.
                    </p>

                    <!-- Color Palette Showcase -->
                    <div class="space-y-8">
                        
                        <!-- Brand & Status -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Marca y Estados</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <!-- Brand Primary -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-brand-500 shadow-md shadow-brand-500/30 mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Brand Primary</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">var(--color-brand-500)</span>
                                </div>
                                <!-- Success Accent -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-green-500/20 border border-green-500/30 flex items-center justify-center text-green-500 font-bold mb-2">✓</div>
                                    <span class="text-xs font-semibold text-slate-200">Success Accent</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-green-500</span>
                                </div>
                                <!-- Danger Accent -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-red-500/20 border border-red-500/30 flex items-center justify-center text-red-500 font-bold mb-2">✗</div>
                                    <span class="text-xs font-semibold text-slate-200">Danger Accent</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-red-500</span>
                                </div>
                            </div>
                        </div>

                        <!-- Light Mode System -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Modo Claro (Fondos y Superficies)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <!-- Light Base -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#F8FAFC] border border-slate-300 mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Light Base</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#F8FAFC]</span>
                                </div>
                                <!-- Light Surface -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#FFFFFF] border border-slate-300 mb-2 shadow-sm"></div>
                                    <span class="text-xs font-semibold text-slate-200">Light Surface</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#FFFFFF]</span>
                                </div>
                                <!-- Light Elevated -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#F1F5F9] border border-slate-300 mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Light Elevated</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#F1F5F9]</span>
                                </div>
                            </div>
                        </div>

                        <!-- Dark Mode System -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Modo Oscuro (Fondos y Superficies)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <!-- Dark Base -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#020617] border border-dark-border mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Dark Base</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#020617]</span>
                                </div>
                                <!-- Dark Surface -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#0F172A] border border-dark-border mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Dark Surface</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#0F172A]</span>
                                </div>
                                <!-- Dark Elevated -->
                                <div class="flex flex-col items-center p-3 rounded-lg bg-dark-elevated/40 border border-dark-border">
                                    <div class="w-12 h-12 rounded-lg bg-[#1E293B] border border-dark-border mb-2"></div>
                                    <span class="text-xs font-semibold text-slate-200">Dark Elevated</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">bg-[#1E293B]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Color Themes List Mockup (col-span-1) -->
                <div class="lg:col-span-1 border-t lg:border-t-0 lg:border-l border-dark-border pt-6 lg:pt-0 lg:pl-8 flex flex-col">
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Paletas de Colores Curadas</h4>
                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Previsualización estática de los 23 temas de color integrados en el selector.
                    </p>

                    <div class="relative mt-2">
                        <!-- Top scroll fade indicator -->
                        <div class="absolute top-0 left-0 right-0 h-6 bg-gradient-to-b from-dark-surface to-transparent pointer-events-none z-10"></div>
                        <!-- Bottom scroll fade indicator -->
                        <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-dark-surface to-transparent pointer-events-none z-10"></div>

                        <!-- Scroll container of color preview cards -->
                        <div class="max-h-[640px] overflow-y-auto p-1 pt-4 pb-6 custom-scrollbar space-y-4">
                            <div 
                                v-for="theme in colorThemes" 
                                :key="theme.value"
                                class="relative rounded-xl border border-dark-border bg-dark-surface/60 hover:border-slate-500 transition-all duration-200 p-4"
                            >
                                <!-- Name and Color Dot -->
                                <div class="flex justify-between items-center mb-3">
                                  <span class="font-bold text-xs text-slate-300">
                                    {{ theme.name }}
                                  </span>
                                  <div class="w-3.5 h-3.5 rounded-full shadow-sm" :class="theme.colorClass"></div>
                                </div>
                                
                                <!-- Mockup Preview UI -->
                                <div class="rounded-lg bg-dark-base p-2.5 border border-dark-border shadow-inner flex flex-col gap-2.5 pointer-events-none">
                                  
                                  <!-- Mock Input (Focused) -->
                                  <div>
                                    <div class="h-1.5 w-8 bg-slate-500 rounded mb-1"></div>
                                    <div class="h-5 w-full rounded border bg-dark-surface flex items-center px-1.5 relative overflow-hidden" :class="theme.borderClass">
                                      <div class="absolute inset-0 opacity-10" :class="theme.bgClass"></div>
                                      <div class="w-px h-3.5 animate-pulse" :class="theme.bgClass"></div>
                                    </div>
                                  </div>

                                  <div class="flex items-center justify-between">
                                    <!-- Mock Checkbox (Checked) -->
                                    <div class="flex items-center gap-1.5">
                                      <div class="w-3.5 h-3.5 rounded flex items-center justify-center" :class="[theme.bgClass, theme.value === 'black' ? 'text-white dark:text-slate-900' : 'text-white']">
                                         <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                                      </div>
                                      <div class="h-1 w-6 bg-slate-500 rounded"></div>
                                    </div>

                                    <!-- Mock Button -->
                                    <div class="px-1.5 py-0.5 rounded text-[8px] font-bold shadow-sm" :class="[theme.bgClass, theme.value === 'black' ? 'text-white dark:text-slate-900' : 'text-white']">
                                      Guardar
                                    </div>
                                  </div>
                                  
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GlowCard>

        <!-- Jetstream Dialog Modal Instance -->
        <DialogModal :show="showModal" @close="showModal = false" max-width="md">
            <template #title>
                Ventana Modal Interactiva
            </template>
            <template #content>
                <div class="space-y-4 text-left">
                    <p class="text-sm text-slate-300 leading-relaxed">
                        ¡Hola! Esto es una <strong>ventana modal interactiva</strong> de demostración.
                    </p>
                    
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Los diálogos modales son excelentes para captar la atención de forma limpia y enfocada, bloqueando la interacción con el resto de la interfaz hasta que se toma una decisión.
                    </p>
                    
                    <div class="p-4 rounded-lg bg-brand-500/10 border border-brand-500/20 text-slate-600 dark:text-slate-300 text-xs">
                        <h4 class="text-xs font-semibold text-brand-700 dark:text-brand-400 mb-1.5">Características Destacadas</h4>
                        <ul class="space-y-1.5 opacity-90 leading-relaxed list-none pl-0">
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 dark:text-brand-400 font-bold">✓</span>
                                <span>Efecto de cristal traslúcido de fondo (backdrop-blur)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 dark:text-brand-400 font-bold">✓</span>
                                <span>Alineación semántica perfecta de controles</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 dark:text-brand-400 font-bold">✓</span>
                                <span>Transiciones animadas suaves al abrir y cerrar</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-brand-600 dark:text-brand-400 font-bold">✓</span>
                                <span>Totalmente adaptable a móviles y tablets</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </template>
            <template #footer>
                <div class="w-1/2 flex justify-start">
                    <JetSecondaryButton @click="showModal = false">
                        Cerrar
                    </JetSecondaryButton>
                </div>
                <div class="w-1/2 flex justify-end">
                    <button 
                        @click="confirmModalDemo"
                        class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-brand-700 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 transition shadow-lg shadow-brand-500/20"
                    >
                        Entendido
                    </button>
                </div>
            </template>
        </DialogModal>

    </div>
</template>

<style scoped>
@keyframes ripple {
  0% {
    transform: scale(0.85);
    opacity: 0;
  }
  50% {
    opacity: 0.25;
  }
  100% {
    transform: scale(1.25);
    opacity: 0;
  }
}

.animate-ripple-1 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
}

.animate-ripple-2 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
  animation-delay: 1.3s;
}

.animate-ripple-3 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
  animation-delay: 2.6s;
}

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
