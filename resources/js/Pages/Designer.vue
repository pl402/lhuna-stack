<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import { VueFlow, Handle, useVueFlow } from "@vue-flow/core";
import { Controls } from "@vue-flow/controls";
import { notify } from "notiwind";
import GlowCard from "@/Components/GlowCard.vue";
import JetDialogModal from "@/Jetstream/DialogModal.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import Select from "@/Components/Select.vue";
import Toggle from "@/Components/Toggle.vue";

// Styles
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import '@vue-flow/controls/dist/style.css';

const props = defineProps({
  entities: Array,
  migrationsList: {
    type: Array,
    default: () => []
  }
});

const nodes = ref([]);
const edges = ref([]);

// Schema state
const selectedNode = ref(null);
const isDrawerOpen = ref(false);
const showRelationModal = ref(false);

// Local state for expanded drawer fields
const expandedFields = ref({});

watch(selectedNode, () => {
  expandedFields.value = {};
});

// UI Layout Designer state
const activeTab = ref('db'); // 'db' or 'ui'
const selectedUiEntityId = ref(null);

const selectedUiEntity = computed(() => {
  if (!selectedUiEntityId.value) return null;
  return nodes.value.find(n => n.id === selectedUiEntityId.value) || null;
});

// Watch selected UI entity ID and active tab to initialize/sync layout without recursive loops
watch(selectedUiEntityId, (newId) => {
  if (newId) {
    const node = nodes.value.find(n => n.id === newId);
    if (node) {
      initLayoutForNode(node);
    }
  }
});

watch(activeTab, (newTab) => {
  if (newTab === 'ui' && selectedUiEntityId.value) {
    const node = nodes.value.find(n => n.id === selectedUiEntityId.value);
    if (node) {
      initLayoutForNode(node);
    }
  }
});

const getAvailableUiElements = (node) => {
  if (!node) return [];
  const dbFields = node.data.fields.filter(f => f.name !== 'id').map(f => f.name);
  const belongsToMany = (node.data.relations || [])
    .filter(r => r.type === 'belongsToMany')
    .map(r => r.relation_name || (r.target.toLowerCase() + 's'));
  return [...dbFields, ...belongsToMany];
};

const getElementName = (fName) => {
  if (!selectedUiEntity.value) return fName;
  const field = selectedUiEntity.value.data.fields.find(f => f.name === fName);
  if (field) return field.label || field.name;
  
  const rel = selectedUiEntity.value.data.relations.find(r => r.relation_name === fName);
  if (rel) return `Asociar ${rel.relation_name || rel.target} (Relación)`;
  
  return fName;
};

const getElementType = (fName) => {
  if (!selectedUiEntity.value) return '';
  const field = selectedUiEntity.value.data.fields.find(f => f.name === fName);
  if (field) return field.type;
  
  const rel = selectedUiEntity.value.data.relations.find(r => r.relation_name === fName);
  if (rel) return 'muchos a muchos';
  
  return '';
};

const initLayoutForNode = (node) => {
  const elements = getAvailableUiElements(node);
  if (!node.data.ui_layout) {
    node.data.ui_layout = {
      sections: [
        {
          id: 'sec-' + Math.random().toString(36).substring(2, 9),
          title: 'Datos Generales',
          columns: 2,
          fields: elements
        }
      ]
    };
  } else {
    // Sync fields from DB to layout
    const layoutFields = [];
    node.data.ui_layout.sections.forEach(s => {
      layoutFields.push(...s.fields);
    });

    let hasChanges = false;

    // Add missing
    elements.forEach(name => {
      if (!layoutFields.includes(name)) {
        if (node.data.ui_layout.sections.length > 0) {
          node.data.ui_layout.sections[0].fields.push(name);
          hasChanges = true;
        }
      }
    });

    // Remove deleted
    node.data.ui_layout.sections.forEach(s => {
      const filtered = s.fields.filter(name => elements.includes(name));
      if (filtered.length !== s.fields.length) {
        s.fields = filtered;
        hasChanges = true;
      }
    });

    if (hasChanges) {
      isDirty.value = true;
    }
  }
};

const onDropOnUnassigned = () => {
  if (!draggedFieldName.value || !selectedUiEntity.value) return;
  const layout = selectedUiEntity.value.data.ui_layout;
  if (!layout) return;

  const fieldName = draggedFieldName.value;
  const sourceSecId = draggedSourceSectionId.value;

  // Remove from source section
  if (sourceSecId !== 'unassigned') {
    const srcSec = layout.sections.find(s => s.id === sourceSecId);
    if (srcSec) {
      srcSec.fields = srcSec.fields.filter(f => f !== fieldName);
      isDirty.value = true;
    }
  }

  draggedFieldName.value = null;
  draggedSourceSectionId.value = null;
};

// Drag and drop fields
const draggedFieldName = ref(null);
const draggedSourceSectionId = ref(null);

const onDragStartField = (fieldName, sourceSectionId) => {
  draggedFieldName.value = fieldName;
  draggedSourceSectionId.value = sourceSectionId;
};

const onDragOverField = (e) => {
  e.preventDefault();
};

const onDropField = (targetSectionId, targetIndex = null) => {
  if (!draggedFieldName.value || !selectedUiEntity.value) return;
  const layout = selectedUiEntity.value.data.ui_layout;
  if (!layout) return;

  const fieldName = draggedFieldName.value;
  const sourceSecId = draggedSourceSectionId.value;

  // Remove from source
  if (sourceSecId !== 'unassigned') {
    const srcSec = layout.sections.find(s => s.id === sourceSecId);
    if (srcSec) {
      srcSec.fields = srcSec.fields.filter(f => f !== fieldName);
    }
  }

  // Add to target
  const tgtSec = layout.sections.find(s => s.id === targetSectionId);
  if (tgtSec) {
    tgtSec.fields = tgtSec.fields.filter(f => f !== fieldName);
    if (targetIndex !== null) {
      tgtSec.fields.splice(targetIndex, 0, fieldName);
    } else {
      tgtSec.fields.push(fieldName);
    }
  }

  draggedFieldName.value = null;
  draggedSourceSectionId.value = null;
  isDirty.value = true;
};

// Drag and drop sections
const draggedSectionId = ref(null);
const onDragStartSection = (secId) => {
  draggedSectionId.value = secId;
};
const onDropSection = (targetSecId) => {
  if (!draggedSectionId.value || !selectedUiEntity.value) return;
  const layout = selectedUiEntity.value.data.ui_layout;
  if (!layout) return;

  const srcIdx = layout.sections.findIndex(s => s.id === draggedSectionId.value);
  const tgtIdx = layout.sections.findIndex(s => s.id === targetSecId);

  if (srcIdx > -1 && tgtIdx > -1 && srcIdx !== tgtIdx) {
    const temp = layout.sections[srcIdx];
    layout.sections.splice(srcIdx, 1);
    layout.sections.splice(tgtIdx, 0, temp);
    isDirty.value = true;
  }
  draggedSectionId.value = null;
};

const addSection = () => {
  if (!selectedUiEntity.value) return;
  if (!selectedUiEntity.value.data.ui_layout) {
    initLayoutForNode(selectedUiEntity.value);
  }
  selectedUiEntity.value.data.ui_layout.sections.push({
    id: 'sec-' + Math.random().toString(36).substring(2, 9),
    title: 'Nueva Sección',
    columns: 2,
    fields: []
  });
  isDirty.value = true;
};

const removeSection = (secId) => {
  if (!selectedUiEntity.value) return;
  const layout = selectedUiEntity.value.data.ui_layout;
  if (!layout) return;

  const sec = layout.sections.find(s => s.id === secId);
  if (sec) {
    const fieldsToMove = sec.fields;
    layout.sections = layout.sections.filter(s => s.id !== secId);
    if (layout.sections.length > 0) {
      layout.sections[0].fields.push(...fieldsToMove);
    }
    isDirty.value = true;
  }
};

const unassignedFields = computed(() => {
  if (!selectedUiEntity.value) return [];
  const layout = selectedUiEntity.value.data.ui_layout;
  const elements = getAvailableUiElements(selectedUiEntity.value);
  if (!layout) return elements;

  const layoutFields = [];
  layout.sections.forEach(s => {
    layoutFields.push(...s.fields);
  });

  return elements.filter(name => !layoutFields.includes(name));
});

