<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage, Link, useForm } from "@inertiajs/inertia-vue3";
import { reactive, ref, watch, toRefs } from "vue";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import Tabla from "@/Components/Tabla.vue";
import { Inertia } from "@inertiajs/inertia";
import Paginador from "@/Components/Paginador.vue";
import { notify } from "notiwind";
import JetInputError from "@/Jetstream/InputError.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import Select from "../Components/Select.vue";
import Autocomplete from "../Components/Autocomplete.vue";
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";

const props = defineProps({
  areas: Object,
  todas_las_areas: Object,
  area: Object,
  filtro: String,
  orderBy: Object,
});

const form = useForm({
  nombre: "",
  descripcion: "",
  area_padre: {
    value: "",
    name: "",
  },
});

const error = reactive({
  nombre: "",
  descripcion: "",
  area_padre: "",
});

const areas = toRefs(props).areas;
const todas_las_areas = toRefs(props).todas_las_areas;
const buscar = ref(props.filtro ? props.filtro : "");
const estadoModalArea = ref(false);
const estadoModalEliminaArea = ref(false);
const estadoModalEditaCampos = ref(false);
const accion = ref("new");
const idActual = ref(null);
const currentArea = ref(null);
const orderByObject = ref(
  props.orderBy
    ? props.orderBy
    : {
      field: "name",
      sort: "asc",
    }
);
let areas_auto = [{ value: null, name: "Ninguno" }];
todas_las_areas.value.forEach((area) => {
  areas_auto.push({
    value: area.id,
    name: area.nombre,
  });
});

const nuevoArea = () => {
  accion.value = "new";
  form.reset("nombre", "descripcion", "area_padre");
  error.nombre = "";
  error.descripcion = "";
  error.area_padre = "";
  estadoModalArea.value = true;
};

const nuevoAreaDo = () => {
  error.nombre = "";
  error.descripcion = "";
  error.area_padre = "";
  form.post(route("areas.store"), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Area creada",
          text: "El area se ha creado exitosamente",
        },
        3000
      );
      estadoModalArea.value = false;
      form.reset("nombre", "descripcion", "area_padre");
    },
    onError: (errors) => {
      if (!errors.nombre && !errors.descripcion && !errors.area_padre) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar area",
          },
          3000
        );
      }
      error.nombre = errors.nombre;
      error.descripcion = errors.descripcion;
      error.area_padre = errors.area_padre;
    },
  });
};

const editaArea = (area) => {
  accion.value = "edit";
  currentArea.value = area;
  idActual.value = area.id;
  form.nombre = area.nombre;
  form.descripcion = area.descripcion;
  if (area.area_padre) {
    form.area_padre.value = area.parent.id;
    form.area_padre.name = area.parent.nombre;
  } else {
    form.area_padre.value = "";
    form.area_padre.name = "";
  }
  error.nombre = "";
  error.descripcion = "";
  error.area_padre = "";
  estadoModalArea.value = true;
};

const editaAreaDo = () => {
  form.post(route("areas.update", idActual.value), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Area editada",
          text: "El area se ha editado exitosamente",
        },
        3000
      );
      estadoModalArea.value = false;
      form.reset("nombre", "descripcion", "area_padre");
    },
    onError: (errors) => {
      if (!errors.nombre && !errors.descripcion && !errors.area_padre) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al editar area",
          },
          3000
        );
      }
      error.nombre = errors.nombre;
      error.descripcion = errors.descripcion;
      error.area_padre = errors.area_padre;
    },
  });
};

const borraAreaDo = () => {
  form.post(route("areas.destroy", idActual.value), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Area eliminado",
          text: "El area se ha eliminado exitosamente",
        },
        3000
      );
      estadoModalArea.value = false;
      estadoModalEliminaArea.value = false;
      form.reset("nombre", "descripcion", "area_padre");
    },
    onError: (errors) => {
      if (!errors.nombre && !errors.descripcion && !errors.area_padre) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar area",
          },
          3000
        );
      }
    },
  });
};
</script>

