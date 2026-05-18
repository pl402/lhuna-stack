<script setup>
import { ref } from 'vue';
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
import { notify } from "notiwind";

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
        <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-dark-surface to-dark-elevated border border-dark-border p-10 mb-12 shadow-2xl shadow-black/50 group">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-brand-500 rounded-full blur-[100px] opacity-20 group-hover:opacity-40 transition-opacity duration-700"></div>
            
            <div class="relative z-10">
                <span class="inline-block py-1 px-3 rounded-full bg-brand-500/10 border border-brand-500/30 text-brand-500 text-xs font-bold tracking-wider uppercase mb-4">
                    Lhuna Stack V2
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-100 mb-4 tracking-tight">
                    El Motor UI Definitivo
                </h1>
                <p class="text-lg text-slate-400 max-w-2xl mb-8 leading-relaxed">
                    Experimenta una paleta de componentes diseñados meticulosamente con obsesión por los detalles. Tipografía nítida, transiciones fluidas y un sistema de temas dinámico totalmente integrado.
                </p>
                <div class="flex flex-wrap gap-4">
                    <ButtonA href="https://github.com/pl402/lhuna-stack/blob/main/README.md" target="_blank" class="justify-center">Explorar Documentación</ButtonA>
                    <a href="https://github.com/pl402/lhuna-stack" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-dark-surface border border-dark-border rounded-md font-semibold text-xs text-slate-300 uppercase tracking-widest shadow-sm shadow-black/20 hover:text-slate-400 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 active:text-slate-200 active:bg-dark-elevated/30 disabled:opacity-25 transition">Ver Repositorio</a>
                </div>
            </div>
        </div>

        <!-- Components Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Botones -->
            <div class="bg-dark-surface border border-dark-border rounded-xl p-6 shadow-lg shadow-black/20 hover:border-brand-500/50 transition-colors duration-300">
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
            </div>

            <!-- Formularios con Pestañas -->
            <div class="bg-dark-surface border border-dark-border rounded-xl p-6 shadow-lg shadow-black/20 hover:border-brand-500/50 transition-colors duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Formularios y Controles</h3>
                </div>

                <!-- Tabs Navigation -->
                <div class="flex border-b border-slate-700/50 dark:border-dark-border mb-5">
                    <button 
                        @click="activeTab = 'basicos'"
                        :class="activeTab === 'basicos' ? 'border-brand-500 text-brand-500 font-semibold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="flex-1 pb-2.5 text-center text-xs font-medium border-b-2 transition duration-200 focus:outline-none"
                    >
                        Básicos
                    </button>
                    <button 
                        @click="activeTab = 'avanzados'"
                        :class="activeTab === 'avanzados' ? 'border-brand-500 text-brand-500 font-semibold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="flex-1 pb-2.5 text-center text-xs font-medium border-b-2 transition duration-200 focus:outline-none"
                    >
                        Avanzados
                    </button>
                    <button 
                        @click="activeTab = 'media'"
                        :class="activeTab === 'media' ? 'border-brand-500 text-brand-500 font-semibold' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                        class="flex-1 pb-2.5 text-center text-xs font-medium border-b-2 transition duration-200 focus:outline-none"
                    >
                        Adicionales
                    </button>
                </div>

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
                        <div class="flex items-center justify-between pt-2 border-t border-slate-700/50 dark:border-dark-border">
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
                        <div class="pt-3 border-t border-slate-700/50 dark:border-dark-border">
                            <label class="flex items-center">
                                <JetCheckbox v-model="checkboxValue" />
                                <span class="ml-2 text-sm text-slate-600 dark:text-slate-300">Acepto los términos y condiciones</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de Estadísticas -->
            <div class="bg-dark-surface border border-dark-border rounded-xl p-6 shadow-lg shadow-black/20 hover:border-brand-500/50 transition-colors duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center text-brand-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-200">Indicadores</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-dark-elevated border border-slate-700/50 flex items-center justify-between">
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
            </div>

            <!-- Alertas / Feedback -->
            <div class="bg-dark-surface border border-dark-border rounded-xl p-6 shadow-lg shadow-black/20 hover:border-brand-500/50 transition-colors duration-300 md:col-span-2 lg:col-span-3">
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
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-brand-500/10 text-brand-700 dark:text-brand-500 border border-brand-500/20">Brand Default</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-green-500/10 text-green-700 dark:text-green-400 border border-green-500/20">Completado</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">En Progreso</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/20">Rechazado</span>
                            <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-500/10 text-slate-700 dark:text-slate-400 border border-slate-500/20">Borrador</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
