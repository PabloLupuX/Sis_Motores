<template>
  <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 p-2 w-full">
    <!-- Botón Exportar Excel -->
    <Button 
      variant="outlined"
      size="small"
      class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white"
      icon="pi pi-file-excel"
      label="Exportar a Excel"
      @click="startDownload('excel')"
      :disabled="loading"
    />

    <!-- Botón Exportar PDF -->
    <Button 
      variant="outlined"
      size="small"
      class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white"
      icon="pi pi-file-pdf"
      label="Exportar a PDF"
      @click="startDownload('pdf')"
      :disabled="loading"
    />

    <!-- Dialog de descarga -->
    <Dialog v-model:visible="loading" modal :closable="false" header="Descargando" :style="{ width: '90%', maxWidth: '400px' }">
      <div class="flex flex-col items-center justify-center p-4 text-center">
        <ProgressSpinner :style="{ width: '60px', height: '60px' }"/>
        <p class="mt-3 font-semibold text-sm sm:text-base">{{ downloadingText }}</p>
      </div>
    </Dialog>

    <!-- Toast -->
    <Toast />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import axios from 'axios'

const toast = useToast()
const loading = ref(false)
const downloadingText = ref('')

const startDownload = async (type: 'pdf' | 'excel') => {
  const url = type === 'pdf' 
    ? '/panel/reports/export-pdf-spaces' 
    : '/panel/reports/export-excel-spaces'

  const filename = type === 'pdf' ? 'Espacios.pdf' : 'Espacios.xlsx'

  try {
    loading.value = true
    downloadingText.value = type === 'pdf' ? 'Descargando PDF...' : 'Descargando Excel...'

    const response = await axios.get(url, { responseType: 'blob' })

    const blob = new Blob([response.data], { type: response.data.type })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()

    toast.add({ 
      severity: 'success', 
      summary: 'Éxito', 
      detail: `${filename} descargado correctamente.`,
      life: 3000
    })
  } catch (error) {
    console.error(error)
    toast.add({ 
      severity: 'error', 
      summary: 'Error', 
      detail: 'Hubo un error al descargar el archivo.',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Centrar spinner dentro del dialog */
.p-dialog .p-dialog-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

/* Ajustar tamaño del texto en móviles */
@media (max-width: 640px) {
  .p-dialog .p-dialog-content p {
    font-size: 0.875rem; /* texto más pequeño en móviles */
  }
}
</style>
