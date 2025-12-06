<script setup lang="ts">
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from 'primevue/button';
import Password from 'primevue/password';
import Message from 'primevue/message';
import { router } from '@inertiajs/vue3';

interface PrimeVueComponent {
    focus?: () => void;
    [key: string]: any;
}

const passwordInput = ref<PrimeVueComponent | null>(null);
const currentPasswordInput = ref<PrimeVueComponent | null>(null);
const formSubmitted = ref(false);
const errorMessage = ref('');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const logFormState = () => {
    console.log('Estado del formulario:', {
        values: {
            current_password: form.current_password ? '****' : '',
            password: form.password ? '****' : '',
            password_confirmation: form.password_confirmation ? '****' : '',
        },
        processing: form.processing,
        errors: form.errors,
    });
};

const updatePassword = () => {
    formSubmitted.value = true;
    errorMessage.value = '';
    
    // Verificar que los campos estén completos
    if (!form.current_password || !form.password || !form.password_confirmation) {
        console.error('Formulario incompleto');
        return;
    }
    
    // Verificar que las contraseñas coincidan
    if (form.password !== form.password_confirmation) {
        errorMessage.value = 'Las contraseñas no coinciden';
        return;
    }
    
    console.log('Enviando formulario...');
    
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Contraseña actualizada con éxito');
            form.reset();
            // Redireccionamos al Dashboard después de la actualización
            router.get(route('dashboard')); // ← Esto redirige al Dashboard
        },
        onError: (errors: any) => {
            console.error('Errores en el formulario:', errors);
            
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                // Enfocar el campo password usando querySelector como alternativa segura
                setTimeout(() => {
                    (document.querySelector('#password input') as HTMLElement)?.focus();
                }, 100);
            }

            if (errors.current_password) {
                form.reset('current_password');
                // Enfocar el campo current_password usando querySelector como alternativa segura
                setTimeout(() => {
                    (document.querySelector('#current_password input') as HTMLElement)?.focus();
                }, 100);
            }
        },
    });
};

</script>

<template>
  <FloatingConfigurator />

  <Head title="Password" />
  <div
    class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen w-full px-4 sm:px-6"
  >
    <div
      class="w-full max-w-4xl bg-surface-0 dark:bg-surface-900 rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row"
    >
      <!-- IMAGEN LATERAL -->
      <div class="hidden md:block w-1/2 relative">
        <img
          src="/imagenes/access/logis.jpg"
          alt="Imagen"
          class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-black/10 flex items-center justify-center">
        </div>
      </div>

      <!-- FORMULARIO -->
      <div class="w-full md:w-1/2 p-8 sm:p-12 flex flex-col justify-center">
        <!-- Encabezado -->
        <div class="text-center mb-8">
          <h1 class="text-surface-900 dark:text-surface-0 text-3xl font-bold mb-2">
            Actualizar Contraseña
          </h1>
          <p class="text-muted-color text-sm">
            Mantén tu cuenta protegida usando una contraseña segura y dificil de adivinar.
          </p>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="updatePassword" class="space-y-7">
          
          <!-- Contraseña Actual -->
          <div>
            <label
              for="current_password"
              class="block text-surface-900 dark:text-surface-0 font-medium text-base mb-1"
            >
              Contraseña Actual
            </label>

            <Password
              id="current_password"
              ref="currentPasswordInput"
              v-model="form.current_password"
              class="w-full"
              toggleMask
              :feedback="false"
              inputClass="w-full" 
            />

            <small
              v-if="form.errors.current_password || (formSubmitted && !form.current_password)"
              class="text-red-600 text-sm font-medium"
            >
              {{ form.errors.current_password || 'Este campo es obligatorio' }}
            </small>
          </div>

          <!-- Nueva Contraseña -->
          <div>
            <label
              for="password"
              class="block text-surface-900 dark:text-surface-0 font-medium text-base mb-1"
            >
              Nueva Contraseña
            </label>

            <Password
              id="password"
              ref="passwordInput"
              v-model="form.password"
              toggleMask
              class="w-full"
              inputClass="w-full"
            />

            <small
              v-if="form.errors.password || (formSubmitted && !form.password)"
              class="text-red-600 text-sm font-medium"
            >
              {{ form.errors.password || 'Este campo es obligatorio' }}
            </small>
          </div>

          <!-- Confirmar Contraseña -->
          <div>
            <label
              for="password_confirmation"
              class="block text-surface-900 dark:text-surface-0 font-medium text-base mb-1"
            >
              Confirmar Contraseña
            </label>

            <Password
              id="password_confirmation"
              v-model="form.password_confirmation"
              toggleMask
              class="w-full"
              :feedback="false"
              inputClass="w-full"
            />

            <small
              v-if="form.errors.password_confirmation || (formSubmitted && !form.password_confirmation)"
              class="text-red-600 text-sm font-medium"
            >
              {{ form.errors.password_confirmation || 'Este campo es obligatorio' }}
            </small>
          </div>

          <!-- Error general -->
          <Message
            v-if="errorMessage"
            severity="error"
            :closable="false"
            class="mb-2 text-sm"
          >
            {{ errorMessage }}
          </Message>

          <!-- Botón -->
          <Button
            type="submit"
            label="Guardar Contraseña"
            class="w-full py-3 font-medium"
            :loading="form.processing"
            @click="logFormState"
          />

          <!-- Éxito -->
          <Message
            v-if="form.recentlySuccessful"
            severity="success"
            :closable="false"
            class="text-sm mt-3"
          >
            ¡Contraseña actualizada!
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