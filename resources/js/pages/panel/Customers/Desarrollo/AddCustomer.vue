<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import ToolsCustomer from './toolsCustomer.vue';

// Interfaces
interface Customer {
    codigo: string;
    nombres: string;
    alias: string;
    telefono: string;
    state: boolean;
}

interface ServerErrors {
    [key: string]: string[];
}

// Toast
const toast = useToast();

// Refs
const submitted = ref<boolean>(false);
const customerDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const customer = ref<Customer>({
    codigo: '',
    nombres: '',
    alias: '',
    telefono: '',
    state: true
});

// Emitir al padre
const emit = defineEmits<{
    (e: 'customer-agregado'): void
}>();

// Resetear valores
function resetCustomer() {
    customer.value = {
        codigo: '',
        nombres: '',
        alias: '',
        telefono: '',
        state: true
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetCustomer();
    customerDialog.value = true;
}

function hideDialog() {
    customerDialog.value = false;
    resetCustomer();
}

// Guardar cliente
async function guardarCustomer() {
    submitted.value = true;
    serverErrors.value = {};

    if (!customer.value.codigo || !customer.value.nombres) return;

    try {
        await axios.post('/cliente', customer.value);

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Cliente registrado correctamente',
            life: 3000
        });

        hideDialog();
        emit('customer-agregado');

    } catch (error) {
        const axiosError = error as AxiosError;

        if (axiosError.response && axiosError.response.status === 422) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el cliente',
                life: 3000
            });
        }
    }
}
</script>

<template>
<Toolbar class="mb-6">
    <template #start>
        <Button label="Cliente" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
        <template #end>
        <ToolsCustomer />       
    </template>
</Toolbar>

<Dialog
    v-model:visible="customerDialog"
    :style="{ width: '95vw', maxWidth: '600px' }"
    header="Registro de cliente"
    :modal="true"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- Código -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-3">Código <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="customer.codigo"
                    required
                    maxlength="11"
                    fluid
                    class="w-full"
                />
                <small v-if="submitted && !customer.codigo" class="text-red-500">El código es obligatorio.</small>
                <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
            </div>

            <!-- Nombres -->
            <div class="col-span-12 md:col-span-8">
                <label class="block font-bold mb-3">Nombres <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="customer.nombres"
                    required
                    maxlength="255"
                    fluid
                    class="w-full"
                />
                <small v-if="submitted && !customer.nombres" class="text-red-500">Los nombres son obligatorios.</small>
                <small v-if="serverErrors.nombres" class="text-red-500">{{ serverErrors.nombres[0] }}</small>
            </div>

            <!-- Alias -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-3">Alias</label>
                <InputText
                    v-model.trim="customer.alias"
                    maxlength="255"
                    fluid
                    class="w-full"
                />
                <small v-if="serverErrors.alias" class="text-red-500">{{ serverErrors.alias[0] }}</small>
            </div>

            <!-- Teléfono -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-3">Teléfono</label>
                <InputText
                    v-model.trim="customer.telefono"
                    maxlength="9"
                    fluid
                    class="w-full"
                />
                <small v-if="serverErrors.telefono" class="text-red-500">{{ serverErrors.telefono[0] }}</small>
            </div>

            <!-- Estado -->
            <div class="col-span-12 md:col-span-4 flex flex-col">
                <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>

                <div class="flex items-center gap-3">
                    <Checkbox v-model="customer.state" :binary="true" />
                    <Tag :value="customer.state ? 'Activo' : 'Inactivo'" :severity="customer.state ? 'success' : 'danger'" />
                </div>

                <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" class="w-full sm:w-auto" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarCustomer" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
