<template>
  <AdminLayout page-title="Dashboard" mobile-page-title="Dashboard">
    <div class="p-4 sm:p-6 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
      <!-- Greeting Card Section -->
      <section 
        class="bg-gradient-to-r from-[#dc2626] to-[#b91c1c] text-white rounded-2xl shadow-xl p-6 flex flex-col sm:flex-row justify-between items-center mb-6 transition-all duration-500 animate-fade-in transform hover:scale-[1.01]"
      >
        <div class="mb-4 sm:mb-0">
          <h1 class="text-2xl md:text-3xl font-bold">Selamat Datang, {{ adminName }} 👋</h1>
          <p class="text-sm text-gray-200 mt-1">
            Hari ini: {{ getCurrentDateFormatted() }}
          </p>
          <p class="text-sm text-gray-100 mt-2">
            Semoga hari ini penuh semangat dan produktif 💪
          </p>
        </div>
        <div class="hidden sm:block">
          <CalendarIcon class="w-16 h-16 text-white opacity-90" />
        </div>
      </section>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div 
          v-for="(stat, index) in stats" 
          :key="stat.name"
          class="bg-white border rounded-2xl p-5 flex items-center justify-between shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
        >
          <div>
            <p class="text-gray-500 text-sm">{{ stat.name }}</p>
            <h2 class="text-3xl font-bold text-gray-900 mt-1">{{ stat.value }}</h2>
          </div>
          <div :class="stat.bgColor" class="p-3 rounded-full">
            <component 
              :is="stat.icon" 
              class="w-8 h-8" 
              :class="stat.color"
            />
          </div>
        </div>
      </div>

      <!-- Charts and Data Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Attendance Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition-all duration-300 hover:shadow-xl">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 flex items-center">
              <TrendingUpIcon class="w-6 h-6 text-[#dc2626] mr-2" />
              Grafik Kehadiran Mingguan
            </h2>
            <button class="text-sm text-[#dc2626] hover:text-[#b91c1c] flex items-center transition-colors duration-200 px-3 py-1 rounded-lg hover:bg-gray-50">
              <BarChartIcon class="w-4 h-4 mr-1" />
              Lihat Detail
            </button>
          </div>
          <div class="h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl border-2 border-dashed border-gray-200">
            <div class="text-center">
              <BarChartIcon class="w-16 h-16 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-500">Grafik kehadiran mingguan akan ditampilkan di sini</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition-all duration-300 hover:shadow-xl">
          <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            <FileTextIcon class="w-6 h-6 text-[#dc2626] mr-2" />
            Aksi Cepat
          </h2>
          <div class="space-y-4">
            <a 
              href="/admin/pegawai" 
              class="flex items-center p-4 rounded-xl border border-gray-200 hover:bg-gradient-to-r hover:from-[#dc2626]/5 hover:to-[#b91c1c]/5 transition-all duration-300 transform hover:scale-[1.02]"
            >
              <div class="p-3 rounded-lg bg-[#dc2626]/10 mr-4">
                <UsersIcon class="w-6 h-6 text-[#dc2626]" />
              </div>
              <div>
                <span class="text-gray-900 font-medium">Kelola Pegawai</span>
                <p class="text-gray-500 text-sm mt-1">Kelola data pegawai dan absensi</p>
              </div>
            </a>
            <a 
              href="/admin/laporan" 
              class="flex items-center p-4 rounded-xl border border-gray-200 hover:bg-gradient-to-r hover:from-[#dc2626]/5 hover:to-[#b91c1c]/5 transition-all duration-300 transform hover:scale-[1.02]"
            >
              <div class="p-3 rounded-lg bg-[#dc2626]/10 mr-4">
                <CalendarDaysIcon class="w-6 h-6 text-[#dc2626]" />
              </div>
              <div>
                <span class="text-gray-900 font-medium">Laporan Absensi</span>
                <p class="text-gray-500 text-sm mt-1">Lihat dan ekspor laporan absensi</p>
              </div>
            </a>
            <a 
              href="/admin/izin" 
              class="flex items-center p-4 rounded-xl border border-gray-200 hover:bg-gradient-to-r hover:from-[#dc2626]/5 hover:to-[#b91c1c]/5 transition-all duration-300 transform hover:scale-[1.02]"
            >
              <div class="p-3 rounded-lg bg-[#dc2626]/10 mr-4">
                <ClipboardIcon class="w-6 h-6 text-[#dc2626]" />
              </div>
              <div>
                <span class="text-gray-900 font-medium">Izin & Cuti</span>
                <p class="text-gray-500 text-sm mt-1">Kelola permintaan izin dan cuti</p>
              </div>
            </a>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 transition-all duration-300 hover:shadow-xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
          <h2 class="text-xl font-semibold text-gray-900 flex items-center">
            <CalendarIcon class="w-6 h-6 text-[#dc2626] mr-2" />
            Daftar Absensi Hari Ini
          </h2>
          <div class="flex gap-2">
            <button class="text-sm px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <DownloadIcon class="w-4 h-4 mr-1" />
              Export
            </button>
            <button class="text-sm px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <FilterIcon class="w-4 h-4 mr-1" />
              Filter
            </button>
          </div>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(person, index) in attendanceData" :key="index" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ person.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.checkIn }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.checkOut }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                    :class="getStatusClass(person.status)"
                  >
                    {{ person.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">{{ person.keterangan }}</td>
              </tr>
              <tr v-if="!attendanceData || attendanceData.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">
                  <CalendarXIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                  <p class="text-lg font-medium text-gray-500">Tidak ada data absensi hari ini</p>
                  <p class="text-gray-400 mt-1">Belum ada pegawai yang melakukan absensi</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination or Load More -->
        <div class="mt-6 flex justify-center">
          <button 
            v-if="attendanceData && attendanceData.length > 0"
            class="px-5 py-2.5 text-sm text-[#dc2626] hover:text-[#b91c1c] font-medium flex items-center transition-colors duration-200 rounded-lg hover:bg-gray-50"
          >
            <ChevronsDownIcon class="w-5 h-5 mr-2" />
            Muat Lebih Banyak
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  CheckCircleIcon, 
  ClockIcon, 
  ClipboardIcon, 
  XCircleIcon,
  CalendarIcon,
  TrendingUpIcon,
  UsersIcon,
  CalendarDaysIcon,
  FileTextIcon,
  BarChartIcon,
  DownloadIcon,
  FilterIcon,
  CalendarXIcon,
  ChevronsDownIcon
} from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

// Get data from the controller
const props = defineProps({
  admin: Object,
  stats: Object,
  attendanceData: Array,
});

// Prepare statistics data
const stats = ref([
  { name: 'Hadir Hari Ini', value: props.stats?.present || 0, icon: CheckCircleIcon, color: 'text-green-600', bgColor: 'bg-green-100' },
  { name: 'Terlambat', value: props.stats?.late || 0, icon: ClockIcon, color: 'text-yellow-600', bgColor: 'bg-yellow-100' },
  { name: 'Izin / Cuti', value: props.stats?.leave || 0, icon: ClipboardIcon, color: 'text-blue-600', bgColor: 'bg-blue-100' },
  { name: 'Tidak Hadir', value: props.stats?.absent || 0, icon: XCircleIcon, color: 'text-red-600', bgColor: 'bg-red-100' }
]);

// Get current date formatted
const getCurrentDateFormatted = () => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date().toLocaleDateString('id-ID', options);
};

// Get admin name from user session
const adminName = usePage().props.auth.user.name;

const getStatusClass = (status) => {
  // Check if status contains "Terlambat" with time difference
  if (status && status.includes('Terlambat')) {
    return 'bg-yellow-100 text-yellow-800';
  }
  
  switch (status) {
    case 'Hadir':
      return 'bg-green-100 text-green-800';
    case 'Terlambat':
      return 'bg-yellow-100 text-yellow-800';
    case 'Izin':
      return 'bg-blue-100 text-blue-800';
    case 'Tidak Hadir':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>

<style scoped>
/* Add fade-in animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
</style>