// Migrations list state
const showMigrationsPanel = ref(false);
const pendingCount = computed(() => {
  return props.migrationsList.filter(m => m.status === 'pending').length;
});

// Auto-expand migrations panel if there are pending migrations
watch(() => props.migrationsList, (newList) => {
  const hasPending = newList.some(m => m.status === 'pending');
  if (hasPending) {
    showMigrationsPanel.value = true;
  }
}, { immediate: true, deep: true });

const cleanMigrationName = (name) => {
  return name.replace(/^\d{4}_\d{2}_\d{2}_\d{6}_/, "");
};

// Modal connection data
const connectionData = ref({
  sourceId: '',
  sourceName: '',
  targetId: '',
  targetName: '',
  type: 'belongsTo',
  foreignKey: '',
  relationName: '',
  generate_assignment_ui: false
});

// Load schema into Vue Flow
const loadSchema = () => {
  if (!props.entities) return;

  const currentNodes = nodes.value || [];

  nodes.value = props.entities.map((entity, index) => {
    const x = entity.position?.x ?? (100 + (index * 250) % 600);
    const y = entity.position?.y ?? (100 + Math.floor(index / 3) * 200);

    const currentNode = currentNodes.find(n => n.id === entity.id);
    const currentFields = currentNode?.data?.fields || [];

    return {
      id: entity.id,
      type: 'entity',
      position: { x, y },
      data: {
        name: entity.name,
        table: entity.table,
        plural_label: entity.plural_label || entity.name + 's',
        icon: entity.icon || 'table',
        fields: (entity.fields || []).map(f => {
          const existingField = currentFields.find(cf => cf.name === f.name);
          const cloned = { ...f };
          if (cloned.name === 'id') {
            cloned.required = true;
            cloned.unique = true;
          }
          cloned._id = cloned._id || existingField?._id || 'field-' + Math.random().toString(36).substring(2, 9);
          return cloned;
        }),
        relations: (entity.relations || []).map(r => ({ ...r })),
        is_system: entity.is_system || false,
        ui_layout: entity.ui_layout || null
      }
    };
  });

  // Load edges from relationships
  const newEdges = [];
  props.entities.forEach(entity => {
    const relations = entity.relations || [];
    relations.forEach((rel, rIdx) => {
      const targetEntity = props.entities.find(e => e.name === rel.target);
      if (targetEntity) {
        newEdges.push({
          id: `e-${entity.id}-${targetEntity.id}-${rel.type}-${rIdx}`,
          source: entity.id,
          target: targetEntity.id,
          label: `${rel.type} (${rel.foreign_key})`,
          animated: true,
          style: { stroke: 'var(--color-brand-500)', strokeWidth: 2 },
          labelStyle: { fontSize: '9px', fontWeight: 'bold' }
        });
      }
    });
  });
  edges.value = newEdges;
};

// Dirty / Unpublished state tracking
const isDirty = ref(false);
const isUnpublished = ref(false);
const schemaReady = ref(false);

onMounted(() => {
  loadSchema();
  // Mark schema as ready on next tick so initial load doesn't trigger dirty
  setTimeout(() => { schemaReady.value = true; }, 300);
});

// Watch semantic content (data only, not positions) to detect unsaved changes
watch(
  // Filter out temporary client fields like expanded state and _id when checking changes
  () => nodes.value.map(n => JSON.stringify({ id: n.id, data: { ...n.data, fields: n.data.fields.map(f => ({ ...f, _id: undefined })) } })).join('||'),
  () => { if (schemaReady.value) isDirty.value = true; }
);
watch(
  () => edges.value.map(e => e.id).join(','),
  () => { if (schemaReady.value) isDirty.value = true; }
);

// Watch entities from backend to reload schema if modified elsewhere
watch(() => props.entities, () => {
  if (!isDirty.value) {
    loadSchema();
  }
}, { deep: true });

// Watch relation type changes to set correct foreign key and relation name defaults
watch(() => connectionData.value.type, (newType) => {
  const sourceName = connectionData.value.sourceName.toLowerCase();
  const targetName = connectionData.value.targetName.toLowerCase();
  
  if (newType === 'belongsTo') {
    connectionData.value.foreignKey = `${targetName}_id`;
    connectionData.value.relationName = targetName;
  } else if (newType === 'hasMany') {
    connectionData.value.foreignKey = `${sourceName}_id`;
    connectionData.value.relationName = targetName + 's';
  } else if (newType === 'belongsToMany') {
    connectionData.value.foreignKey = '';
    connectionData.value.relationName = targetName + 's';
  }
});

// Vue Flow events
const onConnect = (params) => {
  const sourceNode = nodes.value.find(n => n.id === params.source);
  const targetNode = nodes.value.find(n => n.id === params.target);

  if (sourceNode && targetNode) {
    connectionData.value = {
      sourceId: params.source,
      sourceName: sourceNode.data.name,
      targetId: params.target,
      targetName: targetNode.data.name,
      type: 'belongsTo',
      foreignKey: `${targetNode.data.name.toLowerCase()}_id`,
      relationName: targetNode.data.name.toLowerCase(),
      generate_assignment_ui: false
    };
    showRelationModal.value = true;
  }
};

const confirmRelation = () => {
  const data = connectionData.value;
  if (!data) return;

  const sourceNode = nodes.value.find(n => n.id === data.sourceId);
  const targetNode = nodes.value.find(n => n.id === data.targetId);

  if (sourceNode && targetNode) {
    if (!sourceNode.data.relations) sourceNode.data.relations = [];

    sourceNode.data.relations.push({
      type: data.type,
      target: data.targetName,
      foreign_key: data.foreignKey,
      relation_name: data.relationName,
      generate_assignment_ui: data.generate_assignment_ui || false
    });

    // Auto-add foreignId field to source if belongsTo
    if (data.type === 'belongsTo') {
      const hasField = sourceNode.data.fields.some(f => f.name === data.foreignKey);
      if (!hasField) {
        sourceNode.data.fields.push({
          name: data.foreignKey,
          type: 'foreignId',
          label: data.targetName,
          required: true,
          relation_target: targetNode.data.table,
          input_type: 'select',
          show_in_table: true,
          searchable: false,
          sortable: true
        });
      }
    }

    // Auto-add foreignId field to target if hasMany
    if (data.type === 'hasMany') {
      const hasField = targetNode.data.fields.some(f => f.name === data.foreignKey);
      if (!hasField) {
        targetNode.data.fields.push({
          name: data.foreignKey,
          type: 'foreignId',
          label: sourceNode.data.name,
          required: true,
          relation_target: sourceNode.data.table,
          input_type: 'select',
          show_in_table: true,
          searchable: false,
          sortable: true
        });
      }
    }

    // Rebuild edges
    edges.value.push({
      id: `e-${data.sourceId}-${data.targetId}-${data.type}-${Date.now()}`,
      source: data.sourceId,
      target: data.targetId,
      label: `${data.type} (${data.foreignKey})`,
      animated: true,
      style: { stroke: 'var(--color-brand-500)', strokeWidth: 2 },
      labelStyle: { fontSize: '9px', fontWeight: 'bold' }
    });

    notify({ group: "main", title: "Relación Creada", text: "La relación ha sido registrada en el esquema" }, 3000);
  }

  showRelationModal.value = false;
};

// Toolbar actions
const addEntity = () => {
  const nextId = String(Date.now());
  const newName = `Entity_${nodes.value.length + 1}`;
  const newTable = newName.toLowerCase() + 's';

  const newN = {
    id: nextId,
    type: 'entity',
    position: { x: 200 + Math.random() * 100, y: 150 + Math.random() * 100 },
    data: {
      name: newName,
      table: newTable,
      plural_label: newName + 's',
      icon: 'table',
      fields: [
        { name: 'id', type: 'id', label: 'ID', required: true, unique: true, show_in_table: true, searchable: false, sortable: true }
      ],
      relations: [],
      is_system: false
    }
  };

  nodes.value.push(newN);
  selectedNode.value = newN;
  isDrawerOpen.value = true;
};

