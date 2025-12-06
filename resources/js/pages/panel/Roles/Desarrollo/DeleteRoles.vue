<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Tipos
interface Rol {
    id: number;
    name: string;
}

interface ErrorResponse {
    message?: string;
}

// Props
const props = defineProps<{
    visible: boolean;
    rol: Rol | null; // ✅ Aceptar null porque al inicio puede ser null
}>();

// Emit
const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void;
  (e: 'deleted'): void;
}>();

// Toast
const toast = useToast();

// Estado local
const localVisible = ref<boolean>(props.visible);

// Sincronizar props.visible con localVisible
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Función para cerrar dialog
function closeDialog() {
    emit('update:visible', false);
}

// Función para eliminar rol
async function deleterol() {
     if (!props.rol) return;
    try {
        await axios.delete(`/rol/${props.rol.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Rol eliminado correctamente',
            life: 3000
        });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el rol';
        const err = error as AxiosError<ErrorResponse>;
        if (err.response?.data?.message) {
            errorMessage = err.response.data.message;
        }
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: errorMessage,
            life: 3000
        });
    }
}
</script>

<template>
    <Dialog 
        v-model:visible="localVisible" 
        :style="{ width: '90%', maxWidth: '450px' }" 
        header="Confirmar eliminación" 
        :modal="true"
        @update:visible="closeDialog"
    >
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle text-3xl text-red-500" />
            <span v-if="props.rol">
                ¿Estás seguro de eliminar el rol <b>{{ props.rol.name }}</b>?
            </span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" severity="danger" text @click="deleterol" />
        </template>
    </Dialog>
</template>
