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
        ¿Necesitas soporte con el sistema de gestión de motores, mantenimiento o clientes?  
        Nuestro equipo está listo para ayudarte.
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

  </section>
</template>

<script setup lang="ts">
import { MapPin, Phone, Mail, Wrench } from 'lucide-vue-next'

/**
 * Solo información de contacto, sin formulario ni lógica adicional.
 */
const contactos = [
  { title: 'Taller Central', icon: MapPin, info: 'Av. Los Motores 251, Sullana, Piura, Perú' },
  { title: 'Soporte y Mantenimiento', icon: Phone, info: `<a href="tel:+51987654321">+51 987 654 321</a>` },
  { title: 'Correo de atención', icon: Mail, info: `<a href="mailto:soporte@sistemamotores.pe">soporte@sistemamotores.pe</a>` },
  { title: 'Área técnica', icon: Wrench, info: 'Especialistas en diagnóstico y reparación de motores.' },
]
</script>
