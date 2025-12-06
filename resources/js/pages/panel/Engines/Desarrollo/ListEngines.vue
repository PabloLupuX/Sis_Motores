<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteEngine from './DeleteEngine.vue';
import UpdateEngine from './UpdateEngine.vue';
import { useToast } from 'primevue/usetoast';
import Select from 'primevue/select';

interface Engine {
  id: number;
  hp: string;
  tipo: string;
  marca: string;
  modelo: string;
  year: number;
   state: boolean; 
  created_at?: string;
  updated_at?: string;
}
const stateFilter = ref<string | boolean | null>('');
const stateOptions = [
  { label: 'TODOS', value: '' },
  { label: 'ACTIVOS', value: true },
  { label: 'INACTIVOS', value: false },
];

const dt = ref<any>(null);
const engines = ref<Engine[]>([]);
const selectedEngines = ref<Engine[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');

const deleteEngineDialog = ref(false);
const updateEngineDialog = ref(false);

const engine = ref<Engine | null>(null);
const selectedEngineId = ref<number | null>(null);

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });

// Props
const props = defineProps<{ refresh: number }>();
const toast = useToast();

// Watch para refrescar desde padre
watch(() => props.refresh, () => loadEngines());

// --------------
// FORMATEAR FECHAS
// --------------
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

// --------------
// FUNCIONES CRUD
// --------------

function editEngine(e: Engine) {
  selectedEngineId.value = e.id;
  updateEngineDialog.value = true;
}

function confirmDeleteEngine(e: Engine) {
  if (!e.id) return;
  engine.value = e;
  deleteEngineDialog.value = true;
}

function handleEngineUpdated() {
  loadEngines();
}

function handleEngineDeleted() {
  loadEngines();
}

// -------------------------
// CARGAR MOTORES
// -------------------------
const loadEngines = async () => {
  loading.value = true;

  try {
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
       state: stateFilter.value,
    };

    const response = await axios.get('/motor', { params });

    engines.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;

  } catch (error) {
    console.error('Error al cargar motores:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudieron cargar los motores',
      life: 3000
    });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadEngines();
};

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadEngines();
}, 500);

onMounted(() => {
  loadEngines();
});

watch(stateFilter, () => {
  pagination.value.currentPage = 1;
  loadEngines();
});

</script>

<template>
  <DataTable 
    ref="dt"
    v-model:selection="selectedEngines"
    :value="engines"
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
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} motores"
  >

    <!-- HEADER -->
    <template #header>
      <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
        <h4 class="m-0 text-base sm:text-lg md:text-xl">
          LISTA DE MOTORES
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
              placeholder="Buscar motor..."
              class="w-full"
            />
          </IconField>
<Select
  v-model="stateFilter"
  :options="stateOptions"
  optionLabel="label"
  optionValue="value"
  placeholder="Estado"
  class="w-full sm:w-48"
/>


          <!-- REFRESCAR -->
          <Button 
            title="Refrescar" 
            icon="pi pi-refresh"
            outlined
            rounded
            class="w-full sm:w-auto"
            @click="loadEngines"
          />
        </div>
      </div>
    </template>

    <!-- COLUMNAS -->
    <Column selectionMode="multiple" style="width: 1rem" />

    <Column field="hp" header="HP" sortable style="min-width: 8rem" />
    <Column field="tipo" header="Tipo" sortable style="min-width: 10rem" />
    <Column field="marca" header="Marca" sortable style="min-width: 12rem" />
    <Column field="modelo" header="Modelo" sortable style="min-width: 12rem" />
    <Column field="year" header="Año" sortable style="min-width: 10rem" />
<Column field="state" header="Estado" sortable style="min-width: 10rem">
  <template #body="{ data }">
    <Tag 
      :value="data.state ? 'Activo' : 'Inactivo'"
      :severity="data.state ? 'success' : 'danger'"
    />
  </template>
</Column>

    <!-- FECHAS -->
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
          @click="editEngine(slotProps.data)"
        />
        <Button 
          icon="pi pi-trash" 
          outlined 
          rounded 
          severity="danger"
          @click="confirmDeleteEngine(slotProps.data)"
        />
      </template>
    </Column>

  </DataTable>

  <!-- DIALOGS -->
  <DeleteEngine
    v-model:visible="deleteEngineDialog"
    :engine="engine"
    @deleted="handleEngineDeleted"
  />

  <UpdateEngine
    v-model:visible="updateEngineDialog"
    :engineId="selectedEngineId"
    @updated="handleEngineUpdated"
  />
</template>
