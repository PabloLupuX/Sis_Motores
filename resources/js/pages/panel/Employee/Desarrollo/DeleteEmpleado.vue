<script lang="ts" setup>
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Empleado {
    id: number;
    name: string;
    [key: string]: any;
}

const props = defineProps<{
    visible: boolean;
    empleado: Empleado | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

// Sincroniza la visibilidad del dialog
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Cerrar dialog
function closeDialog() {
    emit('update:visible', false);
}

// Eliminar empleado
async function deleteEmpleado() {
    if (!props.empleado) return;

    try {
        await axios.delete(`/empleado/${props.empleado.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Empleado eliminado correctamente',
            life: 3000
        });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el empleado';
        const err = error as AxiosError;
        if (err.response) {
            errorMessage = (err.response.data as any)?.message || errorMessage;
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
            <span v-if="empleado">
                ¿Estás seguro de eliminar al empleado <b>{{ empleado.name }}</b>?
            </span>
            <span v-else>Empleado no disponible</span>
        </div>

        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteEmpleado" />
        </template>
    </Dialog>
</template>
