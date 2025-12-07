<script setup lang="ts">
import { ref } from "vue";

const faqs = ref([
  {
    question: "¿Qué información puedo registrar sobre un motor?",
    answer:
      "El sistema permite registrar HP, tipo, marca, modelo, número de serie, año, accesorios instalados, historial de servicios y estado actual del motor.",
  },
  {
    question: "¿Puedo llevar control del mantenimiento y reparaciones?",
    answer:
      "Sí. Cada motor tiene un historial detallado donde podrás registrar limpieza, reparaciones, diagnósticos, cambios de piezas y cualquier trabajo realizado por el taller.",
  },
  {
    question: "¿Es posible gestionar clientes dentro del sistema?",
    answer:
      "El sistema incluye un módulo completo para administrar clientes, con información personal, teléfonos, alias y motores asociados a cada uno.",
  },
  {
    question: "¿Puede exportar información en PDF o Excel?",
    answer:
      "Sí. Puedes generar reportes PDF y Excel de motores, clientes, accesorios y servicios con un solo clic.",
  },
  {
    question: "¿El sistema permite filtrar y buscar motores?",
    answer:
      "Cuenta con filtros avanzados por estado, marca, tipo, año, cliente, accesorios y búsqueda global rápida.",
  },
  {
    question: "¿El sistema funciona sin internet?",
    answer:
      "Puede ejecutarse de forma local dentro del taller y sincronizarse si se usa un servidor externo.",
  },
]);

const openIndex = ref<number | null>(null);
const toggle = (i: number) => (openIndex.value = openIndex.value === i ? null : i);
</script>

<template>
  <section
    id="faqs"
    class="min-h-screen flex flex-col justify-center 
           bg-gradient-to-b from-blue-50 to-cyan-50 
           dark:from-gray-900 dark:to-gray-800 
           py-16 px-6 transition-colors"
  >
    <div class="max-w-3xl mx-auto text-center mb-10">
      <h2 class="text-3xl font-bold text-blue-700 dark:text-cyan-400">
        Preguntas Frecuentes
      </h2>
      <p class="text-gray-600 dark:text-gray-300 mt-2">
        Aclara tus dudas sobre el sistema de gestión y mantenimiento de motores.
      </p>
    </div>

    <div class="space-y-4 max-w-2xl mx-auto w-full">
      <div
        v-for="(faq, index) in faqs"
        :key="index"
        class="border rounded-xl bg-white dark:bg-gray-800 
               border-gray-200 dark:border-gray-700 shadow"
      >
        <button
          @click="toggle(index)"
          class="w-full text-left px-5 py-4 font-medium 
                 flex justify-between items-center"
        >
          <span>{{ faq.question }}</span>
          <span class="text-lg font-bold">
            {{ openIndex === index ? "-" : "+" }}
          </span>
        </button>

        <div
          v-show="openIndex === index"
          class="px-5 pb-4 text-gray-600 dark:text-gray-300 
                 border-t border-gray-200 dark:border-gray-700"
        >
          {{ faq.answer }}
        </div>
      </div>
    </div>
  </section>
</template>
