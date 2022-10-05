<script setup>
import { useForm } from "@inertiajs/inertia-vue3";
import { ref } from "vue";
import JetButton from "@/Jetstream/Button.vue";
import JetDropdown from "@/Jetstream/Dropdown.vue";
import JetDropdownLink from "@/Jetstream/DropdownLink.vue";
import { ChevronDownIcon } from "@heroicons/vue/solid";
import moment from "moment";
import { notify } from "notiwind";

const props = defineProps({
    area_id: Number,
    pdi_uuid: String,
});

let form = useForm({
    area_frm: props.area_id,
    pdi_frm: props.pdi_uuid,
});

let getReportesInter = null;
let reportesAreas = ref([]);

const generaReporte = () => {
    form.post(
        route("reportes.area", { uuid: props.pdi_uuid, id: props.area_id }),
        {
            preserveScroll: true,
            onSuccess: (rest) => {
                notify(
                    {
                        group: "info",
                        title: "Generando reporte",
                        text: "El reporte se está generando",
                    },
                    3000
                );
                getReportesInter = setInterval(getReportes, 2000);
            },
            onError: (errors) => {
                notify(
                    {
                        group: "error",
                        title: "Ocurrió un error",
                        text: "Error al generar reporte",
                    },
                    3000
                );
            },
        }
    );
};

const fechaHumano = (fecha) => {
    return moment(fecha).format("YYYY/MM/DD HH:mm:ss");
};

function getReportes() {
    // Hace consulta a la API para obtener los datos de la area
    axios
        .get(
            route("reportes.area.list", {
                uuid: props.pdi_uuid,
                id: props.area_id,
            })
        )
        .then((response) => {
            reportesAreas.value = response.data;
            let reporteGenerando = reportesAreas.value.find(
                (reporte) => reporte.estatus === "Generando"
            );
            if (!reporteGenerando) {
                clearInterval(getReportesInter);
            }
        });
}

getReportesInter = setInterval(getReportes, 1000);
</script>

<template>
    <JetDropdown
        class="float-right -top-[1px]"
        width="96"
        align="right"
        position="bottom"
    >
        <template #trigger>
            <JetButton>
                <font-awesome-icon icon="file-pdf" class="mr-2" />Reportes
                <ChevronDownIcon
                    class="ml-2 -mr-0.5 h-4 w-4 text-slate-200 hover:text-slate-100"
                    aria-hidden="true"
                />
            </JetButton>
        </template>
        <template #content>
            <div v-for="reporte in reportesAreas" :key="reporte.uuid">
                <JetDropdownLink
                    v-if="reporte.estatus !== 'Generando'"
                    as="a"
                    target="_blank"
                    :href="'/reportes/descarga/' + reporte.uuid"
                    class="normal-case"
                    title="Descargar reporte"
                >
                    <font-awesome-icon icon="file-arrow-down" class="mr-2" />{{
                        fechaHumano(reporte.created_at)
                    }}
                </JetDropdownLink>
                <div
                    v-if="reporte.estatus === 'Generando'"
                    :title="
                        'Generando reporte desde el ' +
                        fechaHumano(reporte.created_at)
                    "
                    class="normal-case text-gray-500 block px-4 py-2 text-sm leading-5 transition"
                >
                    <font-awesome-icon
                        icon="file-arrow-down"
                        class="mr-2"
                    />Generando reporte...
                </div>
            </div>
            <JetDropdownLink
                as="button"
                class="normal-case bg-slate-100/50 w-52"
                @click="generaReporte"
            >
                <font-awesome-icon icon="gears" class="mr-2" />Generar nuevo
                reporte
            </JetDropdownLink>
        </template>
    </JetDropdown>
</template>
