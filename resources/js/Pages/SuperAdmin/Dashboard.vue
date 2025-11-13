<template>
  <SuperAdminLayout 
    page-title="Dashboard"
    mobile-page-title="Dashboard"
  >
    <div class="max-w-7xl mx-auto">
      <div class="mb-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Selamat datang, Super Admin</h2>
        <p class="text-gray-600 mt-2">Sistem Manajemen Absensi POLDA TIK</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="p-4 rounded-xl bg-blue-100">
              <UsersIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-600">Total Pegawai</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ totalUsers }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="p-4 rounded-xl bg-green-100">
              <ShieldIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-600">Total Admin</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ totalAdmins }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="p-4 rounded-xl bg-purple-100">
              <CheckCircleIcon class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-600">Hadir Hari Ini</p>
              <p class="text-3xl font-bold text-[#dc2626] mt-1">{{ presentToday }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1">
          <div class="flex items-center">
            <div class="p-4 rounded-xl bg-orange-100">
              <BellIcon class="h-8 w-8 text-orange-600" />
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-600">Terlambat Hari Ini</p>
              <p class="text-3xl font-bold text-[#dc2626] mt-1">{{ lateToday }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Attendance Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
              <TrendingUpIcon class="w-6 h-6 text-[#dc2626] mr-2" />
              Statistik Kehadiran Minggu Ini
            </h3>
          </div>
          <div class="h-80">
            <apexchart 
              type="area" 
              :options="attendanceChartOptions" 
              :series="attendanceChartSeries"
              height="100%"
            />
          </div>
        </div>
        
        <!-- Jabatan Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
              <PieChartIcon class="w-6 h-6 text-[#dc2626] mr-2" />
              Distribusi Pegawai per Jabatan
            </h3>
          </div>
          <div class="h-80">
            <apexchart 
              type="donut" 
              :options="jabatanChartOptions" 
              :series="jabatanChartSeries"
              height="100%"
            />
          </div>
        </div>
      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import VueApexCharts from "vue3-apexcharts";
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
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
  MenuIcon,
  TrendingUpIcon,
  PieChartIcon,
  BarChartIcon,
  DownloadIcon,
  ZapIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
  totalUsers: Number,
  totalAdmins: Number,
  presentToday: Number,
  lateToday: Number,
  absentToday: Number,
  jabatanStats: Array,
  weeklyAttendance: Object,
});

// Get page props
const page = usePage();

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
        return val.toFixed(0);
      }
    }
  },
  tooltip: {
    x: {
      format: 'dd/MM/yy'
    }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right'
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

const jabatanChartOptions = computed(() => ({
  chart: {
    type: 'donut',
  },
  labels: props.jabatanStats?.map(stat => stat.name) || [],
  legend: {
    position: 'bottom'
  },
  dataLabels: {
    enabled: false
  }
}));

const jabatanChartSeries = computed(() => {
  return props.jabatanStats?.map(stat => stat.total_users) || [];
});

// Methods
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};
</script>