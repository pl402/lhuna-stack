<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Head, Link, usePage } from "@inertiajs/vue3";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import JetBanner from "@/Jetstream/Banner.vue";
import JetDropdown from "@/Jetstream/Dropdown.vue";
import JetDropdownLink from "@/Jetstream/DropdownLink.vue";
import JetNavLink from "@/Jetstream/NavLink.vue";
import JetResponsiveNavLink from "@/Jetstream/ResponsiveNavLink.vue";
import ActionMessage from "@/Jetstream/ActionMessage.vue";
import Toast from "@/Components/Toast.vue";
import { notify } from "notiwind";
import { VOffline } from "v-offline";

defineProps({
    title: String,
});

const online = ref(false);

const onConnectionChange = (isOnline) => {
    online.value = isOnline;
};

const showDropNav = ref(
    localStorage.showDropNav ? localStorage.showDropNav === "true" : true
);

// Espera a que se cargue la pagina para ocultar el menu
router.on("finish", () => {
    showDropNav.value = false;
    localStorage.showDropNav = showDropNav.value;
});

const logout = () => {
    router.post(route("logout"));
};
const isOpen = ref(localStorage.isOpen ? localStorage.isOpen === "true" : true);
const error = computed(() => usePage().props.flash?.error || null);

const cierraMenu = () => {
    isOpen.value = false;
    localStorage.isOpen = isOpen.value;
};

const abreMenu = () => {
    isOpen.value = true;
    localStorage.isOpen = isOpen.value;
};

const changeShowDropNav = () => {
    showDropNav.value = !showDropNav.value;
    localStorage.showDropNav = showDropNav.value;
};

watch(error, async () => {
    notify(
        {
            group: "error",
            title: "Ocurrió un error",
            text: error.value,
        },
        3000
    );
});

const menu = [
    {
        name: "Escritorio",
        icon: "home",
        href: route("escritorio"),
        active: route().current("escritorio"),
        show: true,
    },
    {
        name: "Usuarios",
        href: route("usuarios.index"),
        icon: "users",
        active:
            route().current("usuarios.index") ||
            route().current("usuarios.search") ||
            route().current("usuarios.filter") ||
            route().current("usuarios"),
        show: true,
    },
    {
        name: "Configuraciones",
        href: route("configuraciones.index"),
        icon: "cog",
        active:
            route().current("configuraciones.index") ||
            route().current("configuraciones.search") ||
            route().current("configuraciones"),
        show: true,
    },
];

const isDark = ref(localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    }
};
</script>
<style>
.slide-fade-enter-active {
    transition: all 0.1s cubic-bezier(0.55, 0, 0.1, 1);
}

.slide-fade-leave-active {
    transition: all 0.1s cubic-bezier(0.55, 0, 0.1, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-50px);
    opacity: 0;
    filter: blur(3px);
}

.slide-fade-enter-to,
.slide-fade-leave-from {
    transform: translateX(0);
    opacity: 1;
    filter: none;
}
</style>

