<template>
<Dialog 
    v-model:visible="dialogVisible" 
    header="Editar Horario" 
    modal 
    :closable="true" 
    :style="{ width: '95vw', maxWidth: '700px' }"
>
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">

            <!-- Fecha -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Fecha <span class="text-red-500">*</span></label>
                <InputText 
                    v-model="schedule.fechaFormatted" 
                    type="date" 
                    required 
                    fluid 
                    class="w-full" 
                    :class="{ 'p-invalid': serverErrors.fecha }" 
                />
                <small v-if="submitted && !schedule.fechaFormatted" class="text-red-500">La fecha es obligatoria.</small>
                <small v-if="serverErrors.fecha" class="text-red-500">{{ serverErrors.fecha[0] }}</small>
            </div>

            <!-- Fecha Inicio -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Fecha y Hora Inicio <span class="text-red-500">*</span></label>
                <InputText 
                    v-model="schedule.fechaInicioFormatted" 
                    type="datetime-local" 
                    required 
                    fluid 
                    class="w-full" 
                    :class="{ 'p-invalid': serverErrors.fechaInicio }" 
                />
                <small v-if="submitted && !schedule.fechaInicioFormatted" class="text-red-500">La fecha de inicio es obligatoria.</small>
                <small v-if="serverErrors.fechaInicio" class="text-red-500">{{ serverErrors.fechaInicio[0] }}</small>
            </div>

            <!-- Fecha Fin -->
            <div class="col-span-12 md:col-span-4">
                <label class="block font-bold mb-2">Fecha y Hora Fin <span class="text-red-500">*</span></label>
                <InputText 
                    v-model="schedule.fechaFinFormatted" 
                    type="datetime-local" 
                    required 
                    fluid 
                    class="w-full" 
                    :class="{ 'p-invalid': serverErrors.fechaFin }" 
                />
                <small v-if="submitted && !schedule.fechaFinFormatted" class="text-red-500">La fecha de fin es obligatoria.</small>
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
                    :class="{ 'p-invalid': serverErrors.idEspacio }"
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
                    :class="{ 'p-invalid': serverErrors.idEmpleado }"
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

    <!-- Footer responsive -->
    <template #footer>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
            <Button label="Cancelar" icon="pi pi-times" text class="w-full sm:w-auto" @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateSchedule" :loading="loading" class="w-full sm:w-auto" />
        </div>
    </template>
</Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import axios, { AxiosResponse } from 'axios';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Schedule {
    fechaFormatted: string;
    fechaInicioFormatted: string;
    fechaFinFormatted: string;
    idEspacio: number | null;
    idEmpleado: number | null;
    state: boolean;
}

interface Espacio {
    id: number;
    name: string;
}

interface Empleado {
    id: number;
    name: string;
    apellido: string;
    nameFull: string;
}

interface ServerErrors {
    [key: string]: string[];
}

// Props
const props = defineProps<{
    visible: boolean;
    scheduleId: number | null;
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

// Toast
const toast = useToast();

// Refs
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});

const schedule = ref<Schedule>({
    fechaFormatted: '',
    fechaInicioFormatted: '',
    fechaFinFormatted: '',
    idEspacio: null,
    idEmpleado: null,
    state: false,
});

const espacios = ref<Espacio[]>([]);
const empleados = ref<Empleado[]>([]);

// Watchers
watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.scheduleId) {
        fetchSchedule();
        fetchEspacios();
        fetchEmpleados();
    }
});

watch(dialogVisible, (val) => emit('update:visible', val));

// Funciones de conversión de fecha/hora
const convertDateToInputFormat = (apiDate: string): string => {
    if (!apiDate) return '';
    console.log('Convirtiendo fecha simple:', apiDate);
    
    try {
        // Probar formato dd/MM/yyyy
        if (apiDate.includes('/')) {
            const [day, month, year] = apiDate.split('/');
            return `${year}-${month.padStart(2,'0')}-${day.padStart(2,'0')}`;
        } else if (apiDate.includes('-')) {
            const [day, month, year] = apiDate.split('-');
            return `${year}-${month.padStart(2,'0')}-${day.padStart(2,'0')}`;
        }
        return '';
    } catch (error) {
        console.error('Error converting date:', apiDate, error);
        return '';
    }
};

