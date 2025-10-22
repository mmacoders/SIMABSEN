<template>
  <header class="flex justify-between items-center bg-white border-b border-gray-200 px-6 py-3 shadow-sm">
    <div class="flex items-center gap-3">
      <button 
        class="text-gray-600 hover:text-red-600 md:hidden" 
        @click="toggleSidebar"
      >
        <MenuIcon class="w-6 h-6" />
      </button>
      <h1 class="text-lg font-semibold text-gray-800">{{ title }}</h1>
    </div>
    <div class="flex items-center gap-4">
      <p class="text-sm text-gray-700 font-medium">{{ role }}</p>
      <div class="relative" ref="dropdownContainer">
        <button 
          class="flex items-center gap-2 focus:outline-none"
          @click="toggleDropdown"
        >
          <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center text-white font-semibold">
            {{ userInitials }}
          </div>
          <ChevronDownIcon class="w-4 h-4 text-gray-500" />
        </button>
        <!-- Dropdown -->
        <div 
          v-if="dropdownOpen" 
          class="absolute right-0 mt-2 bg-white border rounded-lg shadow-lg py-2 w-48 z-50"
        >
          <div class="px-4 py-2 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900">{{ userName }}</p>
            <p class="text-xs text-gray-500">{{ userEmail }}</p>
          </div>
          <a 
            href="/profile" 
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            Profil
          </a>
          <button
            @click="logout"
            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          >
            Keluar
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
  MenuIcon, 
  ChevronDownIcon 
} from 'lucide-vue-next';

// Props
const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard'
  },
  role: {
    type: String,
    default: 'Admin'
  },
  userName: {
    type: String,
    default: 'Admin User'
  },
  userEmail: {
    type: String,
    default: 'admin@example.com'
  }
});

// State
const dropdownOpen = ref(false);
const dropdownContainer = ref(null);

// Computed
const userInitials = computed(() => {
  return props.userName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});

// Methods
const toggleSidebar = () => {
  // This would typically emit an event to toggle the sidebar in the parent component
  document.dispatchEvent(new CustomEvent('toggle-sidebar'));
};

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const logout = () => {
  router.post('/logout');
};

// Click outside handler
const handleClickOutside = (event) => {
  if (dropdownContainer.value && !dropdownContainer.value.contains(event.target)) {
    dropdownOpen.value = false;
  }
};

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>