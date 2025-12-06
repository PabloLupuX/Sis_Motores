<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
//import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import ToolsConfigAlert from './toolsConfigAlert.vue';

// Interfaces

interface ServerErrors {
    [key: string]: string[];
}

// Toast
const toast = useToast();

// Refs
const submitted = ref<boolean>(false);
const configAlertaDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const configAlerta = ref({
    time: '',
    amount: '',
});

// Emite evento al padre
const emit = defineEmits<{
    (e: 'config-alerta-agregada'): void
}>();

// Métodos
function resetConfigAlerta() {
    configAlerta.value = {
        time: '',
        amount: ''
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetConfigAlerta();
    configAlertaDialog.value = true;
}

function hideDialog() {
    configAlertaDialog.value = false;
    resetConfigAlerta();
}

async function guardarConfigAlerta() {
    submitted.value = true;
    serverErrors.value = {};

    // validación rápida en front (evita request innecesario)
    if (configAlerta.value.time === '' || configAlerta.value.time === null || configAlerta.value.time === undefined) return;
    if (configAlerta.value.amount === '' || configAlerta.value.amount === null || configAlerta.value.amount === undefined) return;

    try {
        // Asegurar que se envíen números (no strings)
        const payload = {
            time: Number(configAlerta.value.time),
            amount: Number(configAlerta.value.amount),
        };

        await axios.post('/config_alerta', payload);

        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Configuración de alerta registrada', life: 3000 });
        hideDialog();
        emit('config-alerta-agregada');
    } catch (error) {
        const axiosError = error as AxiosError<any>;
        if (axiosError.response && axiosError.response.status === 422) {
            serverErrors.value = axiosError.response.data.errors || {};
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar la configuración de alerta',
                life: 3000
            });
        }
        console.error(error);
    }
}
</script>

<template>
<Toolbar class="mb-6">
    <template #start>
        <Button label="Nueva configuración" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
    <template #end>
        <ToolsConfigAlert />
    </template>
</Toolbar>

<Dialog 
    v-model:visible="configAlertaDialog" 
    :style="{ width: '95vw', maxWidth: '600px' }" 
    header="Registro de Configuración de Alerta" 
    :modal="true"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- Cantidad -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-3">Cantidad <span class="text-red-500">*</span></label>
                <InputText
                    v-model="configAlerta.amount"
                    type="number"
                    step="1"
                    min="0"
                    placeholder="Ej. 1000"
                    class="w-full"
                />
                <small v-if="submitted && (configAlerta.amount === '' || configAlerta.amount === null || configAlerta.amount === undefined)" class="text-red-500">La cantidad es obligatoria.</small>
                <small v-if="serverErrors.amount" class="text-red-500">{{ serverErrors.amount[0] }}</small>
            </div>

            <!-- Tiempo -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-3">Tiempo <span class="text-red-500">*</span></label>
                <InputText
                    v-model="configAlerta.time"
                    type="number"
                    step="0.01"
                    min="0"
                    placeholder="Ej. 5.5"
                    class="w-full"
                />
                <small v-if="submitted && (configAlerta.time === '' || configAlerta.time === null || configAlerta.time === undefined)" class="text-red-500">El tiempo es obligatorio.</small>
                <small v-if="serverErrors.time" class="text-red-500">{{ serverErrors.time[0] }}</small>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" class="w-full sm:w-auto" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarConfigAlerta" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
