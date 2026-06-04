<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import FilterNode from "./FilterNode.vue";
import GlowCard from "@/Components/GlowCard.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal";
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

const migrateFilters = (filters) => {
  if (!filters || (Array.isArray(filters) && filters.length === 0)) {
    return { type: "group", operator: "and", rules: [] };
  }
  if (Array.isArray(filters)) {
    return {
      type: "group",
      operator: "and",
      rules: filters.map((f) => ({ type: "rule", ...f })),
    };
  }
  return filters;
};

const form = useForm({
  name: props.report?.name || "",
  entity_name: props.report?.entity_name || "",
  fields: props.report?.fields || [],
  filters: migrateFilters(props.report?.filters),
  filters_operator: props.report?.filters_operator || "and",
  sort_by: props.report?.sort_by || "",
  sort_order: props.report?.sort_order || "asc",
  group_by: props.report?.group_by || "",
  aggregations: props.report?.aggregations || [],
});

const selectedEntity = ref(null);
const previewData = ref([]);
const previewHeaders = ref([]);
const loadingPreview = ref(false);

const groupedPreviewData = computed(() => {
  if (!form.group_by || previewData.value.length === 0) {
    return null;
  }
  const groups = {};
  previewData.value.forEach((row) => {
    const groupVal = row._group_by_value !== undefined ? row._group_by_value : "Otros";
    if (!groups[groupVal]) {
      groups[groupVal] = [];
    }
    groups[groupVal].push(row);
  });
  return groups;
});

const getAggregationsForHeader = (headerLabel) => {
  if (!form.aggregations) return [];
  return form.aggregations.filter(agg => {
    const fieldMeta = selectedEntity.value?.fields.find(f => f.name === agg.field);
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
      form.filters = { type: "group", operator: "and", rules: [] };
      form.sort_by = "";
      form.group_by = "";
      form.aggregations = [];
      return;
    }
    selectedEntity.value = props.entities.find((e) => e.name === newEntityName);
    
    // Only reset if creating new or switching entity
    if (!props.report || props.report.entity_name !== newEntityName) {
      form.fields = selectedEntity.value?.fields.map((f) => f.name) || [];
      form.filters = { type: "group", operator: "and", rules: [] };
      form.sort_by = "id";
      form.group_by = "";
      form.aggregations = [];
    }
  },
  { immediate: true }
);

