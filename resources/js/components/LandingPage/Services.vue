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
import { Wrench, Settings, Users, ClipboardList, Database, FileDown, History, PackageCheck } from "lucide-vue-next";

const servicios = [
  {
    titulo: "Gestión de Motores",
    descripcion: "Registro, clasificación y control completo de motores en reparación, limpieza o diagnóstico.",
    icon: Settings,
  },
  {
    titulo: "Mantenimiento y Reparaciones",
    descripcion: "Administración de trabajos mecánicos: limpieza profunda, pruebas, cambios de piezas y diagnósticos.",
    icon: Wrench,
  },
  {
    titulo: "Gestión de Clientes",
    descripcion: "Control organizado de clientes, historial de servicios y motores atendidos.",
    icon: Users,
  },
  {
    titulo: "Control de Accesorios",
    descripcion: "Inventario de accesorios y repuestos instalados o reemplazados en cada motor.",
    icon: PackageCheck,
  },
  {
    titulo: "Historial de Servicios",
    descripcion: "Registro detallado del estado, procesos realizados y seguimiento para cada motor.",
    icon: History,
  },
  {
    titulo: "Reportes PDF y Excel",
    descripcion: "Exportación rápida de motores, clientes, servicios y accesorios en formatos profesionales.",
    icon: FileDown,
  },
];
</script>
