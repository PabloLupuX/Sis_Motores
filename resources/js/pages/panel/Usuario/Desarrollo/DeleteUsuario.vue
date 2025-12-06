<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Definir interfaz del producto/usuario
interface Product {
  id: number;
  name?: string;
}

// Props
const props = defineProps<{
  visible: boolean;
  product: Product | null;
}>();

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void;
  (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

// Sincronizar props.visible con localVisible
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Cerrar diálogo
function closeDialog(): void {
    emit('update:visible', false);
}

// Eliminar producto
async function deleteProduct(): Promise<void> {
    if (!props.product) return;
    try {
        await axios.delete(`/usuarios/${props.product.id}`);
        emit('deleted');
        closeDialog();
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Usuario eliminado correctamente', life: 3000 });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el usuario';
        if (axios.isAxiosError(error)) {
            const axiosError = error as AxiosError<{ message?: string }>;
            errorMessage = axiosError.response?.data?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
  <Dialog 
      v-model:visible="localVisible" 
      :style="{ width: '90%', maxWidth: '450px' }" 
      header="Confirmar" 
      :modal="true"
      @update:visible="closeDialog"
  >
      <div class="flex items-center gap-4">
          <i class="pi pi-exclamation-triangle !text-3xl" />
          <span v-if="props.product">
              ¿Estás seguro de eliminar al Usuario <b>{{ props.product.name }}</b>?
          </span>
      </div>

      <template #footer>
          <Button label="No" icon="pi pi-times" text @click="closeDialog" />
          <Button label="Sí" icon="pi pi-check" @click="deleteProduct" />
      </template>
  </Dialog>
</template>
