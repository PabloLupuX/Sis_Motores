<script setup lang="ts">
import AppLayout from '@/layout/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Password from './settings/Password.vue';
import { ref, onMounted } from 'vue';

const page = usePage();
const mustReset = page.props.mustReset;

// Usuario autenticado
const user = page.props.auth.user;



// ---------------------------
// 📌 Datos del dashboard (API)
// ---------------------------
const datos = ref({
    clientes: 0,
    motores: 0,
    accesorios: 0,
    recepciones: 0,
});

// Cargar datos desde la API
onMounted(async () => {
    const response = await fetch('/datos/dashboard');
    datos.value = await response.json();
});
</script>

<template>
    <Head title="Dashboard" />

    <!-- Si el usuario debe resetear contraseña -->
    <div v-if="mustReset">
        <Password />
    </div>

    <AppLayout v-else>

        <!-- TARJETAS DEL DASHBOARD -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <!-- CLIENTES -->
            <div class="flex flex-col rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Clientes</h3>
                    <span class="rounded-lg bg-blue-100 p-2 text-blue-600 dark:bg-blue-900 dark:text-blue-300"> 👥 </span>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ datos.clientes }}</p>
                <p class="mt-1 text-sm text-green-600 dark:text-green-400">Total registrados</p>
            </div>

            <!-- MOTORES -->
            <div class="flex flex-col rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Motores</h3>
                    <span class="rounded-lg bg-orange-100 p-2 text-orange-600 dark:bg-orange-900 dark:text-orange-300"> 🔧 </span>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ datos.motores }}</p>
                <p class="mt-1 text-sm text-green-600 dark:text-green-400">Motores registrados</p>
            </div>

            <!-- ACCESORIOS -->
            <div class="flex flex-col rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Accesorios</h3>
                    <span class="rounded-lg bg-cyan-100 p-2 text-cyan-600 dark:bg-cyan-900 dark:text-cyan-300"> ⚙️ </span>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ datos.accesorios }}</p>
                <p class="mt-1 text-sm text-green-600 dark:text-green-400">Accesorios almacenados</p>
            </div>

            <!-- RECEPCIONES DE MOTORES -->
            <div class="flex flex-col rounded-xl bg-white p-4 shadow dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Recepciones</h3>
                    <span class="rounded-lg bg-purple-100 p-2 text-purple-600 dark:bg-purple-900 dark:text-purple-300"> 📦 </span>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ datos.recepciones }}</p>
                <p class="mt-1 text-sm text-green-600 dark:text-green-400">Recepciones de motores</p>
            </div>

        </div>

    </AppLayout>
</template>

<style scoped>
body {
    overflow-x: hidden;
}
</style>
