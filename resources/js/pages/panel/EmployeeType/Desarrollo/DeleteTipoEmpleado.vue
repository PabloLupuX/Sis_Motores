<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '90%', maxWidth: '450px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="tipoEmpleado">¿Estás seguro de eliminar este tipo de empleado <b>{{ tipoEmpleado.name }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteTipoEmpleado" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Interface para tipar el tipoEmpleado
interface TipoEmpleado {
    id: number;
    name: string;
    [key: string]: any;
}

// Props tipadas
const props = defineProps<{
    visible: boolean;
    tipoEmpleado: TipoEmpleado | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

// Watch para sincronizar la visibilidad
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Cierra el diálogo
function closeDialog(): void {
    emit('update:visible', false);
}

// Elimina el tipo de empleado
async function deleteTipoEmpleado(): Promise<void> {
    if (!props.tipoEmpleado) return;

    try {
        await axios.delete(`/tipo_empleado/${props.tipoEmpleado.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Tipo de empleado eliminado correctamente',
            life: 3000
        });
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando el tipo de empleado';
        if ((error as AxiosError).response) {
            errorMessage = ((error as AxiosError).response?.data as any)?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>
