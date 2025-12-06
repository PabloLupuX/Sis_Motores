<template>
  <section 
    id="inicio"
    class="mt-[60px] flex h-screen items-center 
           bg-gradient-to-r from-blue-50 to-cyan-50
           dark:from-gray-900 dark:to-gray-800 transition-colors duration-500"
  >
    <div class="mx-auto grid max-w-[1400px] items-center gap-10 px-8 md:grid-cols-2">
      
      <!-- Texto principal -->
      <div>
        <h1 class="text-4xl md:text-6xl font-bold text-blue-700 dark:text-cyan-400 mb-4 transition-colors duration-500">
          Sistema de <br />
          <span class="text-blue-900 dark:text-cyan-200">
            Reconocimiento Facial y Dactilar para la Gestión Eficiente de Espacios de trabajo
          </span>
        </h1>
        <p class="text-lg text-gray-700 dark:text-gray-300 mb-6 transition-colors duration-500">
          Una solución inteligente para la identificación segura, rápida y confiable.
          Ideal para empresas, instituciones y sistemas de control de acceso modernos.
        </p>

        <div class="flex space-x-4">
          <a href="#servicios" 
             class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow transition">
            Ver Funcionalidades
          </a>
          <a href="#contacto"
             class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-6 py-3 rounded-lg transition">
            Contáctanos
          </a>
        </div>
      </div>

      <!-- Carrusel de imágenes -->
      <div class="hidden md:flex flex-col items-center relative w-full overflow-hidden rounded-xl shadow-xl">
        <div
          class="flex transition-transform duration-700 ease-in-out"
          :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
        >
          <img
            v-for="(img, index) in images"
            :key="index"
            :src="img"
            alt="Sistema biométrico"
            class="w-full h-[480px] object-cover flex-shrink-0"
          />
        </div>

        <!-- Botones de navegación -->
        <button 
          @click="prev" 
          class="absolute left-2 top-1/2 -translate-y-1/2 
                 bg-white/70 dark:bg-gray-700/70 
                 p-3 rounded-full shadow 
                 hover:bg-white dark:hover:bg-gray-600 
                 transition-colors"
        >
          &#8592;
        </button>
        <button 
          @click="next" 
          class="absolute right-2 top-1/2 -translate-y-1/2 
                 bg-white/70 dark:bg-gray-700/70 
                 p-3 rounded-full shadow 
                 hover:bg-white dark:hover:bg-gray-600 
                 transition-colors"
        >
          &#8594;
        </button>
      </div>

    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";

const images = [
  "/imagenes/biometrico5.jpg",
  "/imagenes/biometrico6.jpg",
  "/imagenes/biometrico2.png",
  "/imagenes/biometrico3.png",
  "/imagenes/biometrico4.png",
];

const currentIndex = ref(0);

const prev = (): void => {
  currentIndex.value = (currentIndex.value - 1 + images.length) % images.length;
};

const next = (): void => {
  currentIndex.value = (currentIndex.value + 1) % images.length;
};

// Cambio automático cada 5 segundos
let intervalId: number;
onMounted(() => {
  intervalId = window.setInterval(next, 5000);
});
onUnmounted(() => {
  clearInterval(intervalId);
});
</script>
