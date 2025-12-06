<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import axios from 'axios';
import { debounce } from 'lodash';
import { useToast } from 'primevue/usetoast';
import DeleteConfigAlerta from './DeleteConfigAlerta.vue';
import UpdateConfigAlerta from './UpdateConfigAlerta.vue';

// Interfaces
interface ConfigAlerta {
  id: number;
  time: number;
  amount: number;
  creacion?: string;
  actualizacion?: string;
}

// Refs
const dt = ref<any>(null);
const configAlertas = ref<ConfigAlerta[]>([]);
const selectedConfigAlertas = ref<ConfigAlerta[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteConfigAlertaDialog = ref(false);
const updateConfigAlertaDialog = ref(false);
const configAlerta = ref<ConfigAlerta | null>(null);
const selectedConfigAlertaId = ref<number | null>(null);
//const currentPage = ref(1);

// Props
const props = defineProps<{ refresh: number }>();

// Toast
const toast = useToast();

// Watchers
watch(() => props.refresh, () => loadConfigAlerta());

// Funciones
function editConfigAlerta(e: ConfigAlerta) {
  selectedConfigAlertaId.value = e.id ?? null;
  updateConfigAlertaDialog.value = true;
}

function handleConfigAlertaUpdated() {
  loadConfigAlerta();
}

function confirmDeleteConfigAlerta(selected: ConfigAlerta) {
  if (selected.id === undefined) return;
  configAlerta.value = selected;
  deleteConfigAlertaDialog.value = true;
}

const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });

function handleConfigAlertaDeleted() {
  loadConfigAlerta();
}

const loadConfigAlerta = async () => {
  loading.value = true;
  try {
    const params: any = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
    };

    const response = await axios.get('/config_alerta', { params });

    configAlertas.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error('Error al cargar las configuraciones de alerta:', error);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las configuraciones de alerta', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadConfigAlerta();
};

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadConfigAlerta();
}, 500);

onMounted(() => {
  loadConfigAlerta();
});
</script>

<template>
<DataTable 
  ref="dt" 
  v-model:selection="selectedConfigAlertas" 
  :value="configAlertas" 
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
  currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} configuraciones"
>
  <template #header>
    <div class="flex flex-col md:flex-row gap-2 items-start md:items-center justify-between w-full">
      <h4 class="m-0 text-base sm:text-lg md:text-xl">CONFIGURACIÓN DE ALERTAS</h4>
      <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <IconField class="w-full sm:w-64">
          <InputIcon>
            <i class="pi pi-search" />
          </InputIcon>
          <InputText 
            v-model="globalFilterValue" 
            @input="onGlobalSearch" 
            placeholder="Buscar por cantidad..." 
            class="w-full" 
          />
        </IconField>
        <Button 
          title="Refrescar" 
          icon="pi pi-refresh" 
          outlined 
          rounded 
          aria-label="Refresh" 
          class="w-full sm:w-auto" 
          @click="loadConfigAlerta" 
        />
      </div>
    </div>
  </template>

  <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />
  <Column field="id" header="Código" sortable style="min-width: 6rem" />
  <Column field="amount" header="Cantidad" sortable style="min-width: 8rem" />
  <Column field="time" header="Tiempo" sortable style="min-width: 8rem" />
  <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
  <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
  <Column field="actions" header="Acciones" :exportable="false" style="min-width: 8rem">
    <template #body="slotProps">
      <Button title="Editar configuración" icon="pi pi-pencil" outlined rounded class="mr-2" @click="editConfigAlerta(slotProps.data)" />
      <Button title="Eliminar configuración" icon="pi pi-trash" outlined rounded severity="danger"
        @click="confirmDeleteConfigAlerta(slotProps.data)" 
      />
    </template>
  </Column>
</DataTable>

<DeleteConfigAlerta
  v-model:visible="deleteConfigAlertaDialog" 
  :configAlerta="configAlerta" 
  @deleted="handleConfigAlertaDeleted" 
/>
<UpdateConfigAlerta
  v-model:visible="updateConfigAlertaDialog" 
  :configAlertaId="selectedConfigAlertaId"
  @updated="handleConfigAlertaUpdated" 
/>
</template>