const serializeSchema = () => {
  return nodes.value.map(node => ({
    id: node.id,
    name: node.data.name,
    table: node.data.table,
    plural_label: node.data.plural_label,
    icon: node.data.icon,
    position: { x: node.position.x, y: node.position.y },
    fields: (node.data.fields || []).map(f => {
      const { _id, ...rest } = f;
      return rest;
    }),
    relations: node.data.relations,
    is_system: node.data.is_system || false,
    ui_layout: node.data.ui_layout || null
  }));
};

// Guardar esquema
const saving = ref(false);
const saveSchema = async () => {
  saving.value = true;
  try {
    await axios.post(route('designer.save'), { entities: serializeSchema() }, {
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
    });
    isDirty.value = false;
    isUnpublished.value = true;
    notify({ group: "main", title: "Diseño Guardado", text: "El diagrama se ha guardado correctamente." }, 3000);
  } catch (e) {
    notify({ group: "main", title: "Error al guardar", text: e?.response?.data?.message || e.message }, 4000);
  } finally {
    saving.value = false;
  }
};

// Publicar sistema
const showPublishModal = ref(false);
const publishDone = ref(false);
const publishSteps = ref([
  { id: 'save',     label: 'Guardar esquema',    desc: 'Persistiendo el diseño en entities.json…',       status: 'idle' },
  { id: 'generate', label: 'Generar archivos',   desc: 'Creando modelos, controladores, vistas y migraciones…', status: 'idle' },
  { id: 'migrate',  label: 'Aplicar migraciones', desc: 'Ejecutando php artisan migrate…',                status: 'idle' },
]);
const publishError = ref(null);

const setStep = (id, status, error = null) => {
  const s = publishSteps.value.find(s => s.id === id);
  if (s) { s.status = status; if (error) s.error = error; }
};

