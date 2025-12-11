<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Engine {
    id: number;
    hp?: string;
    tipo?: string;
    marca?: string;
    modelo?: string;
    año?: number;
    serie?: string;
}

// Props
const props = defineProps<{
  visible: boolean;
  engine: Engine | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();

// Ref local
const localVisible = ref<boolean>(false);

// Watch visibility
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Close dialog
function closeDialog() {
    emit('update:visible', false);
}

// Delete engine
async function deleteEngine() {
    if (!props.engine) return;

    try {
        await axios.delete(`/motor/${props.engine.id}`);

        emit('deleted');
        closeDialog();

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Motor eliminado correctamente',
            life: 3000
        });

    } catch (error) {
        console.error(error);

        let errorMessage = 'Error eliminando el motor';
        const axiosError = error as AxiosError<any>;

        if (axiosError.response) {
            errorMessage = axiosError.response.data.message || errorMessage;
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
        <i class="pi pi-exclamation-triangle !text-3xl" />

        <span v-if="props.engine">
            ¿Estás seguro de eliminar el motor  
            <b>{{ props.engine.marca }} {{ props.engine.modelo }}</b>?
        </span>
    </div>

    <template #footer>
        <Button label="No" icon="pi pi-times" text @click="closeDialog" />
        <Button label="Sí" icon="pi pi-check" @click="deleteEngine" severity="danger" />
    </template>
</Dialog>
</template>
