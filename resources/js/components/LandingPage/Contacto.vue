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
      <p class="mx-auto mt-4 max-w-2xl text-lg leading-relaxed text-neutral-600 dark:text-gray-300">
        Estamos aquí para ayudarte con información, soporte técnico y atención personalizada.
      </p>
    </div>

    <!-- Opciones de contacto -->
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-2 max-w-4xl w-full">
      <div
        v-for="contacto in contactos"
        :key="contacto.title"
        class="flex items-start gap-4 p-6 
               bg-white dark:bg-gray-800 
               rounded-xl shadow-sm hover:shadow-md 
               border border-gray-100 dark:border-gray-700
               transition-all duration-300"
      >
        <!-- Icono -->
        <div class="flex h-12 w-12 items-center justify-center 
                    rounded-xl bg-blue-50 dark:bg-gray-700 shadow-inner">
          <component :is="contacto.icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
        </div>

        <!-- Texto -->
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
            {{ contacto.title }}
          </h3>
          <p 
            class="text-gray-600 dark:text-gray-300 leading-relaxed text-[15px]" 
            v-html="contacto.info"
          ></p>
        </div>
      </div>
    </div>

  </section>
</template>

<script setup lang="ts">
import { MapPin, Phone, Mail, Building2 } from 'lucide-vue-next';

// Datos mejor formateados, más amigables
const contactos = [
  {
    title: 'NÁUTICA ZAPATA S.R.L.',
    icon: Building2,
    info: `
      RUC: 20605443177<br>
      Especialistas en motores fuera de borda Yamaha.
    `
  },
  {
    title: 'Teléfonos de Atención',
    icon: Phone,
    info: `
      <span class="block"><a href="tel:+51954042627">954 042 627</a></span>
      <span class="block"><a href="tel:+51977276448">977 276 448</a></span>
      <span class="block"><a href="tel:+51992646993">992 646 993</a></span>
    `
  },
  {
    title: 'Ubicación',
    icon: MapPin,
    info: `
      Calle Grau N° 283<br>
      Caleta Parachique – Sechura – Piura
    `
  },
  {
    title: 'Correo Electrónico',
    icon: Mail,
    info: `
      <a class="underline" href="mailto:nauticazapata@outlook.com">
        nauticazapata@outlook.com
      </a>
    `
  },
];
</script>
