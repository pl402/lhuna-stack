<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import GlowCard from "@/Components/GlowCard.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetInputError from "@/Jetstream/InputError.vue";
import Select from "@/Components/Select.vue";
import { notify } from "notiwind";
import axios from "axios";

const props = defineProps({
  entities: Array,
  report: Object,
});

const isEdit = computed(() => !!props.report);

const form = useForm({
  name: props.report?.name || "",
  entity_name: props.report?.entity_name || "",
  fields: props.report?.fields || [],
  filters: props.report?.filters || [],
  sort_by: props.report?.sort_by || "",
  sort_order: props.report?.sort_order || "asc",
});

const selectedEntity = ref(null);
const previewData = ref([]);
const previewHeaders = ref([]);
const loadingPreview = ref(false);
const showPreview = ref(false);

const availableEntities = computed(() => {
  // Filter out system models except User if needed, but normally all are listed in entities
  return props.entities.map((e) => ({
    name: e.plural_label || e.name,
    value: e.name,
  }));
});

// Watch entity selection to load fields and reset selections
watch(
  () => form.entity_name,
  (newEntityName) => {
    if (!newEntityName) {
      selectedEntity.value = null;
      form.fields = [];
      form.filters = [];
      form.sort_by = "";
      return;
    }
    selectedEntity.value = props.entities.find((e) => e.name === newEntityName);
    
    // Only reset if creating new or switching entity
    if (!props.report || props.report.entity_name !== newEntityName) {
      form.fields = selectedEntity.value?.fields.map((f) => f.name) || [];
      form.filters = [];
      form.sort_by = "id";
    }
  },
  { immediate: true }
);

const fieldsOptions = computed(() => {
  if (!selectedEntity.value) return [];
  return selectedEntity.value.fields.map((f) => ({
    name: f.label || f.name,
    value: f.name,
    type: f.type,
  }));
});

const operators = [
  { name: "Igual (=)", value: "=" },
  { name: "Diferente (!=)", value: "!=" },
  { name: "Mayor que (>)", value: ">" },
  { name: "Mayor o igual que (>=)", value: ">=" },
  { name: "Menor que (<)", value: "<" },
  { name: "Menor o igual que (<=)", value: "<=" },
  { name: "Contiene (LIKE)", value: "like" },
];

const addFilter = () => {
  if (!fieldsOptions.value.length) return;
  form.filters.push({
    field: fieldsOptions.value[0].value,
    operator: "=",
    value: "",
  });
};

const removeFilter = (index) => {
  form.filters.splice(index, 1);
};

const getFieldType = (fieldName) => {
  const f = selectedEntity.value?.fields.find((field) => field.name === fieldName);
  return f ? f.type : "string";
};

const runPreview = async () => {
  if (!form.entity_name || form.fields.length === 0) {
    notify(
      {
        group: "error",
        title: "Campos incompletos",
        text: "Seleccione una entidad y al menos una columna para visualizar.",
      },
      3000
    );
    return;
  }

  loadingPreview.value = true;
  showPreview.value = true;
  previewData.value = [];
  previewHeaders.value = [];

  try {
    // We temp save or post preview parameters
    const response = await axios.post(route("reportes.store"), {
      ...form.data(),
      name: "PREVIEW_TEMP",
    });

    if (response.data && response.data.ok) {
      const tempReportId = response.data.report.id;
      const previewRes = await axios.get(route("reportes.preview", tempReportId));
      
      if (previewRes.data && previewRes.data.ok) {
        previewData.value = previewRes.data.data;
        previewHeaders.value = previewRes.data.headers;
      }
      
      // Delete temporary report
      await axios.delete(route("reportes.destroy", tempReportId));
    }
  } catch (err) {
    notify(
      {
        group: "error",
        title: "Error de Vista Previa",
        text: err.response?.data?.message || "Verifique los filtros ingresados.",
      },
      3000
    );
    showPreview.value = false;
  } finally {
    loadingPreview.value = false;
  }
};

