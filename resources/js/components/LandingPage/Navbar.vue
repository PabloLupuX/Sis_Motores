<template>
  <nav class="fixed top-0 left-0 z-50 w-full bg-white shadow-md dark:bg-gray-900 transition-colors duration-300">
    <div class="mx-auto flex max-w-[1280px] items-center justify-between px-6 py-5">
      <!-- Logo -->
      <a href="#inicio" class="flex items-center space-x-2">
        <img :src="logoSrc" alt="Logo" class="h-10 w-10 cursor-pointer transition-all duration-300" />
        <span class="text-lg font-bold text-blue-700 dark:text-cyan-400 cursor-pointer">
          Sistema Biométrico y Dactilar
        </span>
      </a>

      <!-- Menú de escritorio -->
      <div class="hidden items-center space-x-6 md:flex">
        <NavigationMenu>
          <NavigationMenuList class="flex space-x-2">
            <NavigationMenuItem v-for="link in links" :key="link.text">
              <NavigationMenuLink
                :href="link.href"
                class="text-gray-800 hover:text-blue-800 transition dark:text-gray-200 dark:hover:text-cyan-400"
              >
                {{ link.text }}
              </NavigationMenuLink>
            </NavigationMenuItem>
          </NavigationMenuList>
        </NavigationMenu>

        <!-- Botón de modo oscuro -->
        <button
          @click="toggleTheme"
          class="ml-4 rounded-full p-2 hover:scale-105 transition bg-gray-200 dark:bg-gray-700"
          :aria-label="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        >
          <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-gray-800 dark:text-gray-200" />
        </button>

        <!-- Ícono de cuenta -->
        <a href="/login" class="ml-3 flex items-center text-gray-800 hover:text-blue-800 dark:text-gray-200 dark:hover:text-cyan-400">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
            <path fill-rule="evenodd"
              d="M8 0a8 8 0 100 16A8 8 0 008 0zM5.5 5.5a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM8 9c-2.21 0-4 1.57-4 3.5V13h8v-.5C12 10.57 10.21 9 8 9z" />
          </svg>
          <span class="ml-1">Intranet</span>
        </a>
      </div>

      <!-- Botón hamburguesa móvil -->
      <button @click="menuOpen = !menuOpen" class="text-gray-800 dark:text-gray-200 md:hidden">
        <svg v-if="!menuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Menú móvil -->
    <div v-if="menuOpen" class="px-6 pb-4 md:hidden bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
      <NavigationMenu>
        <NavigationMenuList class="flex flex-col items-start space-y-3">
          <NavigationMenuItem v-for="link in links" :key="link.text + '-mobile'">
            <NavigationMenuLink
              :href="link.href"
              class="text-gray-800 hover:text-blue-800 dark:text-gray-200 dark:hover:text-cyan-400"
            >
              {{ link.text }}
            </NavigationMenuLink>
          </NavigationMenuItem>

          <NavigationMenuItem>
            <button
              @click="toggleTheme"
              class="flex items-center gap-2 rounded-lg p-2 bg-gray-100 dark:bg-gray-800 hover:scale-105 transition"
            >
              <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-gray-700 dark:text-gray-200" />
              <span>{{ isDark ? 'Modo claro' : 'Modo oscuro' }}</span>
            </button>
          </NavigationMenuItem>

          <NavigationMenuItem>
            <a href="/login" class="flex items-center gap-2 text-gray-800 dark:text-gray-200 hover:text-blue-800 dark:hover:text-cyan-400">
              <span>Intranet</span>
            </a>
          </NavigationMenuItem>
        </NavigationMenuList>
      </NavigationMenu>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { Moon, Sun } from "lucide-vue-next";
import {
  NavigationMenu,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuLink,
} from "@/components/ui/navigation-menu";

// Estado del menú móvil
const menuOpen = ref<boolean>(false);

// Estado del modo oscuro
const isDark = ref<boolean>(false);

// Computed para alternar el logo
const logoSrc = computed(() => (isDark.value ? "/imagenes/logoB.png" : "/imagenes/logo.png"));

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

// Enlaces del menú
const links = [
  { text: "Inicio", href: "#inicio" },
  { text: "Servicios", href: "#servicios" },
  { text: "FAQS", href: "#faqs" },
  { text: "Contacto", href: "#contacto" },
];
</script>
