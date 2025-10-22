<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AdminSidebar from '@/Components/AdminSidebar.vue';
import AdminHeader from '@/Components/AdminHeader.vue';
import { MenuIcon } from 'lucide-vue-next';

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

// Lifecycle
onMounted(() => {
  // Listen for sidebar toggle events
  document.addEventListener('toggle-sidebar', toggleSidebar);
});

onUnmounted(() => {
  document.removeEventListener('toggle-sidebar', toggleSidebar);
});
</script>

<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Mobile sidebar toggle button -->
    <button 
      class="md:hidden fixed top-4 left-4 z-50 p-2 rounded-md bg-white shadow-md text-gray-600 hover:text-red-600"
      @click="toggleSidebar"
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
      class="flex-1 transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? (isSidebarCollapsed ? 'md:ml-20' : 'md:ml-64') : 'ml-0'"
    >
      <!-- Header -->
      <AdminHeader 
        :title="$slots.header ? '' : 'Dashboard'"
        role="Admin"
        :user-name="$page.props.auth.user.name"
        :user-email="$page.props.auth.user.email"
      >
        <template #header>
          <slot name="header" />
        </template>
      </AdminHeader>

      <!-- Page Content -->
      <main>
        <slot />
      </main>
    </div>
  </div>
</template>