const saveReport = () => {
  if (!form.name) {
    notify(
      {
        group: "error",
        title: "Nombre requerido",
        text: "Escriba un nombre para la plantilla del reporte.",
      },
      3000
    );
    return;
  }

  if (form.fields.length === 0) {
    notify(
      {
        group: "error",
        title: "Columnas requeridas",
        text: "Debe seleccionar al menos una columna para el reporte.",
      },
      3000
    );
    return;
  }

  const endpoint = isEdit.value
    ? route("reportes.update", props.report.id)
    : route("reportes.store");

  axios.post(endpoint, form.data())
    .then((res) => {
      if (res.data && res.data.ok) {
        notify(
          {
            group: "main",
            title: isEdit.value ? "Plantilla actualizada" : "Plantilla guardada",
            text: "El reporte se guardó exitosamente.",
          },
          3000
        );
        router.visit(route("reportes.index"));
      }
    })
    .catch((err) => {
      notify(
        {
          group: "error",
          title: "Error al guardar",
          text: err.response?.data?.message || "Ocurrió un error al procesar el reporte.",
        },
        3000
      );
    });
};
</script>

<template>
  <AppLayout :title="isEdit ? 'Editar Plantilla de Reporte' : 'Nueva Plantilla de Reporte'" :mainScrollable="true">
    <div class="w-full max-w-7xl mx-auto p-6 space-y-6">
      <!-- Back Header -->
      <div class="flex items-center gap-4">
        <Link
          :href="route('reportes.index')"
          class="inline-flex items-center justify-center p-2.5 rounded-xl bg-dark-elevated border border-dark-border text-slate-300 hover:text-white transition"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-100">
            {{ isEdit ? 'Editar Plantilla' : 'Constructor de Reportes' }}
          </h1>
          <p class="text-sm text-slate-400">
            Define la estructura de columnas, los criterios de filtrado y el orden predeterminado.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Card -->
        <div class="lg:col-span-1 space-y-6">
          <GlowCard class="border border-dark-border rounded-2xl bg-dark-surface/40 p-6">
            <h2 class="text-md font-bold text-slate-200 mb-4 border-b border-dark-border/60 pb-2">
              1. General
            </h2>
            <div class="space-y-4">
              <div>
                <JetLabel for="name" value="Nombre del Reporte" class="mb-1" />
                <JetInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  placeholder="Ej. Alumnos con notas aprobatorias"
                  class="w-full"
                  required
                />
              </div>

              <div>
                <JetLabel for="entity" value="Entidad de Datos" class="mb-1" />
                <Select
                  id="entity"
                  v-model="form.entity_name"
                  :options="availableEntities"
                  placeholder="Seleccionar origen..."
                  :disabled="isEdit"
                  class="w-full"
                />
              </div>
            </div>
          </GlowCard>

          <!-- Columns Selection Card -->
          <GlowCard v-if="form.entity_name" class="border border-dark-border rounded-2xl bg-dark-surface/40 p-6">
            <h2 class="text-md font-bold text-slate-200 mb-4 border-b border-dark-border/60 pb-2 flex justify-between items-center">
              <span>2. Seleccionar Columnas</span>
              <span class="text-[10px] text-brand-400 font-semibold bg-brand-500/10 px-2 py-0.5 rounded border border-brand-500/10">
                {{ form.fields.length }} seleccionados
              </span>
            </h2>
            <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
              <label
                v-for="opt in fieldsOptions"
                :key="opt.value"
                class="flex items-center gap-3 p-2 border border-dark-border/40 rounded-xl bg-dark-surface/10 hover:bg-dark-surface/30 cursor-pointer select-none"
              >
                <input
                  type="checkbox"
                  v-model="form.fields"
                  :value="opt.value"
                  class="rounded bg-dark-surface border-dark-border text-brand-500 focus:ring-brand-500"
                />
                <div>
                  <span class="text-sm font-medium text-slate-300 block">{{ opt.name }}</span>
                  <span class="text-[10px] text-slate-500 block uppercase font-mono">{{ opt.type }}</span>
                </div>
              </label>
            </div>
          </GlowCard>
        </div>

        <!-- Filters & Ordering -->
        <div class="lg:col-span-2 space-y-6">
          <GlowCard v-if="form.entity_name" class="border border-dark-border rounded-2xl bg-dark-surface/40 p-6">
            <div class="flex justify-between items-center mb-4 border-b border-dark-border/60 pb-2">
              <h2 class="text-md font-bold text-slate-200">
                3. Filtros Personalizados
              </h2>
              <button
                type="button"
                @click="addFilter"
                class="inline-flex items-center text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors"
              >
                <font-awesome-icon icon="plus" class="mr-1" />
                Agregar Filtro
              </button>
            </div>

            <!-- Filter Row -->
            <div v-if="form.filters.length > 0" class="space-y-3">
              <div
                v-for="(filter, idx) in form.filters"
                :key="idx"
                class="flex flex-col sm:flex-row gap-3 items-end sm:items-center bg-dark-surface/20 border border-dark-border/60 p-3 rounded-xl relative"
              >
                <!-- Field -->
                <div class="w-full sm:flex-1">
                  <JetLabel class="mb-1 sm:hidden">Columna</JetLabel>
                  <select
                    v-model="filter.field"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                  >
                    <option v-for="opt in fieldsOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
                  </select>
                </div>

                <!-- Operator -->
                <div class="w-full sm:w-44">
                  <JetLabel class="mb-1 sm:hidden">Operador</JetLabel>
                  <select
                    v-model="filter.operator"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                  >
                    <option v-for="op in operators" :key="op.value" :value="op.value">{{ op.name }}</option>
                  </select>
                </div>

                <!-- Value -->
                <div class="w-full sm:flex-1">
                  <JetLabel class="mb-1 sm:hidden">Valor</JetLabel>
                  <select
                    v-if="getFieldType(filter.field) === 'boolean'"
                    v-model="filter.value"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500"
                  >
                    <option value="1">Sí (Verdadero)</option>
                    <option value="0">No (Falso)</option>
                  </select>
                  <input
                    v-else
                    v-model="filter.value"
                    type="text"
                    placeholder="Valor..."
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-200 placeholder-slate-500 focus:ring-brand-500"
                  />
                </div>

                <!-- Remove -->
                <button
                  type="button"
                  @click="removeFilter(idx)"
                  class="p-2 text-slate-500 hover:text-red-400 transition-colors"
                  title="Quitar Filtro"
                >
                  <font-awesome-icon icon="trash" />
                </button>
              </div>
            </div>

            <div v-else class="text-center py-6 text-xs text-slate-500 border border-dashed border-dark-border/40 rounded-xl">
              Sin filtros. El reporte cargará todos los registros del origen seleccionado.
            </div>
          </GlowCard>

          <!-- Ordering -->
          <GlowCard v-if="form.entity_name" class="border border-dark-border rounded-2xl bg-dark-surface/40 p-6">
            <h2 class="text-md font-bold text-slate-200 mb-4 border-b border-dark-border/60 pb-2">
              4. Ordenamiento Predeterminado
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <JetLabel value="Ordenar por" class="mb-1" />
                <select
                  v-model="form.sort_by"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2.5 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                >
                  <option value="">Sin ordenamiento</option>
                  <option v-for="opt in fieldsOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
                </select>
              </div>

              <div>
                <JetLabel value="Dirección" class="mb-1" />
                <select
                  v-model="form.sort_order"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2.5 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                >
                  <option value="asc">Ascendente (A-Z, Menor a Mayor)</option>
                  <option value="desc">Descendente (Z-A, Mayor a Menor)</option>
                </select>
              </div>
            </div>
          </GlowCard>

          <!-- Action buttons -->
          <div v-if="form.entity_name" class="flex justify-between items-center gap-4">
            <button
              type="button"
              @click="runPreview"
              class="inline-flex items-center px-4 py-2.5 bg-dark-elevated hover:bg-dark-elevated/80 border border-dark-border text-slate-200 rounded-xl font-bold transition"
            >
              <font-awesome-icon icon="eye" class="mr-2" />
              Vista Previa
            </button>

            <button
              type="button"
              @click="saveReport"
              class="inline-flex items-center px-5 py-2.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-xl font-bold transition shadow-lg shadow-brand-500/20"
            >
              <font-awesome-icon icon="floppy-disk" class="mr-2" />
              {{ isEdit ? 'Actualizar Plantilla' : 'Guardar Plantilla' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Preview Live Area -->
      <GlowCard v-if="showPreview && form.entity_name" class="border border-dark-border rounded-2xl bg-dark-surface/40 p-6">
        <h2 class="text-md font-bold text-slate-200 mb-4 border-b border-dark-border/60 pb-2">
          Vista Previa de Datos
        </h2>

        <div v-if="loadingPreview" class="flex flex-col items-center justify-center py-16 gap-3 text-slate-400">
          <font-awesome-icon icon="spinner" class="animate-spin text-2xl text-brand-500" />
          <span class="text-xs">Ejecutando consulta...</span>
        </div>

        <div v-else-if="previewData.length > 0" class="overflow-x-auto border border-dark-border rounded-xl">
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

        <div v-else class="text-center py-12 text-slate-500">
          Ningún registro coincide con los filtros aplicados.
        </div>
      </GlowCard>
    </div>
  </AppLayout>
</template>
