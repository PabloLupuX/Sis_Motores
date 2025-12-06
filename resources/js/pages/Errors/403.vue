<script setup lang="ts">
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import Button from 'primevue/button';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Acceso denegado" />
    <FloatingConfigurator />
    <div class="bg-surface-50 dark:bg-surface-950 flex items-center justify-center min-h-screen w-full overflow-hidden p-4 sm:p-8">
        <div class="flex flex-col items-center justify-center w-full max-w-2xl">
            <div class="rounded-[56px] p-1 bg-gradient-to-b from-orange-300/40 to-transparent">
                <div class="w-full bg-surface-0 dark:bg-surface-900 py-16 px-6 sm:px-12 md:px-20 flex flex-col items-center rounded-[53px]">
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex justify-center items-center border-2 border-orange-500 rounded-full w-12 h-12 sm:w-16 sm:h-16">
                            <i class="text-orange-500 pi pi-fw pi-lock text-xl sm:text-2xl"></i>
                        </div>
                        <h1 class="font-bold text-3xl sm:text-4xl lg:text-5xl text-center text-surface-900 dark:text-surface-0 mb-2">
                            Acceso denegado
                        </h1>
                        <p class="text-center text-muted-color mb-6 sm:mb-8 px-2 sm:px-6">
                            No tienes los permisos necesarios. Póngase en contacto con los administradores.
                        </p>
                        <img 
                            src="/imagenes/access/asset-access.svg" 
                            alt="Access denied" 
                            class="w-full max-w-md mb-8" 
                        />
                        <div class="w-full flex justify-center">
                            <Link href="/dashboard" class="no-underline w-full sm:w-auto">
                                <Button label="Ir al panel de control" severity="warn" class="w-full sm:w-auto"/>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
