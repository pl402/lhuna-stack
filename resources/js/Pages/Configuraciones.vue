<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage, Link, useForm } from "@inertiajs/vue3";
import { reactive, ref, watch, toRefs } from "vue";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import Tabla from "@/Components/Tabla.vue";
import { router } from "@inertiajs/vue3";
import Paginador from "@/Components/Paginador.vue";
import { notify } from "notiwind";
import JetInputError from "@/Jetstream/InputError.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import Select from "@/Components/Select.vue";
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";
import Progress from "@/Components/Progress.vue";
import GlowCard from "@/Components/GlowCard.vue";


const props = defineProps({
  configuraciones: Object,
  configuracion: Object,
  filtro: String,
  orderBy: Object,
});

const form = useForm({
  clave: "",
  valor: null,
  tipo: "",
});

const error = reactive({
  clave: "",
  valor: "",
  tipo: "",
});

const configuraciones = toRefs(props).configuraciones;
const buscar = ref(props.filtro ? props.filtro : "");
const estadoModalConfiguracion = ref(false);
const estadoModalEliminaConfiguracion = ref(false);

let photoPreview = ref(null);
let photoInput = ref(null);

const accion = ref("new");
const idActual = ref(null);
const currentConfiguracion = ref(null);
const orderByObject = ref(
  props.orderBy
    ? props.orderBy
    : {
       field: "name",
       sort: "asc",
     }
);

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

const radiusOptions = [
  { value: 'none', name: 'Ninguno (Cuadrado)', description: 'Esquinas totalmente rectas a 90 grados.', radiusClass: 'rounded-none', style: 'border-radius: 0px;' },
  { value: 'sm', name: 'Sutil', description: 'Redondez mínima para un estilo más limpio y rígido.', radiusClass: 'rounded-sm', style: 'border-radius: 2px;' },
  { value: 'md', name: 'Estándar', description: 'El estilo estándar balanceado de la interfaz.', radiusClass: 'rounded-md', style: 'border-radius: 6px;' },
  { value: 'lg', name: 'Muy Redondeado', description: 'Redondeado elegante y moderno.', radiusClass: 'rounded-xl', style: 'border-radius: 12px;' },
  { value: 'xl', name: 'Extremo / Píldora', description: 'Curvatura muy de tendencia y amigable.', radiusClass: 'rounded-2xl', style: 'border-radius: 24px;' }
];

const shadowOptions = [
  { value: 'none', name: 'Ninguno (Plano)', description: 'Sin sombras en los elementos, estilo totalmente plano.', shadowClass: 'shadow-none', style: 'box-shadow: none;' },
  { value: 'sm', name: 'Sutil', description: 'Sombras extremadamente discretas y minimalistas.', shadowClass: 'shadow-sm', style: 'box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);' },
  { value: 'md', name: 'Medio', description: 'Sombras estándar con elevación media.', shadowClass: 'shadow-md', style: 'box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);' },
  { value: 'lg', name: 'Elevado / Grande', description: 'Sombras marcadas con profundidad elegante.', shadowClass: 'shadow-lg', style: 'box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);' },
  { value: 'xl', name: 'Flotante / Extra Grande', description: 'Profundidad máxima para elementos flotantes.', shadowClass: 'shadow-xl', style: 'box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);' }
];

const nuevoConfiguracion = () => {
  accion.value = "new";
  form.reset("clave", "valor", "tipo");
  error.clave = "";
  error.valor = "";
  error.tipo = "";
  estadoModalConfiguracion.value = true;
};

const nuevoConfiguracionDo = () => {
  error.clave = "";
  error.valor = "";
  error.tipo = "";
  form.post(route("configuraciones.store"), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Configuración creada",
          text: "La configuración se ha creado exitosamente",
        },
        3000
      );
      estadoModalConfiguracion.value = false;
      form.reset("clave", "valor");
    },
    onError: (errors) => {
      if (!errors.clave && !errors.valor && !errors.tipo) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar la configuración",
          },
          3000
        );
      }
      error.clave = errors.clave;
      error.valor = errors.valor;
      error.tipo = errors.tipo;
    },
  });
};

const editaConfiguracion = (configuracion) => {
  accion.value = "edit";
  currentConfiguracion.value = configuracion;
  idActual.value = configuracion.id;
  form.clave = configuracion.clave;
  form.valor = configuracion.valor;
  form.tipo = configuracion.tipo;
  if (form.tipo === "Imagen") {
    photoPreview.value = form.valor;
  }

  error.clave = "";
  error.valor = "";
  error.tipo = "";
  estadoModalConfiguracion.value = true;
};

