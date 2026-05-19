<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { reactive, ref, toRefs } from "vue";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import Tabla from "@/Components/Tabla.vue";
import Paginador from "@/Components/Paginador.vue";
import { notify } from "notiwind";
import JetInputError from "@/Jetstream/InputError.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import Select from "@/Components/Select.vue";
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";
import GlowCard from "@/Components/GlowCard.vue";


const props = defineProps({
  usuarios: Object,
  usuario: Object,
  filtro: String,
  orderBy: Object,
  auth: Object,
  filtros: Array,
});

const form = useForm({
  name: "",
  titulo: "",
  email: "",
  password: "",
  rol: "",
});

const error = reactive({
  name: "",
  titulo: "",
  email: "",
  password: "",
});

const usuarios = toRefs(props).usuarios;
const buscar = ref(props.filtro ? props.filtro : "");
const filtros = ref(props.filtros ? props.filtros : []);
const estadoModalUsuario = ref(false);
const estadoModalEliminaUsuario = ref(false);
const accion = ref("new");
const idActual = ref(null);
const orderByObject = ref(
  props.orderBy
    ? props.orderBy
    : {
       field: "name",
       sort: "asc",
     }
);

const nuevoUsuario = () => {
  accion.value = "new";
  form.reset("name", "titulo", "email", "password", "rol");
  form.rol = "Usuario";
  error.name = "";
  error.titulo = "";
  error.email = "";
  error.password = "";
  estadoModalUsuario.value = true;
};

const editaUsuario = (usuario) => {
  accion.value = "edit";
  idActual.value = usuario.id;
  form.name = usuario.name;
  form.titulo = usuario.titulo;
  form.email = usuario.email;
  form.rol = usuario.roles.length > 0 ? usuario.roles[0].name : "";
  form.password = "";
  error.name = "";
  error.titulo = "";
  error.email = "";
  error.password = "";
  estadoModalUsuario.value = true;
};

const isAdmin = () => {
  if (props.auth.roles.length > 0) {
    if (props.auth.roles[0] === "Administrador") {
      return true;
    }
  }
};

let roles = [
  {
    name: "Administrador",
  },
  {
    name: "Usuario",
  },
];

const campos = {
  id: {
    label: "ID",
  },
  name: {
    label: "Nombre",
  },
  titulo: {
    label: "Título",
  },
  email: {
    label: "Email",
  },
  rol: {
    label: "Rol",
  },
};

const bg_color = {
  Administrador: "bg-red-600",
  Usuario: "bg-orange-600",
};

const nuevoUsuarioDo = () => {
  error.name = "";
  error.titulo = "";
  error.email = "";
  error.password = "";
  idActual.value = null;
  form.post(route("usuarios.store"), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Usuario creado",
          text: "El usuario se ha creado exitosamente",
        },
        3000
      );
      estadoModalUsuario.value = false;
      form.reset("name", "titulo", "email", "password", "rol");
    },
    onError: (errors) => {
      if (
        !errors.name &&
        !errors.titulo &&
        !errors.email &&
        !errors.password
      ) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar usuario",
          },
          3000
        );
      }

      error.name = errors.name
        ? errors.name.replace("name", "Nombre")
        : null;
      error.email = errors.email
        ? errors.email.replace("email", "Email")
        : null;
      error.password = errors.password
        ? errors.password.replace("password", "Contraseña")
        : null;
    },
  });
};

const editaUsuarioDo = () => {
  form.post(route("usuarios.update", idActual.value), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Usuario editado",
          text: "El usuario se ha editado exitosamente",
        },
        3000
      );
      estadoModalUsuario.value = false;
      form.reset("name", "titulo", "email", "password", "rol");
    },
    onError: (errors) => {
      if (
        !errors.name &&
        !errors.titulo &&
        !errors.email &&
        !errors.password
      ) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al editar usuario",
          },
          3000
        );
      }

      error.name = errors.name
        ? errors.name.replace("name", "Nombre")
        : null;
      error.email = errors.email
        ? errors.email.replace("email", "Email")
        : null;
      error.password = errors.password
        ? errors.password.replace("password", "Contraseña")
        : null;
    },
  });
};

