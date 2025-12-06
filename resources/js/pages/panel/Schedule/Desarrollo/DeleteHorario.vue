<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Schedule {
    id: number;
    fecha?: string;
    fechaInicio?: string;
    fechaFin?: string;
    idEspacio?: number;
    idEmpleado?: number;
    state?: boolean;
}

// Props
const props = defineProps<{
    visible: boolean;
    schedule: Schedule | null;
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

// Función para eliminar horario
async function deleteSchedule() {
    if (!props.schedule) return;

    try {
        await axios.delete(`/horario/${props.schedule.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Horario eliminado correctamente',
            life: 3000
        });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el horario';
        const err = error as AxiosError<{ message?: string }>;
        if (err.response?.data?.message) {
            errorMessage = err.response.data.message;
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
            <span v-if="props.schedule">
                ¿Estás seguro de eliminar el horario del día con código de horario <b>{{ props.schedule.id }}</b>?
            </span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteSchedule" />
        </template>
    </Dialog>
</template>
