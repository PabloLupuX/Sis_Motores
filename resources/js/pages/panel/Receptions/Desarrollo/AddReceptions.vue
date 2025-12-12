<script setup lang="ts">
import axios from 'axios';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import Toolbar from 'primevue/toolbar';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import ToolsReceptions from './toolsReceptions.vue';

// ---------------------------------------------
// INTERFACES
// ---------------------------------------------
interface Reception {
    engine_id: number | null;
    customer_owner_id: number | null;
    customer_contact_id: number | null;
    fecha_ingreso: string | null;
    problema: string | null;
    accessories: number[];
    numero_serie: string | null;
    tipo_mantenimiento: string | null;
}

interface ServerErrors {
    [key: string]: string[];
}

// ---------------------------------------------
// STATE
// ---------------------------------------------
const toast = useToast();
const submitted = ref(false);
const isSaving = ref(false);
const dialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const props = defineProps({
    search: String,
    state: [String, Boolean],
    dateRange: Array,
});
const maintenanceTypes = ref([
    { label: 'Preventivo', value: 'preventivo' },
    { label: 'Correctivo', value: 'correctivo' },
    { label: 'Predictivo', value: 'predictivo' },
    { label: 'Proactivo', value: 'proactivo' },
    { label: 'Detectivo / Inspección', value: 'detectivo_inspeccion' },
]);

const reception = ref<Reception>({
    engine_id: null,
    customer_owner_id: null,
    customer_contact_id: null,
    fecha_ingreso: null,
    problema: null,
    accessories: [],
    numero_serie: null,
    tipo_mantenimiento: null,
});

// Datos remotos
const engines = ref<any[]>([]);
const customers = ref<any[]>([]);
const accessories = ref<any[]>([]);

// Archivos
const selectedFiles = ref<File[]>([]);
const uploadedMedia = ref<{ type: string; url: string }[]>([]);

// ---------------------------------------------
// GRABACIÓN DE AUDIO
// ---------------------------------------------
const isRecording = ref(false);
const mediaRecorder = ref<MediaRecorder | null>(null);
const audioChunks: Blob[] = [];
const recordedAudios = ref<File[]>([]);

// Función segura para crear URLs
function getAudioUrl(file: File) {
    if (!file) return '';
    try {
        return URL.createObjectURL(file);
    } catch {
        return '';
    }
}
// Obtener URL para fotos o videos
function getMediaPreview(file: File) {
    try {
        return URL.createObjectURL(file);
    } catch {
        return '';
    }
}

// Determinar si es imagen
function isImage(file: File) {
    return file.type.startsWith('image');
}

// Determinar si es video
function isVideo(file: File) {
    return file.type.startsWith('video');
}

// Eliminar archivo multimedia
function removeFile(index: number) {
    selectedFiles.value.splice(index, 1);
}

// Iniciar grabación
async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        mediaRecorder.value = new MediaRecorder(stream);
        audioChunks.length = 0;

        mediaRecorder.value.ondataavailable = (e) => audioChunks.push(e.data);

        mediaRecorder.value.onstop = () => {
            const blob = new Blob(audioChunks, { type: 'audio/mp3' });

            const file = new File([blob], `audio_${Date.now()}.mp3`, {
                type: 'audio/mp3',
            });

            recordedAudios.value.push(file);
            selectedFiles.value.push(file);

            toast.add({
                severity: 'success',
                summary: 'Audio grabado',
                detail: 'Audio agregado correctamente',
                life: 3000,
            });
        };

        mediaRecorder.value.start();
        isRecording.value = true;
    } catch {
        toast.add({
            severity: 'error',
            summary: 'Micrófono',
            detail: 'No se pudo acceder al micrófono.',
            life: 3000,
        });
    }
}

function stopRecording() {
    if (mediaRecorder.value && isRecording.value) {
        mediaRecorder.value.stop();
        isRecording.value = false;
    }
}

function removeRecordedAudio(index: number) {
    const file = recordedAudios.value[index];

    selectedFiles.value = selectedFiles.value.filter((f) => f !== file);
    recordedAudios.value.splice(index, 1);

    toast.add({
        severity: 'info',
        summary: 'Audio eliminado',
        detail: 'Se eliminó correctamente',
        life: 2000,
    });
}

// ---------------------------------------------
// TOKEN API
// ---------------------------------------------
const API_TOKEN = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.MjAyNS0wMi0xNFQxMTo0Nzo1NC4xMjM0NTY3ODla.fA9k3Q1hGf7Z2rVtS8xM9uJmW4rQ2sT0pL5bX8fR3nE';

const API_UPLOAD_URL = 'https://codigodeprogramacion.com/api/upload.php';

