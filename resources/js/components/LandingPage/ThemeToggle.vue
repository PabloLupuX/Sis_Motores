<template>
  <button
    @click="toggleTheme"
    class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 hover:scale-105 transition"
    :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
  >
    <span v-if="isDark">🌞</span>
    <span v-else>🌙</span>
  </button>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";

const isDark = ref<boolean>(false);

onMounted(() => {
  const html = document.documentElement;
  const storedTheme = localStorage.getItem("theme");
  const prefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

  isDark.value = storedTheme === "dark" || (!storedTheme && prefersDark);
  html.classList.toggle("app-dark", isDark.value);
});

const toggleTheme = (): void => {
  const html = document.documentElement;
  isDark.value = !isDark.value;

  html.classList.toggle("app-dark", isDark.value);
  localStorage.setItem("theme", isDark.value ? "dark" : "light");
};
</script>