<template>
    <div>
        <Head :title="title" />
        <div class="min-h-screen bg-dark-base text-slate-200 relative selection:bg-brand-500/30 selection:text-brand-200">
            <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-brand-500/10 blur-[120px] rounded-full pointer-events-none z-0"></div>
            <nav
                class="sm:hidden bg-dark-surface/80 border-b border-dark-border fixed top-0 z-50 w-full backdrop-blur-md shadow-lg shadow-black/20"
            >
                <!-- Primary Navigation Menu -->
                <div
                    class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 bg-dark-surface/90 border-b border-dark-border shadow-sm backdrop-blur-lg"
                >
                    <div class="flex justify-between h-12">
                        <div class="flex items-center p-3">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('escritorio')">
                                    <JetApplicationMark
                                        class="block h-7 w-auto"
                                    />
                                </Link>
                            </div>
                        </div>
                        <h2
                            class="text-lg font-bold text-slate-100 flex my-auto truncate tracking-tight"
                        >
                            <VOffline @detected-condition="onConnectionChange">
                                <template v-if="online">
                                    <span class="text-green-500">
                                        <font-awesome-icon
                                            icon="circle"
                                            class="w-3 mr-2"
                                        />
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-red-500">
                                        <font-awesome-icon
                                            icon="circle"
                                            class="w-3 mr-2"
                                        />
                                    </span>
                                </template>
                            </VOffline>
                            <!-- Titulo menu celular -->
                            Lhuna-Stack<span
                                class="font-light text-slate-500 mx-2 opacity-50"
                                >/</span
                            ><span
                                class="font-medium text-brand-700 dark:text-brand-400 truncate"
                                >{{ title }}</span
                            >
                        </h2>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-500/10 focus:outline-none focus:bg-brand-500/20 focus:text-brand-700 dark:focus:text-brand-400 transition-all duration-300"
                                @click="changeShowDropNav"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <Transition name="slide-fade">
                                        <path
                                            v-if="!showDropNav"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            v-else
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </Transition>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    class="md:hidden transition-all duration-300 ease-in-out"
                    :class="
                        showDropNav
                            ? 'mt-0 opacity-100'
                            : '-mt-[150%] opacity-0'
                    "
                >
                    <div class="">
                        <JetResponsiveNavLink
                            v-for="(item, index) in menu"
                            :key="index"
                            :href="item.href"
                            :active="item.active"
                            v-show="item.show"
                        >
                            <font-awesome-icon
                                :icon="item.icon ? item.icon : 'square-pen'"
                                class="w-5 mr-2"
                            />{{ item.name }}
                        </JetResponsiveNavLink>
                    </div>
                    <div class="border-t border-dark-border my-[1px]"></div>
                    <!-- Responsive Settings Options -->
                    <div>
                        <div class="space-y-1">
                            <JetResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                <font-awesome-icon
                                    icon="user-gear"
                                    class="w-5 mr-2"
                                />{{ $page.props.user.name }}
                                <div class="font-medium text-sm text-brand-700 dark:text-brand-400">
                                    Usuario
                                    {{ $page.props.auth.roles[0] }}
                                </div>
                            </JetResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <JetResponsiveNavLink as="button">
                                    <font-awesome-icon
                                        icon="right-from-bracket"
                                        class="w-5 mr-2"
                                    />Cerrar sesión
                                </JetResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="flex">
                <div
                    class="flex-col h-screen bg-dark-surface/95 backdrop-blur-xl border-r border-dark-border shadow-2xl fixed transition-all duration-300 ease-in-out z-50"
                    :class="
                        isOpen
                            ? 'hidden sm:block md:block sm:w-60 md:w-60 opacity-100 ml-0'
                            : '-ml-60 opacity-0'
                    "
                >
                    <div class="space-y-3">
                        <div class="flex items-center p-2 justify-between">
                            <!-- Logo -->
                            <Link :href="route('escritorio')" class="mr-auto">
                                <JetApplicationMark class="block h-7 w-auto" />
                            </Link>
                            <h2
                                class="text-xl font-bold text-slate-100 flex-1 text-center tracking-tight"
                            >
                                <!-- Titulo menu lateral oculto -->
                                Lhuna-Stack
                            </h2>
                            <div class="ml-auto">
                                <button
                                    class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-500/10 focus:outline-none focus:bg-brand-500/20 focus:text-brand-700 dark:focus:text-brand-400 transition-all duration-300"
                                    @click="cierraMenu()"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            class="inline-flex"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6
                                            6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="">
                                <JetResponsiveNavLink
                                    v-for="(item, index) in menu"
                                    :key="index"
                                    :href="item.href"
                                    :active="item.active"
                                    v-show="item.show"
                                >
                                    <font-awesome-icon
                                        :icon="
                                            item.icon ? item.icon : 'square-pen'
                                        "
                                        class="w-5 mr-2"
                                    />{{ item.name }}
                                </JetResponsiveNavLink>
                            </div>
                            <div
                                class="border-t border-dark-border my-[1px]"
                            ></div>
                            <!-- Responsive Settings Options -->
                            <div>
                                <!-- Page Footer -->
                                <footer
                                    class="bg-dark-surface/80 backdrop-blur-md absolute bottom-0 w-full border-t border-dark-border"
                                >
                                    <div class="py-1 px-4 text-center">
                                        <!-- copyrigth  -->
                                        <div class="text-xs text-slate-500">
                                            <div>
                                                <a
                                                    href="https://lhuna.dev"
                                                    target="_blank"
                                                    >© lhuna.dev - 2023</a
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </footer>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="w-full transition-all duration-300 ease-in-out"
                    :class="
                        isOpen
                            ? 'ml-0 sm:ml-60 md:ml-60'
                            : 'ml-0 sm:ml-0 md:ml-0'
                    "
                >
                    <!-- Page Heading -->
                    <header
                        class="bg-dark-surface/80 border-b border-dark-border sticky sm:top-0 top-12 z-40 backdrop-blur-xl shadow-lg shadow-black/10 hidden sm:flex"
                    >
                        <div
                            class="items-center hidden sm:flex md:flex transition-all duration-300 ease-in-out w-60"
                            :class="
                                isOpen ? '-ml-56 opacity-0' : 'ml-2 opacity-100'
                            "
                        >
                            <Link :href="route('escritorio')" class="mr-auto">
                                <JetApplicationMark class="block h-7 w-auto" />
                            </Link>
                            <h2
                                class="text-xl font-bold text-slate-100 flex-1 text-center tracking-tight"
                            >
                                <!-- Titulo menu lateral oculto -->
                                Lhuna-Stack
                            </h2>
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-500/10 focus:outline-none focus:bg-brand-500/20 focus:text-brand-700 dark:focus:text-brand-400 transition-all duration-300"
                                @click="abreMenu()"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        class="inline-flex"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div class="max-w-full mx-auto p-4 flex-1">
                            <slot v-if="$slots.header" name="header" />
                            <h2
                                v-else
                                class="font-semibold text-xl text-slate-100 leading-tight flex items-center gap-2"
                            >
                                <div class="relative w-2 h-6 flex items-center justify-center">
                                    <div class="absolute inset-0 bg-brand-500 blur-[4px] opacity-70 rounded-full"></div>
                                    <div class="absolute inset-0 bg-brand-500 rounded-full"></div>
                                </div>
                                <span>{{ title }}</span>
                            </h2>
                        </div>
                        <!-- Settings Dropdown -->
                        <div class="relative">
                            <JetDropdown align="left" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md m-3">
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-2 border border-dark-border text-sm leading-4 font-medium rounded-xl text-slate-300 bg-dark-elevated/50 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-brand-500/10 hover:border-brand-500/30 focus:outline-none transition-all duration-300 shadow-sm"
                                        >
                                            <font-awesome-icon
                                                icon="user"
                                                class="w-5 mr-2"
                                            />
                                            {{ $page.props.user.name }}
                                            <svg
                                                class="ml-2 -mr-0.5 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <!-- Account Management -->
                                    <div
                                        class="block px-4 py-2 text-xs text-slate-500"
                                    >
                                        Usuario
                                        {{ $page.props.auth.roles[0] }}
                                    </div>

                                    <JetDropdownLink
                                        :href="route('profile.show')"
                                    >
                                        <font-awesome-icon
                                            icon="user-gear"
                                            class="w-5 mr-2"
                                        />Perfil
                                    </JetDropdownLink>

                                    <div class="border-t border-dark-border" />

                                    <button
                                        @click="toggleTheme"
                                        type="button"
                                        class="block w-full px-4 py-2 text-sm leading-5 text-left text-slate-300 hover:text-brand-700 dark:hover:text-brand-400 hover:bg-dark-elevated focus:outline-none transition-all duration-300"
                                    >
                                        <font-awesome-icon
                                            :icon="isDark ? 'sun' : 'moon'"
                                            class="w-5 mr-2"
                                        />
                                        {{ isDark ? 'Modo Claro' : 'Modo Oscuro' }}
                                    </button>

                                    <div class="border-t border-dark-border" />

                                    <!-- Authentication -->
                                    <form @submit.prevent="logout">
                                        <JetDropdownLink as="button">
                                            <font-awesome-icon
                                                icon="right-from-bracket"
                                                class="w-5 mr-2"
                                            />Cerrar sesión
                                        </JetDropdownLink>
                                    </form>
                                </template>
                            </JetDropdown>
                        </div>
                    </header>
                    <!-- Page Content -->

                    <main :key="$page.url" class="mt-12 sm:mt-0">
                        <slot />
                    </main>
                </div>
            </div>
        </div>
    </div>

    <Toast />
</template>
