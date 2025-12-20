<script setup lang="ts">
import axios from 'axios';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';

const props = defineProps<{
    visible: boolean;
    receptionId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', v: boolean): void;
}>();

const toast = useToast();
const dialogVisible = ref(props.visible);
const loadingData = ref(false);

const engines = ref<any[]>([]);
const customers = ref<any[]>([]);
const accessoriesList = ref<any[]>([]);
const media = ref<{ id: number; type: string; url: string }[]>([]);

const fullImage = ref<string | null>(null);

// 🔥 Área imprimible
const printArea = ref<HTMLElement | null>(null);

// Datos de la recepción
const reception = ref({
    engine_id: null as number | null,
    customer_owner_id: null as number | null,
    customer_contact_id: null as number | null,

    owner_phone: '' as string,
    contact_phone: '' as string,

    tipo_mantenimiento: null as string | null,
    fecha_ingreso: null as Date | null,
    fecha_resuelto: null as Date | null,
    fecha_entrega: null as Date | null,
    problema: '',
    accessories: [] as number[],
});

const mantenimientoOptions = [
    { label: 'Preventivo', value: 'preventivo' },
    { label: 'Correctivo', value: 'correctivo' },
    { label: 'Predictivo', value: 'predictivo' },
    { label: 'Proactivo', value: 'proactivo' },
    { label: 'Detectivo / Inspección', value: 'detectivo_inspeccion' },
];

// Abrir modal
watch(
    () => props.visible,
    async (val) => {
        dialogVisible.value = val;

        if (val && props.receptionId) {
            loadingData.value = true;

            await loadInitialData();
            await fetchReception();

            reception.value.engine_id = Number(reception.value.engine_id);
            reception.value.customer_owner_id = Number(reception.value.customer_owner_id);
            reception.value.customer_contact_id = Number(reception.value.customer_contact_id);

            loadingData.value = false;
        }
    },
);

// Emit event close
watch(dialogVisible, (v) => emit('update:visible', v));

function formatDate(date: string | null) {
    if (!date) return '-';
    return new Date(date).toLocaleString('es-PE');
}

// Load lists
async function loadInitialData() {
    try {
        const [e, c, a] = await Promise.all([
            axios.get('/motor?per_page=9999&state=true'),
            axios.get('/cliente?per_page=9999&state=true'),
            axios.get('/accesorio?per_page=9999&state=true'),
        ]);

        engines.value = e.data.data.map((m: any) => ({
            ...m,
            modeloCompleto: `${m.marca} - ${m.modelo} - ${m.hp} HP - ${m.combustible}`,
        }));

        customers.value = c.data.data.map((x: any) => ({
            ...x,
            displayName: x.alias ? `${x.nombres} (${x.alias})` : x.nombres,
        }));

        accessoriesList.value = a.data.data;
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar listas' });
    }
}

// Fetch reception
async function fetchReception() {
    try {
        const res = await axios.get(`/recepcion/${props.receptionId}`);
        const data = res.data.reception;

        reception.value = {
            engine_id: data.engine_id,
            customer_owner_id: data.customer_owner_id,
            customer_contact_id: data.customer_contact_id,
            owner_phone: data.owner?.telefono || '-',
            contact_phone: data.contact?.telefono || '-',
            problema: data.problema,
            numero_serie: data.numero_serie,
            tipo_mantenimiento: data.tipo_mantenimiento,
            fecha_ingreso: new Date(data.fecha_ingreso),
            fecha_resuelto: data.fecha_resuelto ? new Date(data.fecha_resuelto) : null,
            fecha_entrega: data.fecha_entrega ? new Date(data.fecha_entrega) : null,
            accessories: data.accessories?.map((a: any) => a.id) || [],
        };

        media.value = data.media || [];
    } catch {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la recepción' });
    }
}

// Ver imagen grande
function openFullImage(url: string) {
    fullImage.value = url;
}

