<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Button from 'primevue/button';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Engine {
    hp: string;
    tipo: string;
    marca: string;
    modelo: string;
    year: number | null;
    serie: string;
    state: boolean | null;   // 👈 AÑADIDO
}

interface ServerErrors {
    hp?: string[];
    tipo?: string[];
    marca?: string[];
    modelo?: string[];
    year?: string[];
    serie?: string[];
    state?: string[];        // 👈 AÑADIDO
}

// Props
const props = defineProps<{
    visible: boolean;
    engineId: number | null;
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

const engine = ref<Engine>({
    hp: '',
    tipo: '',
    marca: '',
    modelo: '',
    year: null,
    serie: '',
    state: true,            // 👈 por defecto activo
});

// -------------------------
// TIPOS DE MOTOR
// -------------------------
const tiposMotor = [
    { label: "Diesel", value: "Diesel" },
    { label: "Gasolina", value: "Gasolina" },
    { label: "2T", value: "2T" },
    { label: "4T", value: "4T" },
    { label: "Turbodiésel", value: "Turbodiésel" },
    { label: "Híbrido", value: "Híbrido" },
    { label: "Eléctrico", value: "Eléctrico" },
];

// STATE options
const stateOptions = [
    { label: 'Activo', value: true },
    { label: 'Inactivo', value: false },
];

// -----------------------------
// years (1970 → year actual)
// -----------------------------
const yearOptions = ref<{ label: string; value: number }[]>([]);
const currentYear = new Date().getFullYear();
for (let y = currentYear; y >= 1970; y--) {
    yearOptions.value.push({ label: `${y}`, value: y });
}

// Watchers
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.engineId) {
        fetchEngine();
    }
});
watch(dialogVisible, (val) => emit('update:visible', val));

// Fetch engine
const fetchEngine = async () => {
    try {
        loading.value = true;

        const res = await axios.get(`/motor/${props.engineId}`);
        const data = res.data.engine;

        engine.value = {
            hp: data.hp,
            tipo: data.tipo,
            marca: data.marca,
            modelo: data.modelo,
            year: data.year,
            serie: data.serie,
            state: data.state,           // 👈 AÑADIDO
        };

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el motor',
            life: 3000
        });
    } finally {
        loading.value = false;
    }
};

// Update engine
const updateEngine = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const payload = { ...engine.value };

        await axios.put(`/motor/${props.engineId}`, payload);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Motor actualizado correctamente',
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
                detail: 'No se pudo actualizar el motor',
                life: 3000
            });
        }
    }
};
</script>

<template>
<Dialog 
    v-model:visible="dialogVisible" 
    header="Editar motor" 
    modal 
    :closable="true" 
    :style="{ width: '95vw', maxWidth: '600px' }"
>
    <div class="flex flex-col gap-6">

        <div class="grid grid-cols-12 gap-4">

            <!-- HP -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">HP <span class="text-red-500">*</span></label>
                <InputText
                    v-model="engine.hp"
                    maxlength="50"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.hp }"
                />
                <small v-if="serverErrors.hp" class="text-red-500">{{ serverErrors.hp[0] }}</small>
            </div>

            <!-- TIPO -->
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
                <small v-if="serverErrors.tipo" class="text-red-500">{{ serverErrors.tipo[0] }}</small>
            </div>

            <!-- MARCA -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Marca <span class="text-red-500">*</span></label>
                <InputText
                    v-model="engine.marca"
                    maxlength="100"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.marca }"
                />
                <small v-if="serverErrors.marca" class="text-red-500">{{ serverErrors.marca[0] }}</small>
            </div>

            <!-- MODELO -->
            <div class="col-span-12 md:col-span-6">
                <label class="block font-bold mb-2">Modelo <span class="text-red-500">*</span></label>
                <InputText
                    v-model="engine.modelo"
                    maxlength="150"
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.modelo }"
                />
                <small v-if="serverErrors.modelo" class="text-red-500">{{ serverErrors.modelo[0] }}</small>
            </div>

            <!-- YEAR -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Año <span class="text-red-500">*</span></label>
                <Select
                    v-model="engine.year"
                    :options="yearOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccionar año"
                    class="w-full"
                />
                <small v-if="serverErrors.year" class="text-red-500">{{ serverErrors.year[0] }}</small>
            </div>

            <!-- STATE -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                <Select
                    v-model="engine.state"
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
                @click="updateEngine" 
                :loading="loading" 
                class="w-full sm:w-auto" 
            />
        </div>
    </template>
</Dialog>
</template>
