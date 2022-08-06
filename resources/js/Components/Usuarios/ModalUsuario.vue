<script setup>
import { usePage, Link, useForm } from "@inertiajs/inertia-vue3";
import { reactive, ref, watch, toRefs } from "vue";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";

const props = defineProps({
  accion: {
    type: String,
    default: "submit",
  },
  estadoModalUsuario: {
    type: Boolean,
    default: false,
  },
  idActual: {
    type: Number,
    default: "",
  },
  usuario: {
    type: Object,
    default: {},
  },
});

const accion = ref(props.accion);
const idActual = ref(props.idActual);
const usuario = ref(props.usuario);

const form = useForm({
  name: usuario.value?.name || "",
  email: "sssss",
  password: "",
  rol: "",
});

const error = reactive({
  name: "",
  email: "",
  password: "",
});

const rolles = reactive({
  name: "",
  email: "",
  password: "",
});

const estadoModalEliminaUsuario = ref(false);
const roles = [
  {
    name: "Administrador",
  },
  {
    name: "Contraloría",
  },
  {
    name: "Jefe de Área",
  },
  {
    name: "Enlace",
  },
];

const nuevoUsuarioDo = () => {
  error.name = "";
  error.email = "";
  error.password = "";
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
      form.reset("name", "email", "password", "rol");
    },
    onError: (errors) => {
      if (!errors.name && !errors.email && !errors.password) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al registrar usuario",
          },
          3000
        );
      }

      error.name = errors.name ? errors.name.replace("name", "Nombre") : null;
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
      form.reset("name", "email", "password", "rol");
    },
    onError: (errors) => {
      if (!errors.name && !errors.email && !errors.password) {
        notify(
          {
            group: "error",
            title: "Ocurrió un error",
            text: "Error al editar usuario",
          },
          3000
        );
      }

      error.name = errors.name ? errors.name.replace("name", "Nombre") : null;
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
      form.reset("name", "email", "password", "rol");
    },
    onError: (errors) => {
      if (!errors.name && !errors.email && !errors.password) {
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
<script>
export default {};
</script>

<template>
  <form class="flex flex-col" @submit.prevent="accion === 'new' ? nuevoUsuarioDo : editaUsuarioDo">
    <JetDialogModal v-if="estadoModalUsuario === true" :show="estadoModalUsuario" @close="estadoModalUsuario = false"
      max-width="md" id="elmodal">
      <template #title>{{ accion === "new" ? "Crear nuevo usuario" : "Editar usuario" }}
        <JetDangerButton v-if="accion === 'edit'" @click="estadoModalEliminaUsuario = true" class="-mt-1 float-right">
          <font-awesome-icon icon="trash" class="mr-2" />
          Eliminar Usuario
        </JetDangerButton>
      </template>
      <template #content>
        <label class="block mb-1">
          <JetLabel for="name" value="Nombre" class="float-left" />

          <JetInput id="name" v-model="form.name" placeholder="Juan Perez" type="text" class="mt-1 block w-full"
            required autofocus />

          <JetInputError :message="error.name" class="mt-2" />
        </label>
        <label class="block mb-1">
          <JetLabel for="email" value="Email" class="float-left" />
          <JetInput id="email" placeholder="correo@dominio.com" v-model="form.email" type="email"
            class="mt-1 block w-full" required />
          <JetInputError :message="error.email" class="mt-2" />
        </label>
        <label class="block">
          <JetLabel for="password" value="Contraseña" class="float-left" />
          <JetInput id="password" placeholder="C0n7Ras3ña.Se6uR4" v-model="form.password" type="text"
            class="mt-1 block w-full font-mono" required />
          <JetInputError :message="error.password" class="mt-2" />
        </label>

        <div class="pt-2 pb-1">
          <div class="border-t border-gray-200" />
        </div>
        <label class="block">
          <JetLabel for="rol" value="Rol" class="float-left" />
          <Select id="rol" placeholder="Rol" v-model="form.rol" :value="form.rol" class="mt-1 block w-full" required
            :options="roles" />
          <JetInputError :message="error.rol" class="mt-2" />
        </label>
      </template>
      <template #footer>
        <div class="w-1/2">
          <JetButton @click="estadoModalUsuario = false" class="bg-opacity-95" type="cancel" :disabled="form.processing"
            :class="{ 'opacity-25': form.processing }">Cancelar</JetButton>
        </div>
        <div class="w-1/2">
          <JetButton v-if="accion === 'new'" @click="nuevoUsuarioDo" :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing" class="float-right">
            <font-awesome-icon icon="user-plus" class="mr-2" />Crear Usuario
          </JetButton>

          <JetButton v-if="accion === 'edit'" @click="editaUsuarioDo" :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing" class="float-right">
            <font-awesome-icon icon="floppy-disk" class="mr-2" />Guardar Cambios
          </JetButton>
        </div>
      </template>
    </JetDialogModal>
    <JetDialogModal v-if="estadoModalEliminaUsuario === true" :show="estadoModalEliminaUsuario"
      @close="estadoModalEliminaUsuario = false" max-width="md">
      <template #title>Eliminar usuario</template>
      <template #content>
        <h4 class="block mb-1 text-lg">
          ¿Realmente desea eliminar el usuario?
        </h4>
        <span class="text-red-600">Esta acción no se puede deshacer.</span>
      </template>
      <template #footer>
        <div class="w-1/2">
          <JetButton @click="estadoModalEliminaUsuario = false" class="bg-opacity-95" type="cancel"
            :disabled="form.processing" :class="{ 'opacity-25': form.processing }">Cancelar</JetButton>
        </div>
        <div class="w-1/2">
          <JetDangerButton @click="borraUsuarioDo" :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing" class="float-right">
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar Usuario
          </JetDangerButton>
        </div>
      </template>
    </JetDialogModal>
  </form>
</template>