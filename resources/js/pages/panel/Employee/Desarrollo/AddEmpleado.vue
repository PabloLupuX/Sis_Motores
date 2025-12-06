<template>
  <Toolbar class="mb-6">
    <template #start>
      <Button label="Nuevo empleado" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
    <template #end>
      <ToolsEmployee />
    </template>
  </Toolbar>

  <Dialog v-model:visible="empleadoDialog" :style="{ width: '90%', maxWidth: '600px' }" header="Registro de empleado" :modal="true">
    <div class="flex flex-col gap-6">
      <div class="grid grid-cols-12 gap-4">

        <!-- Nombre -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
          <InputText v-model="empleado.name" placeholder="Ingrese su nombre" maxlength="150" class="w-full" />
          <small v-if="submitted && !empleado.name" class="text-red-500">El nombre es obligatorio.</small>
          <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
        </div>

        <!-- Apellido -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-bold mb-2">Apellido <span class="text-red-500">*</span></label>
          <InputText v-model="empleado.apellido" placeholder="Ingrese su apellido" maxlength="150" class="w-full" />
          <small v-if="submitted && !empleado.apellido" class="text-red-500">El apellido es obligatorio.</small>
          <small v-if="serverErrors.apellido" class="text-red-500">{{ serverErrors.apellido[0] }}</small>
        </div>

        <!-- DNI -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-bold mb-2">DNI <span class="text-red-500">*</span></label>
          <InputText v-model="empleado.codigo" placeholder="Ingrese su DNI" maxlength="8" class="w-full" />
          <small v-if="submitted && !empleado.codigo" class="text-red-500">El DNI es obligatorio.</small>
          <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
        </div>

        <!-- ID Huella -->
        <div class="col-span-12 sm:col-span-6">
          <label class="block font-bold mb-2">ID Huella <span class="text-red-500">*</span></label>
          <InputText v-model.number="empleado.idHuella" placeholder="Ingrese ID de huella" class="w-full" />
          <small v-if="submitted && !empleado.idHuella" class="text-red-500">El ID de huella es obligatorio.</small>
          <small v-if="serverErrors.idHuella" class="text-red-500">{{ serverErrors.idHuella[0] }}</small>
        </div>

        <!-- Tipo de Empleado -->
        <div class="col-span-12">
          <label class="block font-bold mb-2">Tipo de Empleado <span class="text-red-500">*</span></label>
          <Dropdown
            v-model="empleado.employee_type_id"
            :options="tiposEmpleado"
            optionLabel="name"
            optionValue="id"
            placeholder="Seleccione tipo de empleado"
            class="w-full"
            filter
            filterPlaceholder="Buscar tipo de empleado"
          />
          <small v-if="submitted && !empleado.employee_type_id" class="text-red-500">Debe seleccionar un tipo.</small>
          <small v-if="serverErrors.employee_type_id" class="text-red-500">{{ serverErrors.employee_type_id[0] }}</small>
        </div>

        <!-- Estado -->
        <div class="col-span-12 flex items-center gap-3">
          <Checkbox v-model="empleado.state" :binary="true" />
          <Tag :value="empleado.state ? 'Activo' : 'Inactivo'" :severity="empleado.state ? 'success' : 'danger'" />
          <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
        </div>

        <!-- Foto -->
        <div class="col-span-12">
          <label class="block font-bold mb-2">Foto <span class="text-red-500">*</span></label>

          <!-- Subir archivo -->
          <input type="file" @change="onFileChange" accept="image/png, image/jpeg" class="w-full" />

          <!-- Tomar foto -->
          <div class="mt-2 flex flex-wrap gap-2">
            <Button label="Tomar Foto" icon="pi pi-camera" class="p-button-secondary" @click="startCamera" />
          </div>

          <!-- Video en vivo -->
          <div v-if="cameraActive" class="mt-2 flex flex-col gap-2">
            <video ref="video" autoplay playsinline class="w-full max-w-xs mx-auto"></video>
            <div class="flex gap-2 flex-wrap mt-2">
              <Button label="Capturar" icon="pi pi-check" class="p-button-success" @click="capturePhoto" />
              <Button label="Cancelar" icon="pi pi-times" class="p-button-danger" @click="stopCamera" />
            </div>
          </div>

          <!-- Preview y Reintentar -->
          <div v-if="fotoPreview" class="mt-2 flex flex-col gap-2 items-center">
            <img :src="fotoPreview" alt="Preview" class="w-full max-w-xs h-auto border rounded" />
            <Button label="Reintentar" icon="pi pi-refresh" class="p-button-warning" @click="retryPhoto" />
          </div>

          <small v-if="submitted && !empleado.foto" class="text-red-500">La foto es obligatoria.</small>
          <small v-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
        </div>
      </div>
    </div>

    <template #footer>
      <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
      <Button label="Guardar" icon="pi pi-check" @click="guardarEmpleado" />
    </template>
  </Dialog>
