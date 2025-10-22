<template>
  <aside 
    class="bg-[#111827] text-white flex flex-col fixed h-screen transition-all ease-in-out duration-300 shadow-xl z-30 font-['Inter']"
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
        class="p-2 rounded-lg hover:bg-[#1E1E1E] transition-colors duration-200"
        :class="{ 'ml-auto': !isCollapsed }"
      >
        <ChevronLeftIcon v-if="!isCollapsed" class="w-5 h-5" />
        <ChevronRightIcon v-else class="w-5 h-5" />
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
              'flex items-center gap-3 px-3 py-3 rounded-xl transition-all duration-300 group',
              isCurrentPage(item.href) 
                ? 'bg-[#dc2626] text-white font-semibold' 
                : 'hover:bg-[#dc2626]/30 text-gray-300'
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
              class="absolute left-full ml-3 px-2 py-1 bg-[#111827] text-white text-xs rounded-md shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50"
            >
              {{ item.name }}
              <div class="absolute top-1/2 -left-1 transform -translate-y-1/2 w-2 h-2 bg-[#111827] rotate-45"></div>
            </div>
          </component>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
  HomeIcon, 
  BarChart3Icon, 
  ClipboardListIcon,
  UsersIcon,
  ChevronLeftIcon,
  ChevronRightIcon
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

// Menu items for Admin
const menuItems = [
  {
    name: 'Dashboard',
    href: '/admin/dashboard',
    icon: HomeIcon,
    component: 'a'
  },
  {
    name: 'Laporan Absensi',
    href: '/admin/laporan',
    icon: BarChart3Icon,
    component: 'a'
  },
  {
    name: 'Izin & Cuti',
    href: '/admin/izin',
    icon: ClipboardListIcon,
    component: 'a'
  },
  {
    name: 'Data Pegawai',
    href: '/admin/pegawai',
    icon: UsersIcon,
    component: 'a'
  }
];

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
  background: #111827;
}

::-webkit-scrollbar-thumb {
  background: #333333;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #444444;
}
</style>