const fieldsOptions = computed(() => {
  if (!selectedEntity.value) return [];
  const baseFields = selectedEntity.value.fields.map((f) => ({
    name: f.label || f.name,
    value: f.name,
    type: f.type,
  }));

  const relationFields = [];
  if (selectedEntity.value.relations && Array.isArray(selectedEntity.value.relations)) {
    selectedEntity.value.relations.forEach((rel) => {
      const targetEntity = props.entities.find((e) => e.name === rel.target);
      if (targetEntity && Array.isArray(targetEntity.fields)) {
        targetEntity.fields.forEach((f) => {
          relationFields.push({
            name: `${rel.relation_name || rel.target} → ${f.label || f.name}`,
            value: `${rel.relation_name || rel.target}.${f.name}`,
            type: f.type,
          });
        });
      }
    });
  }

  return [...baseFields, ...relationFields];
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

// Filter form & nested rules editing state
const showFilterForm = ref(false);
const editingRuleNode = ref(null);
const filterField = ref("");
const filterOperator = ref("=");
const filterValue = ref("");

const handleEditRule = (ruleNode) => {
  editingRuleNode.value = ruleNode;
  filterField.value = ruleNode.field;
  filterOperator.value = ruleNode.operator;
  filterValue.value = ruleNode.value;
  showFilterForm.value = true;
};

const saveFilter = () => {
  if (!editingRuleNode.value) return;
  editingRuleNode.value.field = filterField.value;
  editingRuleNode.value.operator = filterOperator.value;
  editingRuleNode.value.value = filterValue.value;
  closeFilterForm();
};

const closeFilterForm = () => {
  showFilterForm.value = false;
  editingRuleNode.value = null;
  filterField.value = "";
  filterOperator.value = "=";
  filterValue.value = "";
};

const toggleFiltersOperator = () => {
  form.filters_operator = form.filters_operator === "and" ? "or" : "and";
};

const addAggregation = () => {
  form.aggregations.push({
    field: fieldsOptions.value[0]?.value || "",
    function: "count",
    label: "",
  });
};

const removeAggregation = (index) => {
  form.aggregations.splice(index, 1);
};

const getFieldType = (fieldName) => {
  if (!fieldName) return "string";
  if (fieldName.includes('.')) {
    const parts = fieldName.split('.');
    const relationName = parts[0];
    const actualFieldName = parts[1];
    const rel = selectedEntity.value?.relations?.find((r) => (r.relation_name || r.target) === relationName);
    if (rel) {
      const targetEntity = props.entities.find((e) => e.name === rel.target);
      const f = targetEntity?.fields?.find((field) => field.name === actualFieldName);
      if (f) return f.type;
    }
    return "string";
  }
  const f = selectedEntity.value?.fields.find((field) => field.name === fieldName);
  return f ? f.type : "string";
};

const runPreview = async () => {
  if (!form.entity_name || form.fields.length === 0) {
    previewData.value = [];
    previewHeaders.value = [];
    return;
  }

  loadingPreview.value = true;
  previewData.value = [];
  previewHeaders.value = [];

  try {
    const response = await axios.post(route("reportes.live_preview"), form.data());

    if (response.data && response.data.ok) {
      previewData.value = response.data.data.map((row, idx) => ({
        ...row,
        _row_key: row.id !== undefined ? `row-${row.id}` : `row-idx-${idx}-${JSON.stringify(row)}`
      }));
      previewHeaders.value = response.data.headers;
    }
  } catch (err) {
    console.error("Error en Vista Previa:", err);
    let errorMsg = "Verifique los filtros ingresados.";
    if (err.response?.data) {
      if (err.response.data.errors) {
        errorMsg = Object.values(err.response.data.errors).flat().join(' | ');
      } else if (err.response.data.message) {
        errorMsg = err.response.data.message;
      }
    }
    notify(
      {
        group: "error",
        title: "Error de Vista Previa",
        text: errorMsg,
      },
      3000
    );
  } finally {
    loadingPreview.value = false;
  }
};

// Auto-run preview with debounce when form data changes reactively
let previewTimeout = null;
watch(
  () => [form.entity_name, form.fields, form.filters, form.filters_operator, form.sort_by, form.sort_order, form.group_by, form.aggregations],
  () => {
    if (previewTimeout) clearTimeout(previewTimeout);
    previewTimeout = setTimeout(() => {
      runPreview();
    }, 400);
  },
  { deep: true }
);

onMounted(() => {
  if (form.entity_name && form.fields.length > 0) {
    runPreview();
  }
});

// Drag and drop for selected columns
const orderedFields = ref([]);

const syncFieldsOrder = () => {
  const selected = new Set(form.fields);
  form.fields = orderedFields.value.filter((fName) => selected.has(fName));
};

watch(
  () => selectedEntity.value,
  (entity) => {
    if (!entity) {
      orderedFields.value = [];
      return;
    }
    const allEntityFields = entity.fields.map((f) => f.name);
    const currentSelected = [...form.fields];
    const remaining = allEntityFields.filter((fName) => !currentSelected.includes(fName));
    orderedFields.value = [...currentSelected, ...remaining];
  },
  { immediate: true }
);

const moveFieldUp = (index) => {
  if (index === 0) return;
  const temp = orderedFields.value[index];
  orderedFields.value[index] = orderedFields.value[index - 1];
  orderedFields.value[index - 1] = temp;
  syncFieldsOrder();
};

const moveFieldDown = (index) => {
  if (index === orderedFields.value.length - 1) return;
  const temp = orderedFields.value[index];
  orderedFields.value[index] = orderedFields.value[index + 1];
  orderedFields.value[index + 1] = temp;
  syncFieldsOrder();
};

const draggedFieldIndex = ref(null);

const onDragStartColumn = (index) => {
  draggedFieldIndex.value = index;
};

const onDragOverColumn = (e) => {
  e.preventDefault();
};

const onDropColumn = (targetIndex) => {
  if (draggedFieldIndex.value === null) return;
  const draggedItem = orderedFields.value[draggedFieldIndex.value];
  orderedFields.value.splice(draggedFieldIndex.value, 1);
  orderedFields.value.splice(targetIndex, 0, draggedItem);
  draggedFieldIndex.value = null;
  syncFieldsOrder();
};

const toggleFieldSelection = (fieldName) => {
  if (form.fields.includes(fieldName)) {
    form.fields = form.fields.filter((f) => f !== fieldName);
  } else {
    form.fields.push(fieldName);
  }
  syncFieldsOrder();
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

const showDeleteModal = ref(false);

const confirmDelete = () => {
  showDeleteModal.value = true;
};

const deleteReport = () => {
  router.delete(route("reportes.destroy", props.report.id), {
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
      router.visit(route("reportes.index"));
    }
  });
};
</script>

<template>
  <AppLayout :title="isEdit ? 'Editar Plantilla de Reporte' : 'Nueva Plantilla de Reporte'" :mainScrollable="false">
    <div class="w-full h-full flex flex-col bg-transparent overflow-hidden">
      
      <!-- Header -->
      <div class="px-6 py-4 border-b border-dark-border flex items-center justify-between shrink-0 bg-dark-surface/20">
        <div>
          <h2 class="text-lg font-bold text-slate-100">
            {{ isEdit ? 'Editar Plantilla' : 'Constructor de Reportes' }}
          </h2>
          <p class="text-xs text-slate-400 mt-1">
            Define la estructura de columnas, los criterios de filtrado y el orden predeterminado.
          </p>
        </div>
        <div class="flex gap-2">
          <JetDangerButton v-if="isEdit" @click="confirmDelete" :disabled="form.processing">
            <font-awesome-icon icon="trash" class="mr-2" />
            Eliminar
          </JetDangerButton>
        </div>
      </div>

      <!-- Split Panel: Live Preview (Left) and Sidebar Config (Right) -->
      <div class="flex-1 flex flex-row min-h-0 overflow-hidden">
        <!-- Left Side: Live Preview Area -->
        <div class="flex-1 flex flex-col p-6 min-h-0 overflow-y-auto custom-scrollbar text-left">
          <h3 class="text-md font-bold text-slate-200 mb-4 border-b border-dark-border pb-2">
            Vista Previa de Datos
          </h3>

          <!-- Not configured state -->
          <div v-if="!form.entity_name || form.fields.length === 0" class="flex-1 flex flex-col items-center justify-center text-slate-500 text-sm italic">
            <font-awesome-icon icon="eye" class="text-4xl text-dark-border mb-3" />
            Seleccione una entidad y al menos una columna en el panel derecho para visualizar la vista previa en vivo.
          </div>

          <!-- Running preview loading state -->
          <div v-else-if="loadingPreview && previewData.length === 0" class="flex-1 flex flex-col items-center justify-center py-16 gap-3 text-slate-400">
            <font-awesome-icon icon="spinner" class="animate-spin text-2xl text-brand-500" />
            <span class="text-xs">Ejecutando consulta...</span>
          </div>

          <!-- Table or empty results -->
          <div v-else class="flex-1 min-h-0 flex flex-col">
            <!-- Grouped Visual Sections Layout -->
            <div v-if="groupedPreviewData" class="space-y-6">
              <div v-for="(rows, groupName) in groupedPreviewData" :key="groupName" class="mb-6">
                <!-- Group Header / Section Title -->
                <div class="flex items-center gap-2 mb-2 bg-dark-elevated/20 p-2.5 rounded-lg border border-dark-border/40">
                  <div class="h-2 w-2 rounded-full bg-brand-500 animate-pulse"></div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">
                    {{ rows[0]?._group_by_label || 'Grupo' }}: <span class="text-brand-400 font-semibold">{{ groupName }}</span>
                  </h4>
                  <span class="text-[10px] text-slate-500 font-semibold ml-auto">
                    {{ rows.length }} registros
                  </span>
                </div>
                
                <!-- Table Container -->
                <div class="border border-dark-border rounded-xl overflow-hidden bg-dark-surface/10">
                  <table class="min-w-full divide-y divide-dark-border/40 text-left">
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
                    <tfoot v-if="form.aggregations && form.aggregations.length > 0" class="bg-dark-elevated/40 border-t border-dark-border/60">
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

            <!-- Standard Flat Layout (when not grouped) -->
            <div v-else-if="previewData.length > 0" class="border border-dark-border rounded-xl flex-1 flex flex-col overflow-hidden bg-dark-surface/10">
              <div class="overflow-auto flex-1 custom-scrollbar">
                <table class="min-w-full divide-y divide-dark-border/40 text-left">
                  <thead class="bg-dark-elevated/40 sticky top-0 z-10">
                    <TransitionGroup tag="tr" name="table-header">
                      <th
                        v-for="(header, hIdx) in previewHeaders"
                        :key="header"
                        class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-dark-border/60 bg-dark-elevated transition-all duration-300"
                        :class="{
                          'rounded-tl-xl': hIdx === 0,
                          'rounded-tr-xl': hIdx === previewHeaders.length - 1
                        }"
                      >
                        {{ header }}
                      </th>
                    </TransitionGroup>
                  </thead>
                  <TransitionGroup tag="tbody" name="table-row" class="divide-y divide-dark-border/20">
                    <tr
                      v-for="row in previewData"
                      :key="row._row_key"
                      class="hover:bg-dark-elevated/20 transition-all duration-300"
                    >
                      <td
                        v-for="header in previewHeaders"
                        :key="header"
                        class="px-4 py-3.5 text-sm text-slate-300 font-mono"
                      >
                        {{ row[header] }}
                      </td>
                    </tr>
                  </TransitionGroup>
                </table>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="flex-1 flex items-center justify-center py-12 text-slate-500 italic">
            Ningún registro coincide con los filtros aplicados.
          </div>
        </div>
        </div>

        <!-- Right Side: Sidebar Config Panel -->
        <div style="background-color: rgb(var(--color-bg-surface)) !important; z-index: 10;" class="relative w-80 border-l border-dark-border h-full flex flex-col shrink-0 text-left overflow-y-auto custom-scrollbar p-6 space-y-6">
          
          <!-- 1. General -->
          <div class="space-y-4">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-2">
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
          </div>

          <hr class="border-dark-border" />

          <!-- 2. Seleccionar Columnas -->
          <div v-if="form.entity_name" class="space-y-4">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-2 flex justify-between items-center">
              <span>2. Columnas</span>
              <span class="text-[10px] text-brand-400 font-semibold bg-brand-500/10 px-2 py-0.5 rounded border border-brand-500/10">
                {{ form.fields.length }}
              </span>
            </h2>
            
            <div class="space-y-2">
              <label class="text-[10px] uppercase font-bold tracking-wider text-slate-500 block mb-1">Configurar</label>
              <TransitionGroup name="list-fields" tag="div" class="space-y-2 block relative max-h-[380px] overflow-y-auto pr-1 custom-scrollbar">
                <div
                  v-for="(fName, idx) in orderedFields"
                  :key="fName"
                  class="border p-2.5 rounded-xl cursor-grab active:cursor-grabbing select-none transition flex items-center justify-between"
                  :class="form.fields.includes(fName)
                    ? 'border-dark-border bg-dark-elevated/40 hover:border-brand-500/30'
                    : 'border-dark-border/40 bg-dark-surface/5 border-dashed opacity-50'"
                  draggable="true"
                  @dragstart="onDragStartColumn(idx)"
                  @dragover="onDragOverColumn"
                  @drop.stop="onDropColumn(idx)"
                >
                  <div class="flex items-center gap-2 truncate">
                    <font-awesome-icon icon="bars" class="text-slate-500 text-xs shrink-0 cursor-move" />
                    <span class="text-xs font-semibold text-slate-200 truncate">
                      {{ fieldsOptions.find(o => o.value === fName)?.name || fName }}
                    </span>
                    <span class="text-[10px] text-slate-500 font-mono">({{ fName }})</span>
                  </div>
                  
                  <div class="flex items-center gap-1 shrink-0">
                    <!-- Move Up -->
                    <button
                      type="button"
                      v-if="idx > 0"
                      @click.prevent="moveFieldUp(idx)"
                      class="text-slate-500 hover:text-brand-400 p-1 rounded transition"
                      title="Subir columna"
                    >
                      <font-awesome-icon icon="arrow-up" class="text-xs" />
                    </button>

                    <!-- Move Down -->
                    <button
                      type="button"
                      v-if="idx < orderedFields.length - 1"
                      @click.prevent="moveFieldDown(idx)"
                      class="text-slate-500 hover:text-brand-400 p-1 rounded transition"
                      title="Bajar columna"
                    >
                      <font-awesome-icon icon="arrow-down" class="text-xs" />
                    </button>

                    <!-- Toggle Visibility (Show/Hide) -->
                    <button
                      type="button"
                      @click.prevent="toggleFieldSelection(fName)"
                      class="p-1 rounded transition ml-1"
                      :class="form.fields.includes(fName) ? 'text-brand-400 hover:text-brand-300' : 'text-slate-500 hover:text-slate-300'"
                      :title="form.fields.includes(fName) ? 'Ocultar en reporte' : 'Mostrar en reporte'"
                    >
                      <font-awesome-icon :icon="form.fields.includes(fName) ? 'eye' : 'eye-slash'" class="text-xs" />
                    </button>
                  </div>
                </div>
              </TransitionGroup>
            </div>
          </div>

          <hr v-if="form.entity_name" class="border-dark-border" />

          <!-- 3. Filtros Personalizados -->
          <div v-if="form.entity_name" class="space-y-4">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400">
                3. Filtros Personalizados
              </h2>
            </div>

            <!-- Filter Editor Form (Inline) -->
            <div v-if="showFilterForm" class="flex flex-col gap-3 bg-dark-surface/40 border border-brand-500/20 p-4 rounded-xl shadow-lg relative">
              <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1.5 select-none">
                <font-awesome-icon icon="filter" class="text-brand-400" />
                Editar Filtro
              </h4>

              <!-- Field -->
              <div class="w-full">
                <JetLabel class="mb-1">Columna</JetLabel>
                <select
                  v-model="filterField"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                >
                  <option v-for="opt in fieldsOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
                </select>
              </div>

              <!-- Operator -->
              <div class="w-full">
                <JetLabel class="mb-1">Operador</JetLabel>
                <select
                  v-model="filterOperator"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                >
                  <option v-for="op in operators" :key="op.value" :value="op.value">{{ op.name }}</option>
                </select>
              </div>

              <!-- Value -->
              <div class="w-full">
                <JetLabel class="mb-1">Valor</JetLabel>
                <select
                  v-if="getFieldType(filterField) === 'boolean'"
                  v-model="filterValue"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500"
                >
                  <option value="1">Sí (Verdadero)</option>
                  <option value="0">No (Falso)</option>
                </select>
                <div v-else-if="getFieldType(filterField) === 'date'" class="space-y-2">
                  <select
                    :value="['{HOY}', '{AYER}', '{INICIO_MES}', '{FIN_MES}'].includes(filterValue) ? filterValue : 'custom'"
                    @change="(e) => filterValue = e.target.value === 'custom' ? '' : e.target.value"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500"
                  >
                    <option value="custom">Fecha Específica...</option>
                    <option value="{HOY}">Hoy (Día Actual)</option>
                    <option value="{AYER}">Ayer</option>
                    <option value="{INICIO_MES}">Inicio del Mes</option>
                    <option value="{FIN_MES}">Fin del Mes</option>
                  </select>
                  <input
                    v-if="!['{HOY}', '{AYER}', '{INICIO_MES}', '{FIN_MES}'].includes(filterValue)"
                    v-model="filterValue"
                    type="date"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-200 focus:ring-brand-500"
                  />
                </div>
                <div v-else-if="filterField.endsWith('user_id') || filterField === 'id'" class="space-y-2">
                  <select
                    :value="filterValue === '{USUARIO_AUTENTICADO}' ? filterValue : 'custom'"
                    @change="(e) => filterValue = e.target.value === 'custom' ? '' : e.target.value"
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-300 focus:ring-brand-500"
                  >
                    <option value="custom">Valor Específico...</option>
                    <option value="{USUARIO_AUTENTICADO}">Usuario Autenticado (Mí mismo)</option>
                  </select>
                  <input
                    v-if="filterValue !== '{USUARIO_AUTENTICADO}'"
                    v-model="filterValue"
                    type="text"
                    placeholder="Valor..."
                    class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-200 placeholder-slate-500 focus:ring-brand-500"
                    @keyup.enter="saveFilter"
                  />
                </div>
                <input
                  v-else
                  v-model="filterValue"
                  type="text"
                  placeholder="Valor..."
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2 px-3 text-xs text-slate-200 placeholder-slate-500 focus:ring-brand-500"
                  @keyup.enter="saveFilter"
                />
              </div>

              <!-- Form Buttons -->
              <div class="flex justify-end gap-2 mt-2">
                <button
                  type="button"
                  @click="closeFilterForm"
                  class="px-3 py-1.5 bg-dark-elevated hover:bg-dark-surface text-slate-300 border border-dark-border rounded-lg font-bold text-xs uppercase tracking-wider transition duration-200"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  @click="saveFilter"
                  class="px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg font-bold text-xs uppercase tracking-wider transition duration-200"
                >
                  <font-awesome-icon icon="check" class="mr-1" />
                  Guardar
                </button>
              </div>
            </div>

            <!-- Recursive Filters Tree -->
            <div class="space-y-2">
              <FilterNode
                :node="form.filters"
                :fields-options="fieldsOptions"
                :operators="operators"
                :get-field-type="getFieldType"
                :on-edit-rule="handleEditRule"
              />
            </div>
          </div>

          <hr v-if="form.entity_name" class="border-dark-border" />

          <!-- Ordering -->
          <div v-if="form.entity_name" class="space-y-4 mb-6">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-2">
              4. Ordenamiento Predeterminado
            </h2>
            <div class="space-y-4">
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
          </div>

          <hr v-if="form.entity_name" class="border-dark-border" />

          <!-- 5. Agrupación y Métricas -->
          <div v-if="form.entity_name" class="space-y-4 mb-6">
            <h2 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-2">
              5. Agrupación y Métricas (Analítica)
            </h2>
            <div class="space-y-4">
              <div>
                <JetLabel value="Agrupar por" class="mb-1" />
                <select
                  v-model="form.group_by"
                  class="w-full bg-dark-surface border border-dark-border rounded-xl py-2.5 px-3 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                >
                  <option value="">Sin agrupación (Listado plano)</option>
                  <option v-for="opt in fieldsOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
                </select>
              </div>

              <!-- Metric Aggregations list if group_by is active -->
              <div v-if="form.group_by" class="space-y-3">
                <div class="flex justify-between items-center">
                  <JetLabel value="Métricas calculadas" />
                  <button
                    type="button"
                    @click="addAggregation"
                    class="text-[10px] font-bold text-brand-400 hover:text-brand-300 transition"
                  >
                    + Agregar métrica
                  </button>
                </div>

                <div v-if="form.aggregations.length > 0" class="space-y-3">
                  <div
                    v-for="(agg, aIdx) in form.aggregations"
                    :key="aIdx"
                    class="p-3 bg-dark-surface/40 border border-dark-border rounded-xl space-y-2 relative"
                  >
                    <!-- Select field to calculate -->
                    <div>
                      <JetLabel class="text-[10px] mb-1">Columna a calcular</JetLabel>
                      <select
                        v-model="agg.field"
                        class="w-full bg-dark-surface border border-dark-border rounded-xl py-1.5 px-2 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500"
                      >
                        <option v-for="opt in fieldsOptions" :key="opt.value" :value="opt.value">{{ opt.name }}</option>
                      </select>
                    </div>

                    <!-- Select function -->
                    <div>
                      <JetLabel class="text-[10px] mb-1">Función</JetLabel>
                      <select
                        v-model="agg.function"
                        class="w-full bg-dark-surface border border-dark-border rounded-xl py-1.5 px-2 text-xs text-slate-300 focus:ring-brand-500 focus:border-brand-500"
                      >
                        <option value="count">Contar (COUNT)</option>
                        <option value="sum">Suma (SUM)</option>
                        <option value="avg">Promedio (AVG)</option>
                        <option value="min">Mínimo (MIN)</option>
                        <option value="max">Máximo (MAX)</option>
                      </select>
                    </div>

                    <!-- Custom Label/Header -->
                    <div>
                      <JetLabel class="text-[10px] mb-1">Etiqueta de columna</JetLabel>
                      <input
                        v-model="agg.label"
                        type="text"
                        placeholder="Ej. Promedio Final, Total..."
                        class="w-full bg-dark-surface border border-dark-border rounded-xl py-1.5 px-2 text-xs text-slate-200 placeholder-slate-500 focus:ring-brand-500"
                      />
                    </div>

                    <!-- Remove metric -->
                    <div class="flex justify-end">
                      <button
                        type="button"
                        @click="removeAggregation(aIdx)"
                        class="text-[10px] text-red-400 hover:text-red-300 transition"
                      >
                        Quitar métrica
                      </button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-[10px] text-slate-500 italic border border-dashed border-dark-border rounded-xl">
                  Sin métricas. El reporte solo mostrará la columna de agrupación.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Action Buttons -->
      <div class="flex justify-between items-center py-4 px-6 border-t border-dark-border bg-dark-surface/20 shrink-0">
        <div>
          <Link :href="route('reportes.index')">
            <JetSecondaryButton type="button">
              Cancelar
            </JetSecondaryButton>
          </Link>
        </div>
        <div class="flex gap-3">
          <JetButton
            type="button"
            @click="saveReport"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            <font-awesome-icon icon="floppy-disk" class="mr-2" />
            {{ isEdit ? 'Guardar Cambios' : 'Crear Registro' }}
          </JetButton>
        </div>
      </div>
      
    </div>

    <!-- Delete Confirmation Modal -->
    <JetConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>Eliminar Plantilla de Reporte</template>
      <template #content>
        <div class="text-left">
          <p class="text-sm text-slate-300">¿Estás seguro de que deseas eliminar la plantilla de reporte "{{ report?.name }}"?</p>
          <span class="text-red-500 text-xs block mt-1">Esta acción no se puede deshacer.</span>
        </div>
      </template>
      <template #footer>
        <div class="w-1/2 text-left">
          <JetSecondaryButton @click="showDeleteModal = false" class="bg-opacity-95" type="button" :disabled="form.processing">Cancelar</JetSecondaryButton>
        </div>
        <div class="w-1/2">
          <JetDangerButton @click="deleteReport" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="float-right">
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar Registro
          </JetDangerButton>
        </div>
      </template>
    </JetConfirmationModal>
  </AppLayout>
</template>

<style scoped>
/* Animation for sorting and modifying fields list */
.list-fields-move {
  transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}
.list-fields-enter-active,
.list-fields-leave-active {
  transition: all 0.3s ease;
}
.list-fields-enter-from,
.list-fields-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
/* Ensure leaving items are taken out of flow so moving items animate smoothly */
.list-fields-leave-active {
  position: absolute !important;
  width: 100%;
}

/* Animations for Table Live Preview */
.table-row-move {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.table-row-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.table-row-leave-active {
  transition: all 0.25s ease-in;
  /* Avoid absolute position to prevent table layout breakdown, 
     a smooth fade & height shrink works best for standard table elements */
}
.table-row-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.table-row-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.table-header-move {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.table-header-enter-active,
.table-header-leave-active {
  transition: all 0.3s ease;
}
.table-header-enter-from,
.table-header-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
