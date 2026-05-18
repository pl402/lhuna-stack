<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    href: String,
    as: String,
    target: String,
});
</script>

<template>
    <div>
        <button
            v-if="as === 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-sm leading-5 text-left text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-dark-elevated focus:outline-none focus:bg-dark-elevated transition-all duration-300"
        >
            <slot />
        </button>

        <a
            v-else-if="as === 'a'"
            :href="href"
            :target="target"
            class="block px-4 py-2 text-sm leading-5 text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-dark-elevated focus:outline-none focus:bg-dark-elevated transition-all duration-300"
        >
            <slot />
        </a>

        <Link
            v-else
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-dark-elevated focus:outline-none focus:bg-dark-elevated transition-all duration-300"
        >
            <slot />
        </Link>
    </div>
</template>
