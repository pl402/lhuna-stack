<script>
import { Link } from "@inertiajs/vue3";
export default {
    props: {
        links: Array,
        total: Number,
        to: Number,
        from: Number,
    },
    components: {
        Link,
    },
};
</script>
<template>
    <div
        class="grid px-2 text-xs font-semibold tracking-wide text-slate-300 uppercase bg-dark-surface/50 bg-glass-gradient backdrop-blur-md border-t border-dark-border sm:grid-cols-9"
    >
        <span class="flex items-center col-span-3">
            <slot name="controles" />
            Mostrando {{ from }}-{{ to }} de {{ total }}
        </span>
        <span class="col-span-2"></span>
        <span class="flex col-span-4 mt-2 sm:mt-auto sm:justify-end">
            <nav aria-label="Table navigation">
                <ul class="inline-flex items-center">
                    <li v-for="(link, k) in links" :key="k">
                        <div
                            v-if="link.url === null"
                            class="px-3 py-1 mx-1 rounded-md rounded-l-lg focus:outline-none focus:ring-2 ring-slate-400 text-gray-400"
                        >
                            <span v-if="link.label === 'Anterior'">
                                <font-awesome-icon icon="angle-left" />
                            </span>
                            <span v-else-if="link.label === 'Siguiente'">
                                <font-awesome-icon icon="angle-right" />
                            </span>
                            <span v-else> {{ link.label }} </span>
                        </div>
                        <Link
                            v-else
                            class="inline-flex items-center px-4 py-2 m-1 border border-transparent rounded-md font-semibold text-xs text-grey-700 uppercase tracking-widest hover:bg-brand-400 hover:text-white active:bg-brand-600 focus:outline-none focus:border-brand-500 focus:ring focus:ring-brand-500 disabled:opacity-25 transition"
                            :class="{
                                'bg-gray-900 text-white': link.active,
                            }"
                            :href="link.url"
                        >
                            <span v-if="link.label === 'Anterior'">
                                <font-awesome-icon icon="angle-left" />
                            </span>
                            <span v-else-if="link.label === 'Siguiente'">
                                <font-awesome-icon icon="angle-right" />
                            </span>
                            <span v-else> {{ link.label }} </span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </span>
    </div>
</template>
