<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Customer {
    codigo: string;
    nombres: string;
    alias: string;
    telefono: string;
    state: boolean | null;
}

interface ServerErrors {
    codigo?: string[];
    nombres?: string[];
    alias?: string[];
    telefono?: string[];
    state?: string[];
}

// Props
const props = defineProps<{
    visible: boolean;
    customerId: number | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();

// Refs
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const customer = ref<Customer>({
    codigo: '',
    nombres: '',
    alias: '',
    telefono: '',
    state: true,
});

// ESTADO
const stateOptions = [
    { label: 'Activo', value: true },
    { label: 'Inactivo', value: false },
];

// Watchers
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.customerId) {
        fetchCustomer();
    }
});

watch(dialogVisible, (val) => emit('update:visible', val));

// Fetch customer
const fetchCustomer = async () => {
    try {
        loading.value = true;

        const res = await axios.get(`/cliente/${props.customerId}`);
        const data = res.data.customer;

        customer.value = {
            codigo: data.codigo,
            nombres: data.nombres,
            alias: data.alias ?? '',
            telefono: data.telefono ?? '',
            state: data.state, // 👈 ahora correcto
        };

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el cliente',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

// Update customer
const updateCustomer = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const payload = {
            ...customer.value
        };

        await axios.put(`/cliente/${props.customerId}`, payload);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Cliente actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');

    } catch (error) {
        const axiosError = error as AxiosError<any>;

        if (axiosError.response?.data?.errors) {
            serverErrors.value = axiosError.response.data.errors;
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
                detail: 'No se pudo actualizar el cliente',
                life: 3000
            });
        }
    }
};
</script>

<template>
<Dialog 
    v-model:visible="dialogVisible" 
    header="Editar cliente" 
    modal 
    :closable="true" 
    :style="{ width: '95vw', maxWidth: '600px' }"
>
    <div class="flex flex-col gap-6">

        <div class="grid grid-cols-12 gap-4">

            <!-- Código -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Código *</label>
                <InputText
                    v-model="customer.codigo"
                    maxlength="11"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.codigo }"
                />
                <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
            </div>

            <!-- Nombres -->
            <div class="col-span-12 md:col-span-8">
                <label class="block font-bold mb-2">Nombres *</label>
                <InputText
                    v-model="customer.nombres"
                    maxlength="255"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.nombres }"
                />
                <small v-if="serverErrors.nombres" class="text-red-500">{{ serverErrors.nombres[0] }}</small>
            </div>

            <!-- Alias -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Alias</label>
                <InputText
                    v-model="customer.alias"
                    maxlength="255"
                    class="w-full"
                />
                <small v-if="serverErrors.alias" class="text-red-500">{{ serverErrors.alias[0] }}</small>
            </div>

            <!-- Teléfono -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Teléfono</label>
                <InputText
                    v-model="customer.telefono"
                    maxlength="9"
                    class="w-full"
                />
                <small v-if="serverErrors.telefono" class="text-red-500">{{ serverErrors.telefono[0] }}</small>
            </div>

            <!-- Estado -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Estado *</label>
                <Select
                    v-model="customer.state"
                    :options="stateOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar estado"
                    class="w-full"
                />
                <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>

            </div>

        </div>
    </div>

    <!-- Footer -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text class="w-full sm:w-auto" @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateCustomer" :loading="loading" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
