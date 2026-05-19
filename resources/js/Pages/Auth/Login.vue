<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import JetAuthenticationCard from "@/Jetstream/AuthenticationCard.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetInput from "@/Jetstream/Input.vue";
import JetCheckbox from "@/Jetstream/Checkbox.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetValidationErrors from "@/Jetstream/ValidationErrors.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const shakeCard = ref(false);

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
        onError: () => {
            shakeCard.value = true;
            // Shaking animation lasts 400ms, reset after 500ms
            setTimeout(() => {
                shakeCard.value = false;
            }, 500);
        }
    });
};
</script>

<template>
    <Head title="Acceso" />

    <JetAuthenticationCard :shake="shakeCard">
        <template #logo>
            <div class="relative flex items-center justify-center w-48 h-48 mb-2">
                <!-- Concentric pulsing rings (Subtle 25% opacity borders) -->
                <div class="absolute inset-0 rounded-full border border-dark-border/25 animate-ripple-1"></div>
                <div class="absolute inset-5 rounded-full border border-dark-border/25 animate-ripple-2"></div>
                <div class="absolute inset-10 rounded-full border border-dark-border/25 animate-ripple-3"></div>

                <!-- Logo Image (No Link, 25% larger: w-[120px]) -->
                <img :src="$page.props.logo" class="w-[120px] h-[120px] relative z-10 select-none pointer-events-none" />
            </div>
        </template>

        <!-- Welcome header -->
        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-slate-100">¡Te damos la bienvenida!</h2>
            <p class="text-xs text-slate-400 mt-1">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        <JetValidationErrors />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-500">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <JetLabel for="email" value="Email" class="text-slate-300 font-semibold mb-1" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"></path>
                        </svg>
                    </span>
                    <JetInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="block w-full pl-10 bg-dark-surface/50 border-dark-border text-slate-100 placeholder-slate-500 focus:border-brand-500 focus:ring-brand-500/30"
                        placeholder="tucorreo@ejemplo.com"
                        required
                        autofocus
                    />
                </div>
            </div>

            <div>
                <JetLabel for="password" value="Contraseña" class="text-slate-300 font-semibold mb-1" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <svg class="w-5 h-5 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    <JetInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="block w-full pl-10 bg-dark-surface/50 border-dark-border text-slate-100 placeholder-slate-500 focus:border-brand-500 focus:ring-brand-500/30"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <JetCheckbox
                        v-model:checked="form.remember"
                        name="remember"
                        class="rounded border-dark-border bg-dark-surface/50 text-brand-500 focus:ring-brand-500"
                    />
                    <span class="ml-2 text-sm text-slate-400 group-hover:text-slate-200 transition-colors select-none">Recordarme</span>
                </label>
            </div>

            <div class="flex items-center justify-between pt-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-slate-400 hover:text-slate-100 transition-colors"
                >
                    ¿Olvidó su contraseña?
                </Link>

                <JetButton
                    class="ml-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l4-4m0 0l-4-4m4 4H3m5 4v1a3 3 0 003 3h7a3 3 0 003-3V7a3 3 0 00-3-3h-7a3 3 0 00-3 3v1"></path>
                    </svg>
                    <span>Ingresar</span>
                </JetButton>
            </div>
        </form>
    </JetAuthenticationCard>
</template>

<style scoped>
@keyframes ripple {
  0% {
    transform: scale(0.85);
    opacity: 0;
  }
  50% {
    opacity: 0.25; /* Peak opacity at 25% */
  }
  100% {
    transform: scale(1.25);
    opacity: 0;
  }
}

.animate-ripple-1 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
}

.animate-ripple-2 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
  animation-delay: 1.3s;
}

.animate-ripple-3 {
  animation: ripple 4s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
  animation-delay: 2.6s;
}
</style>
