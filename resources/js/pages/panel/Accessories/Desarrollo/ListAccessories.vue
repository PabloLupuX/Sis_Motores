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
import { useToast } from 'primevue/usetoast';

// IMPORTAR LOS DIALOGS
import DeleteAccessory from './DeleteAccessories.vue';
import UpdateAccessory from './UpdateAccessories.vue';

// --- INTERFACE ---
interface Accessory {
  id: number;
  name: string;
  state: boolean;
  created_at?: string;
  updated_at?: string;
}

const dt = ref<any>(null);
const accessories = ref<Accessory[]>([]);
const selectedAccessories = ref<Accessory[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');

// NEW: filtro por estado
const stateFilter = ref<string | boolean | null>('');
const stateOptions = [
  { label: 'TODOS', value: '' },
  { label: 'ACTIVOS', value: true },
  { label: 'INACTIVOS', value: false },
];

// dialogs
const deleteAccessoryDialog = ref(false);
const updateAccessoryDialog = ref(false);

const accessory = ref<Accessory | null>(null);
const selectedAccessoryId = ref<number | null>(null);

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });

// props
const props = defineProps<{ refresh?: number }>();
const toast = useToast();

// refrescar desde padre
watch(() => props.refresh, () => loadAccessories());

// refrescar por estado
watch(stateFilter, () => {
  pagination.value.currentPage = 1;
  loadAccessories();
});

// formato de fecha
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

// CRUD ----------------------------------------
function editAccessory(a: Accessory) {
  selectedAccessoryId.value = a.id;
  updateAccessoryDialog.value = true;
}

function confirmDeleteAccessory(a: Accessory) {
  accessory.value = a;
  deleteAccessoryDialog.value = true;
}

function handleAccessoryUpdated() {
  loadAccessories();
}

function handleAccessoryDeleted() {
  loadAccessories();
}

// CARGAR --------------------------------------
const loadAccessories = async () => {
  loading.value = true;

  try {
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
      state: stateFilter.value,
    };

    const response = await axios.get('/accesorio', { params });

    accessories.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;

  } catch (error) {
    console.error('Error al cargar accesorios:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron cargar los accesorios',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadAccessories();
};

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadAccessories();
}, 500);

onMounted(() => {
  loadAccessories();
});
</script>

<template>
  <DataTable 
    ref="dt"
    v-model:selection="selectedAccessories"
    :value="accessories"
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
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} accesorios"
  >

    <!-- HEADER -->
    <template #header>
      <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
        <h4 class="m-0 text-base sm:text-lg md:text-xl">
          LISTA DE ACCESORIOS
        </h4>

        <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">

          <!-- BUSCADOR -->
          <IconField class="w-full sm:w-64">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText
              v-model="globalFilterValue"
              @input="onGlobalSearch"
              placeholder="Buscar accesorio..."
              class="w-full"
            />
          </IconField>

          <!-- ESTADO -->
          <Select
            v-model="stateFilter"
            :options="stateOptions"
            optionLabel="label"
            placeholder="Estado"
            optionValue="value"
            class="w-full sm:w-48"
          />

          <!-- REFRESCAR -->
          <Button 
            title="Refrescar" 
            icon="pi pi-refresh"
            outlined
            rounded
            class="w-full sm:w-auto"
            @click="loadAccessories"
          />
        </div>
      </div>
    </template>

    <!-- COLUMNAS -->
    <Column selectionMode="multiple" style="width: 1rem" />

    <Column field="name" header="Nombre" sortable style="min-width: 16rem" />

    <Column field="state" header="Estado" sortable style="min-width: 10rem">
      <template #body="{ data }">
        <Tag 
          :value="data.state ? 'Activo' : 'Inactivo'" 
          :severity="data.state ? 'success' : 'danger'"
        />
      </template>
    </Column>

    <Column header="Creación" sortable style="min-width: 13rem">
      <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
    </Column>

    <Column header="Actualización" sortable style="min-width: 13rem">
      <template #body="{ data }">{{ formatDate(data.updated_at) }}</template>
    </Column>

    <!-- ACCIONES -->
    <Column header="Acciones" :exportable="false" style="min-width: 9rem">
      <template #body="slotProps">
        <Button 
          icon="pi pi-pencil" 
          outlined 
          rounded 
          class="mr-2"
          @click="editAccessory(slotProps.data)"
        />
        <Button 
          icon="pi pi-trash" 
          outlined 
          rounded 
          severity="danger"
          @click="confirmDeleteAccessory(slotProps.data)"
        />
      </template>
    </Column>

  </DataTable>

  <!-- DIALOGS -->
  <DeleteAccessory
    v-model:visible="deleteAccessoryDialog"
    :accessory="accessory"
    @deleted="handleAccessoryDeleted"
  />

  <UpdateAccessory
    v-model:visible="updateAccessoryDialog"
    :accessoryId="selectedAccessoryId"
    @updated="handleAccessoryUpdated"
  />
</template>
