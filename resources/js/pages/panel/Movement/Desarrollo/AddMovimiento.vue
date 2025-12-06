<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo Movimiento" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
            <template #end>
            <ToolsMovement />       
        </template>
    </Toolbar>

    <Dialog 
        v-model:visible="movimientoDialog" 
        :modal="true" 
        :style="{ width: '95vw', maxWidth: '600px' }"
        header="Registro de Movimiento" 
        class="w-full max-w-full sm:max-w-lg"
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
                    <InputText v-model.trim="movimiento.error" maxlength="3" placeholder="Ingrese código de error" class="w-full"/>
                    <small v-if="serverErrors.error" class="text-red-500">{{ serverErrors.error[0] }}</small>
                </div>

                <!-- Fechas -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Envío ESP32</label>
                    <InputText type="datetime-local" v-model="movimiento.fechaEnvioESP32" class="w-full"/>
                    <small v-if="serverErrors.fechaEnvioESP32" class="text-red-500">{{ serverErrors.fechaEnvioESP32[0] }}</small>
                </div>

                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Recepción</label>
                    <InputText type="datetime-local" v-model="movimiento.fechaRecepcion" class="w-full"/>
                    <small v-if="serverErrors.fechaRecepcion" class="text-red-500">{{ serverErrors.fechaRecepcion[0] }}</small>
                </div>

                <div class="col-span-12">
                    <label class="block font-bold mb-2">Fecha Reconocimiento</label>
                    <InputText type="datetime-local" v-model="movimiento.fechaReconocimiento" class="w-full"/>
                    <small v-if="serverErrors.fechaReconocimiento" class="text-red-500">{{ serverErrors.fechaReconocimiento[0] }}</small>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text class="mr-2" @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarMovimiento" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import ToolsMovement from './toolsMovement.vue';


const tiposMovimiento = ref([
    { label: 'Cara', value: 1 },
    { label: 'Huella', value: 2 }
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

// Refs
const toast = useToast();
const submitted = ref(false);
const movimientoDialog = ref(false);
const serverErrors = ref<Record<string, string[]>>({});
const espacios = ref<Espacio[]>([]);
const emit = defineEmits<{
    (e: 'movimiento-agregado'): void
}>();

const movimiento = ref<Movimiento>({
    idEspacio: null,
    idTipo: null, // 🔹 Número en lugar de string
    reconocido: false,
    access: false,
    error: '',
    fechaEnvioESP32: '',
    fechaRecepcion: '',
    fechaReconocimiento: ''
});

// Abrir modal
function openNew() {
    resetMovimiento();
    movimientoDialog.value = true;
    fetchEspacios();
}

// Cerrar modal
function hideDialog() {
    movimientoDialog.value = false;
    resetMovimiento();
}

// Reset
function resetMovimiento() {
    movimiento.value = {
        idEspacio: null,
        idTipo: null,
        reconocido: false,
        access: false,
        error: '',
        fechaEnvioESP32: '',
        fechaRecepcion: '',
        fechaReconocimiento: ''
    };
    serverErrors.value = {};
    submitted.value = false;
}

// Cargar espacios
function fetchEspacios() {
    axios.get<{ data: Espacio[] }>('/espacio', { params: { state: 1 } })
        .then(res => {
            espacios.value = res.data.data;
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
        });
}

// Guardar movimiento
function guardarMovimiento() {
    submitted.value = true;
    serverErrors.value = {};

    if (!movimiento.value.idEspacio || !movimiento.value.idTipo) return;

    axios.post('/movimiento', movimiento.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Movimiento registrado', life: 3000 });
            hideDialog();
            emit('movimiento-agregado');
        })
        .catch((error: AxiosError<{ errors?: Record<string, string[]> }>) => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar el movimiento',
                    life: 3000
                });
            }
        });
}
</script>
