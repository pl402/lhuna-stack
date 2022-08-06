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
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";
import moment from 'moment';
import ButtonA from "@/Components/ButtonA.vue";

const props = defineProps({
  reportes: Object,
  reporte: Object,
  filtro: String,
  orderBy: Object,
  auth: Object,
});

const reportes = toRefs(props).reportes;
const buscar = ref(props.filtro ? props.filtro : "");

const orderByObject = ref(
  props.orderBy
    ? props.orderBy
    : {
      field: "name",
      sort: "asc",
    }
);

const fechaHumano = (fecha) => {
  return moment(fecha).format("YYYY/MM/DD HH:mm:ss");
}

</script>
<template>
  <AppLayout title="Reportes">
    <div class="py-4">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="
            bg-white
            overflow-hidden
            shadow-xl
            sm:rounded-lg
            border border-slate-300/90
          ">
          <Buscador v-model="buscar" :orderByObject="orderByObject" ruta="reportes" autofocus />
          <Tabla v-if="reportes.data.length > 0">
            <template #col>
              <th class="px-4 py-1 w-20">
                <Ordena v-model="orderByObject" ruta="reportes" :buscar="buscar" titulo="ID" campo="id" />
              </th>
              <th class="px-4 py-1">
                <Ordena v-model="orderByObject" ruta="reportes" :buscar="buscar" titulo="Titulo" campo="titulo" />
              </th>
              <th class="px-4 py-1">
                <Ordena v-model="orderByObject" ruta="reportes" :buscar="buscar" titulo="Iniciado" campo="created_at" />
              </th>
              <th class="px-4 py-1">
                <Ordena v-model="orderByObject" ruta="reportes" :buscar="buscar" titulo="Finalizado"
                  campo="updated_at" />
              </th>
              <th class="px-4 py-1 w-36">Usuario</th>
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

              </th>
            </template>
            <template #row>
              <tr class="text-slate-700 hover:bg-slate-300 border-b border-slate-200" v-for="reporte in reportes.data"
                :key="reporte.id">
                <td class="px-4 py-1">{{ reporte.id }}</td>
                <td class="px-4 py-1">{{ reporte.titulo }}</td>
                <td class="px-4 py-1">{{ fechaHumano(reporte.created_at) }}</td>
                <td class="px-4 py-1">{{ fechaHumano(reporte.updated_at) }}</td>
                <td class="px-4 py-1">
                  {{ reporte.user.name }}
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
                  <ButtonA :href="'/reportes/descarga/' + reporte.uuid" target="_blank" itle="Descargar reporte">
                    <font-awesome-icon icon="file-arrow-down" class="mr-2" /> Descargar
                  </ButtonA>
                </td>
              </tr>
            </template>
            <template #pagination>
              <Paginador :links="reportes.links" :total="reportes.total" :to="reportes.to" :from="reportes.from" />
            </template>
          </Tabla>
          <div v-else class="text-center p-5 text-slate-500 text-md">
            Sin Reportes
            <br />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
