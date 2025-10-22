<template>
  <aside 
    class="bg-[#111111] text-white flex flex-col fixed h-full transition-all ease-in-out duration-300 shadow-lg  z-30"
    :class="[
      isCollapsed ? 'w-20' : 'w-64',
      { 'hidden md:block': !sidebarOpen }
    ]"
  >
    <!-- Sidebar Header with Logo and Toggle Button -->
    <div class="px-4 py-4 border-b border-gray-800 flex items-center justify-between">
      <transition name="fade" mode="out-in">
        <div v-if="!isCollapsed" key="full-logo" class="flex items-center gap-3">
          <img src="/logo-polda.svg" alt="Logo POLDA" class="w-10" />
          <span class="font-bold text-lg">POLDA TIK</span>
        </div>
        <div v-else key="collapsed-logo" class="flex justify-center w-full">
          <img src="/logo-polda.svg" alt="Logo POLDA" class="w-10" />
        </div>
      </transition>
      
      <button 
        @click="toggleCollapse" 
        class="p-2 rounded-lg hover:bg-[#222222] transition-colors duration-200"
        :class="{ 'ml-auto': !isCollapsed }"
      >
        <MenuIcon v-if="!isCollapsed" class="w-5 h-5" />
        <MenuIcon v-else class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-6 overflow-y-auto">
      <ul class="space-y-2">
        <li v-for="item in menuItems" :key="item.name">
          <component
            :is="item.component"
            :href="item.href"
            :class="[
              'flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-200 group',
              isCurrentPage(item.href) 
                ? 'bg-[#C62828] text-white font-semibold' 
                : 'hover:bg-[#C62828]/90 text-gray-300'
            ]"
            @click="handleMenuClick"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5 flex-shrink-0"
              :class="{ 'mx-auto': isCollapsed }"
            />
            
            <transition name="fade">
              <span v-if="!isCollapsed" class="text-sm">
                {{ item.name }}
              </span>
            </transition>
            
            <!-- Tooltip for collapsed state -->
            <div 
              v-if="isCollapsed" 
              class="absolute left-full ml-3 px-2 py-1 bg-[#111111] text-white text-xs rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50"
            >
              {{ item.name }}
              <div class="absolute top-1/2 -left-1 transform -translate-y-1/2 w-2 h-2 bg-[#111111] rotate-45"></div>
            </div>
          </component>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { 
  HomeIcon, 
  CalendarCheckIcon, 
  UserIcon,
  MenuIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: true
  }
});

// Emits
const emit = defineEmits(['update:sidebarOpen', 'toggle-collapse']);

// State
const isCollapsed = ref(false);

// Menu items
const menuItems = [
  {
    name: 'Dashboard',
    href: '/user/dashboard',
    icon: HomeIcon,
    component: 'a'
  },
  {
    name: 'Absensi',
    href: '/user/absensi',
    icon: CalendarCheckIcon,
    component: 'a'
  },
  {
    name: 'Profil',
    href: '/user/profil',
    icon: UserIcon,
    component: 'a'
  }
];

// Computed
const currentPage = computed(() => {
  const url = window.location.pathname;
  return menuItems.find(item => item.href === url)?.name || '';
});

// Methods
const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);
};

const isCurrentPage = (href) => {
  return window.location.pathname === href;
};

const handleMenuClick = () => {
  // Close sidebar on mobile after clicking a menu item
  if (window.innerWidth < 768) {
    emit('update:sidebarOpen', false);
  }
};

// Watch for route changes to update active state
router.on('navigate', () => {
  // Force re-render to update active state
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #111111;
}

::-webkit-scrollbar-thumb {
  background: #333333;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #444444;
}
</style>