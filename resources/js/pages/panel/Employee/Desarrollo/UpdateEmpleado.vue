<template>
  <Dialog
    v-model:visible="dialogVisible"
    header="Editar Empleado"
    modal
    :closable="true"
    :style="{ width: '90%', maxWidth: '650px' }"
  >
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
          <input type="file" @change="onFileChange" class="w-full" accept="image/png, image/jpeg" />

          <!-- Tomar foto -->
          <div class="mt-2 flex flex-wrap gap-2">
            <Button label="Tomar Foto" icon="pi pi-camera" class="p-button-secondary" @click="startCamera" />
          </div>

          <!-- Video en vivo -->
          <div v-if="cameraActive" class="mt-2 flex flex-col gap-2 items-center">
            <video ref="video" autoplay playsinline class="w-full max-w-xs"></video>
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

          <small v-if="submitted && !empleado.foto && !fotoPreview" class="text-red-500">La foto es obligatoria.</small>
          <small v-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
        </div>
      </div>
    </div>

    <template #footer>
      <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
      <Button label="Guardar" icon="pi pi-check" @click="updateEmpleado" :loading="loading" />
    </template>
  </Dialog>
</template>

<script lang="ts" setup>
import { ref, Ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Dropdown from 'primevue/dropdown';
import axios, { AxiosError } from 'axios';
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

const props = defineProps<{ visible: boolean; empleadoId: number }>();
const emit = defineEmits<{ (e: 'update:visible', value: boolean): void; (e: 'updated'): void }>();

const toast = useToast();
const dialogVisible = ref(props.visible);
const loading = ref(false);
const submitted = ref(false);
const serverErrors = ref<ServerErrors>({});

const empleado: Ref<Empleado> = ref({
  name: '',
  apellido: '',
  codigo: '',
  employee_type_id: null,
  idHuella: '',
  foto: null,
  state: true
});
interface TipoEmpleado {
  id: number;
  name: string;
}

const tiposEmpleado = ref<TipoEmpleado[]>([]);
const fotoPreview = ref<string | null>(null);
const cameraActive = ref(false);
const video = ref<HTMLVideoElement | null>(null);
let stream: MediaStream | null = null;
let fotoOriginal: string | null = null;

// Watches
watch(
  () => props.visible,
  val => {
    dialogVisible.value = val;
    if (val && props.empleadoId) {
      fetchEmpleado();
      fetchTiposEmpleado();
    }
  }
);

watch(dialogVisible, val => {
  if (!val) resetEmpleado();
  emit('update:visible', val);
});

// Reset
function resetEmpleado() {
  empleado.value.foto = null;
  fotoPreview.value = fotoOriginal;
  serverErrors.value = {};
  submitted.value = false;
  stopCamera();
}

// Fetch empleado
async function fetchEmpleado() {
  try {
    loading.value = true;
    const res = await axios.get(`/empleado/${props.empleadoId}`);
    const data = res.data.employee;
    empleado.value = {
      name: data.name,
      apellido: data.apellido,
      codigo: data.codigo,
      employee_type_id: data.employee_type_id,
      idHuella: data.idHuella ?? '',
      foto: null,
      state: data.state
    };
    fotoOriginal = data.foto ? `/uploads/fotos/empleados/${data.foto}` : null;
    fotoPreview.value = fotoOriginal;
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el empleado', life: 3000 });
    console.error(error);
  } finally {
    loading.value = false;
  }
}

// Fetch tipos
async function fetchTiposEmpleado() {
  try {
    const res = await axios.get('/tipo_empleado', { params: { state: 1 } });
    tiposEmpleado.value = res.data.data;
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de empleado', life: 3000 });
    console.error(error);
  }
}

// Foto
function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0] ?? null;
  if (file) {
    empleado.value.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
  }
}

// Cámara
function startCamera() {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then(s => {
      stream = s;
      cameraActive.value = true;
      if (video.value) video.value.srcObject = stream;
    })
    .catch(() => {
      toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo acceder a la cámara', life:
// Continuación de <script lang="ts" setup>
3000 });
    });
}

function stopCamera() {
  if (stream) {
    stream.getTracks().forEach(track => track.stop());
    stream = null;
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

// Update empleado
async function updateEmpleado() {
  submitted.value = true;
  serverErrors.value = {};

  if (!empleado.value.name || !empleado.value.codigo || !empleado.value.employee_type_id) {
    toast.add({
      severity: 'warn',
      summary: 'Campos incompletos',
      detail: 'Complete todos los campos obligatorios',
      life: 3000
    });
    return;
  }

  const formData = new FormData();
  formData.append('_method', 'PUT'); // Laravel PUT
  formData.append('name', empleado.value.name);
  formData.append('apellido', empleado.value.apellido);
  formData.append('idHuella', String(empleado.value.idHuella));
  formData.append('codigo', empleado.value.codigo);
  formData.append('employee_type_id', String(empleado.value.employee_type_id));
  formData.append('state', empleado.value.state ? '1' : '0');

  if (empleado.value.foto) {
    formData.append('foto', empleado.value.foto);
  }

  try {
    loading.value = true;
    await axios.post(`/empleado/${props.empleadoId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    toast.add({
      severity: 'success',
      summary: 'Actualizado',
      detail: 'Empleado actualizado correctamente',
      life: 3000
    });

    dialogVisible.value = false;
    emit('updated');
  } catch (error) {
    const err = error as AxiosError;
    if (err.response && err.response.status === 422) {
      serverErrors.value = (err.response.data as any).errors || {};
      toast.add({
        severity: 'error',
        summary: 'Error de validación',
        detail: 'Revisa los campos e intenta nuevamente',
        life: 5000
      });
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'No se pudo actualizar el empleado',
        life: 3000
      });
    }
    console.error(error);
  } finally {
    loading.value = false;
  }
}
</script>