// ---------------------------------------------
// CARGAR DATOS
// ---------------------------------------------
async function loadEngines() {
    const res = await axios.get('/motor?per_page=9999&state=true');
    engines.value = res.data.data.map((m: any) => ({
        ...m,
        modeloCompleto: `${m.marca} - ${m.modelo} - ${m.hp} HP - ${m.combustible}`,
    }));
}

async function loadCustomers() {
    const res = await axios.get('/cliente?per_page=9999&state=true');
    customers.value = res.data.data.map((c: any) => ({
        ...c,
        displayName: c.alias ? `${c.nombres} (${c.alias})` : c.nombres,
    }));
}

async function loadAccessories() {
    const res = await axios.get('/accesorio?per_page=9999&state=true');
    accessories.value = res.data.data;
}

// ---------------------------------------------
// ARCHIVOS
// ---------------------------------------------
function handleFiles(event: any) {
    selectedFiles.value.push(...Array.from(event.target.files));
}

async function uploadFileToExternalAPI(file: File) {
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await axios.post(API_UPLOAD_URL, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                Authorization: `Bearer ${API_TOKEN}`,
            },
        });

        return response.data.success ? response.data.url : null;
    } catch {
        return null;
    }
}

// ---------------------------------------------
// FORMULARIO
// ---------------------------------------------
const emit = defineEmits<{
    (e: 'reception-agregado'): void;
}>();

function resetForm() {
    reception.value = {
        engine_id: null,
        customer_owner_id: null,
        customer_contact_id: null,
        fecha_ingreso: null,
        problema: null,
        accessories: [],
        numero_serie: null,
        tipo_mantenimiento: null,
    };

    selectedFiles.value = [];
    recordedAudios.value = [];
    uploadedMedia.value = [];
    submitted.value = false;
    isSaving.value = false;
    serverErrors.value = {};
}

function openNew() {
    resetForm();
    dialog.value = true;

    loadEngines();
    loadCustomers();
    loadAccessories();
}

function hideDialog() {
    dialog.value = false;
}

async function guardarRecepcion() {
    submitted.value = true;

    if (
        !reception.value.engine_id ||
        !reception.value.customer_owner_id ||
        !reception.value.customer_contact_id ||
        !reception.value.fecha_ingreso ||
        !reception.value.problema ||
        !reception.value.numero_serie ||
        !reception.value.tipo_mantenimiento
    ) {
        return;
    }

    isSaving.value = true;
    uploadedMedia.value = [];

    for (const file of selectedFiles.value) {
        const url = await uploadFileToExternalAPI(file);

        if (url) {
            let type = 'foto';
            if (file.type.startsWith('video')) type = 'video';
            if (file.type.startsWith('audio')) type = 'audio';

            uploadedMedia.value.push({ type, url });
        }
    }

    const payload = {
        ...reception.value,
        media: uploadedMedia.value,
    };

    try {
        await axios.post('/recepcion', payload);

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Recepción registrada correctamente',
            life: 3000,
        });

        hideDialog();
        emit('reception-agregado');
    } finally {
        isSaving.value = false;
    }
}
</script>

