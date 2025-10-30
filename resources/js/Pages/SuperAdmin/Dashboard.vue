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
            <div class="p-4 rounded-xl bg-yellow-100">
              <BuildingIcon class="h-8 w-8 text-yellow-600" />
            </div>
            <div class="ml-5">
              <p class="text-sm font-medium text-gray-600">Total Bidang</p>
              <p class="text-3xl font-bold text-gray-900 mt-1">{{ totalBidangs }}</p>
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
        
        <!-- Bidang Distribution Chart -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h3 class="text-xl font-semibold text-gray-900 flex items-center">
              <PieChartIcon class="w-6 h-6 text-[#dc2626] mr-2" />
              Distribusi Pegawai per Bidang
            </h3>
          </div>
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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
          <h3 class="text-xl font-semibold text-gray-900 flex items-center">
            <BuildingIcon class="w-6 h-6 text-[#dc2626] mr-2" />
            Rekap Absensi per Bidang
          </h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
          <div
            v-for="bidang in bidangStats"
            :key="bidang.id"
            class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl transform hover:-translate-y-1"
          >
            <div class="flex items-center mb-4">
              <div class="p-3 rounded-lg bg-red-50">
                <BuildingIcon class="h-6 w-6 text-[#dc2626]" />
              </div>
              <h4 class="font-semibold text-gray-900 ml-4 text-lg">{{ bidang.nama_bidang }}</h4>
            </div>
            <div class="space-y-3">
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
  totalBidangs: Number,
  presentToday: Number,
  absentToday: Number,
  bidangStats: Array,
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

// Logout function
const logout = () => {
  router.post(route('logout'));
};
</script>