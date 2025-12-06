<script setup lang="ts">
import { ref } from "vue";

const faqs = ref([
  { question: "¿Qué tipo de reconocimiento utiliza el sistema?", answer: "El sistema integra reconocimiento facial y dactilar basado en inteligencia artificial." },
  { question: "¿Se puede integrar con otros sistemas?", answer: "Sí, es compatible con APIs y sistemas de gestión de accesos y recursos humanos." },
  { question: "¿Los datos biométricos están seguros?", answer: "Sí, toda la información se cifra mediante estándares AES-256 y cumple con normativas de protección de datos." },
  { question: "¿Funciona sin conexión a internet?", answer: "Puede operar de forma local con sincronización automática cuando se restablece la conexión." },
]);

const openIndex = ref<number | null>(null);
const toggle = (i: number) => (openIndex.value = openIndex.value === i ? null : i);
</script>

<template>
  <section
  id="faqs"
  class="bg-gradient-to-b from-blue-50 to-cyan-50 dark:from-gray-900 dark:to-gray-800 py-12 px-6"
>
  <div class="max-w-3xl mx-auto text-center mb-8">
    <h2 class="text-3xl font-bold text-blue-700 dark:text-cyan-400">Preguntas Frecuentes</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">
      Aclara tus dudas sobre el sistema de reconocimiento biométrico.
    </p>
  </div>

  <div class="space-y-4 max-w-2xl mx-auto">
    <div
      v-for="(faq, index) in faqs"
      :key="index"
      class="border rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow"
    >
      <button
        @click="toggle(index)"
        class="w-full text-left px-5 py-4 font-medium flex justify-between items-center"
      >
        <span>{{ faq.question }}</span>
        <span class="text-lg font-bold">{{ openIndex === index ? '-' : '+' }}</span>
      </button>
      <div
        v-show="openIndex === index"
        class="px-5 pb-4 text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-700"
      >
        {{ faq.answer }}
      </div>
    </div>
  </div>
</section>
</template>