<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Recepción" icon="pi pi-plus" severity="secondary" @click="openNew" />
        </template>
        <template #end>
            <ToolsReceptions :search="props.search" :state="props.state" :dateRange="props.dateRange" />
        </template>
    </Toolbar>

    <Dialog v-model:visible="dialog" :style="{ width: '95vw', maxWidth: '750px' }" header="Registro de Recepción" modal>
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- MOTOR -->
                <div class="col-span-12">
                    <label class="font-bold">Motor *</label>
                    <Select
                        v-model="reception.engine_id"
                        :options="engines"
                        optionLabel="modeloCompleto"
                        optionValue="id"
                        placeholder="Seleccionar motor"
                        class="mt-1 w-full"
                        filter
                    />
                    <small v-if="submitted && !reception.engine_id" class="text-red-500"> El motor es obligatorio. </small>
                </div>

                <!-- DUEÑO -->
                <div class="col-span-12">
                    <label class="font-bold">Dueño *</label>
                    <Select
                        v-model="reception.customer_owner_id"
                        :options="customers"
                        optionLabel="displayName"
                        optionValue="id"
                        placeholder="Seleccionar cliente dueño"
                        class="mt-1 w-full"
                        filter
                    />
                    <small v-if="submitted && !reception.customer_owner_id" class="text-red-500"> Obligatorio. </small>
                </div>

                <!-- CONTACTO -->
                <div class="col-span-12">
                    <label class="font-bold">Contacto *</label>
                    <Select
                        v-model="reception.customer_contact_id"
                        :options="customers"
                        optionLabel="displayName"
                        optionValue="id"
                        placeholder="Seleccionar contacto"
                        class="mt-1 w-full"
                        filter
                    />
                    <small v-if="submitted && !reception.customer_contact_id" class="text-red-500"> Obligatorio. </small>
                </div>

                <!-- NÚMERO DE SERIE -->
                <div class="col-span-12">
                    <label class="font-bold">Número de Serie *</label>

                    <InputText v-model.trim="reception.numero_serie" class="mt-1 w-full" maxlength="100" placeholder="Ingrese el número de serie" />

                    <small v-if="submitted && !reception.numero_serie" class="text-red-500"> El número de serie es obligatorio. </small>

                    <small v-if="serverErrors.numero_serie" class="text-red-500">
                        {{ serverErrors.numero_serie[0] }}
                    </small>
                </div>
                <!-- TIPO DE MANTENIMIENTO -->
                <div class="col-span-12">
                    <label class="font-bold">Tipo de Mantenimiento *</label>

                    <Select
                        v-model="reception.tipo_mantenimiento"
                        :options="maintenanceTypes"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccionar tipo de mantenimiento"
                        class="mt-1 w-full"
                    />

                    <small v-if="submitted && !reception.tipo_mantenimiento" class="text-red-500"> El tipo de mantenimiento es obligatorio. </small>

                    <small v-if="serverErrors.tipo_mantenimiento" class="text-red-500">
                        {{ serverErrors.tipo_mantenimiento[0] }}
                    </small>
                </div>

                <!-- PROBLEMA -->
                <div class="col-span-12">
                    <label class="font-bold">Problema *</label>
                    <Textarea v-model="reception.problema" rows="4" class="mt-1 w-full" placeholder="Describa el problema del motor..." />
                    <small v-if="submitted && !reception.problema" class="text-red-500"> El problema es obligatorio. </small>
                </div>

                <!-- ACCESORIOS -->
                <div class="col-span-12">
                    <label class="font-bold">Accesorios</label>
                    <MultiSelect
                        v-model="reception.accessories"
                        :options="accessories"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccionar accesorios"
                        class="mt-1 w-full"
                        filter
                        display="chip"
                    />
                </div>

                <!-- ARCHIVOS -->
                <div class="col-span-12">
                    <label class="font-bold">Fotos / Videos / Audios</label>

                    <input type="file" multiple accept="image/*,video/*,audio/*" class="mt-1 w-full" @change="handleFiles" />

                    <!-- 🔥 PREVISUALIZACIÓN DE ARCHIVOS -->
                    <div v-if="selectedFiles.length" class="mt-4 grid grid-cols-12 gap-4">
                        <div v-for="(file, index) in selectedFiles" :key="index" class="col-span-6 rounded border bg-gray-50 p-3 md:col-span-4">
                            <!-- Imagen -->
                            <img v-if="isImage(file)" :src="getMediaPreview(file)" class="mb-2 h-32 w-full rounded object-cover" />

                            <!-- Video -->
                            <video v-if="isVideo(file)" :src="getMediaPreview(file)" controls class="mb-2 h-32 w-full rounded"></video>

                            <!-- Audio grabado ya se muestra en otra sección -->

                            <p class="truncate text-xs">{{ file.name }}</p>

                            <Button label="Eliminar" icon="pi pi-trash" severity="danger" text class="mt-2" @click="removeFile(index)" />
                        </div>
                    </div>
                </div>

                <!-- GRABAR AUDIO -->
                <div class="col-span-12">
                    <label class="font-bold">Grabar Audio</label>

                    <div class="mt-2 flex gap-3">
                        <Button v-if="!isRecording" label="Grabar" icon="pi pi-microphone" severity="danger" @click="startRecording" />

                        <Button v-if="isRecording" label="Detener" icon="pi pi-stop" severity="warning" @click="stopRecording" />
                    </div>

                    <!-- LISTA DE AUDIOS -->
                    <div v-if="recordedAudios.length" class="mt-3 space-y-3">
                        <div v-for="(audio, index) in recordedAudios" :key="index" class="rounded border bg-gray-50 p-3">
                            <label class="font-semibold">Audio {{ index + 1 }}</label>

                            <audio :src="getAudioUrl(audio)" controls class="mt-2 w-full"></audio>

                            <Button label="Eliminar" icon="pi pi-trash" severity="danger" text class="mt-2" @click="removeRecordedAudio(index)" />
                        </div>
                    </div>
                </div>

                <!-- FECHA -->
                <div class="col-span-12">
                    <label class="font-bold">Fecha de ingreso *</label>
                    <Calendar v-model="reception.fecha_ingreso" showTime hourFormat="24" class="mt-1 w-full" />
                    <small v-if="submitted && !reception.fecha_ingreso" class="text-red-500"> La fecha de ingreso es obligatoria. </small>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <template #footer>
            <div class="flex justify-end gap-2">
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />

                <Button
                    :label="isSaving ? 'Guardando...' : 'Guardar'"
                    :icon="isSaving ? 'pi pi-spin pi-spinner' : 'pi pi-check'"
                    :disabled="isSaving"
                    @click="guardarRecepcion"
                />
            </div>
        </template>
    </Dialog>
</template>
