<template>
  <section 
    id="servicios"
    class="flex flex-col items-center justify-center min-h-screen bg-white dark:bg-gray-900 px-6 py-16"
  >
    <h2 class="text-3xl font-bold text-blue-700 dark:text-cyan-400 mb-10 text-center">
      Funcionalidades del Sistema
    </h2>

    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 max-w-6xl w-full">
      <div 
        v-for="(s, i) in servicios" :key="i"
        class="p-6 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition"
      >
        <component :is="s.icon" class="w-10 h-10 text-blue-600 dark:text-cyan-400 mb-4" />
        <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">{{ s.titulo }}</h3>
        <p class="text-gray-600 dark:text-gray-300">{{ s.descripcion }}</p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { Fingerprint, Camera, Shield, Clock, UserCheck, Database } from "lucide-vue-next";

const servicios = [
  { titulo: "Reconocimiento Facial", descripcion: "Identificación mediante análisis de rasgos faciales en tiempo real.", icon: Camera },
  { titulo: "Reconocimiento Dactilar", descripcion: "Lectura precisa y rápida de huellas dactilares con tecnología biométrica.", icon: Fingerprint },
  { titulo: "Seguridad de Datos", descripcion: "Cifrado avanzado y almacenamiento seguro de información biométrica.", icon: Shield },
  { titulo: "Registro Automático", descripcion: "Control de asistencia y accesos sin contacto físico.", icon: Clock },
  { titulo: "Gestión de Usuarios", descripcion: "Panel administrativo para la gestión y verificación de identidades.", icon: UserCheck },
  { titulo: "Base de Datos Inteligente", descripcion: "Sincronización con sistemas externos y almacenamiento eficiente.", icon: Database },
];
</script>
