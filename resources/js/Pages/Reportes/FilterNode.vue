<script setup>
import { ref } from "vue";

const props = defineProps({
  node: Object,
  parentGroup: Object,
  index: Number,
  fieldsOptions: Array,
  operators: Array,
  getFieldType: Function,
  onEditRule: Function, // callback to open the edit rule form: (ruleNode, parentGroup, index)
});

const toggleOperator = () => {
  if (props.node.type === "group") {
    props.node.operator = props.node.operator === "and" ? "or" : "and";
  }
};

const addRule = () => {
  if (props.node.type === "group") {
    props.node.rules.push({
      type: "rule",
      field: props.fieldsOptions[0]?.value || "",
      operator: "=",
      value: "",
    });
  }
};

const addSubgroup = () => {
  if (props.node.type === "group") {
    props.node.rules.push({
      type: "group",
      operator: "and",
      rules: [],
    });
  }
};

const removeNode = () => {
  if (props.parentGroup && props.parentGroup.rules) {
    props.parentGroup.rules.splice(props.index, 1);
  }
};
</script>

<template>
  <div class="w-full">
    <!-- Group Node -->
    <div
      v-if="node.type === 'group'"
      class="border border-dark-border/60 bg-dark-surface/10 rounded-xl p-3.5 space-y-3 relative transition-all"
    >
      <!-- Group Header -->
      <div class="flex items-center justify-between border-b border-dark-border/40 pb-2">
        <div class="flex items-center gap-2">
          <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider">Grupo</span>
          <button
            type="button"
            @click="toggleOperator"
            class="px-2 py-0.5 bg-brand-500/10 border border-brand-500/20 hover:border-brand-500/40 rounded text-[10px] font-bold text-brand-400 uppercase transition"
          >
            {{ node.operator === 'and' ? 'Y (AND)' : 'O (OR)' }}
          </button>
        </div>

        <!-- Group Actions -->
        <div class="flex items-center gap-2">
          <button
            type="button"
            @click="addRule"
            class="text-[10px] font-semibold text-brand-400 hover:text-brand-300 transition"
            title="Añadir regla de filtrado"
          >
            + Regla
          </button>
          <button
            type="button"
            @click="addSubgroup"
            class="text-[10px] font-semibold text-slate-400 hover:text-slate-200 transition"
            title="Añadir subgrupo"
          >
            + Grupo
          </button>
          <button
            v-if="parentGroup"
            type="button"
            @click="removeNode"
            class="text-[10px] font-semibold text-red-500/70 hover:text-red-400 transition"
            title="Eliminar este grupo"
          >
            Eliminar
          </button>
        </div>
      </div>

      <!-- Group Children -->
      <div v-if="node.rules && node.rules.length > 0" class="space-y-3 pl-2.5 border-l border-dark-border/40">
        <div
          v-for="(subNode, idx) in node.rules"
          :key="idx"
          class="flex flex-col"
        >
          <!-- Conjunction connector shown between elements in the group -->
          <div v-if="idx > 0" class="flex items-center my-1.5 pl-3">
            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 bg-dark-surface border border-dark-border rounded text-slate-500">
              {{ node.operator === 'and' ? 'Y' : 'O' }}
            </span>
          </div>

          <!-- Recursive Render -->
          <FilterNode
            :node="subNode"
            :parent-group="node"
            :index="idx"
            :fields-options="fieldsOptions"
            :operators="operators"
            :get-field-type="getFieldType"
            :on-edit-rule="onEditRule"
          />
        </div>
      </div>

      <!-- Empty group state -->
      <div v-else class="text-center py-3 text-[11px] text-slate-500 italic">
        Grupo vacío. Añade reglas o subgrupos.
      </div>
    </div>

    <!-- Rule Node (Chip representation) -->
    <div
      v-else-if="node.type === 'rule'"
      class="flex items-center justify-between border border-dark-border bg-dark-surface/30 hover:border-brand-500/30 rounded-xl px-3.5 py-2 text-xs text-slate-300 transition cursor-pointer select-none"
      @click="onEditRule(node, parentGroup, index)"
    >
      <div class="flex items-center gap-1.5 truncate">
        <span class="opacity-75 font-semibold text-slate-400">
          {{ fieldsOptions.find(o => o.value === node.field)?.name || node.field }}
        </span>
        <span class="text-slate-500 font-mono text-[9px] uppercase">
          {{ operators.find(op => op.value === node.operator)?.value || node.operator }}
        </span>
        <span class="font-bold text-slate-100 truncate">
          {{ 
            node.value === '1' && getFieldType(node.field) === 'boolean' ? 'Sí' : 
            node.value === '0' && getFieldType(node.field) === 'boolean' ? 'No' : 
            node.value === '{HOY}' ? 'Hoy' :
            node.value === '{AYER}' ? 'Ayer' :
            node.value === '{INICIO_MES}' ? 'Inicio de Mes' :
            node.value === '{FIN_MES}' ? 'Fin de Mes' :
            node.value === '{USUARIO_AUTENTICADO}' ? 'Usuario Autenticado' :
            node.value 
          }}
        </span>
      </div>

      <div class="flex items-center gap-1 shrink-0 ml-2" @click.stop>
        <button
          type="button"
          @click="onEditRule(node, parentGroup, index)"
          class="p-1 text-slate-500 hover:text-blue-400 transition"
          title="Editar regla"
        >
          <font-awesome-icon icon="edit" class="w-3 h-3" />
        </button>
        <button
          type="button"
          @click="removeNode"
          class="p-1 text-slate-500 hover:text-red-400 transition"
          title="Eliminar regla"
        >
          <font-awesome-icon icon="times" class="w-3 h-3" />
        </button>
      </div>
    </div>
  </div>
</template>
