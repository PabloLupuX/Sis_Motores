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
            <span v-if="movimiento">
                ¿Estás seguro de eliminar el movimiento <b>{{ movimiento.id }}</b>?
            </span>
        </div>

        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteMovimiento" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Tipos
interface Movimiento {
    id: number;
    [key: string]: any; // Para mantener otras propiedades dinámicas
}

// Props
const props = defineProps<{
    visible: boolean;
    movimiento: Movimiento | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

// Toast
const toast = useToast();

// Local visible
const localVisible = ref<boolean>(false);
watch(() => props.visible, val => localVisible.value = val);

// Función para cerrar dialog
function closeDialog() {
    emit('update:visible', false);
}

// Eliminar movimiento
async function deleteMovimiento() {
    if (!props.movimiento) return; // <-- Verificamos que exista

    try {
        await axios.delete(`/movimiento/${props.movimiento.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Movimiento eliminado correctamente',
            life: 3000
        });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el movimiento';
        const err = error as AxiosError<{ message?: string }>;
        if (err.response) {
            errorMessage = err.response.data.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>