const publishSystem = async () => {
  publishSteps.value.forEach(s => { s.status = 'idle'; delete s.error; });
  publishError.value = null;
  publishDone.value = false;
  showPublishModal.value = true;

  const entities = serializeSchema();

  setStep('save', 'loading');
  try {
    await axios.post(route('designer.save'), { entities }, {
      headers: { 'X-Inertia': 'true', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
    });
    setStep('save', 'done');
  } catch (e) {
    setStep('save', 'error', e?.response?.data?.message || e.message);
    publishError.value = 'save';
    return;
  }

  setStep('generate', 'loading');
  try {
    await axios.post(route('designer.generate'), { entities }, {
      headers: { 'X-Inertia': 'true', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
    });
    setStep('generate', 'done');
  } catch (e) {
    setStep('generate', 'error', e?.response?.data?.message || e.message);
    publishError.value = 'generate';
    return;
  }

  setStep('migrate', 'loading');
  try {
    await axios.post(route('designer.migrate'), {}, {
      headers: { 'X-Inertia': 'true', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }
    });
    setStep('migrate', 'done');
  } catch (e) {
    setStep('migrate', 'error', e?.response?.data?.message || e.message);
    publishError.value = 'migrate';
    return;
  }

  publishDone.value = true;
  isDirty.value = false;
  isUnpublished.value = false;
  setTimeout(() => router.reload({ only: ['migrationsList'] }), 800);
};

const closePublishModal = () => {
  if (publishDone.value || publishError.value) {
    showPublishModal.value = false;
  }
};

// Node selection
const selectNode = (event) => {
  selectedNode.value = event.node;
  isDrawerOpen.value = true;
};

// Drawer field editing
const addField = () => {
  if (!selectedNode.value) return;
  const newIndex = selectedNode.value.data.fields.length;
  selectedNode.value.data.fields.push({
    _id: 'field-' + Math.random().toString(36).substring(2, 9),
    name: 'nuevo_campo',
    type: 'string',
    label: 'Nuevo Campo',
    required: false,
    unique: false,
    default: '',
    input_type: 'text',
    show_in_table: true,
    searchable: true,
    sortable: true
  });
  expandedFields.value[newIndex] = true;
};

const removeField = (index) => {
  if (!selectedNode.value) return;
  const f = selectedNode.value.data.fields[index];
  if (f.name === 'id') return;
  selectedNode.value.data.fields.splice(index, 1);
};

const moveFieldUp = (index) => {
  if (!selectedNode.value || index === 0) return;
  const fields = selectedNode.value.data.fields;
  if (fields[0].name === 'id' && index === 1) return;
  
  const temp = fields[index];
  fields.splice(index, 1);
  fields.splice(index - 1, 0, temp);
  
  const wasExpandedThis = expandedFields.value[index];
  const wasExpandedPrev = expandedFields.value[index - 1];
  expandedFields.value[index] = wasExpandedPrev;
  expandedFields.value[index - 1] = wasExpandedThis;
};

const moveFieldDown = (index) => {
  if (!selectedNode.value) return;
  const fields = selectedNode.value.data.fields;
  if (index === fields.length - 1) return;
  if (fields[index].name === 'id') return;
  
  const temp = fields[index];
  fields.splice(index, 1);
  fields.splice(index + 1, 0, temp);
  
  const wasExpandedThis = expandedFields.value[index];
  const wasExpandedNext = expandedFields.value[index + 1];
  expandedFields.value[index] = wasExpandedNext;
  expandedFields.value[index + 1] = wasExpandedThis;
};

const removeRelation = (index) => {
  if (!selectedNode.value) return;
  const rel = selectedNode.value.data.relations[index];
  selectedNode.value.data.relations.splice(index, 1);
  edges.value = edges.value.filter(e => !(e.source === selectedNode.value.id && e.label.includes(rel.foreign_key)));
};

const deleteEntity = () => {
  if (!selectedNode.value) return;
  const idToDelete = selectedNode.value.id;
  edges.value = edges.value.filter(e => e.source !== idToDelete && e.target !== idToDelete);
  nodes.value = nodes.value.filter(n => n.id !== idToDelete);
  isDrawerOpen.value = false;
  selectedNode.value = null;
  notify({ group: "main", title: "Entidad Eliminada", text: "La entidad y sus relaciones fueron quitadas del canvas." }, 3000);
};

// Selection options
const fieldTypes = [
  { name: 'ID (Autoincrementable)', value: 'id', disabled: true },
  { name: 'String (Cadena)', value: 'string' },
  { name: 'Text (Texto Largo)', value: 'text' },
  { name: 'Integer (Entero)', value: 'integer' },
  { name: 'Decimal (Moneda/Número)', value: 'decimal' },
  { name: 'Boolean (Sí/No)', value: 'boolean' },
  { name: 'Date (Fecha)', value: 'date' },
  { name: 'Datetime (Fecha y Hora)', value: 'datetime' },
  { name: 'ForeignID (Relación)', value: 'foreignId' }
];

const inputTypes = [
  { name: 'Texto', value: 'text' },
  { name: 'Número', value: 'number' },
  { name: 'Caja Selección (Select)', value: 'select' },
  { name: 'Interruptor (Toggle)', value: 'toggle' },
  { name: 'Área de Texto', value: 'textarea' },
  { name: 'Fecha', value: 'date' }
];

const relationTypes = [
  { name: 'Pertenece a (belongsTo)', value: 'belongsTo' },
  { name: 'Tiene muchos (hasMany)', value: 'hasMany' },
  { name: 'Muchos a muchos (belongsToMany)', value: 'belongsToMany' }
];

const availableIcons = [
  { name: 'Tabla (Estándar)', value: 'table', icon: 'table' },
  { name: 'Usuarios / Contactos', value: 'users', icon: 'users' },
  { name: 'Libros / Educación', value: 'book', icon: 'book' },
  { name: 'Cajas / Inventario', value: 'box', icon: 'box' },
  { name: 'Calendario / Eventos', value: 'calendar', icon: 'calendar' },
  { name: 'Etiquetas / Categorías', value: 'tags', icon: 'tags' },
  { name: 'Ajustes / Engranajes', value: 'gears', icon: 'gears' },
  { name: 'Lista / Tareas', value: 'list-check', icon: 'list-check' },
  { name: 'Estrella / Favoritos', value: 'star', icon: 'star' },
  { name: 'Edificio / Finanzas', value: 'building-columns', icon: 'building-columns' },
  { name: 'Factura / Ventas', value: 'file-invoice-dollar', icon: 'file-invoice-dollar' },
  { name: 'Reloj / Historial', value: 'clock', icon: 'clock' },
  { name: 'Base de datos', value: 'database', icon: 'database' },
  { name: 'Archivo / Reportes', value: 'file', icon: 'file' },
  { name: 'Hogar / Propiedades', value: 'home', icon: 'home' }
];

const showIconPopover = ref(false);

const toggleIconPopover = (event) => {
  showIconPopover.value = !showIconPopover.value;
};

const selectIcon = (value) => {
  if (selectedNode.value) {
    selectedNode.value.data.icon = value;
  }
  showIconPopover.value = false;
};

const handleWindowClick = (event) => {
  if (showIconPopover.value) {
    const popoverContainer = document.querySelector('.icon-popover-container');
    if (popoverContainer && !popoverContainer.contains(event.target)) {
      showIconPopover.value = false;
    }
  }
};

onMounted(() => {
  window.addEventListener('click', handleWindowClick);
});

onUnmounted(() => {
  window.removeEventListener('click', handleWindowClick);
});
</script>

<template>
  <AppLayout title="Diseñador de Entidades" :mainScrollable="false">
    <div class="w-full flex-1 flex flex-col min-h-0 bg-dark-base overflow-hidden transition-colors duration-300">
      
      <!-- Top Tab Navigation Bar -->
      <div class="px-6 py-2 border-b border-dark-border bg-dark-surface/40 flex items-center justify-between shrink-0">
        <div class="flex gap-2">
          <button 
            type="button"
            @click="activeTab = 'db'" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
            :class="activeTab === 'db' ? 'bg-brand-500/10 border border-brand-500/20 text-brand-400' : 'text-slate-400 hover:text-slate-200'"
          >
            <font-awesome-icon icon="database" />
            Diseñador de Base de Datos
          </button>
          <button 
            type="button"
            @click="activeTab = 'ui'" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
            :class="activeTab === 'ui' ? 'bg-brand-500/10 border border-brand-500/20 text-brand-400' : 'text-slate-400 hover:text-slate-200'"
          >
            <font-awesome-icon icon="palette" />
            Diseñador de Interfaces
          </button>
        </div>
      </div>

      <!-- DB Designer Mode -->
      <div v-show="activeTab === 'db'" class="flex-1 flex min-h-0 relative">
        <!-- Flow Canvas Area -->
        <div class="flex-1 h-full min-h-0 relative select-none">
          <VueFlow
            v-model:nodes="nodes"
            v-model:edges="edges"
            @connect="onConnect"
            @node-click="selectNode"
            class="h-full w-full"
            :fit-view-on-init="true"
          >
            <!-- Custom Entity UML Node Template -->
            <template #node-entity="{ data }">
              <div class="border border-dark-border bg-dark-surface/95 backdrop-blur-md rounded-lg shadow-xl shadow-black/40 min-w-[220px] overflow-hidden hover:border-brand-500/50 transition-all duration-300">
                <div class="px-3 py-2 bg-brand-500/10 border-b border-dark-border flex items-center justify-between">
                  <span class="font-bold text-slate-100 flex items-center gap-1.5 text-xs">
                    <font-awesome-icon icon="table" class="text-brand-500 text-xs" />
                    {{ data.name }}
                  </span>
                  <span v-if="data.is_system" class="px-1.5 py-0.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 text-[8px] rounded uppercase font-bold border border-blue-500/20">Sistema</span>
                  <span v-else class="text-[9px] font-mono text-slate-500">{{ data.table }}</span>
                </div>
                <div class="p-2 space-y-1 text-left bg-dark-elevated/45">
                  <div 
                    v-for="field in data.fields" 
                    :key="field.name" 
                    class="flex items-center justify-between text-[11px] py-0.5 border-b border-dark-border/20 last:border-b-0"
                  >
                    <span class="text-slate-300 font-medium">
                      {{ field.name }}
                      <span v-if="field.required" class="text-red-500 font-bold">*</span>
                    </span>
                    <span class="px-1 py-0.2 bg-dark-elevated text-slate-400 text-[8px] rounded font-mono border border-dark-border">
                      {{ field.type }}
                    </span>
                  </div>
                </div>
                <!-- handles for relationships -->
                <Handle type="target" position="left" id="t" class="!bg-brand-500 !w-3 !h-3 !border-dark-surface" />
                <Handle type="source" position="right" id="s" class="!bg-brand-500 !w-3 !h-3 !border-dark-surface" />
              </div>
            </template>

            <Controls />
          </VueFlow>

          <!-- Top Floating Toolbar -->
          <div class="absolute top-4 left-4 z-10 flex gap-2">
            <button 
              type="button"
              @click.prevent="addEntity" 
              class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-xl font-bold transition duration-200 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/40 text-xs gap-2"
            >
              <font-awesome-icon icon="plus" />
              Nueva Entidad
            </button>

            <!-- Guardar: lightweight, solo persiste JSON -->
            <button 
              type="button"
              @click.prevent="saveSchema" 
              :disabled="saving"
              class="relative inline-flex items-center justify-center px-4 py-2.5 bg-dark-elevated hover:bg-dark-surface text-slate-200 hover:text-slate-100 rounded-xl font-bold transition duration-200 text-xs gap-2 disabled:opacity-50 border border-dark-border"
              :class="isDirty ? 'border-amber-500/60 shadow-sm shadow-amber-500/20' : 'border-dark-border'"
              :title="isDirty ? 'Hay cambios sin guardar' : 'Solo guarda el diagrama sin generar archivos'"
            >
              <font-awesome-icon :icon="saving ? 'spinner' : 'floppy-disk'" :class="{ 'animate-spin': saving }" />
              {{ saving ? 'Guardando...' : 'Guardar' }}
            </button>

            <!-- Publicar: guarda + genera + migra -->
            <button 
              type="button"
              @click.prevent="publishSystem" 
              :disabled="showPublishModal && !publishDone && !publishError"
              class="relative inline-flex items-center justify-center px-5 py-2.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-xl font-bold transition duration-200 shadow-lg text-xs gap-2 disabled:opacity-60"
              :class="isDirty || isUnpublished ? 'shadow-amber-500/30 ring-1 ring-amber-400/40' : 'shadow-brand-500/25 hover:shadow-brand-500/40'"
              :title="isDirty ? 'Hay cambios sin guardar y sin publicar' : isUnpublished ? 'Cambios guardados pero no publicados al sistema' : 'Guarda, genera todos los archivos y aplica las migraciones'"
            >
              <font-awesome-icon icon="rocket" class="text-white/90" />
              Publicar Sistema
            </button>
          </div>

          <!-- Top Right Floating Status Badge -->
          <div class="absolute top-4 right-4 z-10 flex items-center gap-2 select-none">
            <div 
              class="px-3 py-2 rounded-xl border backdrop-blur-md text-xs font-bold transition flex items-center gap-2"
              :class="[
                isDirty 
                  ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' 
                  : (isUnpublished || pendingCount > 0)
                    ? 'bg-orange-500/10 border-orange-500/20 text-orange-400'
                    : 'bg-green-500/10 border-green-500/20 text-green-400'
              ]"
            >
              <span class="flex h-2 w-2 relative">
                <span 
                  v-if="isDirty || isUnpublished || pendingCount > 0"
                  class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                  :class="isDirty ? 'bg-amber-400' : 'bg-orange-400'"
                ></span>
                <span 
                  class="relative inline-flex rounded-full h-2 w-2"
                  :class="isDirty ? 'bg-amber-500' : (isUnpublished || pendingCount > 0) ? 'bg-orange-500' : 'bg-green-500'"
                ></span>
              </span>
              <span>
                {{ 
                  isDirty 
                    ? 'Diseño con cambios sin guardar' 
                    : (isUnpublished || pendingCount > 0)
                      ? 'Esquema pendiente de Publicar'
                      : 'Sistema Sincronizado'
                }}
              </span>
            </div>
          </div>

          <!-- Publish Progress Modal -->
          <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div 
              v-if="showPublishModal"
              class="absolute inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
              @click.self="closePublishModal"
            >
              <div class="bg-dark-surface border border-dark-border rounded-2xl shadow-2xl shadow-black/60 w-full max-w-md mx-4 overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-dark-border flex items-center gap-3">
                  <div 
                    class="w-9 h-9 rounded-xl flex items-center justify-center"
                    :class="publishDone ? 'bg-green-500/15' : publishError ? 'bg-red-500/15' : 'bg-brand-500/15'"
                  >
                    <font-awesome-icon 
                      :icon="publishDone ? 'circle-check' : publishError ? 'circle-xmark' : 'rocket'"
                      :class="publishDone ? 'text-green-400' : publishError ? 'text-red-400' : 'text-brand-400'"
                    />
                  </div>
                  <div>
                    <h3 class="font-bold text-slate-100 text-sm">
                      {{ publishDone ? '¡Sistema publicado!' : publishError ? 'Error al publicar' : 'Publicando sistema...' }}
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                      {{ publishDone ? 'Todos los cambios fueron aplicados correctamente.' : publishError ? 'El proceso se detuvo en el paso con error.' : 'Ejecutando los pasos de publicación...' }}
                    </p>
                  </div>
                </div>

                <!-- Steps -->
                <div class="px-6 py-5 space-y-3">
                  <div 
                    v-for="(step, i) in publishSteps" 
                    :key="step.id"
                    class="flex items-start gap-3.5 p-3.5 rounded-xl border transition-all duration-300"
                    :class="{
                      'bg-dark-elevated/40 border-dark-border': step.status === 'idle',
                      'bg-brand-500/8 border-brand-500/25': step.status === 'loading',
                      'bg-green-500/8 border-green-500/20': step.status === 'done',
                      'bg-red-500/8 border-red-500/20': step.status === 'error',
                    }"
                  >
                    <!-- Step Icon -->
                    <div 
                      class="mt-0.5 w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 text-sm"
                      :class="{
                        'bg-slate-700/50 text-slate-500': step.status === 'idle',
                        'bg-brand-500/20 text-brand-400': step.status === 'loading',
                        'bg-green-500/20 text-green-400': step.status === 'done',
                        'bg-red-500/20 text-red-400': step.status === 'error',
                      }"
                    >
                      <font-awesome-icon 
                        :icon="step.status === 'idle' ? String(i + 1) === '1' ? 'floppy-disk' : i + 1 === 2 ? 'gears' : 'database' 
                               : step.status === 'loading' ? 'spinner' 
                               : step.status === 'done' ? 'check' 
                               : 'xmark'"
                        :class="{ 'animate-spin': step.status === 'loading' }"
                        class="text-xs"
                      />
                    </div>

                    <!-- Step Info -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <span 
                          class="font-semibold text-xs"
                          :class="{
                            'text-slate-400': step.status === 'idle',
                            'text-brand-300': step.status === 'loading',
                            'text-green-400': step.status === 'done',
                            'text-red-400': step.status === 'error',
                          }"
                        >{{ step.label }}</span>
                        <span 
                          v-if="step.status === 'loading'"
                          class="text-[9px] px-1.5 py-0.5 bg-brand-500/20 text-brand-400 rounded-full font-bold uppercase tracking-wider animate-pulse"
                        >En progreso</span>
                        <span 
                          v-else-if="step.status === 'done'"
                          class="text-[9px] px-1.5 py-0.5 bg-green-500/20 text-green-400 rounded-full font-bold uppercase tracking-wider"
                        >Listo</span>
                        <span 
                          v-else-if="step.status === 'error'"
                          class="text-[9px] px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full font-bold uppercase tracking-wider"
                        >Error</span>
                      </div>
                      <p class="text-[10px] text-slate-500 mt-0.5">{{ step.desc }}</p>
                      <!-- Error detail -->
                      <p v-if="step.error" class="text-[10px] text-red-400/80 mt-1.5 bg-red-500/5 border border-red-500/10 rounded-lg px-2 py-1.5 font-mono break-all">{{ step.error }}</p>
                    </div>
                  </div>
                </div>

                <!-- Footer -->
                <div class="px-6 pb-5 flex justify-end">
                  <button 
                    type="button"
                    @click="closePublishModal"
                    :disabled="!publishDone && !publishError"
                    class="px-5 py-2 rounded-xl text-sm font-semibold transition duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
                    :class="publishDone ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-dark-elevated border border-dark-border text-slate-300 hover:text-white'"
                  >
                    {{ publishDone ? 'Cerrar' : publishError ? 'Cerrar' : 'Procesando...' }}
                  </button>
                </div>
              </div>
            </div>
          </Transition>

          <!-- Floating Migrations Panel (Bottom-Left) -->
          <div class="absolute bottom-4 left-16 z-10 w-80 bg-dark-surface/90 border border-dark-border backdrop-blur-md rounded-xl shadow-xl shadow-black/40 flex flex-col overflow-hidden text-left">
            <!-- Panel Header -->
            <button 
              type="button"
              @click="showMigrationsPanel = !showMigrationsPanel" 
              class="px-4 py-2.5 bg-dark-elevated/45 border-b border-dark-border flex items-center justify-between w-full text-slate-200 hover:text-slate-100 transition"
            >
              <span class="font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                <font-awesome-icon icon="database" class="text-amber-500" />
                Migraciones ({{ pendingCount }} pendientes)
              </span>
              <font-awesome-icon :icon="showMigrationsPanel ? 'chevron-down' : 'chevron-up'" class="text-xs" />
            </button>

            <!-- Panel Body -->
            <div v-if="showMigrationsPanel" class="flex-1 overflow-y-auto p-2 space-y-1.5 custom-scrollbar max-h-56">
              <div 
                v-for="m in migrationsList" 
                :key="m.name" 
                class="flex items-center justify-between p-2 rounded-lg text-[10px] transition border border-dark-border/50 hover:border-dark-border bg-dark-elevated/10"
              >
                <div class="flex flex-col truncate pr-2">
                  <span class="font-mono text-slate-300 truncate" :title="m.name">{{ cleanMigrationName(m.name) }}</span>
                  <span class="text-[8px] text-slate-500 font-mono">{{ m.timestamp }}</span>
                </div>
                
                <div>
                  <span 
                    v-if="m.status === 'ran'" 
                    class="px-1.5 py-0.5 bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20 rounded font-bold uppercase text-[7px]"
                  >
                    Ejecutada
                  </span>
                  <span 
                    v-else-if="m.status === 'pending'" 
                    class="px-1.5 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded font-bold uppercase text-[7px] animate-pulse"
                  >
                    Pendiente
                  </span>
                  <span 
                    v-else 
                    class="px-1.5 py-0.5 bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 rounded font-bold uppercase text-[7px]"
                  >
                    Sin archivo
                  </span>
                </div>
              </div>
              <div v-if="migrationsList.length === 0" class="text-center p-3 text-slate-500 text-xs italic">
                Sin migraciones registradas.
              </div>
            </div>
          </div>
        </div>

        <!-- Sliding Glassmorphism Drawer Editor (Right Side) -->
        <Transition name="drawer">
          <div 
            v-if="isDrawerOpen && selectedNode" 
            class="w-[450px] border-l border-dark-border bg-dark-surface/90 backdrop-blur-2xl h-full flex flex-col z-20 shadow-2xl relative"
          >
            <!-- Drawer Header -->
            <div class="p-4 border-b border-dark-border flex items-center justify-between">
              <h3 class="text-md font-bold text-slate-100 flex items-center gap-2">
                <font-awesome-icon icon="table" class="text-brand-500" />
                Editar Entidad
              </h3>
              <button type="button" @click.prevent="isDrawerOpen = false" class="p-1 hover:bg-dark-elevated rounded-lg text-slate-400 hover:text-slate-100 transition">
                <font-awesome-icon icon="times" class="w-5 h-5" />
              </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto p-4 space-y-5 text-left custom-scrollbar">
              <!-- System Entity Warning -->
              <div v-if="selectedNode.data.is_system" class="p-3 bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs rounded-xl flex items-start gap-2">
                <font-awesome-icon icon="circle-check" class="mt-0.5" />
                <span><strong>Entidad del Sistema</strong>: Esta tabla es de solo lectura. No se generará código para ella, pero puedes usarla para diseñar relaciones desde otras tablas.</span>
              </div>

              <!-- Table details -->
              <div class="space-y-3">
                <div>
                  <JetLabel for="e-name" value="Nombre del Modelo (Singular)" />
                  <JetInput id="e-name" v-model="selectedNode.data.name" type="text" class="mt-1 block w-full text-sm font-semibold" :disabled="selectedNode.data.is_system" />
                </div>
                <div>
                  <JetLabel for="e-table" value="Nombre de Tabla" />
                  <JetInput id="e-table" v-model="selectedNode.data.table" type="text" class="mt-1 block w-full text-sm font-mono" :disabled="selectedNode.data.is_system" />
                </div>
                <div class="flex items-end gap-2">
                  <div class="flex-1">
                    <JetLabel for="e-plural" value="Etiqueta Plural (Sidebar)" />
                    <JetInput id="e-plural" v-model="selectedNode.data.plural_label" type="text" class="mt-1 block w-full text-sm" :disabled="selectedNode.data.is_system" />
                  </div>
                  <div v-if="!selectedNode.data.is_system" class="relative icon-popover-container">
                    <JetLabel value="Icono" class="text-xs mb-1 block" />
                    <button 
                      type="button"
                      @click.stop="toggleIconPopover"
                      class="h-[38px] w-[42px] flex items-center justify-center border border-dark-border bg-dark-surface hover:bg-dark-elevated rounded-xl transition text-slate-300 hover:text-slate-100"
                      title="Seleccionar Icono"
                    >
                      <font-awesome-icon :icon="selectedNode.data.icon || 'table'" class="text-lg" />
                    </button>
                    
                    <!-- Icon Popover -->
                    <div 
                      v-if="showIconPopover"
                      class="absolute bottom-12 right-0 w-64 bg-dark-surface/95 border border-dark-border backdrop-blur-md p-3 rounded-xl shadow-2xl z-50 animate-slide-up"
                    >
                      <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-2">Selecciona un icono</p>
                      <div class="grid grid-cols-5 gap-1.5">
                        <button 
                          v-for="ico in availableIcons"
                          :key="ico.value"
                          type="button"
                          @click.prevent="selectIcon(ico.value)"
                          :class="[
                            'p-2 flex items-center justify-center border rounded-lg transition hover:bg-dark-elevated/50 group/icobtn',
                            selectedNode.data.icon === ico.value
                              ? 'border-brand-500 bg-brand-500/10 text-brand-400 font-bold'
                              : 'border-dark-border bg-dark-surface/40 text-slate-400 hover:text-slate-200'
                          ]"
                          :title="ico.name"
                        >
                          <font-awesome-icon :icon="ico.icon" class="text-sm transition-transform duration-200 group-hover/icobtn:scale-110" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <hr class="border-dark-border" />

              <!-- Fields/Columns List -->
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <h4 class="text-xs uppercase font-bold tracking-wider text-slate-400">Columnas / Campos</h4>
                  <button 
                    type="button"
                    v-if="!selectedNode.data.is_system"
                    @click.prevent="addField" 
                    class="px-2.5 py-1.5 bg-brand-500/10 text-brand-400 hover:bg-brand-500/20 active:bg-brand-500/30 rounded-lg text-xs font-bold transition flex items-center gap-1"
                  >
                    <font-awesome-icon icon="plus" class="text-[10px]" />
                    Agregar Campo
                  </button>
                </div>

                <TransitionGroup name="list-fields" tag="div" class="space-y-2 block relative">
                  <GlowCard 
                    v-for="(field, index) in selectedNode.data.fields" 
                    :key="field._id"
                    class="p-3 !bg-dark-elevated/15 transition duration-200"
                  >
                    <!-- Card Header (Always Visible) -->
                    <div 
                      @click="expandedFields[index] = !expandedFields[index]"
                      class="flex items-center justify-between gap-3 cursor-pointer select-none group/header"
                    >
                      <div class="flex items-center gap-2 truncate flex-1 text-left">
                        <font-awesome-icon 
                          :icon="expandedFields[index] ? 'chevron-down' : 'chevron-right'" 
                          class="text-[10px] text-slate-500 group-hover/header:text-slate-300 transition-colors duration-200" 
                        />
                        <span class="font-mono text-xs font-semibold text-slate-200 truncate group-hover/header:text-slate-100 transition-colors duration-200">
                          {{ field.name || '(sin nombre)' }}
                        </span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-dark-surface border border-dark-border rounded font-mono text-slate-400 max-w-[180px] truncate">
                          {{ fieldTypes.find(t => t.value === field.type)?.name || field.type }}
                        </span>
                      </div>

                      <div class="flex items-center gap-1.5 shrink-0" @click.stop>
                        <!-- Subir Campo -->
                        <button 
                          type="button"
                          v-if="field.name !== 'id' && index > (selectedNode.data.fields[0].name === 'id' ? 1 : 0)" 
                          @click.prevent="moveFieldUp(index)" 
                          class="text-slate-500 hover:text-brand-400 p-1 rounded transition"
                          title="Subir Campo"
                        >
                          <font-awesome-icon icon="arrow-up" class="text-xs" />
                        </button>

                        <!-- Bajar Campo -->
                        <button 
                          type="button"
                          v-if="field.name !== 'id' && index < selectedNode.data.fields.length - 1" 
                          @click.prevent="moveFieldDown(index)" 
                          class="text-slate-500 hover:text-brand-400 p-1 rounded transition"
                          title="Bajar Campo"
                        >
                          <font-awesome-icon icon="arrow-down" class="text-xs" />
                        </button>

                        <!-- Borrar Campo -->
                        <button 
                          type="button"
                          v-if="field.name !== 'id' && !selectedNode.data.is_system" 
                          @click.prevent="removeField(index)" 
                          class="text-slate-500 hover:text-red-500 p-1.5 rounded transition"
                          title="Borrar Campo"
                        >
                          <font-awesome-icon icon="trash" class="text-xs" />
                        </button>
                      </div>
                    </div>

                    <!-- Expanded Body -->
                    <div v-if="expandedFields[index]" class="mt-3.5 pt-3.5 border-t border-dark-border grid grid-cols-2 gap-2 text-left animate-slide-up">
                      <div class="col-span-2">
                        <JetLabel :for="`f-name-${index}`" value="Nombre Campo" class="text-[10px]" />
                        <input 
                          :id="`f-name-${index}`" 
                          v-model="field.name" 
                          :disabled="field.name === 'id' || selectedNode.data.is_system" 
                          type="text" 
                          class="mt-0.5 block w-full px-2 py-1 bg-dark-surface border border-dark-border text-slate-100 rounded text-xs font-mono focus:border-brand-500 focus:ring-0 disabled:opacity-50"
                        />
                      </div>
                      <div>
                        <JetLabel :for="`f-label-${index}`" value="Etiqueta (UI)" class="text-[10px]" />
                        <input :id="`f-label-${index}`" v-model="field.label" type="text" class="mt-0.5 block w-full px-2 py-1 bg-dark-surface border border-dark-border text-slate-100 rounded text-xs focus:border-brand-500 focus:ring-0" :disabled="selectedNode.data.is_system" />
                      </div>
                      <div>
                        <JetLabel :for="`f-type-${index}`" value="Tipo de Dato" class="text-[10px]" />
                        <Select :id="`f-type-${index}`" v-model="field.type" :value="field.type" :options="fieldTypes" class="mt-0.5 block w-full !py-1 !text-xs !bg-dark-surface" :disabled="field.name === 'id' || selectedNode.data.is_system" />
                      </div>
                      
                      <div v-if="field.name !== 'id'">
                        <JetLabel :for="`f-input-${index}`" value="Control UI" class="text-[10px]" />
                        <Select :id="`f-input-${index}`" v-model="field.input_type" :value="field.input_type" :options="inputTypes" class="mt-0.5 block w-full !py-1 !text-xs !bg-dark-surface" :disabled="selectedNode.data.is_system" />
                      </div>
                      <div v-if="field.name !== 'id'">
                        <JetLabel :for="`f-def-${index}`" value="Valor Default" class="text-[10px]" />
                        <input :id="`f-def-${index}`" v-model="field.default" type="text" class="mt-0.5 block w-full px-2 py-1 bg-dark-surface border border-dark-border text-slate-100 rounded text-xs focus:border-brand-500 focus:ring-0" :disabled="selectedNode.data.is_system" />
                      </div>

                      <!-- Toggles block -->
                      <div class="col-span-2 mt-3.5 pt-2 border-t border-dark-border grid grid-cols-3 gap-2 text-left">
                        <label class="flex flex-col gap-1 items-start">
                          <span class="text-[9px] text-slate-400">Requerido</span>
                          <Toggle v-model="field.required" :disabled="field.name === 'id' || selectedNode.data.is_system" />
                        </label>
                        <label class="flex flex-col gap-1 items-start">
                          <span class="text-[9px] text-slate-400">Único</span>
                          <Toggle v-model="field.unique" :disabled="field.name === 'id' || selectedNode.data.is_system" />
                        </label>
                        <label class="flex flex-col gap-1 items-start">
                          <span class="text-[9px] text-slate-400">Mostrar Tabla</span>
                          <Toggle v-model="field.show_in_table" :disabled="selectedNode.data.is_system" />
                        </label>
                        <label class="flex flex-col gap-1 items-start">
                          <span class="text-[9px] text-slate-400">Buscable</span>
                          <Toggle v-model="field.searchable" :disabled="selectedNode.data.is_system" />
                        </label>
                        <label class="flex flex-col gap-1 items-start">
                          <span class="text-[9px] text-slate-400">Ordenable</span>
                          <Toggle v-model="field.sortable" :disabled="selectedNode.data.is_system" />
                        </label>
                      </div>
                    </div>
                  </GlowCard>
                </TransitionGroup>
              </div>

              <hr class="border-dark-border" />

              <!-- Relations List -->
              <div class="space-y-4">
                <h4 class="text-xs uppercase font-bold tracking-wider text-slate-400">Relaciones</h4>
                <div v-if="selectedNode.data.relations?.length === 0" class="text-slate-500 text-xs italic">
                  Sin relaciones. Conecta entidades arrastrando un nodo hacia otro para crearlas.
                </div>
                <div v-else class="space-y-2">
                  <div 
                    v-for="(rel, index) in selectedNode.data.relations" 
                    :key="index"
                    class="p-3 border border-dark-border bg-dark-surface/50 rounded-lg flex items-center justify-between text-left"
                  >
                    <div>
                      <p class="text-xs font-bold text-slate-200">
                        {{ rel.relation_name }}
                      </p>
                      <p class="text-[10px] text-slate-500">
                        {{ rel.type }} -> <span class="font-bold text-slate-400">{{ rel.target }}</span> ({{ rel.foreign_key }})
                      </p>
                    </div>
                    <button type="button" @click.prevent="removeRelation(index)" class="text-slate-500 hover:text-red-500 transition p-1.5" title="Borrar Relación">
                      <font-awesome-icon icon="trash" class="text-xs" />
                    </button>
                  </div>
                </div>
              </div>

              <hr class="border-dark-border" />

              <!-- Delete Entity Button -->
              <div v-if="!selectedNode.data.is_system" class="pt-2">
                <JetDangerButton type="button" @click.prevent="deleteEntity" class="w-full text-center flex justify-center py-2.5">
                  <font-awesome-icon icon="trash" class="mr-2" />
                  Eliminar Entidad Completa
                </JetDangerButton>
              </div>
            </div>
          </div>
        </Transition>

      </div>

      <!-- UI Layout Designer Mode -->
      <div v-show="activeTab === 'ui'" class="flex-1 flex min-h-0 relative bg-dark-base overflow-hidden">
        <!-- Sidebar: List of custom entities -->
        <div class="w-64 border-r border-dark-border bg-dark-surface/50 flex flex-col p-4 shrink-0 text-left">
          <h3 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-3">Entidades Disponibles</h3>
          <div class="flex-1 overflow-y-auto space-y-1 custom-scrollbar">
            <button
              v-for="node in nodes.filter(n => !n.data.is_system)"
              :key="node.id"
              type="button"
              @click="selectedUiEntityId = node.id"
              class="w-full text-left p-3 rounded-xl border text-xs transition flex items-center gap-2"
              :class="[
                selectedUiEntityId === node.id
                  ? 'bg-brand-500/10 border-brand-500/30 text-brand-400 font-semibold'
                  : 'bg-dark-elevated/10 border-dark-border/30 text-slate-300 hover:bg-dark-elevated/20'
              ]"
            >
              <font-awesome-icon :icon="node.data.icon || 'table'" />
              <span>{{ node.data.plural_label || node.data.name }}</span>
            </button>
            <div v-if="nodes.filter(n => !n.data.is_system).length === 0" class="text-center py-6 text-slate-500 text-xs italic">
              Crea una entidad primero
            </div>
          </div>
        </div>

        <!-- Canvas Central de Diseño de Bloques -->
        <div class="flex-1 flex flex-col min-h-0 bg-dark-base relative p-6">
          <div v-if="selectedUiEntity" class="flex-1 flex flex-col min-h-0">
            <!-- Header of Entity Layout -->
            <div class="flex items-center justify-between border-b border-dark-border pb-4 mb-4">
              <div>
                <h2 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                  <font-awesome-icon :icon="selectedUiEntity.data.icon || 'table'" class="text-brand-500" />
                  Diseñador de Interfaz: <span class="text-brand-400">{{ selectedUiEntity.data.name }}</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Define las secciones y distribuye las columnas para los formularios CRUD.</p>
              </div>
              <button
                type="button"
                @click.prevent="addSection"
                class="inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-xl font-bold transition text-xs gap-2 shadow-lg shadow-brand-500/20"
              >
                <font-awesome-icon icon="plus" />
                Agregar Sección Visual
              </button>
            </div>

            <!-- Central workspace -->
            <div class="flex-1 flex gap-6 min-h-0">
              <!-- Left workspace side: The Sections List -->
              <div 
                class="flex-1 overflow-y-auto space-y-4 pr-2 custom-scrollbar"
                @dragover="onDragOverField"
              >
                <div 
                  v-for="sec in selectedUiEntity.data.ui_layout?.sections || []" 
                  :key="sec.id"
                  class="border border-dark-border bg-dark-surface/90 border border-dark-border backdrop-blur-md rounded-2xl p-4 transition-all duration-300 relative group/section"
                  draggable="true"
                  @dragstart="onDragStartSection(sec.id)"
                  @dragover="onDragOverField"
                  @drop="onDropSection(sec.id)"
                >
                  <div class="flex items-center justify-between border-b border-dark-border/40 pb-3 mb-3 cursor-move">
                    <div class="flex items-center gap-3">
                      <span class="text-slate-500 hover:text-slate-300">
                        <font-awesome-icon icon="list-check" />
                      </span>
                      <input 
                        v-model="sec.title" 
                        type="text" 
                        class="bg-transparent border-0 focus:ring-0 text-sm font-bold text-slate-200 p-0 w-64 focus:border-b focus:border-brand-500/30"
                        placeholder="Nombre de la Sección..."
                        @change="isDirty = true"
                      />
                    </div>
                    
                    <div class="flex items-center gap-3">
                      <div class="flex items-center gap-1">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider mr-1">Columnas:</span>
                        <select 
                          v-model.number="sec.columns" 
                          class="bg-dark-surface border border-dark-border rounded-lg text-xs text-slate-300 py-1 px-2 cursor-pointer focus:ring-brand-500 focus:border-brand-500"
                          @change="isDirty = true"
                        >
                          <option :value="1">1 Columna</option>
                          <option :value="2">2 Columnas</option>
                          <option :value="3">3 Columnas</option>
                        </select>
                      </div>

                      <button 
                        type="button"
                        v-if="(selectedUiEntity.data.ui_layout?.sections || []).length > 1"
                        @click.prevent="removeSection(sec.id)" 
                        class="text-slate-500 hover:text-red-400 transition p-1"
                        title="Eliminar Sección"
                      >
                        <font-awesome-icon icon="trash" />
                      </button>
                    </div>
                  </div>

                  <div 
                    class="min-h-[80px] rounded-xl border border-dashed border-dark-border/60 p-3 bg-dark-elevated/5 transition duration-200"
                    :class="{ 'border-brand-500/50 bg-brand-500/5': draggedFieldName }"
                    @dragover="onDragOverField"
                    @drop.stop="onDropField(sec.id)"
                  >
                    <div 
                      class="grid gap-3"
                      :class="{
                        'grid-cols-1': sec.columns === 1,
                        'grid-cols-1 md:grid-cols-2': sec.columns === 2,
                        'grid-cols-1 md:grid-cols-3': sec.columns === 3
                      }"
                    >
                      <div 
                        v-for="(fName, fIdx) in sec.fields" 
                        :key="fName"
                        class="border border-dark-border bg-dark-elevated/40 hover:border-brand-500/30 p-2.5 rounded-xl cursor-grab active:cursor-grabbing select-none transition flex items-center justify-between"
                        draggable="true"
                        @dragstart="onDragStartField(fName, sec.id)"
                        @dragover="onDragOverField"
                        @drop.stop="onDropField(sec.id, fIdx)"
                      >
                        <div class="flex items-center gap-2 truncate">
                          <font-awesome-icon icon="list-check" class="text-slate-500 text-xs shrink-0" />
                          <span class="font-mono text-xs text-slate-200 truncate">
                            {{ getElementName(fName) }}
                          </span>
                          <span class="text-[9px] text-slate-500 font-mono truncate">({{ fName }})</span>
                        </div>
                        
                        <span class="text-[8px] px-1.5 py-0.5 rounded bg-dark-surface border border-dark-border text-slate-400 font-mono shrink-0">
                          {{ getElementType(fName) }}
                        </span>
                      </div>
                    </div>
                    <div v-if="sec.fields.length === 0" class="text-center py-4 text-slate-500 text-xs italic select-none">
                      Arrastra campos aquí
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right workspace side -->
              <div class="w-64 border border-dark-border bg-dark-surface/90 backdrop-blur-md rounded-2xl p-4 flex flex-col min-h-0 text-left shrink-0">
                <h4 class="text-xs uppercase font-bold tracking-wider text-slate-400 mb-2">Campos sin Asignar</h4>
                <p class="text-[10px] text-slate-500 mb-3">Cualquier campo aquí no aparecerá en el formulario visual.</p>
                <div 
                  class="flex-1 overflow-y-auto space-y-2 border border-dashed border-dark-border/60 rounded-xl p-2 bg-dark-elevated/5 custom-scrollbar"
                  @dragover="onDragOverField"
                  @drop.stop="onDropOnUnassigned"
                >
                  <div 
                    v-for="fName in unassignedFields" 
                    :key="fName"
                    class="border border-dark-border bg-dark-elevated/40 hover:border-brand-500/30 p-2.5 rounded-xl cursor-grab active:cursor-grabbing select-none transition flex items-center justify-between"
                    draggable="true"
                    @dragstart="onDragStartField(fName, 'unassigned')"
                  >
                    <div class="flex items-center gap-1.5 truncate">
                      <font-awesome-icon icon="list-check" class="text-slate-500 text-xs shrink-0" />
                      <span class="font-mono text-xs text-slate-200 truncate">{{ getElementName(fName) }}</span>
                    </div>
                  </div>
                  <div v-if="unassignedFields.length === 0" class="text-center py-6 text-slate-500 text-[10px] italic">
                    Todos los campos asignados
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="flex-1 flex flex-col items-center justify-center text-slate-500 text-sm italic">
            <font-awesome-icon icon="palette" class="text-4xl text-dark-border mb-3" />
            Selecciona una entidad en la barra lateral para diseñar su interfaz de usuario
          </div>

          <!-- Bottom floating action toolbar -->
          <div class="absolute bottom-4 left-4 z-10 flex gap-2">
            <button 
              type="button"
              @click.prevent="saveSchema" 
              :disabled="saving"
              class="relative inline-flex items-center justify-center px-4 py-2.5 bg-dark-elevated hover:bg-dark-surface text-slate-200 hover:text-slate-100 rounded-xl font-bold transition duration-200 text-xs gap-2 disabled:opacity-50 border border-dark-border"
              :class="isDirty ? 'border-amber-500/60 shadow-sm shadow-amber-500/20' : 'border-dark-border'"
            >
              <font-awesome-icon :icon="saving ? 'spinner' : 'floppy-disk'" :class="{ 'animate-spin': saving }" />
              {{ saving ? 'Guardando...' : 'Guardar' }}
            </button>

            <button 
              type="button"
              @click.prevent="publishSystem" 
              class="relative inline-flex items-center justify-center px-5 py-2.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-xl font-bold transition duration-200 shadow-lg text-xs gap-2"
            >
              <font-awesome-icon icon="rocket" class="text-white/90" />
              Publicar Sistema
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Crear Relación -->
      <JetDialogModal :show="showRelationModal" @close="showRelationModal = false" max-width="sm">
        <template #title>
          Crear Nueva Relación
        </template>
        <template #content>
          <div class="space-y-4 text-left">
            <div class="p-2 border border-dark-border bg-dark-elevated/10 rounded-lg text-xs space-y-1 text-slate-300">
              <p>Origen: <span class="font-bold text-slate-100">{{ connectionData.sourceName }}</span></p>
              <p>Destino: <span class="font-bold text-slate-100">{{ connectionData.targetName }}</span></p>
            </div>

            <div>
              <JetLabel for="rel-type" value="Tipo de Relación" />
              <Select id="rel-type" v-model="connectionData.type" :value="connectionData.type" :options="relationTypes" class="mt-1 block w-full" />
            </div>

            <div v-if="connectionData.type !== 'belongsToMany'">
              <JetLabel for="rel-fk" value="Clave Foránea" />
              <JetInput id="rel-fk" v-model="connectionData.foreignKey" type="text" class="mt-1 block w-full text-sm font-mono" />
            </div>

            <div>
              <JetLabel for="rel-name" value="Nombre de Relación (Método)" />
              <JetInput id="rel-name" v-model="connectionData.relationName" type="text" class="mt-1 block w-full text-sm font-mono" />
            </div>


          </div>
        </template>
        <template #footer>
          <div class="w-1/2">
            <JetSecondaryButton type="button" @click.prevent="showRelationModal = false">Cancelar</JetSecondaryButton>
          </div>
          <div class="w-1/2">
            <JetButton type="button" @click.prevent="confirmRelation" class="float-right">Confirmar Relación</JetButton>
          </div>
        </template>
      </JetDialogModal>

    </div>
  </AppLayout>
