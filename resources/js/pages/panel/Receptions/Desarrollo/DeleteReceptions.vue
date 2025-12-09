<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Interfaces que SI coinciden con tu tabla
interface Reception {
    id: number;

    engine?: {
        marca: string;
        modelo: string;
    };

    owner?: {
        nombres: string;
    };

    contact?: {
        nombres: string;
    };

    problema?: string;
    fecha_ingreso?: string | null;
}

// Props
const props = defineProps<{
  visible: boolean;
  reception: Reception | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();

// Control local
const localVisible = ref<boolean>(false);

// Sincroniza visibilidad con el padre
watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

// Cerrar modal
function closeDialog() {
    emit('update:visible', false);
}

// Eliminar recepción
async function deleteReception() {
    if (!props.reception) return;

    try {
        await axios.delete(`/recepcion/${props.reception.id}`);

        emit('deleted');
        closeDialog();

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Recepción eliminada correctamente',
            life: 3000
        });

    } catch (error) {
        console.error(error);

        let errorMessage = 'Error eliminando la recepción';
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
    :style="{ width: '90%', maxWidth: '480px' }"
    header="Confirmar eliminación"
    :modal="true"
    @update:visible="closeDialog"
>
    <div class="flex items-start gap-4">
        <i class="pi pi-exclamation-triangle text-yellow-600 text-3xl" />

        <div class="space-y-2">
            <p class="text-base">
                ¿Estás seguro de eliminar la recepción 
                <b>#{{ props.reception?.id }}</b>?
            </p>

            <!-- Datos de la recepción -->
            <div class="mt-3 text-sm text-gray-700 space-y-1" v-if="props.reception">

                <p><b>Cliente:</b> {{ props.reception.owner?.nombres ?? 'Sin cliente' }}</p>
                <p><b>Contacto:</b> {{ props.reception.contact?.nombres ?? 'Sin contacto' }}</p>
                <p>
                    <b>Motor:</b>
                    {{ props.reception.engine?.marca ?? '-' }}
                    {{ props.reception.engine?.modelo ?? '' }}
                </p>

                <p><b>Problema:</b> {{ props.reception.problema ?? 'No registrado' }}</p>

                <p>
                    <b>Fecha ingreso:</b> 
                    {{ props.reception.fecha_ingreso ?? '-' }}
                </p>

            </div>
        </div>
    </div>

    <template #footer>
        <Button 
            label="No, cancelar" 
            icon="pi pi-times" 
            text
            @click="closeDialog" 
        />

        <Button 
            label="Sí, eliminar" 
            icon="pi pi-trash" 
            severity="danger"
            @click="deleteReception"
        />
    </template>
</Dialog>
</template>
