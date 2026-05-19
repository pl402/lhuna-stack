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
        class="grid px-4 py-2 text-xs font-semibold tracking-wide text-slate-400 uppercase bg-dark-surface/50 bg-glass-gradient backdrop-blur-md border-t border-dark-border sm:grid-cols-9 items-center"
    >
        <span class="flex items-center col-span-3 text-slate-500 dark:text-slate-400">
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
                            class="inline-flex items-center px-3 py-1.5 mx-1 border border-dark-border/20 rounded-md font-semibold text-xs text-slate-500/40 dark:text-slate-600 uppercase tracking-widest bg-dark-elevated/10 select-none cursor-not-allowed"
                        >
                            <span v-if="link.label === 'Anterior' || link.label.includes('Anterior')">
                                <font-awesome-icon icon="angle-left" />
                            </span>
                            <span v-else-if="link.label === 'Siguiente' || link.label.includes('Siguiente')">
                                <font-awesome-icon icon="angle-right" />
                            </span>
                            <span v-else> {{ link.label }} </span>
                        </div>
                        <Link
                            v-else
                            class="inline-flex items-center px-3 py-1.5 mx-1 border rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring focus:ring-brand-500/30 transition-all duration-200"
                            :class="link.active
                                ? 'bg-brand-500 border-brand-500 text-white shadow-md shadow-brand-500/20'
                                : 'bg-dark-elevated/40 border-dark-border/50 text-slate-600 dark:text-slate-300 hover:bg-brand-500 hover:text-white hover:border-brand-500'"
                            :href="link.url"
                        >
                            <span v-if="link.label === 'Anterior' || link.label.includes('Anterior')">
                                <font-awesome-icon icon="angle-left" />
                            </span>
                            <span v-else-if="link.label === 'Siguiente' || link.label.includes('Siguiente')">
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