</template>

<script lang="ts" setup>
import { ref, Ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import ToolsEmployee from './toolsEmployee.vue';
import { useToast } from 'primevue/usetoast';

interface Empleado {
  name: string;
  apellido: string;
  codigo: string;
  employee_type_id: number | null;
  idHuella: '';
  foto: File | null;
  state: boolean;
}

interface ServerErrors {
  [key: string]: string[];
}

const toast = useToast();

const empleadoDialog = ref(false);
const submitted = ref(false);
const serverErrors = ref<ServerErrors>({});
interface TipoEmpleado {
  id: number;
  name: string;
}

const tiposEmpleado = ref<TipoEmpleado[]>([]);

const empleado: Ref<Empleado> = ref({
  name: '',
  apellido: '',
  codigo: '',
  employee_type_id: null,
  idHuella: '',
  foto: null,
  state: true
});

const fotoPreview = ref<string | null>(null);
const cameraActive = ref(false);
const video = ref<HTMLVideoElement | null>(null);
let stream: MediaStream | null = null;

const emit = defineEmits<{
  (e: 'empleado-agregado'): void;
}>();

function resetEmpleado() {
  empleado.value = {
    name: '',
    apellido: '',
    codigo: '',
    employee_type_id: null,
    idHuella: '',
    foto: null,
    state: true
  };
  fotoPreview.value = null;
  serverErrors.value = {};
  submitted.value = false;
}

function openNew() {
  resetEmpleado();
  empleadoDialog.value = true;
  fetchTiposEmpleado();
}

function hideDialog() {
  empleadoDialog.value = false;
  resetEmpleado();
}

function fetchTiposEmpleado() {
  axios.get('/tipo_empleado', { params: { state: 1 } })
    .then(res => tiposEmpleado.value = res.data.data)
    .catch(() => {
      toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de empleado', life: 3000 });
    });
}

function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  if (file) {
    empleado.value.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
  }
}

function startCamera() {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => {
      stream = s;
      cameraActive.value = true;
      if (video.value) video.value.srcObject = stream;
    })
    .catch(() => {
      toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo acceder a la cámara', life: 3000 });
    });
}

function stopCamera() {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
  }
  cameraActive.value = false;
}

function capturePhoto() {
  if (!video.value) return;
  const canvas = document.createElement('canvas');
  canvas.width = video.value.videoWidth;
  canvas.height = video.value.videoHeight;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;
  ctx.drawImage(video.value, 0, 0, canvas.width, canvas.height);

  canvas.toBlob(blob => {
    if (!blob) return;
    const file = new File([blob], `empleado_${Date.now()}.png`, { type: 'image/png' });
    empleado.value.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
    stopCamera();
  }, 'image/png');
}

function retryPhoto() {
  fotoPreview.value = null;
  empleado.value.foto = null;
  startCamera();
}

function guardarEmpleado() {
  submitted.value = true;
  serverErrors.value = {};

  const { name, apellido, codigo, employee_type_id, idHuella, foto } = empleado.value;

  if (!name || !apellido || !codigo || !employee_type_id || !idHuella || !foto) {
    toast.add({ severity: 'warn', summary: 'Campos incompletos', detail: 'Debe completar todos los campos', life: 3000 });
    return;
  }

  if (!/^\d{8}$/.test(codigo)) {
    toast.add({ severity: 'warn', summary: 'DNI inválido', detail: 'El DNI debe ser 8 números', life: 3000 });
    return;
  }

  if (!Number.isInteger(idHuella) || idHuella <= 0) {
    toast.add({ severity: 'warn', summary: 'Huella inválida', detail: 'La huella debe ser un número', life: 3000 });
    return;
  }

  const formData = new FormData();
  for (const key in empleado.value) {
    const value = empleado.value[key as keyof Empleado];
    if (value !== null) formData.append(key, value as any);
  }

  axios.post('/empleado', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
    .then(() => {
      toast.add({ severity: 'success', summary: 'Éxito', detail: 'Empleado registrado', life: 3000 });
      hideDialog();
      emit('empleado-agregado');
    })
    .catch((error: AxiosError) => {
      if (error.response && error.response.status === 422) {
        serverErrors.value = (error.response.data as any).errors || {};
      } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo registrar el empleado', life: 3000 });
      }
    });
}
</script>
