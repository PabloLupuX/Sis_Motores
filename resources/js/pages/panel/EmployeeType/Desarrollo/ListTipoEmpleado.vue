<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedTiposEmpleados"
        :value="tiposEmpleados"
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
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} tipos de empleados"
        class="w-full"
    >
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Tipos Empleados</h4>
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    <IconField class="flex-1 min-w-[150px]">
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar tipo empleado..." class="w-full"/>
                    </IconField>

                    <Select
                        v-model="selectedEstadoTipoEmpleado"
                        :options="estadoTipoEmpleadoOptions"
                        optionLabel="name"
                        placeholder="Estado del tipo empleado"
                        class="w-full md:w-auto"
                    />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadTipoEmpleado" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 13rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="state" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <div class="flex flex-wrap gap-1">
                    <Button icon="pi pi-pencil" outlined rounded class="mr-1" @click="editTipoEmpleado(slotProps.data)" />
                    <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteTipoEmpleado(slotProps.data)" />
                </div>
            </template>
        </Column>
    </DataTable>

    <DeleteTipoEmpleado
        v-model:visible="deleteTipoEmpleadoDialog"
        :tipoEmpleado="tipoEmpleado"
        @deleted="handleTipoEmpleadoDeleted"
    />

    <UpdateTipoEmpleado
        v-model:visible="updateTipoEmpleadoDialog"
        :tipoEmpleadoId="selectedTipoEmpleadoId"
        @updated="handleTipoEmpleadoUpdated"
    />
</template>

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
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteTipoEmpleado from './DeleteTipoEmpleado.vue';
import UpdateTipoEmpleado from './UpdateTipoEmpleado.vue';

// Interfaces
interface TipoEmpleado {
    id: number;
    name: string;
    state: boolean | number;
    creacion?: string;
    actualizacion?: string;
    [key: string]: any;
}

// Props tipadas
const props = defineProps<{
    refresh: number;
}>();

// Refs tipadas
const dt = ref<any>(null);
const tiposEmpleados = ref<TipoEmpleado[]>([]);
const selectedTiposEmpleados = ref<TipoEmpleado[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteTipoEmpleadoDialog = ref<boolean>(false);
const tipoEmpleado = ref<TipoEmpleado | null>(null);
const selectedTipoEmpleadoId = ref<number | null>(null);
const selectedEstadoTipoEmpleado = ref<{ name: string; value: any } | null>(null);
const updateTipoEmpleadoDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

// Watchers
watch(() => props.refresh, () => loadTipoEmpleado());
watch(() => selectedEstadoTipoEmpleado.value, () => {
    currentPage.value = 1;
    loadTipoEmpleado();
});

// Opciones de estado
const estadoTipoEmpleadoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

// Paginación
const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

// Filtros
const filters = ref<{ state: any; online?: any }>({ state: null });

// Funciones
const editTipoEmpleado = (te: TipoEmpleado) => {
    selectedTipoEmpleadoId.value = te.id;
    updateTipoEmpleadoDialog.value = true;
};

const confirmDeleteTipoEmpleado = (selected: TipoEmpleado) => {
    tipoEmpleado.value = selected;
    deleteTipoEmpleadoDialog.value = true;
};

const handleTipoEmpleadoUpdated = () => loadTipoEmpleado();
const handleTipoEmpleadoDeleted = () => loadTipoEmpleado();

const loadTipoEmpleado = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };

        if (selectedEstadoTipoEmpleado.value !== null && selectedEstadoTipoEmpleado.value.value !== '') {
            params.state = selectedEstadoTipoEmpleado.value.value;
        }

        const response = await axios.get('/tipo_empleado', { params });
        tiposEmpleados.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar tipos empleados:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadTipoEmpleado();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadTipoEmpleado();
}, 500);

onMounted(() => loadTipoEmpleado());
</script>
