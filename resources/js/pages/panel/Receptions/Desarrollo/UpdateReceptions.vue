<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import Calendar from "primevue/calendar";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import MultiSelect from "primevue/multiselect";
import axios from "axios";
import { useToast } from "primevue/usetoast";

// PROPS ------------------------------------
const props = defineProps<{
    visible: boolean;
    receptionId: number | null;
}>();

const emit = defineEmits<{
    (e: "update:visible", v: boolean): void;
    (e: "updated"): void;
}>();

// STATE ------------------------------------
const toast = useToast();
const loading = ref(false);
const loadingData = ref(false);
const submitted = ref(false);
const dialogVisible = ref(props.visible);

// bloquear edición si reception.state = false
const isLocked = ref(false);

// Datos remotos
const engines = ref<any[]>([]);
const customers = ref<any[]>([]);
const accessoriesList = ref<any[]>([]);

// Archivos
const selectedFiles = ref<File[]>([]); // nuevos archivos
const existingMedia = ref<{ id: number; type: string; url: string }[]>([]);
const deletedMediaIds = ref<number[]>([]);

// Formulario
const reception = ref({
    engine_id: null,
    customer_owner_id: null,
    customer_contact_id: null,
    fecha_ingreso: null,
    fecha_resuelto: null,
    fecha_entrega: null,
    problema: "",
    accessories: [] as number[],
    state: true, // <--- IMPORTANTE
});

// ------------ FUNCIONES ARCHIVOS ------------
function isImage(file: File) {
    return file.type.startsWith("image");
}
function isVideo(file: File) {
    return file.type.startsWith("video");
}
function getMediaPreview(file: File) {
    return URL.createObjectURL(file);
}

// ------------ GRABACIÓN DE AUDIO ------------
const isRecording = ref(false);
const mediaRecorder = ref<MediaRecorder | null>(null);
const audioChunks: Blob[] = [];
const recordedAudios = ref<File[]>([]);

function getAudioUrl(file: File) {
    return URL.createObjectURL(file);
}

async function startRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder.value = new MediaRecorder(stream);
        audioChunks.length = 0;

        mediaRecorder.value.ondataavailable = (e) => audioChunks.push(e.data);

        mediaRecorder.value.onstop = () => {
            const blob = new Blob(audioChunks, { type: "audio/mp3" });
            const file = new File([blob], `audio_${Date.now()}.mp3`, { type: "audio/mp3" });

            recordedAudios.value.push(file);
            selectedFiles.value.push(file);

            toast.add({ severity: "success", summary: "Audio grabado", detail: "Audio agregado", life: 3000 });
        };

        mediaRecorder.value.start();
        isRecording.value = true;

    } catch {
        toast.add({ severity: "error", summary: "Error", detail: "No se puede usar el micrófono", life: 3000 });
    }
}

function stopRecording() {
    if (mediaRecorder.value) {
        mediaRecorder.value.stop();
        isRecording.value = false;
    }
}

function removeRecordedAudio(index: number) {
    const file = recordedAudios.value[index];
    selectedFiles.value = selectedFiles.value.filter(f => f !== file);
    recordedAudios.value.splice(index, 1);
}

// ------------ WATCH PARA ABRIR MODAL ------------
watch(() => props.visible, async (val) => {
    dialogVisible.value = val;

    if (val && props.receptionId) {
        loadingData.value = true;

        selectedFiles.value = [];
        recordedAudios.value = [];
        deletedMediaIds.value = [];

        await loadInitialData();
        await fetchReception();

        loadingData.value = false;
    }
});

watch(dialogVisible, (v) => emit("update:visible", v));

// ------------ CARGAR LISTAS ------------
async function loadInitialData() {
    const [e, c, a] = await Promise.all([
        axios.get("/motor?per_page=9999&state=true"),
        axios.get("/cliente?per_page=9999&state=true"),
        axios.get("/accesorio?per_page=9999&state=true"),
    ]);

    engines.value = e.data.data.map((m: any) => ({
        ...m,
        modeloCompleto: `${m.marca} - ${m.modelo} - ${m.hp} HP - ${m.year}`,
    }));

    customers.value = c.data.data.map((x: any) => ({
        ...x,
        displayName: x.alias ? `${x.nombres} (${x.alias})` : x.nombres,
    }));

    accessoriesList.value = a.data.data;
}

// ------------ CARGAR RECEPCIÓN ------------
async function fetchReception() {
    try {
        const res = await axios.get(`/recepcion/${props.receptionId}`);
        const data = res.data.reception;

        reception.value = {
            engine_id: data.engine_id,
            customer_owner_id: data.customer_owner_id,
            customer_contact_id: data.customer_contact_id,
            problema: data.problema,
            fecha_ingreso: new Date(data.fecha_ingreso),
            fecha_resuelto: data.fecha_resuelto ? new Date(data.fecha_resuelto) : null,
            fecha_entrega: data.fecha_entrega ? new Date(data.fecha_entrega) : null,
            accessories: data.accessories?.map((x: any) => x.id) || [],
            state: data.state,
        };

        existingMedia.value = data.media || [];

        // 🔒 BLOQUEAR SI ESTÁ CERRADA
        isLocked.value = !data.state;

    } catch {
        toast.add({ severity: "error", summary: "Error", detail: "No se pudo cargar la recepción" });
    }
}

