<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import ToolsAccessories from './ToolsAccessories.vue';

// Interfaces
interface Accessory {
    name: string;
    state: boolean | null;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();

// Refs
const submitted = ref(false);
const accessoryDialog = ref(false);
const serverErrors = ref<ServerErrors>({});

const accessory = ref<Accessory>({
    name: '',
    state: true,
});

// Opciones de estado
const stateOptions = [
    { label: 'Activo', value: true },
    { label: 'Inactivo', value: false }
];

// Emitir al padre
const emit = defineEmits<{
    (e: 'accessory-agregado'): void
}>();

// Resetear datos
function resetAccessory() {
    accessory.value = {
        name: '',
        state: true,
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetAccessory();
    accessoryDialog.value = true;
}

function hideDialog() {
    accessoryDialog.value = false;
    resetAccessory();
}

// Guardar accesorio
async function guardarAccessory() {
    submitted.value = true;
    serverErrors.value = {};

    if (!accessory.value.name || accessory.value.state === null) {
        return;
    }

    try {
        await axios.post('/accesorio', accessory.value);

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Accesorio registrado correctamente',
            life: 3000
        });

        hideDialog();
        emit('accessory-agregado');

    } catch (error) {
        const axiosError = error as AxiosError;

        if (axiosError.response && axiosError.response.status === 422) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el accesorio',
                life: 3000
            });
        }
    }
}
</script>

<template>
<Toolbar class="mb-6">
    <template #start>
        <Button label="Accesorio" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
   </template>
        <template #end>
        <ToolsAccessories />       
    </template>
</Toolbar>

<Dialog
    v-model:visible="accessoryDialog"
    :style="{ width: '95vw', maxWidth: '600px' }"
    header="Registro de accesorio"
    :modal="true"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- NOMBRE -->
            <div class="col-span-12">
                <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="accessory.name"
                    maxlength="150"
                    class="w-full"
                />
                <small v-if="submitted && !accessory.name" class="text-red-500">El nombre es obligatorio.</small>
                <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name?.[0] }}</small>
            </div>

            <!-- ESTADO -->
            <div class="col-span-12">
                <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                <Select
                    v-model="accessory.state"
                    :options="stateOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar estado"
                    class="w-full"
                />
                <small v-if="submitted && accessory.state === null" class="text-red-500">El estado es obligatorio.</small>
                <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state?.[0] }}</small>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" class="w-full sm:w-auto" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarAccessory" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
