<template>
    <div class="flex min-h-screen bg-[#F5F6FA]">
        <!-- Sidebar -->
        <SuperAdminSidebar 
          :sidebar-open="sidebarOpen"
          @update:sidebarOpen="sidebarOpen = $event"
          @toggle-collapse="handleSidebarCollapse"
        />
        
        <!-- Main Content -->
        <div class="flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
            <!-- Header -->
            <header class="bg-white shadow-md z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <div class="flex items-center">
                            <button 
                                @click="toggleSidebar" 
                                class="md:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 mr-2"
                            >
                                <MenuIcon class="h-6 w-6" />
                            </button>
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pengaturan Sistem</h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="relative">
                                <button class="p-2 rounded-full hover:bg-gray-100">
                                    <BellIcon class="h-5 w-5 text-gray-600" />
                                </button>
                            </div>
                            <div class="relative">
                                <button @click="toggleProfileMenu" class="flex items-center space-x-2 focus:outline-none">
                                    <div class="h-8 w-8 rounded-full bg-[#C62828] flex items-center justify-center text-white font-semibold">
                                        SA
                                    </div>
                                    <span class="text-gray-700 hidden md:block">Super Admin</span>
                                    <ChevronDownIcon class="h-4 w-4 text-gray-500 hidden md:block" />
                                </button>
                                <!-- Profile Dropdown -->
                                <div v-if="profileMenuOpen" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                                    <div class="py-1">
                                        <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <UserIcon class="h-4 w-4 inline mr-2" />
                                            Profil
                                        </a>
                                        <a href="/logout" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <LogOutIcon class="h-4 w-4 inline mr-2" />
                                            Keluar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Toast Container -->
                    <div id="toast-container"></div>

                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h1>
                        <p class="text-gray-600">Konfigurasi pengaturan global aplikasi absensi</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Settings Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                                <form @submit.prevent="saveSettings">
                                    <!-- Location Settings -->
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Kantor</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="location_latitude" value="Latitude" />
                                                <TextInput
                                                    id="location_latitude"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    v-model="settingsForm.location_latitude"
                                                    :disabled="loading"
                                                />
                                                <div v-if="errors.location_latitude" class="mt-2 text-sm text-red-600">
                                                    {{ errors.location_latitude[0] }}
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <InputLabel for="location_longitude" value="Longitude" />
                                                <TextInput
                                                    id="location_longitude"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    v-model="settingsForm.location_longitude"
                                                    :disabled="loading"
                                                />
                                                <div v-if="errors.location_longitude" class="mt-2 text-sm text-red-600">
                                                    {{ errors.location_longitude[0] }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <InputLabel for="location_radius" value="Radius Presensi (meter)" />
                                            <input
                                                id="location_radius"
                                                type="range"
                                                min="10"
                                                max="1000"
                                                step="10"
                                                class="w-full mt-1"
                                                v-model.number="settingsForm.location_radius"
                                                :disabled="loading"
                                            />
                                            <div class="flex justify-between text-sm text-gray-600 mt-1">
                                                <span>10m</span>
                                                <span class="font-medium">{{ settingsForm.location_radius }}m</span>
                                                <span>1000m</span>
                                            </div>
                                            <div v-if="errors.location_radius" class="mt-2 text-sm text-red-600">
                                                {{ errors.location_radius[0] }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Work Hours Settings -->
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jam Kerja</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="jam_masuk" value="Jam Masuk" />
                                                <input
                                                    id="jam_masuk"
                                                    type="time"
                                                    class="mt-1 block w-full border-gray-300 focus:border-[#C62828] focus:ring-[#C62828] rounded-md shadow-sm"
                                                    v-model="settingsForm.jam_masuk"
                                                    :disabled="loading"
                                                />
                                                <div v-if="errors.jam_masuk" class="mt-2 text-sm text-red-600">
                                                    {{ errors.jam_masuk[0] }}
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <InputLabel for="jam_pulang" value="Jam Pulang" />
                                                <input
                                                    id="jam_pulang"
                                                    type="time"
                                                    class="mt-1 block w-full border-gray-300 focus:border-[#C62828] focus:ring-[#C62828] rounded-md shadow-sm"
                                                    v-model="settingsForm.jam_pulang"
                                                    :disabled="loading"
                                                />
                                                <div v-if="errors.jam_pulang" class="mt-2 text-sm text-red-600">
                                                    {{ errors.jam_pulang[0] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Save Button -->
                                    <div class="flex items-center justify-end">
                                        <PrimaryButton 
                                            :disabled="loading"
                                            class="bg-[#C62828] hover:bg-[#b71c1c] px-6 py-3 transition-all duration-300"
                                        >
                                            <Spinner v-if="loading" class="h-5 w-5 mr-2" />
                                            <SaveIcon v-else class="h-5 w-5 mr-2" />
                                            <span v-if="loading">Menyimpan...</span>
                                            <span v-else>Simpan Pengaturan</span>
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Map Preview -->
                        <div>
                            <MapPreview 
                                :latitude="settingsForm.location_latitude"
                                :longitude="settingsForm.location_longitude"
                                :radius="settingsForm.location_radius"
                            />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import MapPreview from '@/Components/MapPreview.vue';
import Spinner from '@/Components/Spinner.vue';
import {
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogOutIcon,
    SaveIcon,
    MenuIcon
} from 'lucide-vue-next';

// State
const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const loading = ref(false);
const errors = ref({});

// Form - initialize with empty values to be populated from API
const settingsForm = ref({
    location_latitude: '',
    location_longitude: '',
    location_radius: 100,
    jam_masuk: '',
    jam_pulang: '',
});

// Methods
const toggleProfileMenu = () => {
    profileMenuOpen.value = !profileMenuOpen.value;
};

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
    sidebarCollapsed.value = collapsed;
};

// Load settings from API
const loadSettings = async () => {
    try {
        const response = await axios.get('/api/system-settings');
        console.log('API Response:', response.data);
        if (response.data) {
            // For SystemSetting model, the data is returned directly
            // For other responses, it might be wrapped in a data property
            const settingsData = response.data.data || response.data;
            
            // Map the API response to our form fields
            settingsForm.value.location_latitude = settingsData.location_latitude !== null ? 
                settingsData.location_latitude.toString() : '';
            settingsForm.value.location_longitude = settingsData.location_longitude !== null ? 
                settingsData.location_longitude.toString() : '';
            settingsForm.value.location_radius = parseInt(settingsData.location_radius) || 100;
            
            // Handle time formatting for input fields (convert HH:mm:ss to HH:mm)
            if (settingsData.jam_masuk) {
                const timeStr = settingsData.jam_masuk.toString();
                settingsForm.value.jam_masuk = timeStr.length >= 5 ? timeStr.substring(0, 5) : timeStr;
            } else {
                settingsForm.value.jam_masuk = '';
            }
            
            if (settingsData.jam_pulang) {
                const timeStr = settingsData.jam_pulang.toString();
                settingsForm.value.jam_pulang = timeStr.length >= 5 ? timeStr.substring(0, 5) : timeStr;
            } else {
                settingsForm.value.jam_pulang = '';
            }
        }
        console.log('Form data after loading:', settingsForm.value);
    } catch (error) {
        console.error('Error loading settings:', error);
        // Initialize with default values if API fails
    }
};

// Save settings to API
const saveSettings = async () => {
    try {
        loading.value = true;
        errors.value = {};
        
        // Log the data being sent
        console.log('Sending data:', settingsForm.value);
        
        const response = await axios.put('/api/system-settings', settingsForm.value);
        
        console.log('Save response:', response.data);
        
        if (response.data.success) {
            toast.success(response.data.message);
            await loadSettings(); // Refresh data
        } else {
            toast.error('Gagal menyimpan pengaturan.');
        }
    } catch (error) {
        console.error('Error saving settings:', error);
        if (error.response && error.response.status === 422) {
            // Validation errors
            errors.value = error.response.data.errors;
            toast.error('Validasi gagal. Periksa kembali data yang dimasukkan.');
        } else {
            toast.error('Gagal menyimpan pengaturan.');
        }
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(() => {
    loadSettings();
});

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
    if (profileMenuOpen.value && !event.target.closest('.relative')) {
        profileMenuOpen.value = false;
    }
});
</script>

<style scoped>
/* Add any additional styles here if needed */
</style>