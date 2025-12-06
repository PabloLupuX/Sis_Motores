<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import ToolsEngine from './ToolsEngine.vue';

// Interfaces
interface Engine {
    hp: string;
    tipo: string;
    marca: string;
    modelo: string;
    year: number | null;
    serie: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();

// Refs
const submitted = ref(false);
const engineDialog = ref(false);
const serverErrors = ref<ServerErrors>({});

const engine = ref<Engine>({
    hp: '',
    tipo: '',
    marca: '',
    modelo: '',
    year: null,
    serie: '',
});

// TIPOS DE MOTOR
const tiposMotor = [
    { label: 'Diesel', value: 'Diesel' },
    { label: 'Gasolina', value: 'Gasolina' },
    { label: '2T', value: '2T' },
    { label: '4T', value: '4T' },
    { label: 'Turbodiésel', value: 'Turbodiésel' },
    { label: 'Híbrido', value: 'Híbrido' },
    { label: 'Eléctrico', value: 'Eléctrico' },
];

// yearS (1970 → year actual)
const yearOptions = ref<{ label: string; value: number }[]>([]);
const currentYear = new Date().getFullYear();
for (let y = currentYear; y >= 1970; y--) {
    yearOptions.value.push({ label: `${y}`, value: y });
}

// Emitir al padre
const emit = defineEmits<{
    (e: 'engine-agregado'): void
}>();

// Resetear datos
function resetEngine() {
    engine.value = {
        hp: '',
        tipo: '',
        marca: '',
        modelo: '',
        year: null,
        serie: '',
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetEngine();
    engineDialog.value = true;
}

function hideDialog() {
    engineDialog.value = false;
    resetEngine();
}

// Guardar motor
async function guardarEngine() {
    submitted.value = true;
    serverErrors.value = {};

    if (!engine.value.hp || !engine.value.tipo || !engine.value.marca || !engine.value.modelo || !engine.value.year || !engine.value.serie) {
        return;
    }

    try {
        await axios.post('/motor', engine.value);

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Motor registrado correctamente',
            life: 3000
        });

        hideDialog();
        emit('engine-agregado');

    } catch (error) {
        const axiosError = error as AxiosError;

        if (axiosError.response && axiosError.response.status === 422) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el motor',
                life: 3000
            });
        }
    }
}
</script>

<template>
<Toolbar class="mb-6">
    <template #start>
        <Button label="Motor" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
    <template #end>
        <ToolsEngine />
    </template>
</Toolbar>

<Dialog
    v-model:visible="engineDialog"
    :style="{ width: '95vw', maxWidth: '600px' }"
    header="Registro de motor"
    :modal="true"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- HP -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">HP <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="engine.hp"
                    maxlength="50"
                    class="w-full"
                />
                <small v-if="submitted && !engine.hp" class="text-red-500">El HP es obligatorio.</small>
                <small v-if="serverErrors.hp" class="text-red-500">{{ serverErrors.hp?.[0] }}</small>
            </div>

            <!-- TIPO (SELECT) -->
            <div class="col-span-12 md:col-span-8">
                <label class="block font-bold mb-2">Tipo <span class="text-red-500">*</span></label>
                <Select
                    v-model="engine.tipo"
                    :options="tiposMotor"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar tipo"
                    class="w-full"
                />
                <small v-if="submitted && !engine.tipo" class="text-red-500">El tipo es obligatorio.</small>
                <small v-if="serverErrors.tipo" class="text-red-500">{{ serverErrors.tipo?.[0] }}</small>
            </div>

            <!-- MARCA -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Marca <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="engine.marca"
                    maxlength="100"
                    class="w-full"
                />
                <small v-if="submitted && !engine.marca" class="text-red-500">La marca es obligatoria.</small>
                <small v-if="serverErrors.marca" class="text-red-500">{{ serverErrors.marca?.[0] }}</small>
            </div>

            <!-- MODELO -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Modelo <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="engine.modelo"
                    maxlength="150"
                    class="w-full"
                />
                <small v-if="submitted && !engine.modelo" class="text-red-500">El modelo es obligatorio.</small>
                <small v-if="serverErrors.modelo" class="text-red-500">{{ serverErrors.modelo?.[0] }}</small>
            </div>

            <!-- year (SELECT) -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">year <span class="text-red-500">*</span></label>
                <Select
                    v-model="engine.year"
                    :options="yearOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar year"
                    class="w-full"
                />
                <small v-if="submitted && !engine.year" class="text-red-500">El year es obligatorio.</small>
                <small v-if="serverErrors.year" class="text-red-500">{{ serverErrors.year?.[0] }}</small>
            </div>

            <!-- SERIE -->
            <div class="col-span-12 md:col-span-8">
                <label class="block font-bold mb-2">Serie <span class="text-red-500">*</span></label>
                <InputText
                    v-model.trim="engine.serie"
                    maxlength="150"
                    class="w-full"
                />
                <small v-if="submitted && !engine.serie" class="text-red-500">La serie es obligatoria.</small>
                <small v-if="serverErrors.serie" class="text-red-500">{{ serverErrors.serie?.[0] }}</small>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" class="w-full sm:w-auto" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarEngine" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>
