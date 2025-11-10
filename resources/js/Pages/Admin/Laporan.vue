<template>
    <AdminLayout page-title="Laporan Absensi" mobile-page-title="Laporan">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Title + Controls -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Title + Description -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileTextIcon class="text-red-600" />
                        Laporan Absensi
                    </h1>
                    <p class="text-gray-600 mt-2">Rekapitulasi absensi seluruh pegawai POLDA TIK.</p>
                </div>

                <!-- Right: Search + Filter + Export -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Bar -->
                    <div class="relative">
                        <SearchIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari laporan..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64"
                            @input="handleSearch"
                        />
                    </div>

                    <!-- Filter Button -->
                    <div class="relative">
                        <button
                            ref="filterButton"
                            @click="showFilter = !showFilter"
                            class="px-3 py-2 border rounded-lg flex items-center space-x-2"
                            :class="isFilterActive ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:bg-gray-50'"
                        >
                            <FilterIcon class="h-5 w-5" :class="isFilterActive ? 'text-red-600' : 'text-gray-500'" />
                            <span>Rentang Waktu</span>
                        </button>

                        <!-- Filter Popover -->
                        <div 
                            v-if="showFilter" 
                            ref="filterPopover"
                            class="absolute right-0 mt-2 bg-white shadow-lg rounded-xl border p-4 z-50 w-80 max-w-sm"
                        >
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                    <input
                                        type="date"
                                        v-model="filters.start_date"
                                        class="w-full border border-gray-300 rounded-lg p-2"
                                    />
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                    <input
                                        type="date"
                                        v-model="filters.end_date"
                                        class="w-full border border-gray-300 rounded-lg p-2"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button
                                    @click="resetFilters"
                                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200"
                                >
                                    Reset
                                </button>
                                <button
                                    @click="applyFilters"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200"
                                >
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Export Dropdown -->
                    <div class="relative">
                        <button
                            @click="showExportDropdown = !showExportDropdown"
                            class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center"
                            title="Export"
                        >
                            <FileDownIcon class="h-5 w-5" />
                        </button>

                        <!-- Export Dropdown Menu -->
                        <div 
                            v-if="showExportDropdown" 
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                        >
                            <button
                                @click="exportToExcel"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                            >
                                <FileSpreadsheetIcon class="h-4 w-4 mr-2" />
                                Export ke Excel
                            </button>
                            <button
                                @click="exportToPDF"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                            >
                                <FileTextIcon class="h-4 w-4 mr-2" />
                                Export ke PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-red-600 text-white text-left">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Nama</th>
                                <th class="px-6 py-3">Jam Masuk</th>
                                <th class="px-6 py-3">Jam Keluar</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="attendance in attendancesData" :key="attendance.id" class="border-b hover:bg-red-50">
                                <td class="px-6 py-3 text-gray-700">{{ formatDate(attendance.tanggal) }}</td>
                                <td class="px-6 py-3 font-medium text-gray-900">{{ attendance.user?.name || '-' }}</td>
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
                            <tr v-if="!attendancesData || attendancesData.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data absensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination (only show if attendances is paginated) -->
                <div v-if="attendances && attendances.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="fetchPage(attendances.prev_page_url)" 
                            :disabled="!attendances.prev_page_url"
                            :class="attendances.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Sebelumnya
                        </button>
                        <button 
                            @click="fetchPage(attendances.next_page_url)" 
                            :disabled="!attendances.next_page_url"
                            :class="attendances.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Berikutnya
                        </button>
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
                                    <button v-else-if="link.label === 'pagination.previous'" 
                                       @click="fetchPage(link.url)"
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                        <ChevronLeftIcon class="h-5 w-5" />
                                    </button>
                                    <button v-else-if="link.label === 'pagination.next'" 
                                       @click="fetchPage(link.url)"
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                        <ChevronRightIcon class="h-5 w-5" />
                                    </button>
                                    <button v-else 
                                       @click="fetchPage(link.url)"
                                       :class="link.active ? 'z-10 bg-red-600 text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-100 relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg'">
                                        {{ link.label }}
                                    </button>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    FileSpreadsheetIcon,
    FileDownIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    FileTextIcon,
    SearchIcon,
    FilterIcon
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
    attendances: [Array, Object],
    startDate: String,
    endDate: String,
});

// State
const showFilter = ref(false);
const showExportDropdown = ref(false);
const searchQuery = ref('');
const filterButton = ref(null);
const filterPopover = ref(null);

const filters = ref({
    start_date: props.startDate || '',
    end_date: props.endDate || '',
});

// Computed properties
const isFilterActive = computed(() => {
    return filters.value.start_date || filters.value.end_date;
});

// Computed property to handle both array and paginated object formats
const attendancesData = computed(() => {
    if (!props.attendances) return [];
    
    // If it's an array, return it directly
    if (Array.isArray(props.attendances)) {
        return props.attendances;
    }
    
    // If it's a paginated object, return the data array
    if (props.attendances.data && Array.isArray(props.attendances.data)) {
        return props.attendances.data;
    }
    
    return [];
});

// Methods
const handleSearch = debounce(() => {
    // We can implement search functionality here if needed
    // For now, we'll just close any open dropdowns
    showExportDropdown.value = false;
}, 300);

const applyFilters = () => {
    router.get(route('admin.laporan'), {
        ...filters.value
    }, {
        preserveState: true,
        replace: true
    });
    showFilter.value = false;
};

const resetFilters = () => {
    filters.value = {
        start_date: '',
        end_date: '',
    };
    applyFilters();
};

const exportToExcel = () => {
    router.post(route('admin.laporan.export'), {
        ...filters.value,
        format: 'excel'
    });
    showExportDropdown.value = false;
};

const exportToPDF = () => {
    router.post(route('admin.laporan.export'), {
        ...filters.value,
        format: 'pdf'
    });
    showExportDropdown.value = false;
};

const fetchPage = (url) => {
    if (url) {
        router.get(url);
    }
};

// Format date to Indonesian format
const formatDate = (dateString) => {
    if (!dateString) return '-';
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
    if (!attendance) return 'Belum Absen';
    
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

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
    // Close export dropdown
    if (showExportDropdown.value && 
        !event.target.closest('[title="Export"]') && 
        !event.target.closest('.absolute.right-0.mt-2.w-48')) {
        showExportDropdown.value = false;
    }
    
    // Close filter popover
    if (showFilter.value && 
        filterButton.value && 
        filterPopover.value && 
        !filterButton.value.contains(event.target) && 
        !filterPopover.value.contains(event.target)) {
        showFilter.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>