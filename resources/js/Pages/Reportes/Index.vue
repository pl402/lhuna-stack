<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted, onUnmounted, toRefs } from "vue";
import GlowCard from "@/Components/GlowCard.vue";
import Tabla from "@/Components/Tabla.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal";
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";
import { notify } from "notiwind";
import axios from "axios";

const props = defineProps({
  reports: Object, // Paginated list from backend
  entities: Array,
  filtro: String,
  orderBy: Object,
  filtros: Array,
});

// Infinite Scroll Logic
const itemsLista = ref([...props.reports.data]);
const paginaActual = ref(props.reports.current_page);
const ultimaPagina = ref(props.reports.last_page);
const loadingMore = ref(false);

const hasMore = computed(() => paginaActual.value < ultimaPagina.value);

watch(
  () => props.reports,
  (newReports) => {
    if (newReports.current_page === 1) {
      itemsLista.value = [...newReports.data];
      paginaActual.value = newReports.current_page;
      ultimaPagina.value = newReports.last_page;
    }
  },
  { deep: true }
);

const loadMore = async () => {
  if (loadingMore.value || !hasMore.value) return;
  loadingMore.value = true;

  const nextPage = paginaActual.value + 1;
  const currentParams = new URLSearchParams(window.location.search);
  currentParams.set("page", nextPage);

  try {
    const response = await axios.get(
      `${window.location.pathname}?${currentParams.toString()}`,
      {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    if (response.data && response.data.reports) {
      itemsLista.value.push(...response.data.reports.data);
      paginaActual.value = response.data.reports.current_page;
      ultimaPagina.value = response.data.reports.last_page;
    }
  } catch (err) {
    console.error("Error al cargar más reportes:", err);
  } finally {
    loadingMore.value = false;
  }
};

const loadMoreTrigger = ref(null);
let infiniteObserver = null;

const setupObserver = () => {
  if (infiniteObserver) {
    infiniteObserver.disconnect();
  }

  infiniteObserver = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        loadMore();
      }
    },
    {
      root: null,
      rootMargin: "150px",
    }
  );

  if (loadMoreTrigger.value) {
    infiniteObserver.observe(loadMoreTrigger.value);
  }
};

onMounted(() => {
  setupObserver();
});

onUnmounted(() => {
  if (infiniteObserver) {
    infiniteObserver.disconnect();
  }
});

// Search & Filter definitions
const buscar = ref(props.filtro ? props.filtro : "");
const filtros = ref(props.filtros ? props.filtros : []);
const orderByObject = ref(
  props.orderBy ? props.orderBy : { field: "id", sort: "asc" }
);

const campos = {
  id: { label: "ID", type: "number", defaultCondition: "=" },
  name: { label: "Nombre de la Plantilla", type: "text", defaultCondition: "LIKE" },
  entity_name: { label: "Entidad de Origen", type: "text", defaultCondition: "LIKE" },
};

// Modal Preview states
const selectedReport = ref(null);
const previewData = ref([]);
const previewHeaders = ref([]);
const loadingPreview = ref(false);
const showPreviewModal = ref(false);
const showDeleteModal = ref(false);
const reportToDelete = ref(null);

const groupedPreviewData = computed(() => {
  if (!selectedReport.value?.group_by || previewData.value.length === 0) {
    return null;
  }
  const groups = {};
  previewData.value.forEach((row, idx) => {
    const groupVal = row._group_by_value !== undefined ? row._group_by_value : "Otros";
    if (!groups[groupVal]) {
      groups[groupVal] = [];
    }
    const rowWithKey = {
      ...row,
      _row_key: row.id !== undefined ? `row-${row.id}` : `row-idx-${idx}-${JSON.stringify(row)}`
    };
    groups[groupVal].push(rowWithKey);
  });
  return groups;
});

const getAggregationsForHeader = (headerLabel) => {
  if (!selectedReport.value?.aggregations) return [];
  const entity = props.entities.find(e => e.name === selectedReport.value.entity_name);
  return selectedReport.value.aggregations.filter(agg => {
    const fieldMeta = entity?.fields.find(f => f.name === agg.field);
    const label = fieldMeta?.label || agg.field;
    return label === headerLabel;
  });
};

