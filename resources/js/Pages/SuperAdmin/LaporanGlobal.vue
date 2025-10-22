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
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Global</h2>
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
                                        <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <UserIcon class="h-4 w-4 inline mr-2" />
                                            Profil
                                        </Link>
                                        <button @click="logout" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <LogOutIcon class="h-4 w-4 inline mr-2" />
                                            Keluar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-8">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Success/Error Messages -->
                    <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ $page.props.flash.error }}
                    </div>

                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2 mb-2">
                            <FileTextIcon class="text-red-600" />
                            Laporan Global Absensi
                        </h1>
                        <p class="text-gray-600">Rekapitulasi absensi seluruh pegawai POLDA TIK.</p>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bidang</label>
                                <select
                                    v-model="filters.bidang_id"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                    @change="applyFilters"
                                >
                                    <option value="">Semua Bidang</option>
                                    <option v-for="bidang in bidangs" :key="bidang.id" :value="bidang.id">
                                        {{ bidang.nama_bidang }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <input
                                    type="date"
                                    v-model="filters.start_date"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                    @change="applyFilters"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                <input
                                    type="date"
                                    v-model="filters.end_date"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                    @change="applyFilters"
                                />
                            </div>
                            
                            <div class="md:col-span-3 flex justify-end gap-2 mt-2">
                                <button
                                    @click="resetFilters"
                                    class="border border-red-500 text-red-500 px-4 py-2 rounded-lg hover:bg-red-50 transition-all duration-200 ease-in-out"
                                >
                                    Reset
                                </button>
                                <button
                                    @click="exportToExcel"
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-all duration-200 ease-in-out flex items-center"
                                >
                                    <FileSpreadsheetIcon class="h-5 w-5 mr-2" />
                                    Excel
                                </button>
                                <button
                                    @click="exportToPDF"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200 ease-in-out flex items-center"
                                >
                                    <FileDownIcon class="h-5 w-5 mr-2" />
                                    PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Table -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-red-600 text-white text-left">
                                    <tr>
                                        <th class="px-6 py-3">Nama</th>
                                        <th class="px-6 py-3">Bidang</th>
                                        <th class="px-6 py-3">Tanggal</th>
                                        <th class="px-6 py-3">Masuk</th>
                                        <th class="px-6 py-3">Keluar</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="attendance in attendances.data" :key="attendance.id" class="border-b hover:bg-red-50">
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ attendance.user.name }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ attendance.user.bidang?.nama_bidang || '-' }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ formatDate(attendance.tanggal) }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ attendance.waktu_masuk || '-' }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ attendance.waktu_keluar || '-' }}</td>
                                        <td class="px-6 py-3">
                                            <span
                                                :class="getStatusClass(attendance)"
                                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                            >
                                                {{ getStatusText(attendance) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">
                                            {{ attendance.keterangan || '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data absensi
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a :href="attendances.prev_page_url" 
                                   :class="attendances.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                                    Sebelumnya
                                </a>
                                <a :href="attendances.next_page_url" 
                                   :class="attendances.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                                    Berikutnya
                                </a>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Menampilkan
                                        <span class="font-medium">{{ attendances.from || 0 }}</span>
                                        sampai
                                        <span class="font-medium">{{ attendances.to || 0 }}</span>
                                        dari
                                        <span class="font-medium">{{ attendances.total }}</span>
                                        hasil
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                                        <template v-for="(link, index) in attendances.links" :key="index">
                                            <span v-if="link.url === null" 
                                                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed rounded-lg">
                                                {{ link.label }}
                                            </span>
                                            <a v-else-if="link.label === 'pagination.previous'" 
                                               :href="link.url"
                                               class="relative inline-flex items-center px-2 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                                <ChevronLeftIcon class="h-5 w-5" />
                                            </a>
                                            <a v-else-if="link.label === 'pagination.next'" 
                                               :href="link.url"
                                               class="relative inline-flex items-center px-2 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                                <ChevronRightIcon class="h-5 w-5" />
                                            </a>
                                            <a v-else 
                                               :href="link.url"
                                               :class="link.active ? 'z-10 bg-red-600 text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-100 relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg'">
                                                {{ link.label }}
                                            </a>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import {
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogOutIcon,
    FileSpreadsheetIcon,
    FileDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    MenuIcon,
    FileTextIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    attendances: Object,
    bidangs: Array,
});

// State
const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const filters = ref({
    bidang_id: '',
    start_date: '',
    end_date: '',
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

// Logout function
const logout = () => {
    router.post(route('logout'));
};

const applyFilters = () => {
    router.get(route('superadmin.laporan'), {
        ...filters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    filters.value = {
        bidang_id: '',
        start_date: '',
        end_date: '',
    };
    applyFilters();
};

const exportToExcel = () => {
    router.post(route('superadmin.laporan.export.excel'), {
        ...filters.value
    });
};

const exportToPDF = () => {
    router.post(route('superadmin.laporan.export.pdf'), {
        ...filters.value
    });
};

// Format date to Indonesian format
const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Get status class based on attendance status
const getStatusClass = (attendance) => {
    // Use the actual status values from the database
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'bg-blue-100 text-blue-700';
        case 'sakit':
            return 'bg-yellow-100 text-yellow-700';
        case 'terlambat':
        case 'Terlambat':
            return 'bg-orange-100 text-orange-700';
        case 'hadir':
        case 'Hadir':
            // For 'hadir' status, check if the user has actually checked in
            return attendance.waktu_masuk ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700';
        case 'Izin Parsial (Check-in)':
        case 'Izin Parsial (Selesai)':
            return 'bg-purple-100 text-purple-700';
        default:
            // Handle any other status values or null status
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'bg-green-100 text-green-700'; // Completed attendance
            } else if (attendance.waktu_masuk) {
                return 'bg-blue-100 text-blue-700'; // Check-in only
            } else {
                return 'bg-gray-200 text-gray-700'; // No attendance
            }
    }
};

// Get status text based on attendance status
const getStatusText = (attendance) => {
    // Use the actual status values from the database
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'Izin';
        case 'sakit':
            return 'Sakit';
        case 'terlambat':
        case 'Terlambat':
            return 'Terlambat';
        case 'hadir':
        case 'Hadir':
            // For 'hadir' status, check if the user has actually checked in
            return attendance.waktu_masuk ? 'Hadir' : 'Belum Absen';
        case 'Izin Parsial (Check-in)':
            return 'Izin Parsial (Check-in)';
        case 'Izin Parsial (Selesai)':
            return 'Izin Parsial (Selesai)';
        default:
            // Handle any other status values or null status
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'Hadir';
            } else if (attendance.waktu_masuk) {
                return 'Sudah Check-in';
            } else {
                return attendance.status || 'Belum Absen';
            }
    }
};

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
    if (profileMenuOpen.value && !event.target.closest('.relative')) {
        profileMenuOpen.value = false;
    }
});
</script>