<template>
  <AppLayout title="Areas">
    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="
            bg-white
            overflow-hidden
            shadow-xl
            sm:rounded-lg
            border border-slate-300/90
          ">
          <Buscador v-model="buscar" :orderByObject="orderByObject" ruta="areas" autofocus />
          <Tabla v-if="areas.data.length > 0">
            <template #col>
              <th class="px-4 py-1 w-32">
                <Ordena v-model="orderByObject" ruta="areas" :buscar="buscar" titulo="ID" campo="id" />
              </th>
              <th class="px-4 py-1">
                <Ordena v-model="orderByObject" ruta="areas" :buscar="buscar" titulo="Nombre" campo="nombre" />
              </th>
              <th class="px-4 py-1">
                <Ordena v-model="orderByObject" ruta="areas" :buscar="buscar" titulo="Descripción"
                  campo="descripcion" />
              </th>
              <th class="px-4 py-1">
                <font-awesome-icon icon="angle-up" class="mr-1 text-green-600" />Depende /
                <font-awesome-icon icon="angle-down" class="mr-1 text-blue-600" />Dependientes
              </th>
              <th class="
                  px-4
                  py-1
                  w-5
                  text-center
                  sticky
                  right-0
                  bg-slate-200/50
                  border-l border-slate-200
                  backdrop-blur
                ">
                <JetButton @click="nuevoArea">
                  <font-awesome-icon icon="add" />
                </JetButton>
              </th>
            </template>
            <template #row>
              <tr class="text-slate-700 hover:bg-slate-300 border-b border-slate-200" v-for="area in areas.data"
                :key="area.id">
                <td class="px-4 py-1">{{ area.id }}</td>
                <td class="px-4 py-1">{{ area.nombre }}</td>
                <td class="px-4 py-1">{{ area.descripcion }}</td>
                <td class="px-4 py-1">
                  <span v-if="area.parent">
                    <font-awesome-icon icon="angle-up" class="mr-1 text-green-600" />{{ area.parent ? area.parent.nombre
                        : ""
                    }}
                  </span>
                  <span v-if="area.parent && area.children.length > 0" class="mx-2 text-gray-400">/</span>
                  <span v-if="area.children.length > 0">
                    <font-awesome-icon icon="angle-down" class="mr-1 text-blue-600" />
                    {{ area.children.length > 0 ? area.children.length : "" }}
                  </span>
                </td>
                <td class="
                    px-4
                    py-1
                    text-center
                    sticky
                    right-0
                    bg-slate-200/50
                    border-l border-slate-200
                    backdrop-blur
                  ">
                  <JetButton class="bg-blue-500 hover:bg-blue-700 text-white" @click="editaArea(area)">
                    <font-awesome-icon icon="pen" />
                  </JetButton>
                </td>
              </tr>
            </template>
            <template #pagination>
              <Paginador :links="areas.links" :total="areas.total" :to="areas.to" :from="areas.from" />
            </template>
          </Tabla>
          <div v-else class="text-center p-5 text-slate-500 text-md">
            Sin Areas
            <br />
            <JetButton @click="nuevoArea" class="mt-2">
              <font-awesome-icon icon="add" class="mr-2" />Agregar Nueva Area
            </JetButton>
          </div>
        </div>
      </div>
    </div>

    <form class="flex flex-col" @submit.prevent="accion === 'new' ? nuevoAreaDo : editaAreaDo">
      <JetDialogModal v-if="estadoModalArea === true" :show="estadoModalArea" @close="estadoModalArea = false"
        max-width="md" id="elmodal">
        <template #title>{{ accion === "new" ? "Crear nuevo area" : "Editar area" }}
          <JetDangerButton v-if="accion === 'edit'" @click="estadoModalEliminaArea = true" class="-mt-1 float-right">
            <font-awesome-icon icon="trash" class="mr-2" />
            Eliminar Area
          </JetDangerButton>
        </template>
        <template #content>
          <label class="block mb-1">
            <JetLabel for="nombre" value="Nombre" class="float-left" />
            <JetInput id="nombre" v-model="form.nombre" placeholder="Nombre del área o unidad administrativa"
              type="text" class="mt-1 block w-full" required autofocus />
            <JetInputError :message="error.nombre" class="mt-2" />
          </label>
          <label class="block mb-1">
            <JetLabel for="descripcion" value="Descripción" class="float-left" />
            <JetInput id="descripcion" v-model="form.descripcion"
              placeholder="Breve descripción del área o unidad administrativa" type="text" class="mt-1 block w-full"
              required />
            <JetInputError :message="error.descripcion" class="mt-2" />
          </label>
          <div v-if="accion === 'edit'" class="pt-2 pb-1">
            <div class="border-t border-gray-200" />
          </div>
          <label v-if="accion === 'edit'" class="block mb-1">
            <JetLabel for="area_padre" value="Depende de" class="float-left" />
            <Autocomplete id="area_padre" v-model="form.area_padre" placeholder="Escriba una opción..."
              class="mt-1 block w-full" :options="areas_auto" />
            <JetInputError :message="error.area_padre" class="mt-2" />
          </label>
        </template>
        <template #footer>
          <div class="w-1/2">
            <JetButton @click="estadoModalArea = false" class="bg-opacity-95" type="cancel" :disabled="form.processing"
              :class="{ 'opacity-25': form.processing }">Cancelar</JetButton>
          </div>
          <div class="w-1/2">
            <JetButton v-if="accion === 'new'" @click="nuevoAreaDo" :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing" class="float-right">
              <font-awesome-icon icon="plus" class="mr-2" />Crear Area
            </JetButton>

            <JetButton v-if="accion === 'edit'" @click="editaAreaDo" :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing" class="float-right">
              <font-awesome-icon icon="floppy-disk" class="mr-2" />Guardar
              Cambios
            </JetButton>
          </div>
        </template>
      </JetDialogModal>
    </form>
    <JetDialogModal v-if="estadoModalEliminaArea === true" :show="estadoModalEliminaArea"
      @close="estadoModalEliminaArea = false" max-width="md">
      <template #title>Eliminar area</template>
      <template #content>
        <h4 class="block mb-1 text-lg">¿Realmente desea eliminar esta área?</h4>
        <span class="text-red-600">Esta acción no se puede deshacer.</span>
      </template>
      <template #footer>
        <div class="w-1/2">
          <JetButton @click="estadoModalEliminaArea = false" class="bg-opacity-95" type="cancel"
            :disabled="form.processing" :class="{ 'opacity-25': form.processing }">Cancelar</JetButton>
        </div>
        <div class="w-1/2">
          <JetDangerButton @click="borraAreaDo" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
            class="float-right">
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar Area
          </JetDangerButton>
        </div>
      </template>
    </JetDialogModal>
  </AppLayout>
</template>
