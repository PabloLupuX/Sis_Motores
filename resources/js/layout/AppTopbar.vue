<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useLayout } from '@/layout/composables/layout';
import AppConfigurator from './AppConfigurator.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

// Importamos Heroicons
import { WrenchIcon } from '@heroicons/vue/24/solid';

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();

const showLogoutModal = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const goToProfile = () => {
    router.get('/settings/profile');
};

const confirmLogout = () => {
    showLogoutModal.value = true;
};

const cancelLogout = () => {
    showLogoutModal.value = false;
};
</script>

<template>
  <div class="layout-topbar">
    <div class="layout-topbar-logo-container">
      <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
        <i class="pi pi-bars"></i>
      </button>
      <!-- Logo con ícono + texto -->
      <Link href="/dashboard" class="layout-topbar-logo flex items-center gap-2">
          <WrenchIcon class="h-8 w-8 text-primary" />
          <span class="font-serif text-base font-semibold">Sistema de Logistica</span>
      </Link>
    </div>

    <div class="layout-topbar-actions">
      <div class="layout-config-menu">
        <button type="button" class="layout-topbar-action" @click="toggleDarkMode">
          <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
        </button>
        <div class="relative">
          <button
            v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
            type="button" class="layout-topbar-action layout-topbar-action-highlight">
            <i class="pi pi-palette"></i>
          </button>
          <AppConfigurator />
        </div>
      </div>

      <button class="layout-topbar-menu-button layout-topbar-action"
        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }">
        <i class="pi pi-ellipsis-v"></i>
      </button>

      <div class="layout-topbar-menu hidden lg:block">
        <div class="layout-topbar-menu-content">
          <button type="button" class="layout-topbar-action" @click="goToProfile">
            <i class="pi pi-user"></i>
            <span>Perfil</span>
          </button>
          <button type="button" @click="confirmLogout" class="layout-topbar-action">
            <i class="pi pi-sign-out"></i>
            <span>Cerrar</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de confirmación -->
  <div v-if="showLogoutModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 w-80">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
        Confirmación
      </h2>
      <p class="text-gray-700 dark:text-gray-300 mb-6">
        ¿Estás seguro de que deseas salir del sistema?
      </p>
      <div class="flex justify-center space-x-4">
        <button @click="logout" class="px-4 py-2 rounded text-white" style="background-color: var(--primary-color);">
          Sí, salir
        </button>
        <button @click="cancelLogout"
          class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="css">
/* Puedes ajustar el tamaño del icono si lo quieres más grande */
</style>
