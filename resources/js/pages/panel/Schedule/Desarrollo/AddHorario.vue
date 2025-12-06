<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo horario" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <ToolsSchedule />
        </template>
    </Toolbar>

    <Dialog 
        v-model:visible="scheduleDialog" 
        header="Registro de horario" 
        modal 
        :style="{ width: '95vw', maxWidth: '700px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">

                <!-- Fecha -->
                <div class="col-span-12 md:col-span-4">
                    <label class="block font-bold mb-2">Fecha <span class="text-red-500">*</span></label>
                    <InputText v-model="schedule.fecha" type="date" required fluid class="w-full" />
                    <small v-if="submitted && !schedule.fecha" class="text-red-500">La fecha es obligatoria.</small>
                    <small v-if="serverErrors.fecha" class="text-red-500">{{ serverErrors.fecha[0] }}</small>
                </div>

                <!-- Fecha y Hora Inicio -->
                <div class="col-span-12 md:col-span-4">
                    <label class="block font-bold mb-2">Fecha y Hora Inicio <span class="text-red-500">*</span></label>
                    <InputText v-model="schedule.fechaInicio" type="datetime-local" required fluid class="w-full" />
                    <small v-if="submitted && !schedule.fechaInicio" class="text-red-500">La fecha de inicio es obligatoria.</small>
                    <small v-if="serverErrors.fechaInicio" class="text-red-500">{{ serverErrors.fechaInicio[0] }}</small>
                </div>

                <!-- Fecha y Hora Fin -->
                <div class="col-span-12 md:col-span-4">
                    <label class="block font-bold mb-2">Fecha y Hora Fin <span class="text-red-500">*</span></label>
                    <InputText v-model="schedule.fechaFin" type="datetime-local" required fluid class="w-full" />
                    <small v-if="submitted && !schedule.fechaFin" class="text-red-500">La fecha de fin es obligatoria.</small>
                    <small v-if="serverErrors.fechaFin" class="text-red-500">{{ serverErrors.fechaFin[0] }}</small>
                </div>

                <!-- Espacio -->
                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Espacio <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="schedule.idEspacio"
                        :options="espacios"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione un espacio"
                        fluid
                        filter
                        filterPlaceholder="Buscar espacio"
                        class="w-full"
                    />
                    <small v-if="submitted && !schedule.idEspacio" class="text-red-500">Debe seleccionar un espacio.</small>
                    <small v-if="serverErrors.idEspacio" class="text-red-500">{{ serverErrors.idEspacio[0] }}</small>
                </div>

                <!-- Empleado -->
                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Empleado <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="schedule.idEmpleado"
                        :options="empleados"
                        optionLabel="nameFull"
                        optionValue="id"
                        placeholder="Seleccione un empleado"
                        fluid
                        filter
                        filterPlaceholder="Buscar empleado"
                        class="w-full"
                    />
                    <small v-if="submitted && !schedule.idEmpleado" class="text-red-500">Debe seleccionar un empleado.</small>
                    <small v-if="serverErrors.idEmpleado" class="text-red-500">{{ serverErrors.idEmpleado[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="schedule.state" :binary="true" />
                        <Tag :value="schedule.state ? 'Activo' : 'Inactivo'" :severity="schedule.state ? 'success' : 'danger'" />
                    </div>
                    <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
                <Button label="Cancelar" icon="pi pi-times" text class="w-full sm:w-auto" @click="hideDialog" />
                <Button label="Guardar" icon="pi pi-check" @click="guardarSchedule" class="w-full sm:w-auto" />
            </div>
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
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';
import ToolsSchedule from './toolsSchedule.vue';

// Interfaces
interface Espacio {
    id: number;
    name: string;
}

interface Empleado {
    id: number;
    name: string;
    apellido: string;
    nameFull?: string;
}

interface Schedule {
    fecha: string;
    fechaInicio: string;
    fechaFin: string;
    idEspacio: number | null;
    idEmpleado: number | null;
    state: boolean;
}

interface ServerErrors {
    [key: string]: string[];
}

// Estado
const toast = useToast();
const submitted = ref(false);
const scheduleDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const espacios = ref<Espacio[]>([]);
const empleados = ref<Empleado[]>([]);
const schedule = ref<Schedule>({
    fecha: '',
    fechaInicio: '',
    fechaFin: '',
    idEspacio: null,
    idEmpleado: null,
    state: true,
});

// Emit
const emit = defineEmits<{
    (e: 'schedule-agregado'): void;
}>();

// Funciones
function resetSchedule() {
    schedule.value = {
        fecha: '',
        fechaInicio: '',
        fechaFin: '',
        idEspacio: null,
        idEmpleado: null,
        state: true,
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetSchedule();
    scheduleDialog.value = true;
    fetchEspacios();
    fetchEmpleados();
}

function hideDialog() {
    scheduleDialog.value = false;
    resetSchedule();
}

function fetchEspacios() {
    axios.get<{ data: Espacio[] }>('/espacio', { params: { state: 1 } })
        .then(res => { espacios.value = res.data.data; })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
        });
}

function fetchEmpleados() {
    axios.get<{ data: Empleado[] }>('/empleado', { params: { state: 1 } })
        .then(res => {
            empleados.value = res.data.data.map(emp => ({ ...emp, nameFull: emp.name + ' ' + emp.apellido }));
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
        });
}

function guardarSchedule() {
    submitted.value = true;
    serverErrors.value = {};

    if (!schedule.value.fecha || !schedule.value.fechaInicio || !schedule.value.fechaFin || !schedule.value.idEspacio || !schedule.value.idEmpleado) return;

    axios.post('/horario', schedule.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Horario registrado', life: 3000 });
            hideDialog();
            emit('schedule-agregado');
        })
        .catch((error: AxiosError<{ errors: ServerErrors }>) => {
            if (error.response?.status === 422 && error.response.data.errors) {
                serverErrors.value = error.response.data.errors;
            } else {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo registrar el horario', life: 3000 });
            }
        });
}
</script>
