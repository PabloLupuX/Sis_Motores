<template>
    <Dialog 
        v-model:visible="dialogVisible" 
        header="Editar Movimiento" 
        :modal="true"
        class="w-full max-w-full sm:max-w-lg"
        :style="{ width: '95vw', maxWidth: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">

                <!-- Espacio -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Espacio <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="movimiento.idEspacio"
                        :options="espacios"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione un espacio"
                        class="w-full"
                        filter
                        filterPlaceholder="Buscar espacio"
                        :class="{ 'p-invalid': serverErrors.idEspacio }"
                    />
                    <small v-if="submitted && !movimiento.idEspacio" class="text-red-500">Debe seleccionar un espacio.</small>
                    <small v-if="serverErrors.idEspacio" class="text-red-500">{{ serverErrors.idEspacio[0] }}</small>
                </div>

                <!-- Tipo -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Tipo <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="movimiento.idTipo"
                        :options="tiposMovimiento"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione un tipo"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.idTipo }"
                    />
                    <small v-if="submitted && !movimiento.idTipo" class="text-red-500">El tipo es obligatorio.</small>
                    <small v-if="serverErrors.idTipo" class="text-red-500">{{ serverErrors.idTipo[0] }}</small>
                </div>

                <!-- Reconocido -->
                <div class="col-span-12 sm:col-span-6 flex items-center gap-2">
                    <Checkbox v-model="movimiento.reconocido" :binary="true" />
                    <label class="font-bold">Reconocido</label>
                </div>

                <!-- Acceso -->
                <div class="col-span-12 sm:col-span-6 flex items-center gap-2">
                    <Checkbox v-model="movimiento.access" :binary="true" />
                    <label class="font-bold">Acceso</label>
                </div>

                <!-- Error -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Error</label>
                    <InputText 
                        v-model.trim="movimiento.error" 
                        maxlength="3" 
                        placeholder="Ingrese código de error" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.error }"
                    />
                    <small v-if="serverErrors.error" class="text-red-500">{{ serverErrors.error[0] }}</small>
                </div>

                <!-- Fechas -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Envío ESP32</label>
                    <InputText 
                        type="datetime-local" 
                        v-model="movimiento.fechaEnvioESP32" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.fechaEnvioESP32 }"
                    />
                    <small v-if="serverErrors.fechaEnvioESP32" class="text-red-500">{{ serverErrors.fechaEnvioESP32[0] }}</small>
                </div>

                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Recepción</label>
                    <InputText 
                        type="datetime-local" 
                        v-model="movimiento.fechaRecepcion" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.fechaRecepcion }"
                    />
                    <small v-if="serverErrors.fechaRecepcion" class="text-red-500">{{ serverErrors.fechaRecepcion[0] }}</small>
                </div>

                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Reconocimiento</label>
                    <InputText 
                        type="datetime-local" 
                        v-model="movimiento.fechaReconocimiento" 
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.fechaReconocimiento }"
                    />
                    <small v-if="serverErrors.fechaReconocimiento" class="text-red-500">{{ serverErrors.fechaReconocimiento[0] }}</small>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text class="mr-2" @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateMovimiento" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';


// Tipos de movimiento
const tiposMovimiento = ref([
    { label: 'Cara', value: 1 },
    { label: 'Huella', value: 2 },
]);
// Tipos
interface Movimiento {
    idEspacio: number | null;
    idTipo: number | null; // 🔹 Cambiado a number
    reconocido: boolean;
    access: boolean;
    error: string;
    fechaEnvioESP32: string;
    fechaRecepcion: string;
    fechaReconocimiento: string;
}

interface Espacio {
    id: number;
    name: string;
}

interface ServerErrors {
    [key: string]: string[];
}

// Props
const props = defineProps<{
    visible: boolean;
    movimientoId: number | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

// Ref
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const espacios = ref<Espacio[]>([]);
const movimiento = ref<Movimiento>({
    idEspacio: null,
    idTipo: null,
    reconocido: false,
    access: false,
    error: '',
    fechaEnvioESP32: '',
    fechaRecepcion: '',
    fechaReconocimiento: ''
});

// Toast
const toast = useToast();

// Watchers
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.movimientoId) {
        fetchMovimiento();
        fetchEspacios();
    }
});
watch(dialogVisible, (val) => emit('update:visible', val));

// Funciones
const formatDateToInput = (dateString: string | null | undefined): string => {
    if (!dateString) return '';
    const [datePart, timePart, meridiem] = dateString.split(' ');
    const [dd, mm, yyyy] = datePart.split('-');
    let [hh, min] = timePart.split(':');
    let hour = parseInt(hh, 10);
    if (meridiem === 'PM' && hour < 12) hour += 12;
    if (meridiem === 'AM' && hour === 12) hour = 0;
    return `${yyyy}-${mm}-${dd}T${hour.toString().padStart(2, '0')}:${min}`;
};

// Cargar movimiento
const fetchMovimiento = async (): Promise<void> => {
    try {
        loading.value = true;
        const res = await axios.get(`/movimiento/${props.movimientoId}`);
        if (!res.data || !res.data.movement) {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Movimiento no encontrado', life: 3000 });
            return;
        }
        const data = res.data.movement;
        movimiento.value = {
            idEspacio: data.idEspacio ?? null,
            idTipo: data.idTipo ?? null,
            reconocido: data.reconocido ?? false,
            access: data.access ?? false,
            error: data.error ?? '',
            fechaEnvioESP32: formatDateToInput(data.fechaEnvioESP32),
            fechaRecepcion: formatDateToInput(data.fechaRecepcion),
            fechaReconocimiento: formatDateToInput(data.fechaReconocimiento)
        };
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el movimiento', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Cargar espacios
const fetchEspacios = async (): Promise<void> => {
    try {
        const res = await axios.get('/espacio', { params: { state: 1 } });
        espacios.value = res.data.data;
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
        console.error(error);
    }
};

// Actualizar movimiento
const updateMovimiento = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};
    if (!movimiento.value.idEspacio || !movimiento.value.idTipo) return;

    try {
        loading.value = true;
        await axios.put(`/movimiento/${props.movimientoId}`, movimiento.value);
        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Movimiento actualizado correctamente', life: 3000 });
        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        const err = error as AxiosError<{ errors?: ServerErrors }>;
        if (err.response?.data?.errors) {
            serverErrors.value = err.response.data.errors;
            toast.add({ severity: 'error', summary: 'Error de validación', detail: 'Revisa los campos e intenta nuevamente', life: 5000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar el movimiento', life: 3000 });
        }
        console.error(error);
    } finally {
        loading.value = false;
    }
};
</script>