const borraUsuarioDo = () => {
  form.post(route("usuarios.destroy", idActual.value), {
    preserveScroll: true,
    onSuccess: (rest) => {
      notify(
        {
          group: "main",
          title: "Usuario eliminado",
          text: "El usuario se ha eliminado exitosamente",
        },
        3000
      );
      estadoModalUsuario.value = false;
      estadoModalEliminaUsuario.value = false;
      form.reset("name", "titulo", "email", "password", "rol");
    },
    onError: (errors) => {
      if (
        !errors.name &&
        !errors.titulo &&
        !errors.email &&
        !errors.password
      ) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar usuario",
          },
          3000
        );
      }
    },
  });
};


</script>
<template>
  <AppLayout title="Usuarios">
    <div class="max-w-full mx-auto px-0 sm:px-8 py-4 pt-0 sm:pt-4">
      <GlowCard class="sm:rounded-lg">
        <Buscador
            v-model="buscar"
            :orderByObject="orderByObject"
            ruta="usuarios"
            :filtros="filtros"
            :campos="campos"
            autofocus
          />
          <Tabla v-if="usuarios.data.length > 0">
            <template #col>
              <th class="px-4 py-3 w-20">
                <Ordena
                  v-model="orderByObject"
                  ruta="usuarios"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="ID"
                  campo="id"
                />
              </th>
              <th class="px-4 py-3">
                <Ordena
                  v-model="orderByObject"
                  ruta="usuarios"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="Nombre"
                  campo="name"
                />
              </th>
              <th class="px-4 py-3">
                <Ordena
                  v-model="orderByObject"
                  ruta="usuarios"
                  :buscar="buscar"
                  :filtros="filtros"
                  titulo="Título"
                  campo="titulo"
                />
              </th>
              <th class="px-4 py-3 w-36">Rol</th>
              <th
                class="px-4 py-3 w-5 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border"
              >
                <button 
                    @click="nuevoUsuario" 
                    class="inline-flex items-center justify-center px-3 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold transition duration-200 shadow-md shadow-brand-500/20 hover:shadow-lg hover:shadow-brand-500/30"
                    title="Agregar Usuario"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
              </th>
            </template>
            <template #row>
              <tr
                class="text-slate-300 hover:bg-dark-elevated border-b border-dark-border"
                v-for="usuario in usuarios.data"
                :key="usuario.id"
              >
                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-300">{{ usuario.id }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-brand-500/20 flex items-center justify-center text-xs font-bold text-brand-500 uppercase">
                      {{ usuario.name.slice(0, 2) }}
                    </div>
                    <div>
                      <p class="text-sm font-medium text-slate-700 dark:text-slate-200 text-left">{{ usuario.name }}</p>
                      <p class="text-xs text-slate-500 dark:text-slate-400 text-left">{{ usuario.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 text-left">{{ usuario.titulo }}</td>
                <td class="px-4 py-3 text-left">
                  <div v-for="p in usuario.roles" :key="p.id">
                    <span
                      v-if="p.name === 'Administrador'"
                      class="px-2 py-0.5 rounded text-xs font-semibold bg-red-500/10 text-red-700 dark:text-red-400 border border-red-500/20 nowrap"
                    >
                      {{ p.name }}
                    </span>
                    <span
                      v-else
                      class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20 nowrap"
                    >
                      {{ p.name }}
                    </span>
                  </div>
                </td>
                <td
                  class="px-4 py-3 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border"
                >
                  <button
                    @click="editaUsuario(usuario)"
                    class="inline-flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded transition duration-200 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </button>
                </td>
              </tr>
            </template>
            <template #pagination>
              <Paginador
                :links="usuarios.links"
                :total="usuarios.total"
                :to="usuarios.to"
                :from="usuarios.from"
              />
            </template>
          </Tabla>
          <div v-else class="text-center p-5 text-slate-400 text-md">
            Sin Usuarios
            <br />
            <button 
                @click="nuevoUsuario" 
                class="mt-2 inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-lg font-semibold text-xs uppercase tracking-widest transition duration-200 shadow-md shadow-brand-500/20"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Agregar Nuevo Usuario
            </button>
          </div>
      </GlowCard>
    </div>

    <form
      class="flex flex-col"
      @submit.prevent="accion === 'new' ? nuevoUsuarioDo : editaUsuarioDo"
    >
      <JetDialogModal
        :show="estadoModalUsuario"
        @close="estadoModalUsuario = false"
        max-width="md"
      >
        <template #title
          >{{
            accion === "new"
              ? "Crear nuevo usuario"
              : "Editar usuario"
          }}
          <JetDangerButton
            v-if="accion === 'edit'"
            @click="estadoModalEliminaUsuario = true"
            class="-mt-1 float-right"
            :disabled="idActual === 1 || form.processing"
          >
            <font-awesome-icon icon="trash" class="mr-2" />
            Eliminar Usuario
          </JetDangerButton>
        </template>
        <template #content>
          <label class="block mb-1">
            <JetLabel
              for="name"
              value="Nombre"
              class="float-left"
            />

            <JetInput
              id="name"
              v-model="form.name"
              placeholder="Juan Perez"
              type="text"
              class="mt-1 block w-full"
              required
              autofocus
            />

            <JetInputError :message="error.name" class="mt-2" />
          </label>
          <label class="block mb-1">
            <JetLabel
              for="titulo"
              value="Titulo"
              class="float-left"
            />

            <JetInput
              id="titulo"
              v-model="form.titulo"
              placeholder="Ing."
              type="text"
              class="mt-1 block w-full"
              required
            />

            <JetInputError :message="error.titulo" class="mt-2" />
          </label>
          <label class="block mb-1">
            <JetLabel
              for="email"
              value="Email"
              class="float-left"
            />
            <JetInput
              id="email"
              placeholder="correo@dominio.com"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full"
              required
            />
            <JetInputError :message="error.email" class="mt-2" />
          </label>
          <label class="block">
            <JetLabel
              for="password"
              value="Contraseña"
              class="float-left"
            />
            <JetInput
              id="password"
              placeholder="C0n7Ras3ña.Se6uR4"
              v-model="form.password"
              type="text"
              class="mt-1 block w-full font-mono"
              required
            />
            <JetInputError :message="error.password" class="mt-2" />
          </label>

          <div class="pt-2 pb-1">
            <div class="border-t border-dark-border" />
          </div>
          <label class="block">
            <JetLabel for="rol" value="Rol" class="float-left" />
            <Select
              id="rol"
              placeholder="Rol"
              v-model="form.rol"
              :value="form.rol"
              class="mt-1 block w-full"
              required
              :options="roles"
              :disabled="idActual === 1"
            />
            <JetInputError :message="error.rol" class="mt-2" />
          </label>
        </template>
        <template #footer>
          <div class="w-1/2">
            <JetSecondaryButton
              @click="estadoModalUsuario = false"
              class="bg-opacity-95"
              type="cancel"
              :disabled="form.processing"
              :class="{ 'opacity-25': form.processing }"
              >Cancelar</JetSecondaryButton
            >
          </div>
          <div class="w-1/2">
            <JetButton
              v-if="accion === 'new'"
              @click="nuevoUsuarioDo"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              class="float-right"
            >
              <template v-if="form.processing">
                <font-awesome-icon
                  icon="spinner"
                  class="mr-2 animate-spin"
                />Creando...
              </template>
              <template v-else>
                <font-awesome-icon
                  icon="plus"
                  class="mr-2"
                />Crear Usuario
              </template>
            </JetButton>

            <JetButton
              v-if="accion === 'edit'"
              @click="editaUsuarioDo"
              :class="{ 'opacity-25': form.processing }"
              class="float-right"
              :disabled="form.processing"
            >
              <template v-if="form.processing">
                <font-awesome-icon
                  icon="spinner"
                  class="mr-2 animate-spin"
                />Guardando...
              </template>
              <template v-else>
                <font-awesome-icon
                  icon="floppy-disk"
                  class="mr-2"
                />Guardar Cambios
              </template>
            </JetButton>
          </div>
        </template>
      </JetDialogModal>
    </form>
    <JetConfirmationModal
      :show="estadoModalEliminaUsuario"
      @close="estadoModalEliminaUsuario = false"
    >
      <template #title>Eliminar usuario</template>
      <template #content>
        <h4 class="block mb-1 text-lg">
          ¿Realmente desea eliminar este usuario?
        </h4>
        <span class="text-red-600"
          >Esta acción no se puede deshacer.</span
        >
      </template>
      <template #footer>
        <div class="w-1/2">
          <JetSecondaryButton
            @click="estadoModalEliminaUsuario = false"
            class="bg-opacity-95"
            type="cancel"
            :disabled="form.processing"
            :class="{ 'opacity-25': form.processing }"
            >Cancelar</JetSecondaryButton
          >
        </div>
        <div class="w-1/2">
          <JetDangerButton
            @click="borraUsuarioDo"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            class="float-right"
          >
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar
            Usuario
          </JetDangerButton>
        </div>
      </template>
    </JetConfirmationModal>
  </AppLayout>
</template>
