<script setup lang="ts">
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import Button from 'primevue/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <Head title="No encontrado" />
  <FloatingConfigurator />
  <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen w-full overflow-hidden p-4 sm:p-8">
    <div class="flex flex-col items-center justify-center w-full max-w-2xl">
      <div class="rounded-[56px] p-1 bg-gradient-to-b from-pink-300/40 to-transparent">
        <div class="w-full bg-surface-0 dark:bg-surface-900 py-16 px-6 sm:px-12 md:px-20 flex flex-col items-center rounded-[53px]">
          <div class="flex flex-col items-center gap-4">
            <div class="flex justify-center items-center border-2 border-pink-500 rounded-full w-12 h-12 sm:w-16 sm:h-16">
              <i class="pi pi-fw pi-exclamation-circle text-pink-500 text-xl sm:text-2xl"></i>
            </div>
            <h1 class="text-center font-bold text-3xl sm:text-4xl lg:text-5xl text-surface-900 dark:text-surface-0 mb-2">
              Se ha producido un error
            </h1>
            <p class="text-center text-muted-color mb-6 sm:mb-8 px-2 sm:px-6">
              El recurso solicitado no está disponible.
            </p>
            <img src="/imagenes/access/asset-error.svg" alt="Error" class="w-full max-w-md mb-8" />
            <div class="w-full flex justify-center">
              <Link href="/dashboard" class="no-underline w-full sm:w-auto">
                <Button label="Ir al panel de control" severity="danger" class="w-full sm:w-auto"/>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>