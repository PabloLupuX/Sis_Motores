<template>
    <Head title="Espacios de Trabajo" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera/>
            </template>

            <template v-else>
                <div class="card">
                     <AddReceptions 
                        :search="filtros.search"
                        :state="filtros.state"
                        :dateRange="filtros.dateRange"
                        @reception-agregado="refrescarListado" 
                    />
                     <ListReceptions 
                        :refresh="refreshKey" 
                        @updateFilters="filtros = $event"
                    />
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';
import AddReceptions from './Desarrollo/AddReceptions.vue';
import ListReceptions from './Desarrollo/ListReceptions.vue';

const isLoading = ref(true);
const refreshKey = ref(0);

function refrescarListado() {
    refreshKey.value++;
}
onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
const filtros = ref({
    search: "",
    state: "",
    dateRange: null
});

</script>
