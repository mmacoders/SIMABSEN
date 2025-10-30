<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Mobile sidebar overlay -->
    <div 
      v-if="sidebarOpen && !isSidebarCollapsed" 
      class="fixed inset-0 bg-black/30 z-20 md:hidden transition-opacity duration-300"
      @click="sidebarOpen = false"
    ></div>

    <!-- Mobile sidebar toggle button -->
    <button 
      class="md:hidden fixed top-4 left-4 z-30 p-2 rounded-md bg-white shadow-md text-gray-600 hover:text-[#dc2626] transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#dc2626]/30"
      @click="toggleSidebar"
      aria-label="Toggle sidebar"
    >
      <MenuIcon class="w-6 h-6" />
    </button>

    <!-- Sidebar -->
    <AdminSidebar 
      :sidebarOpen="sidebarOpen" 
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarToggle"
    />

    <!-- Main Content -->
    <div 
      class="flex-1 flex flex-col transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? (isSidebarCollapsed ? 'md:ml-20' : 'md:ml-64') : 'ml-0'"
    >
      <!-- Header -->
      <AdminHeader 
        :title="pageTitle || 'Dashboard'"
        :mobile-title="mobilePageTitle || 'Dashboard'"
        role="Admin"
        :user-name="$page.props.auth.user.name"
        :user-email="$page.props.auth.user.email"
        :user-profile-pic="$page.props.auth.user.profile_pict"
      >
        <template #header>
          <slot name="header" />
        </template>
      </AdminHeader>

      <!-- Page Content -->
      <main class="flex-1 bg-gray-50 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import AdminHeader from '@/Components/AdminHeader.vue';
import { MenuIcon } from 'lucide-vue-next';

// Props for page title
const props = defineProps({
  pageTitle: {
    type: String,
    default: ''
  },
  mobilePageTitle: {
    type: String,
    default: ''
  }
});

// State
const sidebarOpen = ref(true);
const isSidebarCollapsed = ref(false);

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarToggle = (collapsed) => {
  isSidebarCollapsed.value = collapsed;
};

// Close sidebar when clicking on overlay on mobile
const closeSidebarOnOverlayClick = (event) => {
  if (!sidebarOpen.value) return;
  
  const sidebar = document.querySelector('aside');
  if (sidebar && !sidebar.contains(event.target) && window.innerWidth < 768) {
    sidebarOpen.value = false;
  }
};

// Lifecycle
onMounted(() => {
  // Listen for sidebar toggle events
  document.addEventListener('toggle-sidebar', toggleSidebar);
  document.addEventListener('click', closeSidebarOnOverlayClick);
});

onUnmounted(() => {
  document.removeEventListener('toggle-sidebar', toggleSidebar);
  document.removeEventListener('click', closeSidebarOnOverlayClick);
});
</script>