<script lang="ts" setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppMenuItem from './AppMenuItem.vue';

// Tipos para usuario y permisos
interface User {
    permissions?: string[];
}

interface Auth {
    user?: User;
}

// Obtenemos los props de la página
const page = usePage<{ auth: Auth }>();

// Computed para permisos del usuario
const permissions = computed<string[]>(() => page.props.auth.user?.permissions ?? []);

// Función para verificar permisos
const hasPermission = (perm: string): boolean => permissions.value.includes(perm);

// Tipos del menú
interface MenuItem {
    label: string;
    icon?: string;
    to?: string;
    items?: MenuItem[];
}

// Computed para el modelo del menú
const model = computed<MenuItem[]>(() => [
    {
        label: 'Home',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }
        ]
    },
    {
        label: 'Gestion de Trabajo',
        items: [
            hasPermission('ver espacios') && { label: 'Espacios', icon: 'pi pi-fw pi-briefcase', to: '/espacios' },
        ].filter(Boolean) as MenuItem[],
    },
    {
        label: 'Usuarios y Seguridad',
        items: [
            hasPermission('ver usuarios') && { label: 'Gestión de Usuarios', icon: 'pi pi-fw pi-user-edit', to: '/usuario' },
            hasPermission('ver roles') && { label: 'Roles', icon: 'pi pi-fw pi-shield', to: '/roles' },
        ].filter(Boolean) as MenuItem[],
    },
].filter(section => section.items && section.items.length > 0) as MenuItem[]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item :item="item" :index="i" />
        </template>
    </ul>
</template>

<style scoped lang="scss">
/* Estilos personalizados opcionales */
</style>
