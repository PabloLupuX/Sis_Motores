<template>
    <Dialog 
        :visible="visible" 
        @update:visible="emit('update:visible', $event)" 
        :style="{ width: '95vw', maxWidth: '1000px' }"
        header="Editar Roles y Permisos" 
        modal
    >
        <div class="flex flex-col gap-6">
            <div>
                <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                <InputText v-model="rolName" fluid required maxlength="100" />
            </div>

            <div>
                <label class="block font-bold mb-3">Permisos <span class="text-red-500">*</span></label>

                <div v-if="loading" class="text-center p-4">
                    <i class="pi pi-spin pi-spinner text-2xl"></i>
                    <p>Cargando permisos...</p>
                </div>

                <div v-else class="permisos-container">
                    <div v-for="(permisos, categoria) in permisosAgrupados" :key="categoria" class="mb-4">
                        <Fieldset :toggleable="true" class="shadow-sm">
                            <template #legend>
                                <div class="flex justify-between items-center w-full">
                                    <span class="font-bold">{{ categoria }}</span>
                                    <div class="fieldset-actions flex gap-2">
                                        <Button 
                                            icon="pi pi-check-square" 
                                            size="small" 
                                            text
                                            @click.stop="seleccionarTodos(categoria)"
                                            v-tooltip="{ value: 'Seleccionar todos', showDelay: 1000, hideDelay: 300 }" 
                                        />
                                        <Button 
                                            icon="pi pi-times" 
                                            severity="danger" 
                                            size="small" 
                                            text
                                            @click.stop="deseleccionarTodos(categoria)"
                                            v-tooltip="{ value: 'Deseleccionar todos', showDelay: 1000, hideDelay: 300 }"
                                        />
                                    </div>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="permiso of permisos" :key="permiso.id"
                                    class="flex items-center gap-2 p-2 rounded-md">
                                    <Checkbox v-model="permisosSeleccionados" :inputId="'permiso_' + permiso.id"
                                        :value="permiso.id" />
                                    <label :for="'permiso_' + permiso.id" class="cursor-pointer">
                                        {{ permiso.name }}
                                    </label>
                                </div>
                            </div>
                        </Fieldset>
                    </div>

                    <small v-if="submitted && permisosSeleccionados.length === 0" class="text-red-500 block mt-2">
                        Debe seleccionar al menos un permiso
                    </small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="emit('update:visible', false)" />
            <Button label="Guardar" icon="pi pi-check" @click="updateRol" :loading="saving" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import axios, { AxiosResponse } from 'axios';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Fieldset from 'primevue/fieldset';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Permiso {
    id: number;
    name: string;
}

interface Rol {
    id: number;
    name: string;
    permissions: Permiso[];
}

// Props
const props = defineProps<{
    visible: boolean;
    RolId: number | null; // ✅ Acepta null mientras no haya selección
}>();

// Emit
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

// Toast
const toast = useToast();

// Estado
const allPermissions = ref<Permiso[]>([]);
const permisosSeleccionados = ref<number[]>([]);
const rolName = ref<string>('');
const loading = ref<boolean>(true);
const saving = ref<boolean>(false);
const submitted = ref<boolean>(false);

// Agrupar permisos por categoría
const permisosAgrupados = computed<Record<string, Permiso[]>>(() => {
    const grupos: Record<string, Permiso[]> = {};
    
    allPermissions.value.forEach(permiso => {
        const categoriaNombre = permiso.name.split(' ')[1] || 'Otros';
        if (!grupos[categoriaNombre]) grupos[categoriaNombre] = [];
        grupos[categoriaNombre].push(permiso);
    });
    
    return grupos;
});

// Watch para abrir el dialog y cargar datos
watch(() => props.visible, async (val) => {
    if (val && props.RolId) {
        await fetchPermissions();
        await loadRolData(props.RolId);
    }
});

// Función para cargar datos del rol
const loadRolData = async (id: number) => {
    loading.value = true;
    try {
        const res: AxiosResponse<Rol> = await axios.get(`/rol/${id}`);
        rolName.value = res.data.name;
        permisosSeleccionados.value = res.data.permissions?.map(p => p.id) || [];
    } catch (err) {
        console.error('Error al cargar el rol:', err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el rol', life: 3000 });
    } finally {
        loading.value = false;
    }
};

// Función para obtener todos los permisos
const fetchPermissions = async () => {
    loading.value = true;
    try {
        const res: AxiosResponse<{ permissions: Permiso[] }> = await axios.get('/rol/Permisos');
        allPermissions.value = res.data.permissions || [];
    } catch (err) {
        console.error('Error al obtener permisos:', err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error al obtener permisos', life: 3000 });
        allPermissions.value = [];
    } finally {
        loading.value = false;
    }
};

// Función para actualizar rol
const updateRol = async () => {
    submitted.value = true;
    
    if (!rolName.value || permisosSeleccionados.value.length === 0) return;
    
    saving.value = true;
    try {
        await axios.put(`/rol/${props.RolId}`, {
            name: rolName.value,
            permissions: permisosSeleccionados.value
        });
        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Rol y permisos actualizado correctamente', life: 3000 });
        emit('updated');
        emit('update:visible', false);
    } catch (err) {
        console.error('Error al actualizar el rol:', err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error al actualizar el rol', life: 3000 });
    } finally {
        saving.value = false;
    }
};

// Funciones de seleccionar/deseleccionar todos
function seleccionarTodos(categoria: string) {
    const nuevosSeleccionados = [...permisosSeleccionados.value];
    const permisosCategoria = permisosAgrupados.value[categoria].map(p => p.id);
    permisosCategoria.forEach(id => {
        if (!nuevosSeleccionados.includes(id)) nuevosSeleccionados.push(id);
    });
    permisosSeleccionados.value = nuevosSeleccionados;
}

function deseleccionarTodos(categoria: string) {
    const permisosCategoria = permisosAgrupados.value[categoria].map(p => p.id);
    permisosSeleccionados.value = permisosSeleccionados.value.filter(id => !permisosCategoria.includes(id));
}

// onMounted
onMounted(async () => {
    if (props.visible && props.RolId) {
        await fetchPermissions();
        await loadRolData(props.RolId);
    }
});
</script>
