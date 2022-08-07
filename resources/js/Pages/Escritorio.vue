<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { reactive, ref, watch, toRefs } from "vue";
import { Link } from "@inertiajs/inertia-vue3";
import Tabla from "@/Components/Tabla.vue";
import JetButton from "@/Jetstream/Button.vue";
import ButtonLink from "@/Components/ButtonLink.vue";
import { BadgeCheckIcon } from "@heroicons/vue/solid";
import { notify } from "notiwind";

const props = defineProps({

});
</script>

<template>
  <AppLayout title="Escritorio">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <h3 class="mx-4 my-4 mb-0 text-lg text-gray-700">
        Escritorio
      </h3>
    </div>
  </AppLayout>
</template>
