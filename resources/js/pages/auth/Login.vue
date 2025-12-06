<script setup lang="ts">
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import InlineMessage from 'primevue/inlinemessage';

defineProps({
    status: String,
    canResetPassword: Boolean
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});
const loginError = ref('');

const submit = () => {
    loginError.value = ''; // reset
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: () => {
            // Mostrar mensaje de error
            loginError.value = 'Usuario o Contraseña incorrecto, por favor inténtelo nuevamente.';

            // Ocultar mensaje después de 3 segundos
            setTimeout(() => {
                loginError.value = '';
            }, 2000);
        }
    });
};

</script>

<template>
  <FloatingConfigurator />
  <Head title="Log in" />

  <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen w-full px-4 sm:px-6">

    <div class="w-full max-w-4xl bg-surface-0 dark:bg-surface-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row">

      <!-- Sección de imagen -->
      <div class="hidden md:block w-1/2 relative">
        <img 
          src="/imagenes/access/logis.jpg"
          alt="Restaurante"
          class="h-full w-full object-cover"
        />

        <div class="absolute inset-0 bg-black/10 flex items-center justify-center">
        </div>
      </div>

      <!-- Sección del formulario -->
      <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center">

        <!-- Encabezado -->
        <div class="text-center mb-8">
          <h1 class="text-surface-900 dark:text-surface-0 text-3xl font-bold mb-2">
            ¡Bienvenido!
          </h1>

          <p class="text-muted-color text-base">
            Inicia sesión para continuar
          </p>

          <p class="text-red-500 text-sm font-semibold mt-1 tracking-wide">
            ⚠ Solo personal autorizado
          </p>
        </div>

        <!-- Mensaje de éxito -->
        <Message 
          v-if="status"
          severity="success"
          :closable="false"
          class="mb-4"
        >
          {{ status }}
        </Message>

        <!-- Formulario -->
        <form @submit.prevent="submit" class="space-y-6">

          <!-- Usuario -->
          <div>
            <label 
              for="username"
              class="block text-surface-900 dark:text-surface-0 font-medium mb-1"
            >Usuario</label>

            <InputText
              id="username"
              v-model="form.username"
              placeholder="Usuario"
              class="w-full"
              :class="{ 'p-invalid': form.errors.username }"
              autocomplete="username"
              required
            />

            <InlineMessage 
              v-if="form.errors.username"
              severity="error"
              class="w-full mt-1"
            >
              {{ form.errors.username }}
            </InlineMessage>
          </div>

          <!-- Contraseña -->
          <div>
            <label 
              for="password"
              class="block text-surface-900 dark:text-surface-0 font-medium mb-1"
            >Contraseña</label>

            <Password
              id="password"
              v-model="form.password"
              placeholder="Contraseña"
              :toggleMask="true"
              :feedback="false"
              class="w-full"
              :class="{ 'p-invalid': form.errors.password }"
              inputClass="w-full"
              autocomplete="current-password"
              required
            />

            <InlineMessage 
              v-if="form.errors.password"
              severity="error"
              class="w-full mt-1"
            >
              {{ form.errors.password }}
            </InlineMessage>
          </div>

          <!-- Recordarme -->
          <div class="flex items-center">
            <Checkbox v-model="form.remember" binary id="remember" class="mr-2"/>
            <label 
              for="remember" 
              class="text-surface-600 dark:text-surface-300"
            >
              Recordarme
            </label>
          </div>

          <!-- Botón -->
          <Button
            type="submit"
            label="Iniciar sesión"
            class="w-full py-3 font-medium"
            :loading="form.processing"
            :disabled="form.processing"
          />

          <!-- Mensaje de error -->
          <Message
            v-if="loginError"
            severity="error"
            :closable="false"
            class="mt-2 text-sm"
          >
            {{ loginError }}
          </Message>

        </form>

      </div>
    </div>

  </div>
</template>


<style scoped>
.pi-eye,
.pi-eye-slash {
  transform: scale(1.3);
  margin-right: 0.5rem;
}

:deep(.p-password),
:deep(.p-password-input) {
  width: 100%;
}

:deep(.p-invalid) {
  border-color: var(--red-500);
}
</style>