const calculateAggregationValue = (rows, headerLabel, func) => {
  const values = rows.map(r => parseFloat(r[headerLabel])).filter(v => !isNaN(v));
  if (values.length === 0) return 0;
  
  switch (func.toUpperCase()) {
    case 'SUM':
      return values.reduce((sum, v) => sum + v, 0).toFixed(2);
    case 'AVG':
      return (values.reduce((sum, v) => sum + v, 0) / values.length).toFixed(2);
    case 'MIN':
      return Math.min(...values).toFixed(2);
    case 'MAX':
      return Math.max(...values).toFixed(2);
    case 'COUNT':
      return values.length;
    default:
      return 0;
  }
};

const getEntityLabel = (entityName) => {
  const entity = props.entities.find((e) => e.name === entityName);
  return entity ? entity.plural_label : entityName;
};

const getEntityIcon = (entityName) => {
  const entity = props.entities.find((e) => e.name === entityName);
  return entity ? entity.icon : "file-lines";
};

const loadPreview = async (report) => {
  selectedReport.value = report;
  loadingPreview.value = true;
  showPreviewModal.value = true;
  previewData.value = [];
  previewHeaders.value = [];

  try {
    const response = await axios.get(route("reportes.preview", report.id));
    if (response.data && response.data.ok) {
      previewData.value = response.data.data;
      previewHeaders.value = response.data.headers;
      return true;
    }
  } catch (err) {
    notify(
      {
        group: "error",
        title: "Error al cargar vista previa",
        text: err.response?.data?.message || "Ocurrió un error inesperado.",
      },
      3000
    );
    showPreviewModal.value = false;
  } finally {
    loadingPreview.value = false;
  }
  return false;
};

const printReportDirect = async (report) => {
  const success = await loadPreview(report);
  if (success) {
    setTimeout(() => {
      printReport();
    }, 500);
  }
};

const printReport = () => {
  const printContent = document.getElementById("printable-report-area").innerHTML;
  const originalContent = document.body.innerHTML;

  document.body.innerHTML = `
    <html>
      <head>
        <title>${selectedReport.value.name}</title>
        <style>
          body { font-family: sans-serif; color: #1e293b; padding: 20px; }
          h1 { font-size: 24px; margin-bottom: 5px; color: #0f172a; }
          .subtitle { font-size: 14px; color: #64748b; margin-bottom: 20px; }
          table { width: 100%; border-collapse: collapse; margin-top: 15px; }
          th { background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1; text-align: left; padding: 10px; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #475569; }
          td { border-bottom: 1px solid #e2e8f0; padding: 10px; font-size: 13px; color: #334155; }
          tr:nth-child(even) { background-color: #f8fafc; }
          @media print {
            body { padding: 0; }
            .no-print { display: none; }
          }
        </style>
      </head>
      <body>
        <h1>${selectedReport.value.name}</h1>
        <div class="subtitle">Reporte generado automáticamente basado en ${getEntityLabel(selectedReport.value.entity_name)} | ${new Date().toLocaleString()}</div>
        ${printContent}
      </body>
    </html>
  `;

  window.print();
  document.body.innerHTML = originalContent;
  window.location.reload();
};

const confirmDelete = (report) => {
  reportToDelete.value = report;
  showDeleteModal.value = true;
};

const deleteReport = () => {
  if (!reportToDelete.value) return;
  
  router.delete(route("reportes.destroy", reportToDelete.value.id), {
    onSuccess: () => {
      notify(
        {
          group: "main",
          title: "Plantilla eliminada",
          text: "El reporte se eliminó correctamente.",
        },
        3000
      );
      showDeleteModal.value = false;
      reportToDelete.value = null;
      // Refresh list
      router.visit(route("reportes.index"), { preserveScroll: true });
    }
  });
};

const nuevoRegistro = () => {
  router.visit(route("reportes.create"));
};

const editaRegistro = (id) => {
  router.visit(route("reportes.edit", id));
};
</script>

