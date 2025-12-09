<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Calendar from "primevue/calendar";
import axios from 'axios';
import { debounce } from 'lodash';
import { useToast } from 'primevue/usetoast';
import DeleteReception from './DeleteReceptions.vue';
import UpdateReception from './UpdateReceptions.vue';
import ViewReception from "./ViewReception.vue";

// --- INTERFACE ---
interface Reception {
  id: number;
  engine_id: number;
  customer_owner_id: number;
  customer_contact_id: number;

  engine?: any;
  owner?: any;
  contact?: any;

  fecha_ingreso: string | null;
  fecha_resuelto: string | null;
  fecha_entrega: string | null;

  state: boolean;
  created_at?: string;
  updated_at?: string;
}

const dt = ref<any>(null);
const receptions = ref<Reception[]>([]);
const selectedReceptions = ref<Reception[] | null>(null);
const loading = ref(false);

// filtros
const globalFilterValue = ref('');
const stateFilter = ref<string | boolean | null>('');
const dateRange = ref<[Date | null, Date | null] | null>(null);

// opciones
const stateOptions = [
  { label: 'TODOS', value: '' },
  { label: 'ABIERTOS', value: true },
  { label: 'CERRADOS', value: false },
];

const deleteReceptionDialog = ref(false);
const updateReceptionDialog = ref(false);
const viewReceptionDialog = ref(false);

const selectedReceptionId = ref<number | null>(null);
const reception = ref<Reception | null>(null);

const toast = useToast();

const pagination = ref({
  currentPage: 1,
  perPage: 15,
  total: 0,
});

// formato fecha
function formatDate(dateString: string | undefined | null) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleString('es-PE');
}

// CARGAR RECEPCIONES
const loadReceptions = async () => {
  loading.value = true;

  try {
    let fechaInicio = null;
    let fechaFin = null;

    if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
      fechaInicio = dateRange.value[0].toISOString().slice(0, 10);
      fechaFin = dateRange.value[1].toISOString().slice(0, 10);
    }

    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
      state: stateFilter.value,
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
    };

    const response = await axios.get('/recepcion', { params });

    receptions.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;

  } catch {

    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron cargar las recepciones',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
};

// eventos
function confirmDeleteReception(a: Reception) {
  reception.value = a;
  deleteReceptionDialog.value = true;
}

function handleReceptionDeleted() {
  deleteReceptionDialog.value = false;
  loadReceptions();
}

function editReception(a: Reception) {
  selectedReceptionId.value = a.id;
  updateReceptionDialog.value = true;
}

function viewReception(a: Reception) {
  selectedReceptionId.value = a.id;
  viewReceptionDialog.value = true;
}

// paginación
const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadReceptions();
};

// búsqueda
const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadReceptions();
}, 500);

// watchers
watch(stateFilter, () => {
  pagination.value.currentPage = 1;
  loadReceptions();
});

watch(dateRange, () => {
  pagination.value.currentPage = 1;
  loadReceptions();
});
const props = defineProps({
    refresh: {
        type: Number,
        required: false,
        default: 0,
    },
});

watch(() => props.refresh, () => {
    loadReceptions();
});

onMounted(() => loadReceptions());
</script>

<template>
  <DataTable 
    ref="dt"
    v-model:selection="selectedReceptions"
    :value="receptions"
    dataKey="id"
    :paginator="true"
    :rows="pagination.perPage"
    :totalRecords="pagination.total"
    :loading="loading"
    :lazy="true"
    @page="onPage"
    :rowsPerPageOptions="[15, 20, 25]"
    scrollable
    scrollDirection="both"
    responsiveLayout="scroll"
    class="w-full text-sm sm:text-base"
    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} recepciones"
  >

    <!-- HEADER -->
    <template #header>
      <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
        <h4 class="m-0 text-lg md:text-xl font-bold">LISTA DE RECEPCIONES</h4>

        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">

          <!-- BUSCADOR -->
          <IconField class="w-full sm:w-64">
            <InputIcon><i class="pi pi-search" /></InputIcon>
            <InputText
              v-model="globalFilterValue"
              @input="onGlobalSearch"
              placeholder="Buscar..."
              class="w-full"
            />
          </IconField>
 <!-- FECHA RANGO -->
          <Calendar
            v-model="dateRange"
            placeholder="Rango de fechas"
            selectionMode="range"
            dateFormat="yy-mm-dd"
            class="w-full sm:w-52"
            :manualInput="false"
          />

          <!-- ESTADO -->
          <Select
            v-model="stateFilter"
            :options="stateOptions"
            optionLabel="label"
            optionValue="value"
            placeholder="Estado"
            class="w-full sm:w-40"
          />

         
          <Button 
            title="Refrescar" 
            icon="pi pi-refresh"
            outlined
            rounded
            class="w-full sm:w-auto"
            @click="loadReceptions"
          />
        </div>
      </div>
    </template>

    <!-- COLUMNAS -->
    <Column selectionMode="multiple" style="width: 1rem" />

    <Column header="Motor" style="min-width: 12rem">
      <template #body="{ data }">
        {{ data.engine?.marca }} {{ data.engine?.modelo }}
      </template>
    </Column>

    <Column header="Dueño" style="min-width: 12rem">
      <template #body="{ data }">
        {{ data.owner?.nombres ?? '-' }}
      </template>
    </Column>

    <Column header="Referente" style="min-width: 12rem">
      <template #body="{ data }">
        {{ data.contact?.nombres ?? '-' }}
      </template>
    </Column>

    <Column header="Problema" style="min-width: 14rem">
      <template #body="{ data }">
        {{ data.problema }}
      </template>
    </Column>

    <Column header="Ingreso" style="min-width: 12rem">
      <template #body="{ data }">{{ formatDate(data.fecha_ingreso) }}</template>
    </Column>

    <Column header="Resuelto" style="min-width: 12rem">
      <template #body="{ data }">{{ formatDate(data.fecha_resuelto) }}</template>
    </Column>

    <Column header="Entrega" style="min-width: 12rem">
      <template #body="{ data }">{{ formatDate(data.fecha_entrega) }}</template>
    </Column>

    <Column header="Estado">
      <template #body="{ data }">
<Tag 
    :value="data.state ? 'Abierto' : 'Cerrado'"
    :severity="data.state ? 'success' : 'danger'"
/>
      </template>
    </Column>

    <Column header="Acciones" style="min-width: 14rem">
      <template #body="{ data }">
<Button 
    v-if="data.state"
    icon="pi pi-pencil" 
    outlined 
    rounded 
    class="mr-2" 
    @click="editReception(data)" 
/>
        <Button icon="pi pi-trash" outlined rounded severity="danger" class="mr-2" @click="confirmDeleteReception(data)" />
        <Button icon="pi pi-eye" outlined rounded severity="info" @click="viewReception(data)" />
      </template>
    </Column>
  </DataTable>

  <!-- Modales -->
  <UpdateReception
    v-model:visible="updateReceptionDialog"
    :receptionId="selectedReceptionId"
    @updated="loadReceptions"
  />

  <DeleteReception
    v-model:visible="deleteReceptionDialog"
    :reception="reception"
    @deleted="handleReceptionDeleted"
  />

  <ViewReception
      v-model:visible="viewReceptionDialog"
      :receptionId="selectedReceptionId"
  />
</template>
