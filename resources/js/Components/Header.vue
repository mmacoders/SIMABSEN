<template>
  <header class="top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 shadow-[0_2px_10px_rgba(0,0,0,0.08)] backdrop-blur-md z-40 flex items-center justify-between px-6">
    <!-- Left Section -->
    <div class="flex items-center gap-4">
      <!-- Sidebar Toggle Button -->
      <button 
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-lg hover:bg-gray-100 transition-all duration-200 ease-in-out"
        aria-label="Toggle sidebar"
      >
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>

      <!-- Breadcrumb Navigation -->
      <nav class="hidden md:block">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <a href="/user/dashboard" class="text-gray-500 hover:text-[#C62828] transition-colors">Dashboard</a>
          </li>
          <li>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </li>
          <li>
            <span class="text-[#C62828] font-medium">{{ currentPage }}</span>
          </li>
        </ol>
      </nav>
      
      <!-- Mobile Breadcrumb -->
      <div class="md:hidden">
        <span class="text-[#C62828] font-medium text-sm">{{ mobileBreadcrumb }}</span>
      </div>
    </div>

    <!-- Right Section -->
    <div class="flex items-center gap-4">
      <!-- User Profile -->
      <div class="relative">
        <button 
          @click="toggleDropdown"
          class="flex items-center gap-2 focus:outline-none"
          aria-label="User menu"
        >
          <div class="w-8 h-8 rounded-full bg-gray-200 border-2 border-white shadow-sm overflow-hidden">
            <img 
              src="/avatar.png" 
              alt="User avatar" 
              class="w-full h-full object-cover hover:shadow-[0_0_0_2px_rgba(198,40,40,0.2)] transition-all duration-200"
            >
          </div>
          <span class="hidden md:inline text-gray-700 font-medium text-sm">{{ user.name }}</span>
          <svg 
            class="hidden md:inline w-4 h-4 text-gray-500 transition-transform duration-200" 
            :class="{ 'rotate-180': isDropdownOpen }"
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24" 
            xmlns="http://www.w3.org/2000/svg"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div 
          v-if="isDropdownOpen"
          class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg py-2 border border-gray-100 transition-all duration-200 origin-top-right"
          :class="{ 'opacity-100 scale-100': isDropdownOpen, 'opacity-0 scale-95': !isDropdownOpen }"
        >
          <a 
            href="/user/profil" 
            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Profil Saya
          </a>
          <a 
            href="#" 
            class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
            @click.prevent="logout"
          >
            Logout
          </a>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

// Define props
const props = defineProps({
  user: Object,
  currentPage: String,
  mobileBreadcrumb: String
});

// Define emits
defineEmits(['toggle-sidebar']);

// Dropdown state
const isDropdownOpen = ref(false);

// Toggle dropdown
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

// Close dropdown when clicking outside
document.addEventListener('click', (event) => {
  const dropdown = document.querySelector('.relative');
  if (dropdown && !dropdown.contains(event.target)) {
    isDropdownOpen.value = false;
  }
});

// Logout function
  const logout = () => {
    router.post(route('logout'));
  };
  </script>