<template>
  <AppLayout title="Plantillas de Reportes" :mainScrollable="false">
    <div class="w-full max-w-full mx-auto p-0 flex-1 min-h-0 flex flex-col">
      <GlowCard rounded="rounded-none" class="flex-1 min-h-0 border-0">
        <div class="flex flex-col h-full w-full">
          
          <!-- Unified Buscador component at the top, matching Cursos and Usuarios exactly -->
          <Buscador
            v-model="buscar"
            :orderByObject="orderByObject"
            ruta="reportes"
            :filtros="filtros"
            :campos="campos"
            titulo="reportes"
            autofocus
          />

          <!-- Table Content area -->
          <Tabla v-if="itemsLista.length > 0" heightClass="flex-1 min-h-0">
            <template #col>
              <th class="px-4 py-3 w-20">
                <Ordena
                  v-model="orderByObject"
                  ruta="reportes"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="ID"
                  campo="id"
                />
              </th>
              <th class="px-4 py-3">
                <Ordena
                  v-model="orderByObject"
                  ruta="reportes"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="Nombre de la Plantilla"
                  campo="name"
                />
              </th>
              <th class="px-4 py-3 w-48">
                <Ordena
                  v-model="orderByObject"
                  ruta="reportes"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="Entidad de Datos"
                  campo="entity_name"
                />
              </th>
              <th class="px-4 py-3">Columnas Configuradas</th>
              <th class="px-4 py-3 w-56 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                <div class="flex items-center justify-between gap-3">
                  <span class="text-xs uppercase tracking-wider text-slate-400 pl-1 font-semibold">Acciones</span>
                  <button 
                    @click="nuevoRegistro" 
                    class="inline-flex items-center justify-center px-3 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold transition duration-200 shadow-md shadow-brand-500/20 hover:shadow-lg hover:shadow-brand-500/30"
                    title="Nueva Plantilla"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                  </button>
                </div>
              </th>
            </template>

            <template #row>
              <tr
                class="text-slate-300 hover:bg-dark-elevated border-b border-dark-border"
                v-for="report in itemsLista"
                :key="report.id"
              >
                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-300">{{ report.id }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-slate-600 dark:text-slate-200 text-left">
                  {{ report.name }}
                </td>
                <td class="px-4 py-3 text-sm text-left">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-brand-500/10 text-brand-400 border border-brand-500/20">
                    <font-awesome-icon :icon="getEntityIcon(report.entity_name)" class="text-[10px]" />
                    {{ getEntityLabel(report.entity_name) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 text-left truncate max-w-xs">
                  {{ report.fields.join(', ') }}
                </td>
                <td class="px-4 py-3 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                  <div class="inline-flex rounded shadow-sm">
                    <!-- Preview -->
                    <button
                      @click="loadPreview(report)"
                      class="inline-flex items-center justify-center px-3 py-2 bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white rounded-l border-r border-purple-600/50 transition duration-200 shadow-md shadow-purple-500/20 hover:shadow-lg hover:shadow-purple-500/30"
                      title="Ver Vista Previa"
                    >
                      <font-awesome-icon icon="eye" class="text-xs w-4 h-4" />
                    </button>
                    <!-- Excel -->
                    <a
                      :href="route('reportes.excel', report.id)"
                      class="inline-flex items-center justify-center px-3 py-2 bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white border-r border-emerald-600/50 transition duration-200 shadow-md shadow-emerald-500/20 hover:shadow-lg hover:shadow-emerald-500/30"
                      title="Exportar a Excel"
                    >
                      <font-awesome-icon icon="file-excel" class="text-xs w-4 h-4" />
                    </a>
                    <!-- PDF -->
                    <button
                      @click="printReportDirect(report)"
                      class="inline-flex items-center justify-center px-3 py-2 bg-red-500 hover:bg-red-600 active:bg-red-700 text-white border-r border-red-600/50 transition duration-200 shadow-md shadow-red-500/20 hover:shadow-lg hover:shadow-red-500/30"
                      title="Imprimir / PDF"
                    >
                      <font-awesome-icon icon="file-pdf" class="text-xs w-4 h-4" />
                    </button>
                    <!-- Edit -->
                    <button
                      @click="editaRegistro(report.id)"
                      class="inline-flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded-r transition duration-200 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30"
                      title="Editar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <!-- Trigger row for infinite scroll -->
              <tr ref="loadMoreTrigger" class="opacity-0 h-1">
                <td colspan="100"></td>
              </tr>
            </template>

            <template #pagination>
              <div class="px-4 py-2.5 border-t border-dark-border text-xs font-semibold tracking-wide text-slate-500 uppercase bg-dark-surface/50 bg-glass-gradient backdrop-blur-md flex justify-between items-center select-none">
                <span>Mostrando {{ itemsLista.length }} de {{ reports.total }} plantillas</span>
                <span v-if="loadingMore" class="flex items-center gap-1.5 text-brand-400">
                  <font-awesome-icon icon="spinner" class="animate-spin" />
                  Cargando más...
                </span>
                <span v-else-if="!hasMore && itemsLista.length > 0" class="text-slate-600 dark:text-slate-500">
                  Fin de la lista
                </span>
              </div>
            </template>
          </Tabla>

          <!-- Empty State -->
          <div v-else class="text-center p-12 text-slate-400 text-md flex-1 flex flex-col justify-center items-center">
            <font-awesome-icon icon="file-invoice" class="text-slate-600 text-4xl mb-3" />
            Sin Plantillas de Reportes
            <br />
            <button 
              @click="nuevoRegistro" 
              class="mt-4 inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-lg font-semibold text-xs uppercase tracking-widest transition duration-200 shadow-md shadow-brand-500/20"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Crear Nueva Plantilla
            </button>
          </div>

        </div>
      </GlowCard>
    </div>

    <!-- Preview Modal -->
    <JetDialogModal :show="showPreviewModal" @close="showPreviewModal = false" max-width="5xl">
      <template #title>
        <div class="flex items-center justify-between">
          <div class="truncate text-left">
            <span class="text-xs text-brand-400 uppercase tracking-widest block font-bold">Vista Previa de Reporte</span>
            <span class="text-lg font-bold text-slate-100 block truncate">{{ selectedReport?.name }}</span>
          </div>
          <div class="flex gap-2 mr-6">
            <a
              v-if="selectedReport"
              :href="route('reportes.excel', selectedReport.id)"
              class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition"
            >
              <font-awesome-icon icon="file-excel" class="mr-1.5" />
              Excel
            </a>
            <button
              @click="printReport"
              class="inline-flex items-center px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold transition"
            >
              <font-awesome-icon icon="file-pdf" class="mr-1.5" />
              Imprimir / PDF
            </button>
          </div>
        </div>
      </template>

      <template #content>
        <div v-if="loadingPreview" class="flex flex-col items-center justify-center py-16 gap-3 text-slate-400">
          <font-awesome-icon icon="spinner" class="animate-spin text-3xl text-brand-500" />
          <span class="text-sm">Procesando consulta en tiempo real...</span>
        </div>

        <div v-else-if="previewData.length > 0">
          <div id="printable-report-area">
            <!-- Grouped Layout -->
            <div v-if="groupedPreviewData" class="space-y-6">
              <div v-for="(rows, groupName) in groupedPreviewData" :key="groupName" class="mb-6 text-left">
                <!-- Group Header -->
                <div class="flex items-center gap-2 mb-2 bg-dark-elevated/20 p-2.5 rounded-lg border border-dark-border/40">
                  <div class="h-2 w-2 rounded-full bg-brand-500 animate-pulse"></div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">
                    {{ rows[0]?._group_by_label || 'Grupo' }}: <span class="text-brand-400 font-semibold">{{ groupName }}</span>
                  </h4>
                  <span class="text-[10px] text-slate-500 font-semibold ml-auto">
                    {{ rows.length }} registros
                  </span>
                </div>

                <!-- Table -->
                <div class="border border-dark-border rounded-xl overflow-hidden bg-dark-surface/10">
                  <table class="min-w-full divide-y divide-dark-border/40 text-left bg-dark-surface/10">
                    <thead class="bg-dark-elevated sticky top-0 z-10">
                      <tr>
                        <th
                          v-for="(header, hIdx) in previewHeaders.filter(h => h !== rows[0]?._group_by_label)"
                          :key="header"
                          class="px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-dark-border/60 bg-dark-elevated transition-all duration-300"
                          :class="{
                            'rounded-tl-xl': hIdx === 0,
                            'rounded-tr-xl': hIdx === previewHeaders.filter(h => h !== rows[0]?._group_by_label).length - 1
                          }"
                        >
                          {{ header }}
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-border/20">
                      <tr
                        v-for="row in rows"
                        :key="row._row_key"
                        class="hover:bg-dark-elevated/20 transition-all duration-300"
                      >
                        <td
                          v-for="header in previewHeaders.filter(h => h !== rows[0]?._group_by_label)"
                          :key="header"
                          class="px-4 py-3 text-sm text-slate-300 font-mono"
                        >
                          {{ row[header] }}
                        </td>
                      </tr>
                    </tbody>
                    <tfoot v-if="selectedReport?.aggregations && selectedReport.aggregations.length > 0" class="bg-dark-elevated/40 border-t border-dark-border/60">
                      <tr>
                        <td
                          v-for="header in previewHeaders.filter(h => h !== rows[0]?._group_by_label)"
                          :key="header"
                          class="px-4 py-3 text-xs font-bold text-slate-300"
                        >
                          <div v-for="agg in getAggregationsForHeader(header)" :key="agg.label" class="flex flex-col gap-0.5">
                            <span class="text-[9px] text-slate-500 uppercase font-semibold">{{ agg.label || agg.function }}</span>
                            <span class="text-brand-400 font-mono text-sm font-bold">{{ calculateAggregationValue(rows, header, agg.function) }}</span>
                          </div>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            <!-- Flat Layout -->
            <div v-else class="overflow-x-auto border border-dark-border rounded-xl">
              <table class="min-w-full divide-y divide-dark-border/40 text-left bg-dark-surface/10">
                <thead class="bg-dark-elevated/40">
                  <tr>
                    <th
                      v-for="header in previewHeaders"
                      :key="header"
                      class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-dark-border/60"
                    >
                      {{ header }}
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-dark-border/20">
                  <tr
                    v-for="(row, rIdx) in previewData"
                    :key="rIdx"
                    class="hover:bg-dark-elevated/20 transition-colors"
                  >
                    <td
                      v-for="(header, hIdx) in previewHeaders"
                      :key="hIdx"
                      class="px-4 py-3.5 text-sm text-slate-300"
                    >
                      {{ row[header] }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-16 text-slate-500">
          <font-awesome-icon icon="triangle-exclamation" class="text-amber-500/60 text-2xl mb-2" />
          <p class="text-sm">No se encontraron registros que coincidan con la configuración del reporte.</p>
        </div>
      </template>

      <template #footer>
        <JetSecondaryButton @click="showPreviewModal = false">Cerrar</JetSecondaryButton>
      </template>
    </JetDialogModal>

    <!-- Delete Confirmation Modal -->
    <JetConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>Eliminar Plantilla de Reporte</template>
      <template #content>
        <div class="text-left">
          <p class="text-sm text-slate-300">¿Estás seguro de que deseas eliminar la plantilla de reporte "{{ reportToDelete?.name }}"?</p>
          <span class="text-red-500 text-xs block mt-1">Esta acción no se puede deshacer.</span>
        </div>
      </template>
      <template #footer>
        <div class="w-1/2 text-left">
          <JetSecondaryButton @click="showDeleteModal = false" class="bg-opacity-95" type="button">Cancelar</JetSecondaryButton>
        </div>
        <div class="w-1/2">
          <JetDangerButton @click="deleteReport" class="float-right">
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar Registro
          </JetDangerButton>
        </div>
      </template>
    </JetConfirmationModal>
  </AppLayout>
</template>