</template>

<style>
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';

/* Custom Drawer Animations */
.drawer-enter-active,
.drawer-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.drawer-enter-from,
.drawer-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

/* Custom Vue Flow variables to fit Dark/Light UI themes */
.vue-flow {
  background-color: transparent !important;
}

.vue-flow__edge-path {
  stroke: rgb(var(--color-brand-500-rgb)) !important;
  stroke-width: 2px !important;
  transition: stroke 0.3s;
}

.vue-flow__edge:hover .vue-flow__edge-path,
.vue-flow__edge.selected .vue-flow__edge-path {
  stroke: rgb(var(--color-brand-600-rgb)) !important;
  stroke-width: 3px !important;
}

.vue-flow__edge-textbg {
  fill: rgb(var(--color-bg-surface)) !important;
  transition: fill 0.3s;
}

.vue-flow__edge-text {
  fill: rgb(var(--color-text-300)) !important;
  transition: fill 0.3s;
}

.vue-flow__controls {
  background: rgb(var(--color-bg-surface) / 0.8) !important;
  border: 1px solid rgb(var(--color-border) / 0.15) !important;
  border-radius: 12px !important;
  padding: 4px !important;
  backdrop-filter: blur(10px) !important;
  box-shadow: var(--shadow-md) !important;
  transition: background-color 0.3s, border-color 0.3s !important;
}

.vue-flow__controls-button {
  background: transparent !important;
  border: none !important;
  color: rgb(var(--color-text-400)) !important;
  border-radius: 8px !important;
  transition: all 0.2s !important;
}

.vue-flow__controls-button:hover {
  background: rgb(var(--color-brand-500-rgb) / 0.15) !important;
  color: rgb(var(--color-text-100)) !important;
}

.vue-flow__controls-button svg {
  fill: currentColor !important;
}

/* Custom Scrollbars inside Drawer */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}

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
</style>
