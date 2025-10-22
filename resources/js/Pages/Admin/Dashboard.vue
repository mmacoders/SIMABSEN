<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  CheckCircleIcon, 
  ClockIcon, 
  ClipboardIcon, 
  XCircleIcon,
  CalendarIcon,
  SparklesIcon
} from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

// Get data from the controller
const props = defineProps({
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

<template>
  <AdminLayout>
    <div class="p-6 bg-gray-50">
      <!-- Greeting Card Section -->
      <section 
        class="bg-gradient-to-r from-[#dc2626] to-[#111827] text-white rounded-2xl shadow-md p-6 flex flex-col sm:flex-row justify-between items-center mb-6 transition-all duration-500 animate-fade-in"
      >
        <div class="mb-4 sm:mb-0">
          <h2 class="text-2xl font-semibold">Selamat Datang, {{ adminName }} 👋</h2>
          <p class="text-sm text-gray-200 mt-1">
            Hari ini: {{ getCurrentDateFormatted() }}
          </p>
          <p class="text-sm text-gray-100 mt-2">
            Semoga hari ini penuh semangat dan produktif 💪
          </p>
        </div>
        <div class="hidden sm:block">
          <CalendarIcon class="w-12 h-12 text-white opacity-80" />
        </div>
      </section>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="stat in stats" 
          :key="stat.name"
          class="bg-white border rounded-2xl p-5 flex items-center justify-between shadow-sm hover:shadow-md transition-all duration-300"
        >
          <div>
            <p class="text-gray-500 text-sm">{{ stat.name }}</p>
            <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ stat.value }}</h2>
          </div>
          <component 
            :is="stat.icon" 
            class="w-10 h-10" 
            :class="stat.color"
          />
        </div>
      </div>

      <!-- Chart Placeholder -->
      <div class="mt-8 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Grafik Kehadiran Mingguan</h3>
        <div class="h-64 flex items-center justify-center bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
          <p class="text-gray-500">Grafik kehadiran mingguan akan ditampilkan di sini</p>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="mt-8 bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Absensi Hari Ini</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(person, index) in attendanceData" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ person.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.checkIn }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.checkOut }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                    :class="getStatusClass(person.status)"
                  >
                    {{ person.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ person.keterangan }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

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