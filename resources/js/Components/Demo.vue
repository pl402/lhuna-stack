<script setup>
import { ref, computed } from 'vue';
import JetButton from '@/Jetstream/Button.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import JetInput from '@/Jetstream/Input.vue';
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

const searchQuery = ref('');
const filteredTableData = computed(() => {
    if (!searchQuery.value) return tableData.value;
    const query = searchQuery.value.toLowerCase().trim();
    return tableData.value.filter(user => 
        user.name.toLowerCase().includes(query) || 
        user.email.toLowerCase().includes(query) || 
        user.role.toLowerCase().includes(query)
    );
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
</script>

<template>
    <div class="w-full max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 animate-fade-in">
        
        <!-- Hero Section -->
        <GlowCard class="bg-gradient-to-br from-dark-surface to-dark-elevated p-10 mb-12" orb-size="w-96 h-96" orb-opacity="group-hover:opacity-20">
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-100 mb-4 tracking-tight">
                Lhuna Stack<span class="align-super text-[10px] md:text-xs font-bold tracking-wider uppercase text-brand-700 dark:text-brand-400 bg-brand-500/10 border border-brand-500/20 px-1.5 py-0.5 rounded-full ml-1">v2</span>
            </h1>
            <p class="text-lg text-slate-400 max-w-2xl mb-8 leading-relaxed">
                Experimenta una paleta de componentes diseñados meticulosamente con obsesión por los detalles. Soporta personalización total de colores (23 temas curados), redondez de esquinas (5 estilos), nivel de sombras, modo claro/oscuro y orbes interactivos de seguimiento.
            </p>
            <div class="flex flex-wrap gap-4">
                <ButtonA href="https://github.com/pl402/lhuna-stack/blob/main/README.md" target="_blank" class="justify-center">Explorar Documentación</ButtonA>
                <a href="https://github.com/pl402/lhuna-stack" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-dark-surface border border-dark-border rounded-md font-semibold text-xs text-slate-300 uppercase tracking-widest shadow-sm shadow-black/20 hover:text-slate-400 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 active:text-slate-200 active:bg-dark-elevated/30 disabled:opacity-25 transition">Ver Repositorio</a>
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
                    <div class="flex flex-wrap border-b border-dark-border bg-dark-surface/50 bg-glass-gradient backdrop-blur-md relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            type="text" 
                            placeholder="Buscar usuarios..." 
                            v-model="searchQuery" 
                            class="block w-full pl-10 pr-4 py-3 bg-transparent border-0 text-slate-200 placeholder-slate-500 focus:ring-0 focus:outline-none text-sm rounded-t-xl"
                        />
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
            
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-200">Sistema de Diseño y Tokens</h3>
            </div>

            <p class="text-sm text-slate-400 max-w-3xl mb-8 leading-relaxed">
                Lhuna Stack utiliza variables CSS nativas vinculadas en la base del layout. Esto permite cambiar paletas cromáticas al instante y mantener un control riguroso del contraste y la legibilidad en entornos tanto claros como oscuros.
            </p>

            <!-- Color Palette Showcase -->
            <div class="space-y-8">
                
                <!-- Brand & Status -->
                <div>
                    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Marca y Estados</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
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
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
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
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
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
