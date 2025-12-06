<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import axios, { AxiosResponse } from 'axios';
import { debounce } from 'lodash';
import DeleteHorario from './DeleteHorario.vue';
import UpdateHorario from './UpdateHorario.vue';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Schedule {
    id: number;
    fecha: string;
    fechaInicio: string;
    fechaFin: string;
    espacio: string;
    empleado: string;
    state: boolean;
}

interface EstadoOption {
    name: string;
    value: string | number;
}

// Props
const props = defineProps<{
    refresh: number;
}>();

// Refs
const dt = ref<any>(null);
const schedules = ref<Schedule[]>([]);
const selectedSchedules = ref<Schedule[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteScheduleDialog = ref<boolean>(false);
const schedule = ref<Schedule | null>(null);
const selectedScheduleId = ref<number | null>(null);
const selectedEstadoSchedule = ref<EstadoOption | null>(null);
const updateScheduleDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

// Toast
const toast = useToast();

// Pagination & filters
const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref({
    state: null as null | number
});

const estadoScheduleOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

// Watchers
watch(() => props.refresh, () => {
    loadSchedules();
});

watch(() => selectedEstadoSchedule.value, () => {
    currentPage.value = 1;
    loadSchedules();
});

// Funciones
function editSchedule(scheduleItem: Schedule) {
    selectedScheduleId.value = scheduleItem.id;
    updateScheduleDialog.value = true;
}

function handleScheduleUpdated() {
    loadSchedules();
}

function confirmDeleteSchedule(selected: Schedule) {
    schedule.value = selected;
    deleteScheduleDialog.value = true;
}

function handleScheduleDeleted() {
    loadSchedules();
}

const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

const loadSchedules = async () => {
    loading.value = true;
    try {
        const params: any = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };
        if (selectedEstadoSchedule.value !== null && selectedEstadoSchedule.value.value !== '') {
            params.state = selectedEstadoSchedule.value.value;
        }
        const response: AxiosResponse = await axios.get('/horario', { params });

        schedules.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar horarios:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los horarios', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadSchedules();
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadSchedules();
}, 500);

// Mounted
onMounted(() => {
    loadSchedules();
});
</script>

<template>
<DataTable 
    ref="dt" 
    v-model:selection="selectedSchedules" 
    :value="schedules" 
    dataKey="id" 
    :paginator="true"
    :rows="pagination.perPage" 
    :totalRecords="pagination.total" 
    :loading="loading" 
    :lazy="true" 
    @page="onPage"
    :rowsPerPageOptions="[15, 20, 25]" 
    scrollable 
    scrollHeight="574px"
    responsive-layout="scroll"
    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} horarios"
>
    <!-- HEADER -->
    <template #header>
        <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between w-full">
            <h4 class="m-0 text-lg font-bold">HORARIOS</h4>

            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <!-- Buscador -->
                <IconField class="w-full sm:w-72 md:w-96">
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText 
                        v-model="globalFilterValue" 
                        @input="onGlobalSearch"
                        placeholder="Buscar id, espacio, empleado..."
                        class="w-full"
                    />
                </IconField>

                <!-- Select estado -->
                <Select 
                    v-model="selectedEstadoSchedule" 
                    :options="estadoScheduleOptions" 
                    optionLabel="name"
                    placeholder="Estado" 
                    class="w-full sm:w-auto"
                />

                <!-- Botón refrescar -->
                <Button 
                    icon="pi pi-refresh" 
                    outlined 
                    rounded 
                    aria-label="Refresh" 
                    @click="loadSchedules"
                    class="w-full sm:w-auto"
                />
            </div>
        </div>
    </template>

    <!-- Columnas -->
    <Column selectionMode="multiple" style="width: 2rem" :exportable="false" />
    <Column field="id" header="Código Horario" sortable style="min-width: 10rem" />
    <Column field="fecha" header="Fecha" sortable style="min-width: 8rem" />
    <Column field="fechaInicio" header="Inicio" sortable style="min-width: 8rem" />
    <Column field="fechaFin" header="Fin" sortable style="min-width: 8rem" />
    <Column field="espacio" header="Espacio" sortable style="min-width: 12rem" />
    <Column field="empleado" header="Empleado" sortable style="min-width: 12rem" />

    <!-- Estado -->
    <Column field="state" header="Estado" sortable style="min-width: 8rem">
        <template #body="{ data }">
            <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
        </template>
    </Column>

    <!-- Acciones -->
    <Column field="actions" header="Acciones" :exportable="false" style="min-width: 8rem">
        <template #body="slotProps">
            <div class="flex gap-2">
                <Button icon="pi pi-pencil" outlined rounded class="p-button-sm" @click="editSchedule(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" class="p-button-sm"
                    @click="confirmDeleteSchedule(slotProps.data)" />
            </div>
        </template>
    </Column>
</DataTable>

<!-- Modales -->
<DeleteHorario 
    v-model:visible="deleteScheduleDialog" 
    :schedule="schedule" 
    @deleted="handleScheduleDeleted" 
/>
<UpdateHorario 
    v-model:visible="updateScheduleDialog" 
    :scheduleId="selectedScheduleId"
    @updated="handleScheduleUpdated" 
/>
</template>
