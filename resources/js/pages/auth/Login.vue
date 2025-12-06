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
    <div class="flex flex-col items-center justify-center w-full max-w-md">
      <div
        class="w-full bg-surface-0 dark:bg-surface-900 rounded-3xl shadow-lg py-10 px-6 sm:py-16 sm:px-12"
        style="border-radius: 2rem"
      >
        <!-- Encabezado -->
        <div class="text-center mb-8">
          <h1 class="text-surface-900 dark:text-surface-0 text-2xl sm:text-3xl font-semibold mb-2">
            ¡Bienvenido!
          </h1>
          <p class="text-muted-color text-sm sm:text-base">Inicia sesión para continuar</p>
        </div>

        <!-- Mensaje de éxito -->
        <Message v-if="status" severity="success" :closable="false" class="mb-4 text-sm sm:text-base">
          {{ status }}
        </Message>

        <!-- Formulario -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Usuario -->
          <div>
            <label for="username" class="block text-surface-900 dark:text-surface-0 text-base font-medium mb-1">
              Usuario
            </label>
            <InputText
              id="username"
              type="text"
              placeholder="Usuario"
              class="w-full mb-1"
              v-model="form.username"
              :class="{ 'p-invalid': form.errors.username }"
              autofocus
              required
              autocomplete="username"
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
            <label for="password" class="block text-surface-900 dark:text-surface-0 text-base font-medium mb-1">
              Contraseña
            </label>
            <Password
              id="password"
              v-model="form.password"
              placeholder="Contraseña"
              :toggleMask="true"
              class="w-full"
              :class="{ 'p-invalid': form.errors.password }"
              :feedback="false"
              required
              autocomplete="current-password"
              inputClass="w-full"
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
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <Checkbox v-model="form.remember" id="remember" binary class="mr-2" />
              <label for="remember" class="text-surface-600 dark:text-surface-300 text-sm sm:text-base">
                Recordarme
              </label>
            </div>
          </div>

          <!-- Botón -->
          <Button
            type="submit"
            label="Iniciar sesión"
            class="w-full"
            :loading="form.processing"
            :disabled="form.processing"
          />
          <Message
              v-if="loginError"
              severity="error"
              :closable="false"
              class="mb-4 text-sm sm:text-base"
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