function printPDF() {
    if (!printArea.value) return;

    const content = printArea.value.innerHTML;

    const w = window.open('', '_blank', 'width=900,height=650');
    if (!w) return;

    w.document.write(`
    <html>
    <head>
        <title>Recepción #${props.receptionId}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                font-family: Arial, Helvetica, sans-serif;
                padding: 30px;
                background: #f5f5f7;
            }

            /* ENCABEZADO */
            .header {
                text-align: center;
                padding: 20px;
                background: #1a73e8;
                color: white;
                border-radius: 8px;
                margin-bottom: 25px;
            }

            .header h1 {
                margin: 0;
                font-size: 26px;
                letter-spacing: 1px;
            }

            /* TARJETAS */
            .card {
                background: white;
                padding: 20px;
                border-radius: 10px;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                box-shadow: 0 3px 8px rgba(0,0,0,0.05);
            }

            .section-title {
                font-size: 18px;
                font-weight: bold;
                color: #1a73e8;
                margin-bottom: 10px;
                border-left: 4px solid #1a73e8;
                padding-left: 8px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            td {
                padding: 8px 5px;
                vertical-align: top;
                border-bottom: 1px solid #eee;
            }

            .label {
                font-weight: bold;
                width: 180px;
                color: #333;
                white-space: nowrap;
            }

            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #777;
            }

            /* =====================
               RESPONSIVE (MÓVIL)
               ===================== */
            @media (max-width: 600px) {
                body {
                    padding: 12px;
                }

                .header {
                    padding: 15px;
                }

                .header h1 {
                    font-size: 20px;
                }

                .card {
                    padding: 15px;
                }

                .section-title {
                    font-size: 16px;
                }

                table, tbody, tr, td {
                    display: block;
                    width: 100%;
                }

                tr {
                    margin-bottom: 12px;
                }

                td {
                    border: none;
                    padding: 4px 0;
                }

                .label {
                    width: auto;
                    display: block;
                    font-size: 13px;
                    color: #555;
                }

                td:last-child {
                    font-size: 14px;
                    margin-bottom: 6px;
                }

                .footer {
                    font-size: 11px;
                }
            }

            /* IMPRESIÓN */
            @media print {
                body {
                    background: white;
                    padding: 0;
                }
            }
        </style>
    </head>

    <body>
        <div class="header">
            <h1>REPORTE DE RECEPCIÓN</h1>
            <p>Orden de Trabajo N° ${props.receptionId}</p>
        </div>

        ${content}

        <div class="footer">
            © ${new Date().getFullYear()} - Sistema de Control de Motores
        </div>
    </body>
    </html>
    `);

    w.document.close();
    w.print();
}
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Vista de Recepción" modal :style="{ width: '95vw', maxWidth: '800px' }">
        <!-- LOADING -->
        <div v-if="loadingData" class="py-10 text-center">
            <i class="pi pi-spin pi-spinner mb-3 text-4xl text-primary"></i>
            <p>Cargando datos...</p>
        </div>

        <!-- CONTENIDO -->
        <div v-else class="grid grid-cols-12 gap-4">
            <!-- 🔥 BOTÓN IMPRIMIR -->
            <div class="col-span-12 flex justify-end">
                <Button label="Imprimir PDF" icon="pi pi-print" severity="secondary" @click="printPDF" />
            </div>

            <div class="hidden" ref="printArea">
                <!-- DATOS PRINCIPALES -->
                <div class="card">
                    <div class="section-title">Información del Motor</div>
                    <table>
                        <tr>
                            <td class="label">Motor:</td>
                            <td>{{ engines.find((e) => e.id == reception.engine_id)?.modeloCompleto }}</td>
                        </tr>
                        <tr>
                            <td class="label">Dueño:</td>
                            <td>{{ customers.find((c) => c.id == reception.customer_owner_id)?.displayName }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tel. Dueño:</td>
                            <td>{{ reception.owner_phone }}</td>
                        </tr>
                        <tr>
                            <td class="label">Contacto:</td>
                            <td>{{ customers.find((c) => c.id == reception.customer_contact_id)?.displayName }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tel. Contacto:</td>
                            <td>{{ reception.contact_phone }}</td>
                        </tr>

                        <tr>
                            <td class="label">Mantenimiento:</td>
                            <td>
                                {{ mantenimientoOptions.find((m) => m.value === reception.tipo_mantenimiento)?.label || '-' }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- PROBLEMA -->
                <div class="card">
                    <div class="section-title">Problema Reportado</div>
                    <p>{{ reception.problema }}</p>
                </div>

                <!-- ACCESORIOS -->
                <div class="card">
                    <div class="section-title">Accesorios Recibidos</div>
                    <ul>
                        <li v-for="a in accessoriesList.filter((x) => reception.accessories.includes(x.id))" :key="a.id">
                            {{ a.name }}
                        </li>
                    </ul>
                </div>

                <!-- FECHAS -->
                <div class="card">
                    <div class="section-title">Fechas de Servicio</div>
                    <table>
                        <tbody>
                            <tr>
                                <td class="label">Ingreso:</td>
                                <td>{{ formatDate(reception.fecha_ingreso) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Resuelto:</td>
                                <td>{{ formatDate(reception.fecha_resuelto) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Entrega:</td>
                                <td>{{ formatDate(reception.fecha_entrega) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOTOR -->
            <div class="col-span-12">
                <label class="font-bold">Motor</label>
                <Select v-model="reception.engine_id" :options="engines" optionLabel="modeloCompleto" optionValue="id" disabled class="w-full" />
            </div>

            <!-- DUEÑO Y CONTACTO -->
            <div class="col-span-12 grid grid-cols-12 gap-4">
                <div class="col-span-12 md:col-span-6">
                    <label class="font-bold">Dueño</label>
                    <Select
                        v-model="reception.customer_owner_id"
                        :options="customers"
                        optionLabel="displayName"
                        optionValue="id"
                        disabled
                        class="w-full"
                    />
                    <div class="mt-1 text-sm text-gray-600">Telf. {{ reception.owner_phone }}</div>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="font-bold">Contacto</label>
                    <Select
                        v-model="reception.customer_contact_id"
                        :options="customers"
                        optionLabel="displayName"
                        optionValue="id"
                        disabled
                        class="w-full"
                    />
                    <div class="mt-1 text-sm text-gray-600">Telf. {{ reception.contact_phone }}</div>
                </div>
         <div class="col-span-12 md:col-span-4">
                <label class="font-bold">Nº serie</label>
                <Calendar v-model="reception.numero_serie" disabled showTime hourFormat="24" class="w-full" />
            </div>
                <div class="col-span-12 md:col-span-6">
                    <label class="font-bold">Mantenimiento</label>
                    <Select
                        v-model="reception.tipo_mantenimiento"
                        :options="mantenimientoOptions"
                        optionLabel="label"
                        optionValue="value"
                        disabled
                        class="w-full"
                        placeholder="Tipo de mantenimiento"
                    />
                </div>
            </div>

            <!-- PROBLEMA -->
            <div class="col-span-12">
                <label class="font-bold">Problema</label>
                <Textarea v-model="reception.problema" readonly rows="4" class="w-full" />
            </div>

            <!-- ACCESORIOS -->
            <div class="col-span-12">
                <label class="font-bold">Accesorios</label>

                <ul class="mt-2 space-y-2">
                    <li
                        v-for="a in accessoriesList.filter((x) => reception.accessories.includes(x.id))"
                        :key="a.id"
                        class="flex items-center gap-2 rounded border bg-gray-50 px-3 py-2"
                    >
                        <i class="pi pi-check-circle text-green-600"></i>
                        <span>{{ a.name }}</span>
                    </li>
                </ul>

                <p v-if="!reception.accessories.length" class="mt-2 text-sm text-gray-500">No se registraron accesorios.</p>
            </div>

            <!-- FECHAS -->
            <div class="col-span-12 md:col-span-4">
                <label class="font-bold">Ingreso</label>
                <Calendar v-model="reception.fecha_ingreso" disabled showTime hourFormat="24" class="w-full" />
            </div>

            <div class="col-span-12 md:col-span-4">
                <label class="font-bold">Resuelto</label>
                <Calendar v-model="reception.fecha_resuelto" disabled showTime hourFormat="24" class="w-full" />
            </div>

            <div class="col-span-12 md:col-span-4">
                <label class="font-bold">Entrega</label>
                <Calendar v-model="reception.fecha_entrega" disabled showTime hourFormat="24" class="w-full" />
            </div>

            <!-- MEDIA -->
            <div class="col-span-12 mt-4">
                <h5 class="mb-2 font-bold">Archivos adjuntos</h5>

                <div v-if="media.length" class="grid grid-cols-12 gap-4">
                    <div
                        v-for="m in media"
                        :key="m.id"
                        class="col-span-6 cursor-pointer rounded border bg-gray-50 p-2 md:col-span-4"
                        @click="m.type === 'foto' && openFullImage(m.url)"
                    >
                        <img v-if="m.type === 'foto'" :src="m.url" class="h-32 w-full rounded object-cover" />
                        <video v-if="m.type === 'video'" :src="m.url" controls class="h-32 w-full rounded"></video>
                        <audio v-if="m.type === 'audio'" :src="m.url" controls class="w-full"></audio>
                    </div>
                </div>

                <p v-else class="text-gray-500">No hay archivos adjuntos.</p>
            </div>
        </div>

        <template #footer>
            <Button label="Cerrar" icon="pi pi-times" text @click="dialogVisible = false" />
        </template>
    </Dialog>

    <!-- MODAL IMAGEN COMPLETA -->
    <Dialog v-model:visible="fullImage" modal :style="{ width: '90vw', maxWidth: '900px' }">
        <img :src="fullImage" class="w-full rounded shadow" />
    </Dialog>
</template>
