<template>
  <DataTable
    ref="dt"
    v-model:selection="selectedEmpleados"
    :value="empleados"
    dataKey="id"
    :paginator="true"
    :rows="pagination.perPage"
    :totalRecords="pagination.total"
    :loading="loading"
    :lazy="true"
    @page="onPage"
    :rowsPerPageOptions="[15, 20, 25]"
    responsiveLayout="scroll"
    scrollHeight="auto"
    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} empleados"
  >
    <template #header>
      <div class="flex flex-wrap gap-2 items-center justify-between">
        <h4 class="m-0">EMPLEADOS</h4>
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
          <IconField class="flex-1 md:flex-none">
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText
              v-model="globalFilterValue"
              @input="onGlobalSearch"
              placeholder="Buscar..."
              class="w-full"
            />
          </IconField>
          <Select
            v-model="selectedEstadoEmpleado"
            :options="estadoEmpleadoOptions"
            optionLabel="name"
            placeholder="Estado"
            class="w-full md:w-auto"
          />
          <Button
            title="Refrescar"
            icon="pi pi-refresh"
            outlined
            rounded
            aria-label="Refresh"
            @click="loadEmpleado"
          />
        </div>
      </div>
    </template>

    <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />
    <Column field="name" header="Nombre" sortable style="min-width: 8rem" />
    <Column field="apellido" header="Apellido" sortable style="min-width: 8rem" />
    <Column field="codigo" header="DNI" sortable style="min-width: 6rem" />
    <Column field="empleadoType" header="Tipo de Empleado" sortable style="min-width: 10rem" />
    <Column field="idHuella" header="ID Huella" sortable style="min-width: 6rem" />
    <Column field="creacion" header="Creación" sortable style="min-width: 10rem" />
    <Column field="actualizacion" header="Actualización" sortable style="min-width: 10rem" />
    <Column field="state" header="Estado" sortable style="min-width: 6rem">
      <template #body="{ data }">
        <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
      </template>
    </Column>
    <Column field="foto" header="Foto" style="min-width: 6rem">
      <template #body="{ data }">
        <img
          v-if="data.foto"
          :src="`/uploads/fotos/empleados/${data.foto}`"
          alt="Foto"
          title="Ver foto"
          class="w-12 h-12 rounded-full object-cover cursor-pointer"
          @click="verFoto(data.foto)"
        />
      </template>
    </Column>
    <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
      <template #body="slotProps">
        <div class="flex flex-wrap gap-2">
          <Button
            title="Editar empleado"
            icon="pi pi-pencil"
            outlined
            rounded
            @click="editEmpleado(slotProps.data)"
          />
          <Button
            title="Eliminar empleado"
            icon="pi pi-trash"
            outlined
            rounded
            severity="danger"
            @click="confirmDeleteEmpleado(slotProps.data)"
          />
        </div>
      </template>
    </Column>
  </DataTable>

  <DeleteEmpleado
    v-model:visible="deleteEmpleadoDialog"
    :empleado="empleado"
    @deleted="handleEmpleadoDeleted"
  />
  <UpdateEmpleado
    v-model:visible="updateEmpleadoDialog"
    :empleadoId="selectedEmpleadoId ?? 0"
    @updated="handleEmpleadoUpdated"
  />
  <Dialog v-model:visible="fotoDialog" modal :closable="true" :style="{ width: '50vw' }">
    <img v-if="fotoPreview" :src="fotoPreview" alt="Foto Empleado" class="w-full h-auto object-contain" />
  </Dialog>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteEmpleado from './DeleteEmpleado.vue';
import UpdateEmpleado from './UpdateEmpleado.vue';
import { useToast } from 'primevue/usetoast';

interface Empleado {
  id: number;
  name: string;
  apellido: string;
  codigo: string;
  empleadoType?: string;
  idHuella?: string | number;
  creacion?: string;
  actualizacion?: string;
  state: boolean;
  foto?: string | null;
  [key: string]: any;
}

interface EstadoOption {
  name: string;
  value: any;
}

const toast = useToast();

// refs
const dt = ref<any>(null);
const empleados = ref<Empleado[]>([]);
const selectedEmpleados = ref<Empleado[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteEmpleadoDialog = ref(false);
const empleado = ref<Empleado | null>(null);
const selectedEmpleadoId = ref<number | null>(null);
const selectedEstadoEmpleado = ref<EstadoOption | null>(null);
const updateEmpleadoDialog = ref(false);
const fotoDialog = ref(false);
const fotoPreview = ref('');

// props
const props = defineProps<{ refresh: number }>();

// estado options
const estadoEmpleadoOptions = ref<EstadoOption[]>([
  { name: 'TODOS', value: '' },
  { name: 'ACTIVOS', value: 1 },
  { name: 'INACTIVOS', value: 0 },
]);

// paginación
const pagination = ref({ currentPage: 1, perPage: 15, total: 0 });

// funciones
function verFoto(foto: string | undefined) {
  if (!foto) return;
  fotoPreview.value = `/uploads/fotos/empleados/${foto}`;
  fotoDialog.value = true;
}

function editEmpleado(emp: Empleado) {
  selectedEmpleadoId.value = emp.id;
  updateEmpleadoDialog.value = true;
}

function confirmDeleteEmpleado(selected: Empleado) {
  empleado.value = selected;
  deleteEmpleadoDialog.value = true;
}

function handleEmpleadoUpdated() { loadEmpleado(); }
function handleEmpleadoDeleted() { loadEmpleado(); }

const loadEmpleado = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
      state: selectedEstadoEmpleado.value?.value ?? null
    };
    const response = await axios.get('/empleado', { params });
    empleados.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error(error);
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
  } finally {
    loading.value = false;
  }
};

const onPage = (event: any) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadEmpleado();
};

const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadEmpleado();
}, 500);

// watchers
watch(() => props.refresh, loadEmpleado);
watch(selectedEstadoEmpleado, loadEmpleado);

onMounted(() => { loadEmpleado(); });
</script>
