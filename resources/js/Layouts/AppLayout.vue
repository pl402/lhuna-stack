<script setup>
import { ref, computed, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head, Link, usePage } from "@inertiajs/inertia-vue3";
import JetApplicationMark from "@/Jetstream/ApplicationMark.vue";
import JetBanner from "@/Jetstream/Banner.vue";
import JetDropdown from "@/Jetstream/Dropdown.vue";
import JetDropdownLink from "@/Jetstream/DropdownLink.vue";
import JetNavLink from "@/Jetstream/NavLink.vue";
import JetResponsiveNavLink from "@/Jetstream/ResponsiveNavLink.vue";
import ActionMessage from "../../../vendor/laravel/jetstream/stubs/inertia/resources/js/Jetstream/ActionMessage.vue";
import Toast from "../Components/Toast.vue";
import { notify } from "notiwind";

defineProps({
  title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
  Inertia.put(
    route("current-team.update"),
    {
      team_id: team.id,
    },
    {
      preserveState: false,
    }
  );
};

const logout = () => {
  Inertia.post(route("logout"));
};

const error = computed(() => usePage().props.value.flash?.error || null);
const show = ref(false);

watch(error, async () => {
  show.value = true;
  await notify(
    {
      group: "error",
      title: "Ocurrió un error",
      text: error.value,
    },
    3000
  );
  notify(
    {
      group: "error",
      title: "Ocurrió un error",
      text: error.value,
    },
    3000
  );
});

watch(show, async () => { });

const menu = [
  {
    name: "Escritorio",
    href: route('escritorio'),
    active: route().current('escritorio'),
    show: true,
  },
  {
    name: "Usuarios",
    href: route('usuarios.index'),
    active: route().current('usuarios.index') ||
      route().current('usuarios.search') ||
      route().current('usuarios'),
    show: usePage().props.value.auth.roles[0] === 'Administrador' ||
      usePage().props.value.auth.roles[0] === 'Contraloría',
  },
  {
    name: "Areas",
    href: route('areas.index'),
    active: route().current('areas.index') ||
      route().current('areas.search') ||
      route().current('areas'),
    show: usePage().props.value.auth.roles[0] === 'Administrador',
  },
  {
    name: "Reportes",
    href: route('reportes.index'),
    active: route().current('reportes.index') ||
      route().current('reportes.search') ||
      route().current('reportes'),
    show: usePage().props.value.auth.roles[0] === 'Administrador' ||
      usePage().props.value.auth.roles[0] === 'Contraloría',
  },
  {
    name: "Configuraciones",
    href: route('configuraciones.index'),
    active: route().current('configuraciones.index') ||
      route().current('configuraciones.search') ||
      route().current('configuraciones'),
    show: usePage().props.value.auth.roles[0] === 'Administrador',
  },
]
</script>

<template>
  <div>

    <Head :title="title" />
    <div class="min-h-screen bg-slate-100">
      <nav class="
          bg-slate-100/80
          border-b border-slate-100
          sticky
          top-0
          z-50
          shadow-lg
          backdrop-blur
        ">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-12">
            <div class="flex">
              <!-- Logo -->
              <div class="shrink-0 flex items-center">
                <Link :href="route('escritorio')">
                <JetApplicationMark class="block h-7 w-auto" />
                </Link>
              </div>

              <!-- Navigation Links -->
              <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <JetNavLink v-for="(item, index) in menu" :key="index" :href="item.href" :active="item.active"
                  v-show="item.show">
                  {{ item.name }}
                </JetNavLink>
              </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <div class="ml-3 relative">
                <!-- Teams Dropdown -->
                <JetDropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button type="button" class="
                          inline-flex
                          items-center
                          px-3
                          py-2
                          border border-transparent
                          text-sm
                          leading-4
                          font-medium
                          rounded-md
                          text-slate-500
                          bg-white
                          hover:bg-slate-50 hover:text-slate-700
                          focus:outline-none focus:bg-slate-50
                          active:bg-slate-50
                          transition
                        ">
                        {{ $page.props.user.current_team.name }}

                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                          fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <!-- Team Management -->
                      <template v-if="$page.props.jetstream.hasTeamFeatures">
                        <div class="block px-4 py-2 text-xs text-slate-400">
                          Administrar equipo
                        </div>

                        <!-- Team Settings -->
                        <JetDropdownLink :href="
                          route('teams.show', $page.props.user.current_team)
                        ">
                          Configuración del equipo
                        </JetDropdownLink>

                        <JetDropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                          Crear nuevo equipo
                        </JetDropdownLink>

                        <div class="border-t border-slate-300" />

                        <!-- Team Switcher -->
                        <div class="block px-4 py-2 text-xs text-slate-400">
                          Cambiar de equipo
                        </div>

                        <template v-for="team in $page.props.user.all_teams" :key="team.id">
                          <form @submit.prevent="switchToTeam(team)">
                            <JetDropdownLink as="button">
                              <div class="flex items-center">
                                <svg v-if="
                                  team.id === $page.props.user.current_team_id
                                " class="mr-2 h-5 w-5 text-green-400" fill="none" stroke-linecap="round"
                                  stroke-linejoin="round" stroke-width="2" stroke="currentColor" viewBox="0 0 24 24">
                                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                  {{ team.name }}
                                </div>
                              </div>
                            </JetDropdownLink>
                          </form>
                        </template>
                      </template>
                    </div>
                  </template>
                </JetDropdown>
              </div>

              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <JetDropdown align="right" width="48">
                  <template #trigger>
                    <button v-if="$page.props.jetstream.managesProfilePhotos" class="
                        flex
                        text-sm
                        border-2 border-transparent
                        rounded-full
                        focus:outline-none focus:border-slate-300
                        transition
                      ">
                      <img class="h-8 w-8 rounded-full object-cover" :src="$page.props.user.profile_photo_url"
                        :alt="$page.props.user.name" />
                    </button>

                    <span v-else class="inline-flex rounded-md">
                      <button type="button" class="
                          inline-flex
                          items-center
                          px-3
                          py-2
                          border border-transparent
                          text-sm
                          leading-4
                          font-medium
                          rounded-md
                          text-slate-500
                          bg-white
                          hover:text-slate-700
                          focus:outline-none
                          transition
                        ">
                        {{ $page.props.user.name }}

                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                          fill="currentColor">
                          <path fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <!-- Account Management -->
                    <div class="block px-4 py-2 text-xs text-slate-400">
                      Usuario {{ $page.props.auth.roles[0] }}
                    </div>

                    <JetDropdownLink :href="route('profile.show')">
                      Perfil
                    </JetDropdownLink>

                    <JetDropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                      API Tokens
                    </JetDropdownLink>

                    <div class="border-t border-slate-300" />

                    <!-- Authentication -->
                    <form @submit.prevent="logout">
                      <JetDropdownLink as="button">
                        Cerrar sesión
                      </JetDropdownLink>
                    </form>
                  </template>
                </JetDropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button class="
                  inline-flex
                  items-center
                  justify-center
                  p-2
                  rounded-md
                  text-slate-400
                  hover:text-slate-500 hover:bg-slate-100
                  focus:outline-none focus:bg-slate-100 focus:text-slate-500
                  transition
                " @click="showingNavigationDropdown = !showingNavigationDropdown">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path :class="{
                    hidden: showingNavigationDropdown,
                    'inline-flex': !showingNavigationDropdown,
                  }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path :class="{
                    hidden: !showingNavigationDropdown,
                    'inline-flex': showingNavigationDropdown,
                  }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{
          block: showingNavigationDropdown,
          hidden: !showingNavigationDropdown,
        }" class="sm:hidden">
          <div class="pt-2 pb-3 space-y-1">
            <JetResponsiveNavLink v-for="(item, index) in menu" :key="index" :href="item.href" :active="item.active"
              v-show="item.show">
              {{ item.name }}
            </JetResponsiveNavLink>
          </div>

          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="flex items-center px-4">
              <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 mr-3">
                <img class="h-10 w-10 rounded-full object-cover" :src="$page.props.user.profile_photo_url"
                  :alt="$page.props.user.name" />
              </div>

              <div>
                <div class="font-medium text-base text-slate-800">
                  {{ $page.props.user.name }}
                </div>
                <div class="font-medium text-sm text-slate-500">
                  Usuario {{ $page.props.auth.roles[0] }}
                </div>
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <JetResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                Perfil
              </JetResponsiveNavLink>

              <JetResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')"
                :active="route().current('api-tokens.index')">
                API Tokens
              </JetResponsiveNavLink>

              <!-- Authentication -->
              <form method="POST" @submit.prevent="logout">
                <JetResponsiveNavLink as="button">
                  Cerrar sesión
                </JetResponsiveNavLink>
              </form>

              <!-- Team Management -->
              <template v-if="$page.props.jetstream.hasTeamFeatures">
                <div class="border-t border-slate-300" />

                <div class="block px-4 py-2 text-xs text-slate-400">
                  Administrar equipo
                </div>

                <!-- Team Settings -->
                <JetResponsiveNavLink :href="route('teams.show', $page.props.user.current_team)"
                  :active="route().current('teams.show')">
                  Configuración del equipo
                </JetResponsiveNavLink>

                <JetResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')"
                  :active="route().current('teams.create')">
                  Crear nuevo equipo
                </JetResponsiveNavLink>

                <div class="border-t border-slate-300" />

                <!-- Team Switcher -->
                <div class="block px-4 py-2 text-xs text-slate-400">
                  Cambiar de equipo
                </div>

                <template v-for="team in $page.props.user.all_teams" :key="team.id">
                  <form @submit.prevent="switchToTeam(team)">
                    <JetResponsiveNavLink as="button">
                      <div class="flex items-center">
                        <svg v-if="team.id === $page.props.user.current_team_id" class="mr-2 h-5 w-5 text-green-400"
                          fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          stroke="currentColor" viewBox="0 0 24 24">
                          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>{{ team.name }}</div>
                      </div>
                    </JetResponsiveNavLink>
                  </form>
                </template>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header v-if="$slots.header" class="bg-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto py-2 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="mb-8">
        <slot />
      </main>

      <!-- Page Footer -->
      <footer class="bg-slate-200/80
        shadow-2xl
        fixed
        bottom-0
        w-screen
        border-t
        border-slate-100
        backdrop-blur">
        <div class="max-w-7xl mx-auto py-1 px-4 sm:px-6 lg:px-8">
          <!-- copyrigth  -->
          <div class="flex justify-between items-center text-sm text-slate-400">
            <div>
              <a href="https://lhuna.dev" target="_blank">© lhuna.dev - 2022</a>
            </div>
            <div>
              Sistema de Entrega Recepción (SER)
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>
  <Toast />
</template>
