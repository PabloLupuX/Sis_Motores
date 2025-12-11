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
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteCustomer from './DeleteCustomer.vue';
import UpdateCustomer from './UpdateCustomer.vue';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Customer {
  id: number;
  codigo: string;
  nombres: string;
  alias?: string;
  telefono?: string;
  state: boolean;
  created_at?: string;
  updated_at?: string;
}

interface EstadoOption {
  name: string;
  value: string | number;
}

const dt = ref<any>(null);
const customers = ref<Customer[]>([]);
const selectedCustomers = ref<Customer[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteCustomerDialog = ref(false);
const updateCustomerDialog = ref(false);
const customer = ref<Customer | null>(null);
const selectedCustomerId = ref<number | null>(null);

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });

const selectedEstadoCliente = ref<EstadoOption | null>(null);

const estadoClienteOptions = ref<EstadoOption[]>([
  { name: 'TODOS', value: '' },
  { name: 'ACTIVOS', value: 1 },
  { name: 'INACTIVOS', value: 0 },
]);

// Props
const props = defineProps<{ refresh: number }>();
const toast = useToast();

// Watch para refrescar
watch(() => props.refresh, () => loadCustomers());
watch(() => selectedEstadoCliente.value, () => {
  pagination.value.currentPage = 1;
  loadCustomers();
});

// ------------------------------
// FORMATEADOR DE FECHAS
// ------------------------------
function formatDate(dateString: string | undefined) {
  if (!dateString) return '-';

  const date = new Date(dateString);
  return date.toLocaleString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function getSeverity(state: boolean) {
  return state ? 'success' : 'danger';
}

function getStateLabel(state: boolean) {
  return state ? 'Activo' : 'Inactivo';
}

// ------------------------------
// FUNCIONES
// ------------------------------
function editCustomer(c: Customer) {
  selectedCustomerId.value = c.id ?? null;
  updateCustomerDialog.value = true;
}

function confirmDeleteCustomer(selected: Customer) {
  if (!selected.id) return;
  customer.value = selected;
  deleteCustomerDialog.value = true;
}

function handleCustomerUpdated() {
  loadCustomers();
}

function handleCustomerDeleted() {
  loadCustomers();
}

const loadCustomers = async () => {
  loading.value = true;

  try {
    const params: any = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
    };

    if (selectedEstadoCliente.value !== null && selectedEstadoCliente.value.value !== '') {
      params.state = selectedEstadoCliente.value.value;
    }

    const response = await axios.get('/cliente', { params });

    customers.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error('Error al cargar los clientes:', error);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los clientes', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadCustomers();
};

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadCustomers();
}, 500);

onMounted(() => {
  loadCustomers();
});
</script>

<template>
  <DataTable 
    ref="dt" 
    v-model:selection="selectedCustomers" 
    :value="customers" 
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
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} clientes"
  >

    <!-- HEADER -->
    <template #header>
      <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
        <h4 class="m-0 text-base sm:text-lg md:text-xl">LISTA DE CLIENTES</h4>

        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">

          <!-- BUSCADOR -->
          <IconField class="w-full sm:w-64">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText
              v-model="globalFilterValue"
              @input="onGlobalSearch"
              placeholder="Buscar cliente..."
              class="w-full"
            />
          </IconField>

          <!-- FILTRO POR ESTADO -->
          <Select 
            v-model="selectedEstadoCliente"
            :options="estadoClienteOptions"
            optionLabel="name"
            placeholder="Estado"
            class="w-full sm:w-40"
          />

          <Button 
            title="Refrescar" 
            icon="pi pi-refresh"
            outlined
            rounded
            aria-label="Refresh"
            class="w-full sm:w-auto"
            @click="loadCustomers"
          />
        </div>
      </div>
    </template>

    <!-- COLUMNS -->
    <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />

    <Column field="codigo" header="DNI - RUC" sortable style="min-width: 10rem" />
    <Column field="nombres" header="Nombres" sortable style="min-width: 12rem" />
    <Column field="alias" header="Alias" sortable style="min-width: 10rem" />
    <Column field="telefono" header="Teléfono" sortable style="min-width: 10rem" />

    <!-- ESTADO -->
    <Column header="Estado" sortable style="min-width: 10rem">
      <template #body="{ data }">
        <Tag 
          :value="getStateLabel(data.state)" 
          :severity="getSeverity(data.state)" 
        />
      </template>
    </Column>

    <!-- CREACIÓN -->
    <Column header="Creación" sortable style="min-width: 12rem">
      <template #body="{ data }">
        {{ formatDate(data.created_at) }}
      </template>
    </Column>

    <!-- ACTUALIZACIÓN -->
    <Column header="Actualización" sortable style="min-width: 12rem">
      <template #body="{ data }">
        {{ formatDate(data.updated_at) }}
      </template>
    </Column>

    <!-- ACCIONES -->
    <Column field="actions" header="Acciones" :exportable="false" style="min-width: 8rem">
      <template #body="slotProps">
        <Button 
          title="Editar cliente" 
          icon="pi pi-pencil" 
          outlined 
          rounded 
          class="mr-2"
          @click="editCustomer(slotProps.data)" 
        />
        <Button 
          title="Eliminar cliente" 
          icon="pi pi-trash" 
          outlined 
          rounded 
          severity="danger"
          @click="confirmDeleteCustomer(slotProps.data)" 
        />
      </template>
    </Column>

  </DataTable>

  <!-- DIALOGS -->
  <DeleteCustomer
    v-model:visible="deleteCustomerDialog"
    :customer="customer"
    @deleted="handleCustomerDeleted"
  />

  <UpdateCustomer
    v-model:visible="updateCustomerDialog"
    :customerId="selectedCustomerId"
    @updated="handleCustomerUpdated"
  />
</template>
