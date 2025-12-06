<script setup lang="ts">
import { ref, onBeforeMount, watch, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { useLayout } from '@/layout/composables/layout';

// Interfaces
interface MenuItem {
  label: string;
  icon?: string;
  to?: string;
  url?: string;
  target?: string;
  class?: string;
  items?: MenuItem[];
  command?: (event: { originalEvent: Event; item: MenuItem }) => void;
  requiresAuth?: boolean;
  guestOnly?: boolean;
  permissions?: string[];
  roles?: string[];
  visible?: boolean;
  only?: any[];
  preserveState?: boolean;
  preserveScroll?: boolean;
  disabled?: boolean;
}

interface AuthUser {
  id: number;
  name: string;
  permissions?: string[];
  roles?: string[];
  [key: string]: any;
}

interface Auth {
  user?: AuthUser | null;
}

interface PageProps {
  auth?: Auth | null;
  [key: string]: any;
}

// Props
const props = defineProps({
  item: {
    type: Object as () => MenuItem,
    default: () => ({})
  },
  index: {
    type: Number,
    default: 0
  },
  root: {
    type: Boolean,
    default: true
  },
  parentItemKey: {
    type: String,
    default: null
  }
});

// Layout y página
const page = usePage<PageProps>();
const { layoutState, setActiveMenuItem, toggleMenu } = useLayout();

// Computed
const isAuthenticated = computed(() => !!(page.props.auth?.user));
const authUser = computed<AuthUser | null>(() => {
  return isAuthenticated.value && page.props.auth?.user ? page.props.auth.user : null;
});

// Refs
const isActiveMenu = ref(false);
const itemKey = ref<string | undefined>(undefined);

// Antes de montar
onBeforeMount(() => {
  itemKey.value = props.parentItemKey ? `${props.parentItemKey}-${props.index}` : String(props.index);
  const activeItem = layoutState.activeMenuItem as string | null;
  isActiveMenu.value = activeItem === itemKey.value || (!!activeItem && activeItem.startsWith(itemKey.value + '-'));
});

// Watcher de cambio de menú activo
watch(
  () => layoutState.activeMenuItem,
  (newVal) => {
    const active = newVal as string | null;
    isActiveMenu.value = active === itemKey.value || (!!active && active.startsWith(itemKey.value + '-'));
  }
);

// Funciones
function itemClick(event: Event, item: MenuItem) {
  if (item.requiresAuth && !isAuthenticated.value) {
    event.preventDefault();
    window.location.href = route('login');
    return;
  }

  if (item.permissions && authUser.value) {
    const hasPermission = item.permissions.some(permission =>
      authUser.value?.permissions?.includes(permission) ?? false
    );

    if (!hasPermission) {
      event.preventDefault();
      return;
    }
  }

  if (item.disabled) {
    event.preventDefault();
    return;
  }

  if ((item.to || item.url) && (layoutState.staticMenuMobileActive || layoutState.overlayMenuActive)) {
    toggleMenu();
  }

  if (item.command) {
    item.command({ originalEvent: event, item });
  }

  const foundItemKey = item.items ? (isActiveMenu.value ? props.parentItemKey : itemKey.value) : itemKey.value;
  if (foundItemKey) setActiveMenuItem(foundItemKey);
}

function checkActiveRoute(item: MenuItem) {
  return page.url === item.to;
}

function shouldShowItem(item: MenuItem) {
  if (item.requiresAuth && !isAuthenticated.value) return false;
  if (item.guestOnly && isAuthenticated.value) return false;

  if (item.permissions && authUser.value) {
    const hasPermission = item.permissions.some(permission =>
      authUser.value?.permissions?.includes(permission)
    );
    if (!hasPermission) return false;
  }

  if (item.roles && authUser.value) {
    const hasRole = item.roles.some(role =>
      authUser.value?.roles?.includes(role)
    );
    if (!hasRole) return false;
  }

  return item.visible !== false;
}
</script>

<template>
  <li :class="{ 'layout-root-menuitem': root, 'active-menuitem': isActiveMenu }">
    <!-- Label raíz -->
    <div v-if="root && shouldShowItem(item)" class="layout-menuitem-root-text">{{ item.label }}</div>

    <!-- Enlace externo o con submenú -->
    <a v-if="(!item.to || item.items) && shouldShowItem(item)" :href="item.url" @click="itemClick($event, item)"
      :class="item.class" :target="item.target" tabindex="0">
      <i :class="item.icon" class="layout-menuitem-icon"></i>
      <span class="layout-menuitem-text">{{ item.label }}</span>
      <i class="pi pi-fw pi-angle-down layout-submenu-toggler" v-if="item.items"></i>
    </a>

    <!-- Rutas internas (Inertia Link) -->
    <Link v-if="item.to && !item.items && shouldShowItem(item)" :href="item.to" @click="itemClick($event, item)"
      :class="[item.class, { 'active-route': checkActiveRoute(item) }]" tabindex="0" :only="item.only || []"
      :preserve-state="item.preserveState || false" :preserve-scroll="item.preserveScroll || false">
      <i :class="item.icon" class="layout-menuitem-icon"></i>
      <span class="layout-menuitem-text">{{ item.label }}</span>
    </Link>

    <!-- Submenú recursivo -->
    <Transition v-if="item.items && shouldShowItem(item)" name="layout-submenu">
      <ul v-show="root ? true : isActiveMenu" class="layout-submenu">
        <app-menu-item v-for="(child, i) in item.items.filter(childItem => shouldShowItem(childItem))" :key="i"
          :index="i" :item="child" :parentItemKey="itemKey" :root="false"></app-menu-item>
      </ul>
    </Transition>
  </li>
</template>

<style lang="scss" scoped></style>