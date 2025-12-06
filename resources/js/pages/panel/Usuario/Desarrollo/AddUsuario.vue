<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo usuario" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
    </Toolbar>

    <Dialog 
        v-model:visible="usuarioDialog" 
        :style="{ width: '95vw', maxWidth: '600px' }" 
        header="Registro de usuarios" 
        :modal="true"
    >
        <div class="flex flex-col gap-6">
            <!-- DNI + ESTADO -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-9">
                    <label for="dni" class="block font-bold mb-3">DNI <span class="text-red-500">*</span></label>
                    <InputText 
                        id="dni" 
                        v-model.trim="usuario.dni" 
                        required 
                        autofocus 
                        fluid
                        :class="{ 'p-invalid': (submitted && !usuario.dni) || serverErrors.dni }" 
                        maxlength="8"
                        @keydown.enter="consultarusuarioPorDNI" 
                    />
                    <small v-if="submitted && !usuario.dni" class="text-red-500">El DNI es obligatorio.</small>
                    <small v-else-if="submitted && usuario.dni.length !== 8" class="text-red-500">El DNI debe tener 8 dígitos.</small>
                    <small v-else-if="serverErrors.dni" class="text-red-500">{{ serverErrors.dni[0] }}</small>
                </div>
                <div class="sm:col-span-3 flex flex-col">
                    <label for="status" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="usuario.status" :binary="true" inputId="status" />
                        <Tag :value="usuario.status ? 'Con Acceso' : 'Sin Acceso'"
                            :severity="usuario.status ? 'success' : 'danger'" />
                    </div>
                    <small v-if="submitted && !usuario.status" class="text-red-500">El estado es obligatorio.</small>
                    <small v-else-if="serverErrors.status" class="text-red-500">{{ serverErrors.status[0] }}</small>
                </div>
            </div>

            <!-- NOMBRES -->
            <div>
                <label for="name" class="block font-bold mb-3">Nombres <span class="text-red-500">*</span></label>
                <InputText id="name" v-model.trim="usuario.name" required maxlength="100" disabled fluid />
                <small v-if="submitted && !usuario.name" class="text-red-500">El nombre es obligatorio.</small>
                <small v-else-if="submitted && usuario.name && usuario.name.length < 2" class="text-red-500">El nombre debe tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
            </div>

            <!-- APELLIDOS -->
            <div>
                <label for="apellidos" class="block font-bold mb-3">Apellidos <span class="text-red-500">*</span></label>
                <InputText id="apellidos" v-model.trim="usuario.apellidos" required maxlength="100" disabled fluid />
                <small v-if="submitted && !usuario.apellidos" class="text-red-500">Los apellidos son obligatorios.</small>
                <small v-else-if="submitted && usuario.apellidos && usuario.apellidos.length < 2"
                    class="text-red-500">Los apellidos deben tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.apellidos" class="text-red-500">{{ serverErrors.apellidos[0] }}</small>
            </div>

            <!-- NACIMIENTO + USUARIO -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-6">
                    <label for="nacimiento" class="block font-bold mb-3">
                        Fecha de nacimiento <span class="text-red-500">*</span>
                    </label>
                    <Calendar
                        id="nacimiento"
                        v-model="usuario.nacimiento"
                        dateFormat="dd/mm/yy"
                        showIcon
                        placeholder="Seleccionar fecha"
                        fluid
                        @date-select="actualizarUsername"
                    />
                    <small v-if="submitted && !usuario.nacimiento" class="text-red-500">
                        La fecha de nacimiento es obligatoria.
                    </small>
                    <small v-else-if="serverErrors.nacimiento" class="text-red-500">
                        {{ serverErrors.nacimiento[0] }}
                    </small>
                </div>

                <div class="sm:col-span-6">
                    <label for="username" class="block font-bold mb-3">
                        Usuario <span class="text-red-500">*</span>
                    </label>
                    <InputText 
                        id="username"
                        v-model="usuario.username"
                        disabled
                        fluid
                    />
                    <small v-if="submitted && !usuario.username" class="text-red-500">
                        El nombre de usuario es obligatorio.
                    </small>
                    <small v-else-if="serverErrors.username" class="text-red-500">
                        {{ serverErrors.username[0] }}
                    </small>
                </div>
            </div>

            <!-- EMAIL -->
            <div>
                <label for="email" class="block font-bold mb-3">Email <span class="text-red-500">*</span></label>
                <InputText id="email" v-model.trim="usuario.email" required maxlength="150" fluid />
                <small v-if="submitted && !usuario.email" class="text-red-500">El correo electrónico es obligatorio.</small>
                <small v-else-if="submitted && usuario.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(usuario.email)"
                    class="text-red-500">El correo electrónico debe ser válido.</small>
                <small v-else-if="serverErrors.email" class="text-red-500">{{ serverErrors.email[0] }}</small>
            </div>

            <!-- PASSWORD + ROL -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-6">
                    <label for="password" class="block font-bold mb-3">Contraseña <small class="text-red-500">*</small></label>
                    <Password v-model="usuario.password" toggleMask placeholder="contraseña" fluid :feedback="false"
                        inputId="password" />
                    <small v-if="submitted && !usuario.password" class="text-red-500">La Contraseña es obligatoria.</small>
                    <small v-else-if="submitted && usuario.password && usuario.password.length < 8"
                        class="text-red-500">La Contraseña debe tener al menos 8 caracteres.</small>
                    <small v-else-if="serverErrors.password" class="text-red-500">{{ serverErrors.password[0] }}</small>
                </div>
                
                <div class="sm:col-span-6">
                    <label for="role" class="block font-bold mb-3">Rol <span class="text-red-500">*</span></label>
                    <Dropdown 
                        v-model="usuario.role_id" 
                        :options="roles" 
                        optionLabel="name" 
                        optionValue="id" 
                        placeholder="Seleccione un rol" 
                        fluid 
                        filter
                        filterPlaceholder="Buscar rol"
                    />
                    <small v-if="submitted && !usuario.role_id" class="text-red-500">El rol es obligatorio.</small>
                    <small v-else-if="serverErrors.role_id" class="text-red-500">{{ serverErrors.role_id[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex flex-col sm:flex-row gap-2 justify-end">
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Guardar" icon="pi pi-check" @click="guardarUsuario" />
            </div>
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Password from 'primevue/password';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import { defineEmits } from 'vue';
import Calendar from 'primevue/calendar';

interface Rol {
    id: number;
    name: string;
}

interface Usuario {
    dni: string;
    name: string;
    apellidos: string;
    nacimiento: Date | null;
    email: string;
    username: string;
    password: string;
    status: boolean;
    role_id: number | null;
}

interface ServerErrors {
    dni?: string[];
    name?: string[];
    apellidos?: string[];
    nacimiento?: string[];
    email?: string[];
    password?: string[];
    status?: string[];
    role_id?: string[];
    username?: string[];
}

const toast = useToast();
const roles = ref<Rol[]>([]);
const submitted = ref(false);
const usuarioDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits<{
    (e: 'usuario-agregado'): void;
}>();

const usuarioReseteo: Usuario = {
    dni: '',
    name: '',
    apellidos: '',
    nacimiento: null,
    email: '',
    username: '',
    password: '',
    status: true,
    role_id: null,
};

const usuario = ref<Usuario>({ ...usuarioReseteo });

function openNew(): void {
    submitted.value = false;
    usuarioDialog.value = true;
    usuario.value = { ...usuarioReseteo };
}

function hideDialog(): void {
    usuarioDialog.value = false;
    submitted.value = false;
    usuario.value = { ...usuarioReseteo };
}

function formatearFecha(fecha: Date | null): string {
    if (!fecha) return '';
    const dia = fecha.getDate().toString().padStart(2, '0');
    const mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
    const anio = fecha.getFullYear();
    return `${dia}/${mes}/${anio}`;
}

function consultarusuarioPorDNI(): void {
    const dni = usuario.value.dni;
    if (dni && dni.length === 8) {
        axios.get(`/consulta/${dni}`)
            .then(response => {
                const data = response.data;
                if (data.success && data.data) {
                    const { nombres = '', apellido_paterno = '', apellido_materno = '', fecha_nacimiento = '' } = data.data;

                    usuario.value.name = nombres;
                    usuario.value.apellidos = `${apellido_paterno} ${apellido_materno}`.trim();
                    usuario.value.nacimiento = fecha_nacimiento ? new Date(fecha_nacimiento) : null;

                    usuario.value.username = generarUsername(nombres, apellido_paterno, apellido_materno, usuario.value.nacimiento);
                } else {
                    toast.add({ severity: 'warn', summary: 'No encontrado', detail: 'No se encontraron datos para este DNI', life: 3000 });
                }
            })
            .catch(() => {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo consultar el DNI', life: 3000 });
            });
    }
}

function generarUsername(nombre: string, apellidoPaterno: string, apellidoMaterno: string, nacimiento: Date | null): string {
    const normalizar = (texto: string) =>
        texto
            ?.replace(/ñ/g, 'n')
            .replace(/Ñ/g, 'n')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase() || '';

    const primeraLetraNombre = normalizar(nombre)?.charAt(0);
    const primerApellido = normalizar(apellidoPaterno)?.split(' ')[0];
    const segundoApellido = normalizar(apellidoMaterno)?.split(' ')[0]?.substring(0, 2);

    let diaNacimiento = '00';
    if (nacimiento instanceof Date) {
        diaNacimiento = nacimiento.getDate().toString().padStart(2, '0');
    }

    return `${primeraLetraNombre}${primerApellido}${segundoApellido}${diaNacimiento}`.toUpperCase();
}

function actualizarUsername(): void {
    usuario.value.username = generarUsername(
        usuario.value.name,
        usuario.value.apellidos.split(' ')[0] || '',
        usuario.value.apellidos.split(' ')[1] || '',
        usuario.value.nacimiento
    );
}

function guardarUsuario(): void {
    submitted.value = true;
    serverErrors.value = {};

    const datosEnviar = {
        ...usuario.value,
        nacimiento: formatearFecha(usuario.value.nacimiento),
    };

    axios.post('/usuarios', datosEnviar)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Usuario registrado', life: 3000 });
            hideDialog();
            emit('usuario-agregado');
        })
        .catch((error: AxiosError) => {
            if (error.response && error.response.status === 422) {
                const errors = (error.response.data as any).errors;
                serverErrors.value = errors;
            }
        });
}

onMounted(() => {
    axios.get('/rol')
        .then(response => {
            roles.value = response.data.data;
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los roles', life: 3000 });
        });
});
</script>