const editaConfiguracionDo = () => {
  if (form.tipo === "Imagen") {
    if (photoInput.value) {
      form.valor = photoInput.value.files[0];
    } else {
      form.valor = null;
    }
  }

  form.post(route("configuraciones.update", idActual.value), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Configuración editada",
          text: "La configuración se ha editado exitosamente",
        },
        3000
      );
      estadoModalConfiguracion.value = false;
      form.reset("clave", "valor", "tipo");
      clearPhotoFileInput();
    },
    onError: (errors) => {
      if (!errors.clave && !errors.valor && !errors.tipo) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al editar la configuración",
          },
          3000
        );
      }
      error.clave = errors.clave;
      error.valor = errors.valor;
      error.tipo = errors.tipo;
    },
  });
};

const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();

  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };

  reader.readAsDataURL(photo);
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};


</script>

<template>
  <AppLayout title="Configuraciones">
    <div class="max-w-full mx-auto px-0 sm:px-8 py-4 pt-0 sm:pt-4">
      <GlowCard rounded="sm:rounded-lg">
        <Buscador
          v-model="buscar"
          :orderByObject="orderByObject"
          ruta="configuraciones"
          autofocus
        />
        <Tabla v-if="configuraciones.data.length > 0">
          <template #col>
            <th class="px-4 py-3 w-32">
              <Ordena
                v-model="orderByObject"
                ruta="configuraciones"
                :buscar="buscar"
                titulo="ID"
                campo="id"
              />
            </th>
            <th class="px-4 py-3">
              <Ordena
                v-model="orderByObject"
                ruta="configuraciones"
                :buscar="buscar"
                titulo="Nombre"
                campo="clave"
              />
            </th>
            <th class="px-4 py-3">
              <Ordena
                v-model="orderByObject"
                ruta="configuraciones"
                :buscar="buscar"
                titulo="Descripción"
                campo="valor"
              />
            </th>
            <th
              class="px-4 py-3 w-5 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border"
            >
              Acciones
            </th>
          </template>
          <template #row>
            <tr
              class="text-slate-300 hover:bg-dark-elevated border-b border-dark-border"
              v-for="configuracion in configuraciones.data"
              :key="configuracion.id"
            >
              <td class="px-4 py-3 text-sm text-slate-300">
                {{ configuracion.id }}
              </td>
              <td class="px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 text-left">
                {{ configuracion.clave }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 text-left">
                <div v-if="configuracion.tipo === 'Tema'" class="flex items-center gap-2">
                  <div class="w-3.5 h-3.5 rounded-full shadow-sm" :class="colorThemes.find(t => t.value === configuracion.valor)?.colorClass || 'bg-brand-500'"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">{{ colorThemes.find(t => t.value === configuracion.valor)?.name || configuracion.valor }}</span>
                </div>
                <div v-else-if="configuracion.tipo === 'Redondez'" class="flex items-center gap-2">
                  <div class="w-4 h-4 bg-brand-500 shadow-sm border border-slate-500/30" :class="radiusOptions.find(r => r.value === configuracion.valor)?.radiusClass || 'rounded-md'"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">{{ radiusOptions.find(r => r.value === configuracion.valor)?.name || configuracion.valor }}</span>
                </div>
                <div v-else-if="configuracion.tipo === 'Sombra'" class="flex items-center gap-2">
                  <div class="w-4 h-4 bg-brand-500 border border-slate-500/30" :class="[radiusOptions.find(r => r.value === $page.props.themeRadius)?.radiusClass || 'rounded-md', shadowOptions.find(s => s.value === configuracion.valor)?.shadowClass || 'shadow-lg']"></div>
                  <span class="text-sm text-slate-600 dark:text-slate-300">{{ shadowOptions.find(s => s.value === configuracion.valor)?.name || configuracion.valor }}</span>
                </div>
                <div v-else-if="configuracion.tipo === 'Interruptor'" class="flex items-center gap-2">
                  <span 
                    class="px-2 py-0.5 rounded text-xs font-semibold border"
                    :class="configuracion.valor === 'si'
                      ? 'bg-green-500/10 text-green-700 dark:text-green-400 border-green-500/20'
                      : 'bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-500/20'"
                  >
                    {{ configuracion.valor === 'si' ? 'Activado' : 'Desactivado' }}
                  </span>
                </div>
                <div v-else-if="configuracion.tipo === 'Imagen'" class="flex items-center gap-2">
                  <img v-if="configuracion.valor" :src="configuracion.valor" class="w-8 h-8 rounded border border-slate-500/60" />
                  <span class="text-xs text-slate-500 dark:text-slate-400">Ver imagen</span>
                </div>
                <span v-else>{{ configuracion.valor }}</span>
              </td>
              <td
                class="px-4 py-3 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border"
              >
                <button
                  @click="editaConfiguracion(configuracion)"
                  class="inline-flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded transition duration-200 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30"
                  title="Editar"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
              </td>
            </tr>
          </template>
          <template #pagination>
            <Paginador
              :links="configuraciones.links"
              :total="configuraciones.total"
              :to="configuraciones.to"
              :from="configuraciones.from"
            />
          </template>
        </Tabla>
        <div v-else class="text-center p-5 text-slate-400 text-md">
          Sin resultados
          <br />
        </div>
      </GlowCard>
    </div>

    <form
      class="flex flex-col"
      @submit.prevent="
        accion === 'new' ? nuevoConfiguracionDo : editaConfiguracionDo
      "
    >
      <JetDialogModal
        :show="estadoModalConfiguracion"
        @close="estadoModalConfiguracion = false"
        max-width="md"
        id="elmodal"
      >
        <template #title
          >{{
            accion === "new"
              ? "Crear nuevo configuracion"
              : "Editar configuracion"
          }}
        </template>
        <template #content>
          <label class="block mb-1">
            <JetLabel
              for="clave"
              value="Nombre"
              class="float-left"
            />
            <JetInput
              id="clave"
              v-model="form.clave"
              placeholder="Nombre configuración"
              type="text"
              class="mt-1 block w-full"
              required
              disabled
            />
            <JetInputError :message="error.clave" class="mt-2" />
          </label>
          <label v-if="form.tipo === 'Texto'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Valor"
              class="float-left"
            />
            <JetInput
              id="valor"
              v-model="form.valor"
              placeholder="Valor de la configuración"
              type="text"
              class="mt-1 block w-full"
              required
              autofocus
              :disabled="form.processing"
            />
            <JetInputError :message="error.valor" class="mt-2" />
          </label>
          <label v-if="form.tipo === 'Imagen'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Valor"
              class="float-left"
            />
            <input
              ref="photoInput"
              type="file"
              @change="updatePhotoPreview"
              accept=".png, .PNG, .jpg, .JPG, .jpeg. .JPEG"
              class="w-full border-gray-400 focus:border-dark-border focus:ring focus:ring-slate-400 focus:ring-opacity-50 rounded-md shadow-sm shadow-black/20 disabled:text-slate-400 file:border-solid file:inline-flex file:items-center file:px-4 file:py-2 file:bg-brand-500 file:border file:border-transparent file:rounded-md file:font-semibold file:text-xs file:text-white file:uppercase file:tracking-widest hover:file:bg-gray-700 active:file:bg-gray-900 focus:file:outline-none focus:file:border-brand-500 focus:file:ring focus:file:ring-brand-500 focus:file:ring-opacity-50 file:disabled:opacity-25 file:shadow-sm shadow-black/20 hover:file:shadow-md shadow-black/30 focus:file:shadow-lg shadow-black/40 active:file:ring-2 active:file:ring-brand-500 file:transition"
              :disabled="form.processing"
            />
            <JetInputError :message="error.valor" class="mt-2" />
            <div
              class="w-full text-slate-400 text-center text-sm my-2"
            >
              Se sugiere una medida 500 x 500 píxeles.
            </div>
            <Progress
              v-if="form.progress"
              :value="form.progress.percentage"
            />
            <div v-show="photoPreview" class="mt-2">
              <span
                class="block rounded-md mx-auto w-40 h-40 bg-cover bg-no-repeat bg-center border-[1px] border-gray-500"
                :style="
                  'background-image: url(\'' +
                  photoPreview +
                  '\');'
                "
              />
            </div>
          </label>
          <label v-if="form.tipo === 'Fecha'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Valor"
              class="float-left"
            />
            <JetInput
              id="valor"
              v-model="form.valor"
              placeholder="Valor de la configuración"
              type="date"
              class="mt-1 block w-full"
              required
              autofocus
              :disabled="form.processing"
            />
            <JetInputError :message="error.valor" class="mt-2" />
          </label>
          <div v-if="form.tipo === 'Tema'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Color Principal del Tema"
              class="float-left w-full mb-2"
            />
            <div class="relative mt-2 clear-both">
              <!-- Top scroll fade indicator -->
              <div class="absolute top-0 left-0 right-0 h-6 bg-gradient-to-b from-dark-surface to-transparent pointer-events-none z-10"></div>
              
              <!-- Bottom scroll fade indicator -->
              <div class="absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-dark-surface to-transparent pointer-events-none z-10"></div>

              <div class="grid grid-cols-2 gap-4 max-h-96 overflow-y-auto p-1 pt-4 pb-4 custom-scrollbar">
              <div
                v-for="theme in colorThemes"
                :key="theme.value"
                @click="form.valor = theme.value"
                class="relative cursor-pointer rounded-xl border-2 p-4 transition-all duration-200"
                :class="form.valor === theme.value 
                  ? 'border-brand-500 bg-dark-elevated shadow-lg shadow-brand-500/20' 
                  : 'border-dark-border bg-dark-surface hover:border-slate-500'"
              >
                <!-- Name and Color Dot -->
                <div class="flex justify-between items-center mb-4">
                  <span class="font-bold text-sm" :class="form.valor === theme.value ? 'text-brand-400' : 'text-slate-300'">
                    {{ theme.name }}
                  </span>
                  <div class="w-4 h-4 rounded-full shadow-sm" :class="theme.colorClass"></div>
                </div>
                
                <!-- Mockup Preview UI -->
                <div class="rounded-md bg-dark-base p-3 border border-dark-border shadow-inner flex flex-col gap-3 pointer-events-none">
                  
                  <!-- Mock Input (Focused) -->
                  <div>
                    <div class="h-1.5 w-8 bg-slate-500 rounded mb-1.5"></div>
                    <div class="h-6 w-full rounded border bg-dark-surface flex items-center px-1.5 relative overflow-hidden" :class="theme.borderClass">
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
                      <div class="h-1.5 w-10 bg-slate-500 rounded"></div>
                    </div>

                    <!-- Mock Button -->
                    <div class="px-2 py-0.5 rounded text-[9px] font-bold shadow-sm" :class="[theme.bgClass, theme.value === 'black' ? 'text-white dark:text-slate-900' : 'text-white']">
                      Guardar
                    </div>
                  </div>
                  
                </div>
              </div>
            </div>
            </div>
            <JetInputError :message="error.valor" class="mt-2" />
          </div>

          <div v-if="form.tipo === 'Redondez'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Redondez de las Esquinas"
              class="float-left w-full mb-2"
            />
            <div class="grid grid-cols-1 gap-3 mt-2 clear-both">
              <div
                v-for="radius in radiusOptions"
                :key="radius.value"
                @click="form.valor = radius.value"
                class="cursor-pointer border-2 p-3 transition-all duration-200 flex items-center justify-between"
                :style="radius.style"
                :class="[
                  form.valor === radius.value 
                    ? 'border-brand-500 bg-dark-elevated shadow-lg shadow-brand-500/10' 
                    : 'border-dark-border bg-dark-surface hover:border-slate-500'
                ]"
              >
                <!-- Name and Description -->
                <div class="text-left">
                  <span class="font-bold text-sm block" :class="form.valor === radius.value ? 'text-brand-400' : 'text-slate-300'">
                    {{ radius.name }}
                  </span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ radius.description }}
                  </span>
                </div>

                <!-- Preview Box -->
                <div class="flex items-center gap-2">
                  <div 
                    class="w-12 h-12 bg-brand-500 flex items-center justify-center text-white text-[10px] font-bold shadow-md transition-all duration-300"
                    :style="radius.style"
                    :class="[
                      form.valor === radius.value ? 'shadow-brand-500/30' : 'opacity-70'
                    ]"
                  >
                    Card
                  </div>
                </div>
              </div>
            </div>
            <JetInputError :message="error.valor" class="mt-2" />
          </div>

          <div v-if="form.tipo === 'Sombra'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Tamaño de Sombra"
              class="float-left w-full mb-2"
            />
            <div class="grid grid-cols-1 gap-3 mt-2 clear-both">
              <div
                v-for="shadow in shadowOptions"
                :key="shadow.value"
                @click="form.valor = shadow.value"
                class="cursor-pointer border-2 p-3 transition-all duration-200 flex items-center justify-between"
                :style="[
                  radiusOptions.find(r => r.value === $page.props.themeRadius)?.style || 'border-radius: 6px;',
                  shadow.style
                ]"
                :class="form.valor === shadow.value 
                  ? 'border-brand-500 bg-dark-elevated shadow-lg shadow-brand-500/10' 
                  : 'border-dark-border bg-dark-surface hover:border-slate-500'"
              >
                <!-- Name and Description -->
                <div class="text-left">
                  <span class="font-bold text-sm block" :class="form.valor === shadow.value ? 'text-brand-400' : 'text-slate-300'">
                    {{ shadow.name }}
                  </span>
                  <span class="text-xs text-slate-500 dark:text-slate-400">
                    {{ shadow.description }}
                  </span>
                </div>

                <!-- Preview Box -->
                <div class="flex items-center gap-2">
                  <div 
                    class="w-12 h-12 bg-brand-500 flex items-center justify-center text-white text-[10px] font-bold transition-all duration-300"
                    :style="[
                      radiusOptions.find(r => r.value === $page.props.themeRadius)?.style || 'border-radius: 6px;',
                      shadow.style
                    ]"
                    :class="[
                      form.valor === shadow.value ? 'shadow-brand-500/30' : 'opacity-70'
                    ]"
                  >
                    Sombra
                  </div>
                </div>
              </div>
            </div>
            <JetInputError :message="error.valor" class="mt-2" />
          </div>

          <div v-if="form.tipo === 'Interruptor'" class="block mb-1">
            <JetLabel
              for="valor"
              value="Estado"
              class="float-left w-full mb-2"
            />
            <div class="grid grid-cols-2 gap-4 mt-2 clear-both">
              <div
                @click="form.valor = 'si'"
                class="cursor-pointer border-2 p-4 rounded-xl transition-all duration-200 flex flex-col items-center gap-2"
                :class="form.valor === 'si'
                  ? 'border-brand-500 bg-dark-elevated shadow-lg shadow-brand-500/10'
                  : 'border-dark-border bg-dark-surface hover:border-slate-500'"
              >
                <div class="w-8 h-8 rounded-full bg-brand-500/20 flex items-center justify-center text-brand-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-sm" :class="form.valor === 'si' ? 'text-brand-400' : 'text-slate-300'">Activado</span>
              </div>

              <div
                @click="form.valor = 'no'"
                class="cursor-pointer border-2 p-4 rounded-xl transition-all duration-200 flex flex-col items-center gap-2"
                :class="form.valor === 'no'
                  ? 'border-brand-500 bg-dark-elevated shadow-lg shadow-brand-500/10'
                  : 'border-dark-border bg-dark-surface hover:border-slate-500'"
              >
                <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center text-red-500">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
                <span class="font-bold text-sm" :class="form.valor === 'no' ? 'text-brand-400' : 'text-slate-300'">Desactivado</span>
              </div>
            </div>
            <JetInputError :message="error.valor" class="mt-2" />
          </div>
        </template>
        <template #footer>
          <div class="w-1/2">
            <JetSecondaryButton
              @click="estadoModalConfiguracion = false"
              class="bg-opacity-95"
              type="cancel"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              >Cancelar
            </JetSecondaryButton>
          </div>
          <div class="w-1/2">
            <JetButton
              v-if="accion === 'new'"
              @click="nuevoConfiguracionDo"
              class="float-right"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              <template v-if="form.processing">
                <font-awesome-icon
                  icon="spinner"
                  class="mr-2 animate-spin"
                />Creando Configuración...
              </template>
              <template v-else>
                <font-awesome-icon
                  icon="plus"
                  class="mr-2"
                />Crear Configuración
              </template>
            </JetButton>

            <JetButton
              v-if="accion === 'edit'"
              @click="editaConfiguracionDo"
              class="float-right"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            >
              <template v-if="form.processing">
                <font-awesome-icon
                  icon="spinner"
                  class="mr-2 animate-spin"
                />Guardando...
              </template>
              <template v-else>
                <font-awesome-icon
                  icon="floppy-disk"
                  class="mr-2"
                />Guardar Cambios
              </template>
            </JetButton>
          </div>
        </template>
      </JetDialogModal>
    </form>
  </AppLayout>
</template>
