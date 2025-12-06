<template> 
  <section 
    id="contacto" 
    class="flex min-h-screen flex-col items-center justify-center 
           bg-white dark:bg-gray-900 
           px-6 py-16 transition-colors duration-500"
  >
    <!-- Título -->
    <div class="mb-12 text-center">
      <h2 class="mb-4 text-3xl font-bold text-neutral-900 dark:text-white">
        Contáctanos
      </h2>
      <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-600 dark:text-gray-300">
        ¿Tienes dudas sobre el sistema de reconocimiento facial y dactilar? 
        Escríbenos y nuestro equipo técnico te brindará asistencia personalizada.
      </p>
    </div>

    <!-- Opciones de contacto -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2 max-w-4xl w-full">
      <div
        v-for="contacto in contactos"
        :key="contacto.title"
        class="flex items-start gap-4 p-6 
               bg-white dark:bg-gray-800 
               rounded-2xl shadow-md hover:shadow-lg 
               border border-gray-200 dark:border-gray-700
               transition"
      >
        <!-- Icono -->
        <div class="flex h-12 w-12 items-center justify-center 
                    rounded-full bg-blue-100 dark:bg-gray-700">
          <component :is="contacto.icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
        </div>

        <!-- Texto -->
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
            {{ contacto.title }}
          </h3>
          <p class="mt-1 text-gray-600 dark:text-gray-300" v-html="contacto.info"></p>
        </div>
      </div>
    </div>

    <!-- Botón que abre el modal -->
    <Dialog>
      <DialogTrigger as-child>
        <button 
          class="mt-12 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 shadow">
          Enviar mensaje
        </button>
      </DialogTrigger>

      <DialogContent 
        class="sm:max-w-3xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 
               rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6"
      >
        <DialogHeader>
          <DialogTitle>Formulario de contacto</DialogTitle>
          <DialogDescription>
            Completa los campos y nuestro equipo te responderá a la brevedad.
          </DialogDescription>
        </DialogHeader>

        <!-- Formulario -->
        <form 
          @submit.prevent="enviarFormulario" 
          class="grid gap-6 w-full mt-4"
        >
          <div>
            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Nombre</label>
            <input 
              v-model="form.first_name" 
              type="text" 
              class="w-full rounded-lg border px-4 py-2" required 
              placeholder="Ingresa tu nombre completo"
            />
          </div>

          <div>
            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Correo electrónico</label>
            <input v-model="form.email" type="email" class="w-full rounded-lg border px-4 py-2" required placeholder="correo@ejemplo.com" />
          </div>

          <div>
            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Teléfono</label>
            <input v-model="form.phone" type="text" maxlength="9" class="w-full rounded-lg border px-4 py-2" required placeholder="999999999" />
          </div>

          <div class="sm:col-span-2">
            <label class="block text-gray-700 dark:text-gray-200 font-medium mb-2">Mensaje</label>
            <textarea v-model="form.subject" rows="4" class="w-full rounded-lg border px-4 py-2" required placeholder="Escríbenos tus consultas técnicas o solicitudes..."></textarea>
          </div>

          <div class="flex justify-between sm:col-span-2 gap-4">
            <button type="button" @click="limpiarFormulario" 
              class="w-1/2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-6 py-2 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500">
              Borrar
            </button>
            <button type="submit" class="w-1/2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
              Enviar
            </button>
          </div>

          <!-- Mensaje -->
          <p v-if="mensaje.text" 
             :class="mensaje.tipo === 'success' 
                      ? 'text-green-600 dark:text-green-400 mt-4 text-sm text-center sm:col-span-2' 
                      : 'text-red-600 dark:text-red-400 mt-4 text-sm text-center sm:col-span-2'">
            {{ mensaje.text }}
          </p>
        </form>

        <DialogFooter>
          <DialogClose as-child>
            <button class="mt-4 w-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-6 py-2 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
              Cerrar
            </button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </section>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import axios from 'axios'
import { MapPin, Phone, Mail, Cpu } from 'lucide-vue-next'
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
  DialogClose,
} from '@/components/ui/dialog'

const contactos = [
  { title: 'Oficina principal', icon: MapPin, info: 'Av. Los Innovadores 120, Sullana, Piura, Perú' },
  { title: 'Soporte técnico', icon: Phone, info: `<a href="tel:+51912345678">+51 912 345 678</a>` },
  { title: 'Correo institucional', icon: Mail, info: `<a href="mailto:soporte@biometria.pe">soporte@biometria.pe</a>` },
  { title: 'Área de desarrollo', icon: Cpu, info: 'Equipo especializado en reconocimiento facial y dactilar.' },
]

const mensaje = reactive({
  text: '',
  tipo: ''
})

const form = reactive({
  first_name: '',
  email: '',
  phone: '',
  subject: ''
})

const limpiarFormulario = () => {
  form.first_name = ''
  form.email = ''
  form.phone = ''
  form.subject = ''
}

const enviarFormulario = async () => {
  try {
    await axios.post('/contacto-biometrico', form)
    mensaje.text = 'Tu mensaje fue enviado correctamente 👌 Nuestro equipo te contactará pronto.'
    mensaje.tipo = 'success'
    limpiarFormulario()
  } catch (error) {
    console.error(error)
    mensaje.text = 'Error al enviar el mensaje. Por favor, intenta nuevamente.'
    mensaje.tipo = 'error'
  }

  setTimeout(() => {
    mensaje.text = ''
    mensaje.tipo = ''
  }, 4000)
}
</script>
