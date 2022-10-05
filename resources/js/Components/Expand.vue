<script setup>
import { ref } from "vue";

const props = defineProps({
    pdi: Object,
    title: String,
    icono: String,
    elementos: Array,
    nivel: Number,
});
const expandido = ref(false);
const pdi = ref(props.pdi);

const padding = props.nivel * 5;
const paddingLeft = padding * 2 + 2;
</script>
<script>
export default {};
</script>
<template>
    <div
        class="items-center text-base font-normal rounded-lg text-white hover:bg-black/10 bg-black/10 hover:cursor-pointer w-full"
    >
        <div v-if="expandido" class="w-full">
            <Link
                :href="'/pdi/' + pdi.uuid"
                class="flex items-center text-base font-normal rounded-lg text-white hover:bg-slate-700 hover:cursor-pointer"
            >
                <font-awesome-icon
                    icon="building-columns"
                    class="ml-2 transition duration-75 text-slate-300 group-hover:text-white"
                />
                <span class="ml-3">{{ pdi.nombre }}</span>
            </Link>
            <div
                v-for="elemento in pdi.ejes"
                class="flex items-center text-base font-normal rounded-lg text-white hover:bg-black/10 hover:cursor-pointer p-1"
                :style="'padding-left:' + paddingLeft + 'px'"
            >
                <font-awesome-icon
                    icon="maximize"
                    class="ml-2 transition duration-75 text-slate-300 group-hover:text-white"
                />
                <span class="ml-3">{{ elemento.nombre }}</span>
                <font-awesome-icon
                    icon="plus"
                    class="ml-2 transition duration-75 text-slate-300 group-hover:text-white absolute right-0 mr-8 hover:bg-white/20 rounded-md p-1"
                />
                <font-awesome-icon
                    :icon="expandido ? 'angle-up' : 'angle-down'"
                    @click="expandido = !expandido"
                    class="ml-2 transition duration-75 text-slate-300 group-hover:text-white absolute right-0 mr-2 hover:bg-white/20 rounded-md p-1"
                />
            </div>
        </div>
    </div>
</template>
