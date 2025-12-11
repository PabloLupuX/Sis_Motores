<template>
  <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3 p-2 w-full">
    
    <Button 
      variant="outlined"
      size="small"
      class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white"
      icon="pi pi-file-excel"
      label="Exportar a Excel"
      @click="startDownload('excel')"
      :disabled="loading"
    />

    <Button 
      variant="outlined"
      size="small"
      class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white"
      icon="pi pi-file-pdf"
      label="Exportar a PDF"
      @click="startDownload('pdf')"
      :disabled="loading"
    />

    <Dialog v-model:visible="loading" modal :closable="false" header="Descargando" :style="{ width: '90%', maxWidth: '400px' }">
      <div class="flex flex-col items-center justify-center p-4 text-center">
        <ProgressSpinner :style="{ width: '60px', height: '60px' }"/>
        <p class="mt-3 font-semibold">{{ downloadingText }}</p>
      </div>
    </Dialog>

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

const props = defineProps({
    search: String,
    state: [String, Boolean],
    dateRange: Array
});

const startDownload = async (type: 'pdf' | 'excel') => {

    const url = type === 'pdf'
        ? '/panel/reports/export-pdf-receptions'
        : '/panel/reports/export-excel-receptions';

    const filename = type === 'pdf'
        ? `Lista de Recepciones.pdf`
        : `Lista de Recepciones.xlsx`;

    let fechaInicio = null;
    let fechaFin = null;

    if (props.dateRange && props.dateRange[0] && props.dateRange[1]) {
        fechaInicio = props.dateRange[0].toISOString().slice(0, 10);
        fechaFin = props.dateRange[1].toISOString().slice(0, 10);
    }

    try {
        loading.value = true;
        downloadingText.value = type === 'pdf' ? 'Descargando PDF...' : 'Descargando Excel...';

        const response = await axios.get(url, { 
            responseType: 'blob',
            params: {
                search: props.search,
                state: props.state,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
            }
        });

        const blob = new Blob([response.data], { type: response.data.type });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: `${filename} descargado correctamente.`,
            life: 3000
        });

    } catch (error) {
        console.error(error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Hubo un error al descargar el archivo.',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};
</script>
