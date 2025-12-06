<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Tipo de Empleado" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '90%', maxWidth: '600px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="tipoEmpleado.name"
                        required
                        maxlength="100"
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="col-span-3">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="tipoEmpleado.state" :binary="true" inputId="state" />
                        <Tag :value="tipoEmpleado.state ? 'Activo' : 'Inactivo'" :severity="tipoEmpleado.state ? 'success' : 'danger'" />
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateTipoEmpleado" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

// Interface para tipar el tipo de empleado
interface TipoEmpleado {
    name: string;
    state: boolean;
}

// Props tipadas
const props = defineProps<{
    visible: boolean;
    tipoEmpleadoId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const serverErrors = ref<Record<string, string[]>>({});
const submitted = ref<boolean>(false);
const toast = useToast();
const loading = ref<boolean>(false);
const dialogVisible = ref<boolean>(props.visible);

// Sincroniza visibilidad con la prop
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

// Ref para el tipo de empleado
const tipoEmpleado = ref<TipoEmpleado>({
    name: '',
    state: false
});

// Fetch tipo de empleado
const fetchTipoEmpleado = async (): Promise<void> => {
    if (!props.tipoEmpleadoId) return;
    loading.value = true;
    try {
        const response = await axios.get(`/tipo_empleado/${props.tipoEmpleadoId}`);
        const data = response.data.employeeType;
        tipoEmpleado.value.name = data.name;
        tipoEmpleado.value.state = data.state;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el tipo de empleado',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Watch para cargar datos al abrir el diálogo
watch(() => props.visible, (newVal) => {
    if (newVal && props.tipoEmpleadoId) {
        fetchTipoEmpleado();
    }
});

// Update tipo de empleado
const updateTipoEmpleado = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    if (!props.tipoEmpleadoId) return;

    try {
        const tipoEmpleadoData = {
            name: tipoEmpleado.value.name,
            state: tipoEmpleado.value.state
        };

        await axios.put(`/tipo_empleado/${props.tipoEmpleadoId}`, tipoEmpleadoData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Tipo de empleado actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        const axiosError = error as AxiosError;
        if (axiosError.response?.status === 422 && axiosError.response.data) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
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
                detail: 'No se pudo actualizar el tipo de empleado',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>
