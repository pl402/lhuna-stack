<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { toRefs, computed } from "vue";
import Tabla from "@/Components/Tabla.vue";
import Demo from "@/Components/Demo.vue";

const props = defineProps({});
</script>

<style>
.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.3s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-20px);
    opacity: 0;
}
</style>

<template>
    <AppLayout title="Escritorio">
        <Demo />
    </AppLayout>
</template>