// ------------ ELIMINAR MEDIA EXISTENTE ------------
function removeExistingMedia(id: number) {
    deletedMediaIds.value.push(id);
    existingMedia.value = existingMedia.value.filter(x => x.id !== id);
}

// ------------ GUARDAR RECEPCIÓN ------------
async function updateReception() {

    if (isLocked.value) {
        toast.add({
            severity: "warn",
            summary: "Bloqueado",
            detail: "Esta recepción está cerrada y no se puede editar.",
            life: 3000
        });
        return;
    }

    submitted.value = true;

    // VALIDACIÓN NUEVA → NO SE PUEDE CERRAR SIN FECHAS
    if (reception.value.state === false) {
        if (!reception.value.fecha_resuelto || !reception.value.fecha_entrega) {
            toast.add({
                severity: "warn",
                summary: "No se puede cerrar",
                detail: "Debe registrar 'Fecha resuelto' y 'Fecha entrega' antes de cerrar la recepción.",
                life: 4000,
            });
            return;
        }
    }

    // VALIDACIÓN NORMAL DEL FORMULARIO
    if (
        !reception.value.engine_id ||
        !reception.value.customer_owner_id ||
        !reception.value.customer_contact_id ||
        !reception.value.fecha_ingreso ||
        !reception.value.problema
    )
        return;


    loading.value = true;

    // SUBIR ARCHIVOS NUEVOS ------------------------
    const API_UPLOAD_URL = "https://codigodeprogramacion.com/api/upload.php";
    const API_TOKEN =
        "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.MjAyNS0wMi0xNFQxMTo0Nzo1NC4xMjM0NTY3ODla.fA9k3Q1hGf7Z2rVtS8xM9uJmW4rQ2sT0pL5bX8fR3nE";

    const newMedia: any[] = [];

    for (const f of selectedFiles.value) {
        const form = new FormData();
        form.append("file", f);

        const upload = await axios.post(API_UPLOAD_URL, form, {
            headers: {
                Authorization: `Bearer ${API_TOKEN}`,
                "Content-Type": "multipart/form-data",
            },
        });

        if (upload.data.success) {
            let type = "foto";
            if (f.type.startsWith("video")) type = "video";
            if (f.type.startsWith("audio")) type = "audio";

            const newItem = { type, url: upload.data.url };
            newMedia.push(newItem);

            existingMedia.value.push({
                id: Date.now(),
                ...newItem,
            });
        }
    }

    const payload = {
        ...reception.value,
        media_new: newMedia,
        media_delete: deletedMediaIds.value,
    };

    await axios.put(`/recepcion/${props.receptionId}`, payload);

    toast.add({ severity: "success", summary: "Actualizado", detail: "Recepción guardada", life: 3000 });

    dialogVisible.value = false;
    emit("updated");

    loading.value = false;
}
</script>

