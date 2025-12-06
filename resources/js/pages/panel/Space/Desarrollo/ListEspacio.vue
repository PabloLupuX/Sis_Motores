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
import DeleteEspacio from './DeleteEspacio.vue';
import UpdateEspacio from './UpdateEspacio.vue';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Espacio {
  id: number;
  name: string;
  description: string;
  state: boolean;
  creacion?: string;
  actualizacion?: string;
}

interface EstadoOption {
  name: string;
  value: string | number;
}

// Refs
const dt = ref<any>(null);
const espacios = ref<Espacio[]>([]);
const selectedEspacios = ref<Espacio[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteEspacioDialog = ref(false);
const updateEspacioDialog = ref(false);
const espacio = ref<Espacio | null>(null);
const selectedEspacioId = ref<number | null>(null);
const selectedEstadoEspacio = ref<EstadoOption | null>(null);
const currentPage = ref(1);

// Props
const props = defineProps<{ refresh: number }>();

// Toast
const toast = useToast();

// Watchers
watch(() => props.refresh, () => loadEspacio());

watch(() => selectedEstadoEspacio.value, () => {
  currentPage.value = 1;
  loadEspacio();
});

// Funciones
function editEspacio(e: Espacio) {
  selectedEspacioId.value = e.id ?? null;
  updateEspacioDialog.value = true;
}

const estadoEspacioOptions = ref<EstadoOption[]>([
  { name: 'TODOS', value: '' },
  { name: 'ACTIVOS', value: 1 },
  { name: 'INACTIVOS', value: 0 },
]);

function handleEspacioUpdated() {
  loadEspacio();
}

function confirmDeleteEspacio(selected: Espacio) {
  if (selected.id === undefined) return; // evita pasar objeto incompleto
  espacio.value = selected;
  deleteEspacioDialog.value = true;
}

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });
const filters = ref({ state: null as string | number | null, online: null });

function handleEspacioDeleted() {
  loadEspacio();
}

const loadEspacio = async () => {
  loading.value = true;
  try {
    const params: any = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
      state: filters.value.state,
    };

    if (selectedEstadoEspacio.value !== null && selectedEstadoEspacio.value.value !== '') {
      params.state = selectedEstadoEspacio.value.value;
    }

    const response = await axios.get('/espacio', { params });

    espacios.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error('Error al cargar los espacios:', error);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los espacios', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadEspacio();
};

const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadEspacio();
}, 500);

onMounted(() => {
  loadEspacio();
});
</script>

<template>
<DataTable 
  ref="dt" 
  v-model:selection="selectedEspacios" 
  :value="espacios" 
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
  currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} espacios"
>
  <template #header>
    <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
      <h4 class="m-0 text-base sm:text-lg md:text-xl">ESPACIOS DE TRABAJO</h4>
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <IconField class="w-full sm:w-64">
          <InputIcon>
            <i class="pi pi-search" />
          </InputIcon>
          <InputText 
            v-model="globalFilterValue" 
            @input="onGlobalSearch" 
            placeholder="Buscar espacio..." 
            class="w-full" 
          />
        </IconField>
        <Select 
          v-model="selectedEstadoEspacio" 
          :options="estadoEspacioOptions" 
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
          @click="loadEspacio" 
        />
      </div>
    </div>
  </template>

  <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />
  <Column field="name" header="Nombre" sortable style="min-width: 12rem" />
  <Column field="description" header="Descripción" sortable style="min-width: 15rem" />
  <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
  <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
  <Column field="state" header="Estado" sortable style="min-width: 6rem">
    <template #body="{ data }">
      <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
    </template>
  </Column>
  <Column field="actions" header="Acciones" :exportable="false" style="min-width: 8rem">
    <template #body="slotProps">
      <Button title="Editar espacio" icon="pi pi-pencil" outlined rounded class="mr-2" @click="editEspacio(slotProps.data)" />
      <Button title="Eliminar espacio" icon="pi pi-trash" outlined rounded severity="danger"
        @click="confirmDeleteEspacio(slotProps.data)" 
      />
    </template>
  </Column>
</DataTable>

<DeleteEspacio 
  v-model:visible="deleteEspacioDialog" 
  :espacio="espacio" 
  @deleted="handleEspacioDeleted" 
/>
<UpdateEspacio 
  v-model:visible="updateEspacioDialog" 
  :espacioId="selectedEspacioId"
  @updated="handleEspacioUpdated" 
/>
</template>
