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
import DeleteUsuario from './DeleteUsuario.vue';
import UpdateUsuario from './updateUsuario.vue';

interface User {
  id: number;
  role: string;
  username: string;
  dni: string;
  name1: string;
  email: string;
  creacion: string;
  online: boolean;
  status: boolean;
  [key: string]: any;
}

// Refs
const dt = ref<any>(null);
const users = ref<User[]>([]);
const selectedUsers = ref<User[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteProductDialog = ref(false);
const product = ref<User | null>(null);
const selectedUsuarioId = ref<number | null>(null);
const updateUsuarioDialog = ref(false);

// Props
const props = defineProps<{
  refresh: number;
}>();

// Watch refresh prop
watch(() => props.refresh, () => {
  loadUsers();
});

// Funciones
function editUsuario(usuario: User) {
  selectedUsuarioId.value = usuario.id;
  updateUsuarioDialog.value = true;
}

function handleUsuarioUpdated() {
  loadUsers();
}

function confirmDeleteProduct(usuario: User) {
  product.value = usuario;
  deleteProductDialog.value = true;
}

function handleUserDeleted() {
  loadUsers();
}

// Paginación y filtros
const pagination = ref({
  currentPage: 1,
  perPage: 15,
  total: 0
});

const filters = ref({
  status: null as string | null,
  online: null as string | null
});

// Load users
const loadUsers = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/usuarios', {
      params: {
        page: pagination.value.currentPage,
        per_page: pagination.value.perPage,
        search: globalFilterValue.value,
        status: filters.value.status,
        online: filters.value.online
      }
    });

    users.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error('Error cargando usuarios:', error);
  } finally {
    loading.value = false;
  }
};

// Paginación de DataTable
const onPage = (event: { page: number; rows: number }) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadUsers();
};

// Severidad para tags
const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

// Búsqueda global con debounce
const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadUsers();
}, 500);

// Mounted
onMounted(() => {
  loadUsers();
});
</script>

<template>
<DataTable 
    ref="dt" 
    v-model:selection="selectedUsers" 
    :value="users" 
    dataKey="id" 
    :paginator="true"
    :rows="pagination.perPage" 
    :totalRecords="pagination.total" 
    :loading="loading" 
    :lazy="true" 
    @page="onPage"
    :rowsPerPageOptions="[15, 20, 25]"
    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
    currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} usuarios"
>
    <template #header>
        <div class="flex flex-wrap gap-2 items-center justify-between">
            <h4 class="m-0">Usuarios</h4>
            <div class="flex flex-wrap gap-2">
                <IconField>
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar usuario..." />
                </IconField>
                <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadUsers" />
            </div>
        </div>
    </template>

    <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
    <Column field="role" header="Rol" sortable style="min-width: 4rem"></Column>
    <Column field="username" header="Usuario" sortable style="min-width: 12rem"></Column>
    <Column field="dni" header="DNI" sortable style="min-width: 4rem"></Column>
    <Column field="name1" header="Nombres y Apellidos" sortable style="min-width: 32rem"></Column>
    <Column field="email" header="Email" sortable style="min-width: 25rem"></Column>
    <Column field="creacion" header="Creación" sortable style="min-width: 13rem"></Column>
    <Column field="online" header="Online" sortable style="min-width: 9rem">
        <template #body="{ data }">
            <Tag :value="data.online ? 'En línea' : 'Sin conexión'" :severity="getSeverity(data.online)" />
        </template>
    </Column>
    <Column field="status" header="Estado" sortable style="min-width: 4rem">
        <template #body="{ data }">
            <Tag :value="data.status ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.status)" />
        </template>
    </Column>
    <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
        <template #body="slotProps">
            <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUsuario(slotProps.data)"/>
            <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteProduct(slotProps.data)" />
        </template>
    </Column>
</DataTable>

<DeleteUsuario
    v-model:visible="deleteProductDialog"
    :product="product"
    @deleted="handleUserDeleted"
/>
<UpdateUsuario
    v-model:visible="updateUsuarioDialog"
    :UsuarioId="selectedUsuarioId"
    @updated="handleUsuarioUpdated"
/>
</template>