<template>
<Dialog v-model:visible="dialogVisible" header="Editar Recepción" modal :style="{ width: '95vw', maxWidth: '750px' }">

    <!-- LOADING -->
    <div v-if="loadingData" class="w-full py-10 flex flex-col items-center text-center">
        <i class="pi pi-spin pi-spinner text-4xl text-primary mb-3"></i>
        <p class="text-gray-600 font-semibold">Cargando datos de la recepción...</p>
    </div>

    <!-- FORMULARIO -->
    <div 
        v-else 
        class="grid grid-cols-12 gap-4"
        :class="{ 'pointer-events-none opacity-60': isLocked }"
    >

        <!-- MOTOR -->
        <div class="col-span-12">
            <label class="font-bold">Motor *</label>
            <Select v-model="reception.engine_id" :options="engines" optionLabel="modeloCompleto" optionValue="id" class="w-full" filter />
        </div>

        <!-- DUEÑO -->
        <div class="col-span-12">
            <label class="font-bold">Dueño *</label>
            <Select v-model="reception.customer_owner_id" :options="customers" optionLabel="displayName" optionValue="id" class="w-full" filter />
        </div>

        <!-- CONTACTO -->
        <div class="col-span-12">
            <label class="font-bold">Contacto *</label>
            <Select v-model="reception.customer_contact_id" :options="customers" optionLabel="displayName" optionValue="id" class="w-full" filter />
        </div>

        <!-- PROBLEMA -->
        <div class="col-span-12">
            <label class="font-bold">Problema *</label>
            <Textarea v-model="reception.problema" rows="3" class="w-full" />
        </div>

        <!-- ACCESORIOS -->
        <div class="col-span-12">
            <MultiSelect v-model="reception.accessories" :options="accessoriesList"
                optionLabel="name" optionValue="id" class="w-full" filter display="chip" />
        </div>

        <!-- FECHAS -->
        <div class="col-span-12 md:col-span-4">
            <label class="font-bold">Fecha ingreso *</label>
            <Calendar v-model="reception.fecha_ingreso" showTime hourFormat="24" class="w-full" />
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="font-bold">Fecha resuelto</label>
            <Calendar v-model="reception.fecha_resuelto" showTime hourFormat="24" class="w-full" />
        </div>

        <div class="col-span-12 md:col-span-4">
            <label class="font-bold">Fecha entrega</label>
            <Calendar v-model="reception.fecha_entrega" showTime hourFormat="24" class="w-full" />
        </div>

        <!-- CAMBIAR ESTADO -->
        <div class="col-span-12">
            <label class="font-bold">Estado</label>
            <Select
                v-model="reception.state"
                :options="[
                    { label: 'Abierto', value: true },
                    { label: 'Cerrado', value: false }
                ]"
                optionLabel="label"
                optionValue="value"
                class="w-full"
            />
        </div>

        <!-- ARCHIVOS EXISTENTES -->
        <div class="col-span-12" v-if="existingMedia.length">
            <h3 class="font-bold mb-2">Archivos existentes</h3>

            <div class="grid grid-cols-12 gap-3">

                <div v-for="m in existingMedia" :key="m.id" class="col-span-4 p-2 border rounded">
                    
                    <img v-if="m.type === 'foto'" :src="m.url" class="w-full h-24 object-cover rounded" />

                    <video v-if="m.type === 'video'" :src="m.url" controls class="w-full h-24 rounded"></video>

                    <audio v-if="m.type === 'audio'" :src="m.url" controls class="w-full"></audio>

                    <Button 
                        v-if="!isLocked"
                        label="Eliminar" 
                        icon="pi pi-trash" 
                        severity="danger" 
                        text 
                        class="mt-2"
                        @click="removeExistingMedia(m.id)" 
                    />
                </div>

            </div>
        </div>

        <!-- AGREGAR NUEVOS ARCHIVOS -->
        <div class="col-span-12" v-if="!isLocked">
            <label class="font-bold">Agregar nuevos archivos</label>

            <input 
                type="file"
                multiple
                accept="image/*,video/*,audio/*"
                @change="(e) => selectedFiles.push(...e.target.files)"
                class="w-full mt-2"
            />
        </div>

        <!-- PREVISUALIZACIÓN DE ARCHIVOS NUEVOS -->
        <div v-if="selectedFiles.length && !isLocked" class="col-span-12 grid grid-cols-12 gap-4 mt-2">

            <div v-for="(file, index) in selectedFiles" :key="index"
                class="col-span-6 md:col-span-4 p-3 border rounded bg-gray-50">

                <img v-if="isImage(file)" :src="getMediaPreview(file)" class="w-full h-32 object-cover rounded mb-2" />

                <video v-if="isVideo(file)" :src="getMediaPreview(file)" controls class="w-full h-32 rounded mb-2"></video>

                <p class="text-xs truncate">{{ file.name }}</p>

                <Button 
                    icon="pi pi-trash"
                    label="Eliminar" 
                    text 
                    severity="danger"
                    @click="selectedFiles.splice(index, 1)" 
                    class="mt-2" 
                />
            </div>

        </div>

        <!-- AUDIOS GRABADOS -->
        <div v-if="recordedAudios.length && !isLocked" class="col-span-12 mt-4">
            <h3 class="font-bold">Audios grabados</h3>

            <div class="space-y-2 mt-2">
                <div v-for="(audio, index) in recordedAudios" :key="index" class="p-2 border rounded bg-gray-50">
                    <audio :src="getAudioUrl(audio)" controls class="w-full"></audio>

                    <Button 
                        label="Eliminar" 
                        icon="pi pi-trash" 
                        text 
                        severity="danger"
                        @click="removeRecordedAudio(index)" 
                        class="mt-1" 
                    />
                </div>
            </div>
        </div>

        <!-- GRABACIÓN DE AUDIO -->
        <div class="col-span-12 mt-4" v-if="!isLocked">
            <label class="font-bold">Grabar audio</label>

            <div class="mt-2 flex gap-3">
                <Button v-if="!isRecording" label="Grabar" icon="pi pi-microphone" severity="danger"
                    @click="startRecording" />

                <Button v-if="isRecording" label="Detener" icon="pi pi-stop" severity="warning"
                    @click="stopRecording" />
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />

        <!-- SOLO SI ESTÁ ABIERTO -->
        <Button 
            v-if="!isLocked"
            label="Guardar" 
            icon="pi pi-check" 
            :loading="loading" 
            @click="updateReception" 
        />
    </template>

</Dialog>
</template>
