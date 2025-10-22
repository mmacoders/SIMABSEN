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
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Super Admin</h2>
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
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Selamat datang, Super Admin</h1>
                        <p class="text-gray-600">Sistem Manajemen Absensi POLDA TIK</p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-blue-100">
                                    <UsersIcon class="h-6 w-6 text-blue-600" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Pegawai</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ totalUsers }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-green-100">
                                    <ShieldIcon class="h-6 w-6 text-green-600" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Admin</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ totalAdmins }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-yellow-100">
                                    <BuildingIcon class="h-6 w-6 text-yellow-600" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Total Bidang</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ totalBidangs }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition">
                            <div class="flex items-center">
                                <div class="p-3 rounded-lg bg-purple-100">
                                    <CheckCircleIcon class="h-6 w-6 text-purple-600" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Hadir Hari Ini</p>
                                    <p class="text-2xl font-bold text-[#C62828]">{{ presentToday }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Attendance Chart -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Kehadiran Minggu Ini</h3>
                            <div class="h-80">
                                <apexchart 
                                    type="area" 
                                    :options="attendanceChartOptions" 
                                    :series="attendanceChartSeries"
                                    height="100%"
                                />
                            </div>
                        </div>
                        
                        <!-- Bidang Distribution Chart -->
                        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Pegawai per Bidang</h3>
                            <div class="h-80">
                                <apexchart 
                                    type="donut" 
                                    :options="bidangChartOptions" 
                                    :series="bidangChartSeries"
                                    height="100%"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Attendance by Bidang -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rekap Absensi per Bidang</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div
                                v-for="bidang in bidangStats"
                                :key="bidang.id"
                                class="bg-white rounded-2xl shadow-lg p-5 border border-gray-100 hover:shadow-xl transition"
                            >
                                <div class="flex items-center mb-3">
                                    <div class="p-2 rounded-lg bg-red-50">
                                        <BuildingIcon class="h-5 w-5 text-[#C62828]" />
                                    </div>
                                    <h4 class="font-semibold text-gray-900 ml-3">{{ bidang.nama_bidang }}</h4>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Total Pegawai:</span>
                                        <span class="text-sm font-medium">{{ bidang.total_users }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Hadir Hari Ini:</span>
                                        <span class="text-sm font-medium text-green-600">{{ bidang.present_today }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tidak Hadir:</span>
                                        <span class="text-sm font-medium text-red-600">{{ bidang.total_users - bidang.present_today }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Link
                                :href="route('superadmin.admin')"
                                class="block text-center px-4 py-4 bg-[#C62828] text-white rounded-xl hover:bg-[#b71c1c] transition flex items-center justify-center"
                            >
                                <ShieldIcon class="h-5 w-5 mr-2" />
                                Kelola Admin
                            </Link>
                            <Link
                                :href="route('superadmin.laporan')"
                                class="block text-center px-4 py-4 bg-[#1976D2] text-white rounded-xl hover:bg-[#1565C0] transition flex items-center justify-center"
                            >
                                <FileTextIcon class="h-5 w-5 mr-2" />
                                Laporan Global
                            </Link>
                            <Link
                                :href="route('superadmin.settings')"
                                class="block text-center px-4 py-4 bg-[#4CAF50] text-white rounded-xl hover:bg-[#388E3C] transition flex items-center justify-center"
                            >
                                <SettingsIcon class="h-5 w-5 mr-2" />
                                Pengaturan Sistem
                            </Link>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import VueApexCharts from "vue3-apexcharts";
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import {
    UsersIcon,
    ShieldIcon,
    BuildingIcon,
    CheckCircleIcon,
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogOutIcon,
    FileTextIcon,
    SettingsIcon,
    MenuIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    totalUsers: Number,
    totalAdmins: Number,
    totalBidangs: Number,
    presentToday: Number,
    absentToday: Number,
    bidangStats: Array,
    weeklyAttendance: Object,
});

// State
const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);

// Chart data
const attendanceChartOptions = computed(() => ({
    chart: {
        type: 'area',
        toolbar: {
            show: false
        },
        zoom: {
            enabled: false
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth',
        width: 2
    },
    xaxis: {
        categories: props.weeklyAttendance?.dates || [],
        axisBorder: {
            show: false
        },
        axisTicks: {
            show: false
        }
    },
    yaxis: {
        min: 0,
        labels: {
            formatter: function (val) {
                return Math.floor(val);
            }
        }
    },
    tooltip: {
        x: {
            format: 'dd/MM/yy'
        },
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    },
    colors: ['#1976D2', '#4CAF50'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.3,
            stops: [0, 90, 100]
        }
    }
}));

const attendanceChartSeries = computed(() => [
    {
        name: 'Hadir',
        data: props.weeklyAttendance?.present || []
    },
    {
        name: 'Tidak Hadir',
        data: props.weeklyAttendance?.absent || []
    }
]);

const bidangChartOptions = computed(() => ({
    chart: {
        type: 'donut',
    },
    labels: props.bidangStats?.map(bidang => bidang.nama_bidang) || [],
    colors: ['#1976D2', '#4CAF50', '#FF9800', '#9C27B0'],
    legend: {
        position: 'bottom'
    },
    dataLabels: {
        enabled: false
    },
    plotOptions: {
        pie: {
            donut: {
                size: '70%'
            }
        }
    }
}));

const bidangChartSeries = computed(() => 
    props.bidangStats?.map(bidang => bidang.total_users) || []
);

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

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
    if (profileMenuOpen.value && !event.target.closest('.relative')) {
        profileMenuOpen.value = false;
    }
});
</script>