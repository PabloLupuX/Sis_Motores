<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Accessory {
    name: string;
    state: boolean | null;
}

interface ServerErrors {
    name?: string[];
    state?: string[];
}

// Props
const props = defineProps<{
    visible: boolean;
    accessoryId: number | null;
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

const accessory = ref<Accessory>({
    name: '',
    state: true,
});

// Opciones de estado
const stateOptions = [
    { label: "Activo", value: true },
    { label: "Inactivo", value: false },
];

// -----------------------------
// WATCHERS
// -----------------------------
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.accessoryId) {
        fetchAccessory();
    }
});

watch(dialogVisible, (val) => emit('update:visible', val));


// -----------------------------
// FETCH ACCESSORY
// -----------------------------
const fetchAccessory = async () => {
    try {
        loading.value = true;

        const res = await axios.get(`/accesorio/${props.accessoryId}`);
        const data = res.data.accessory;

        accessory.value = {
            name: data.name,
            state: data.state,
        };

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el accesorio',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};


// -----------------------------
// UPDATE ACCESSORY
// -----------------------------
const updateAccessory = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const payload = { ...accessory.value };

        await axios.put(`/accesorio/${props.accessoryId}`, payload);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Accesorio actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');

    } catch (error) {
        const axiosError = error as AxiosError<any>;

        if (axiosError.response && axiosError.response.data?.errors) {
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
                detail: 'No se pudo actualizar el accesorio',
                life: 3000
            });
        }
    }
};
</script>

<template>
<Dialog 
    v-model:visible="dialogVisible" 
    header="Editar accesorio" 
    modal 
    :closable="true" 
    :style="{ width: '95vw', maxWidth: '600px' }"
>
    <div class="flex flex-col gap-6">

        <div class="grid grid-cols-12 gap-4">

            <!-- NAME -->
            <div class="col-span-12">
                <label class="block font-bold mb-2">
                    Nombre <span class="text-red-500">*</span>
                </label>

                <InputText
                    v-model="accessory.name"
                    maxlength="150"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.name }"
                />

                <small v-if="serverErrors.name" class="text-red-500">
                    {{ serverErrors.name[0] }}
                </small>
            </div>

            <!-- STATE -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">
                    Estado <span class="text-red-500">*</span>
                </label>

                <Select
                    v-model="accessory.state"
                    :options="stateOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar estado"
                    class="w-full"
                />

                <small v-if="serverErrors.state" class="text-red-500">
                    {{ serverErrors.state[0] }}
                </small>
            </div>

        </div>
    </div>

    <!-- FOOTER -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button 
                label="Cancelar" 
                icon="pi pi-times" 
                text 
                class="w-full sm:w-auto" 
                @click="dialogVisible = false" 
            />
            <Button 
                label="Guardar" 
                icon="pi pi-check" 
                @click="updateAccessory" 
                :loading="loading" 
                class="w-full sm:w-auto" 
            />
        </div>
    </template>
</Dialog>
</template>
