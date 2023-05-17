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

defineProps({
    title: String,
});

const showDropNav = ref(false);

const logout = () => {
    router.post(route("logout"));
};
const isOpen = ref(
    localStorage.menuShow ? localStorage.menuShow === "true" : true
);
const error = computed(() => usePage().props.flash?.error || null);

const cierraMenu = () => {
    isOpen.value = false;
    localStorage.menuShow = isOpen.value;
};

const abreMenu = () => {
    isOpen.value = true;
    localStorage.menuShow = isOpen.value;
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

.slide-enter-active,
.slide-leave-active {
    transition: all 0.5s ease-out;
}

.slide-enter,
.slide-leave-to {
    transform: translateX(-100%);
}

.page-enter-active,
.page-leave-active {
    transition: opacity 0.5s;
}

.page-enter,
.page-leave-to {
    opacity: 0;
}
</style>

<template>
    <div>
        <Head :title="title" />
        <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">
            <nav
                class="sm:hidden bg-white/80 border-b border-slate-100 fixed top-0 z-50 w-full backdrop-blur-sm shadow"
            >
                <!-- Primary Navigation Menu -->
                <div
                    class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-100 shadow"
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
                            class="text-xl font-bold text-slate-900 flex my-auto"
                        >
                            Lhuna-Stack
                        </h2>
                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition"
                                @click="showDropNav = !showDropNav"
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
                    <div class="border-t border-slate-200 my-[1px]"></div>
                    <!-- Responsive Settings Options -->
                    <div>
                        <div class="space-y-1">
                            <JetResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                <font-awesome-icon
                                    icon="user"
                                    class="w-5 mr-2"
                                />Usuario
                                {{ $page.props.auth.roles[0] }}
                                <div class="font-medium text-sm text-slate-800">
                                    {{ $page.props.user.name }}
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
                    class="flex-col h-screen bg-white border-r border-slate-200 shadow fixed transition-all duration-300 ease-in-out z-50"
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
                                class="text-xl font-bold text-slate-900 flex-1 text-center"
                            >
                                Lhuna-Stack
                            </h2>
                            <div class="ml-auto">
                                <button
                                    class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition"
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
                                class="border-t border-slate-200 my-[1px]"
                            ></div>
                            <!-- Responsive Settings Options -->
                            <div>
                                <div class="space-y-1">
                                    <JetResponsiveNavLink
                                        :href="route('profile.show')"
                                        :active="
                                            route().current('profile.show')
                                        "
                                    >
                                        <font-awesome-icon
                                            icon="user"
                                            class="w-5 mr-2"
                                        />Usuario
                                        {{ $page.props.auth.roles[0] }}
                                        <div
                                            class="font-medium text-sm text-slate-800"
                                        >
                                            {{ $page.props.user.name }}
                                        </div>
                                    </JetResponsiveNavLink>

                                    <!-- Authentication -->
                                    <form
                                        method="POST"
                                        @submit.prevent="logout"
                                    >
                                        <JetResponsiveNavLink as="button">
                                            <font-awesome-icon
                                                icon="right-from-bracket"
                                                class="w-5 mr-2"
                                            />Cerrar sesión
                                        </JetResponsiveNavLink>
                                    </form>
                                </div>

                                <!-- Page Footer -->
                                <footer
                                    class="bg-white shadow-2xl absolute bottom-0 w-full border-t border-slate-100"
                                >
                                    <div class="py-1 px-4 text-center">
                                        <!-- copyrigth  -->
                                        <div class="text-xs text-slate-400">
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
                        class="bg-white/80 border-b border-slate-300/90 sticky sm:top-0 top-12 z-10 backdrop-blur-sm shadow flex"
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
                                class="text-xl font-bold text-slate-900 flex-1 text-center"
                            >
                                Lhuna-Stack
                            </h2>
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 focus:outline-none focus:bg-slate-100 focus:text-slate-500 transition"
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
                                class="font-semibold text-xl text-slate-800 leading-tight"
                            >
                                {{ title }}
                            </h2>
                        </div>
                    </header>
                    <Transition name="page" mode="out-in" appear>
                        <!-- Page Content -->
                        <main :key="$page.url" class="mt-12 sm:mt-0">
                            <slot />
                        </main>
                    </Transition>
                </div>
            </div>
        </div>
    </div>

    <Toast />
</template>