const convertDateTimeToInputFormat = (apiDateTime: string): string => {
    if (!apiDateTime) return '';
    console.log('Convirtiendo fecha y hora:', apiDateTime);
    
    try {
        // Probar formato "dd/MM/yyyy HH:mm"
        if (apiDateTime.includes('/') && apiDateTime.includes(':')) {
            const [datePart, timePart] = apiDateTime.split(' ');
            const [day, month, year] = datePart.split('/');
            const [hours, minutes] = timePart.split(':');
            return `${year}-${month.padStart(2,'0')}-${day.padStart(2,'0')}T${hours.padStart(2,'0')}:${minutes}`;
        } else if (apiDateTime.includes('-') && apiDateTime.includes(':')) {
            const [datePart, timePart] = apiDateTime.split(' ');
            const [day, month, year] = datePart.split('-');
            const [hours, minutes] = timePart.split(':');
            return `${year}-${month.padStart(2,'0')}-${day.padStart(2,'0')}T${hours.padStart(2,'0')}:${minutes}`;
        }
        return '';
    } catch (error) {
        console.error('Error converting date time:', apiDateTime, error);
        return '';
    }
};

// Fetch Schedule
const fetchSchedule = async () => {
    try {
        loading.value = true;
        const res: AxiosResponse = await axios.get(`/horario/${props.scheduleId}`);
        const data = res.data.schedule;
        
        console.log('Datos completos recibidos del API:', data);

        schedule.value = {
            fechaFormatted: convertDateToInputFormat(data.fecha),
            fechaInicioFormatted: convertDateTimeToInputFormat(data.fechaInicio),
            fechaFinFormatted: convertDateTimeToInputFormat(data.fechaFin),
            idEspacio: data.idEspacio,
            idEmpleado: data.idEmpleado,
            state: data.state
        };
        
        console.log('Datos después de conversión:', schedule.value);
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el horario', life: 3000 });
        console.error('Error fetching schedule:', error);
    } finally {
        loading.value = false;
    }
};

// Fetch Espacios
const fetchEspacios = async () => {
    try {
        const res: AxiosResponse = await axios.get('/espacio', { params: { state: 1 } });
        espacios.value = res.data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
    }
};

// Fetch Empleados
const fetchEmpleados = async () => {
    try {
        const res: AxiosResponse = await axios.get('/empleado', { params: { state: 1 } });
        empleados.value = res.data.data.map((emp: any) => ({
            ...emp,
            nameFull: emp.name + ' ' + emp.apellido
        }));
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
    }
};

// Update Schedule
const updateSchedule = async () => {
    submitted.value = true;
    serverErrors.value = {};

    // Validación básica
    if (!schedule.value.fechaFormatted || !schedule.value.fechaInicioFormatted || 
        !schedule.value.fechaFinFormatted || !schedule.value.idEspacio || !schedule.value.idEmpleado) {
        return;
    }

    try {
        // Preparar datos para enviar al servidor
        const scheduleData = {
            fecha: schedule.value.fechaFormatted,
            fechaInicio: schedule.value.fechaInicioFormatted.replace('T',' ') + ':00',
            fechaFin: schedule.value.fechaFinFormatted.replace('T',' ') + ':00',
            idEspacio: schedule.value.idEspacio,
            idEmpleado: schedule.value.idEmpleado,
            state: schedule.value.state
        };

        await axios.put(`/horario/${props.scheduleId}`, scheduleData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Horario actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            serverErrors.value = error.response.data.errors;
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
                detail: 'No se pudo actualizar el horario',
                life: 3000
            });
        }
        console.error('Error updating schedule:', error);
    }
};
</script>
