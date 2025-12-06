<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';

interface ConfigAlerta {
    time: number;
    amount: number;
}

interface ServerErrors {
    time?: string[];
    amount?: string[];
}

const props = defineProps<{
    visible: boolean;
    configAlertaId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();

const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const configAlerta = ref<ConfigAlerta>({
    time: 0,
    amount: 0,
});

watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.configAlertaId) {
        fetchConfigAlerta();
    }
});
watch(dialogVisible, (val) => emit('update:visible', val));

const fetchConfigAlerta = async () => {
    try {
        loading.value = true;
        const res = await axios.get(`/config_alerta/${props.configAlertaId}`);
        const data = res.data.configAlert;
        configAlerta.value = {
            time: Number(data.time),
            amount: Number(data.amount),
        };
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la configuración de alerta', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateConfigAlerta = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const configAlertaData = {
            time: configAlerta.value.time,
            amount: configAlerta.value.amount,
        };

        await axios.put(`/config_alerta/${props.configAlertaId}`, configAlertaData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Configuración de alerta actualizada correctamente',
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
                detail: 'No se pudo actualizar la configuración de alerta',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>

<template>
<Dialog 
    v-model:visible="dialogVisible" 
    header="Editar Configuración de Alerta" 
    modal 
    :closable="true" 
    :style="{ width: '95vw', maxWidth: '500px' }"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- Cantidad -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Cantidad <span class="text-red-500">*</span></label>
                <InputNumber
                    v-model="configAlerta.amount"
                    :min="0"
                    :useGrouping="false"
                    inputClass="w-full"
                    :class="{ 'p-invalid': serverErrors.amount }"
                />
                <small v-if="serverErrors.amount" class="text-red-500">{{ serverErrors.amount?.[0] }}</small>
            </div>

            <!-- Tiempo -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Tiempo <span class="text-red-500">*</span></label>
                <InputNumber
                    v-model="configAlerta.time"
                    mode="decimal"
                    locale="en-US"
                    :min="0"
                    :maxFractionDigits="2"
                    :step="0.1"
                    :useGrouping="false"
                    inputClass="w-full"
                    :class="{ 'p-invalid': serverErrors.time }"
                    placeholder="Ej: 5.5 (h/m/s)"
                />
                <small v-if="serverErrors.time" class="text-red-500">{{ serverErrors.time?.[0] }}</small>
            </div>
        </div>
    </div>

    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text class="w-full sm:w-auto" @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateConfigAlerta" :loading="loading" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
