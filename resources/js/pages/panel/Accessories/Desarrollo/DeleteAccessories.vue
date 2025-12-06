<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Accessory {
    id: number;
    name?: string;
    state?: boolean;
}

// Props
const props = defineProps<{
  visible: boolean;
  accessory: Accessory | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();

// Local visibility
const localVisible = ref<boolean>(false);

// Sync visibility
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Close dialog
function closeDialog() {
    emit('update:visible', false);
}

// Delete accessory
async function deleteAccessory() {
    if (!props.accessory) return;

    try {
        await axios.delete(`/accesorio/${props.accessory.id}`);

        emit('deleted');
        closeDialog();

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Accesorio eliminado correctamente',
            life: 3000
        });

    } catch (error) {
        console.error(error);

        let errorMessage = 'Error eliminando el accesorio';
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

        <span v-if="props.accessory">
            ¿Estás seguro de eliminar el accesorio  
            <b>{{ props.accessory.name }}</b>?
        </span>
    </div>

    <template #footer>
        <Button 
            label="No" 
            icon="pi pi-times" 
            text 
            @click="closeDialog" 
        />

        <Button 
            label="Sí, eliminar" 
            icon="pi pi-check" 
            @click="deleteAccessory" 
            severity="danger" 
        />
    </template>
</Dialog>
</template>
