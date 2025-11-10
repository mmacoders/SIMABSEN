<template>
  <div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Sidebar -->
    <Sidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content -->
    <div class="flex flex-col flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header 
        :user="user" 
        current-page="Dashboard" 
        mobile-breadcrumb="Dashboard"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-16 p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Greeting Section -->
          <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ greeting }}</h1>
            <p class="text-gray-600 mt-2">{{ currentDate }}</p>
          </div>

          <!-- Info Cards Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- User Info Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl lg:col-span-1">
              <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <UserIcon class="w-6 h-6 text-[#dc2626] mr-2" />
                Informasi Pegawai
              </h2>
              <div class="space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                  <span class="text-gray-500">Nama</span>
                  <span class="text-gray-900 font-medium">{{ user.name }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                  <span class="text-gray-500">Bidang</span>
                  <span class="text-gray-900 font-medium">{{ user.bidang?.nama_bidang || '-' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                  <span class="text-gray-500">Jabatan</span>
                  <span class="text-gray-900 font-medium">{{ user.jabatan || '-' }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                  <span class="text-gray-500">Pangkat/NIP/NRP</span>
                  <span class="text-gray-900 font-medium text-right">{{ user.pangkat }}<br>{{ user.nip || user.nrp }}</span>
                </div>
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                  <span class="text-gray-500">Jam</span>
                  <span class="text-gray-900 font-medium">{{ currentTime }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-500">Status Hari Ini</span>
                  <span :class="getStatusClass(todayStatus)" class="px-3 py-1 rounded-full text-sm font-semibold">
                    {{ todayStatus }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Attendance Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl lg:col-span-2">
              <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
                <CalendarCheckIcon class="w-6 h-6 text-[#dc2626] mr-2" />
                Absensi Hari Ini
              </h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Check-in Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 p-5 flex flex-col justify-between transition-all duration-300 hover:shadow-md">
                  <div>
                    <h3 class="text-lg font-semibold text-[#dc2626] mb-2 flex items-center">
                      <LogInIcon class="w-5 h-5 mr-2" />
                      Check-in
                    </h3>
                    <p class="text-sm text-gray-500 mb-3">
                      {{ todayAttendance && todayAttendance.waktu_masuk ? todayAttendance.waktu_masuk : 'Belum check-in' }}
                    </p>
                  </div>
                  <button
                    :disabled="hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh')"
                    @click="goToAbsensi"
                    class="w-full py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02]"
                    :class="{
                      'bg-gray-100 text-gray-400 cursor-not-allowed': hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh'),
                      'bg-[#dc2626] hover:bg-[#b91c1c] text-white shadow-md': !(hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh'))
                    }"
                  >
                    <LogInIcon v-if="!hasCheckedIn" class="w-5 h-5 mr-2" />
                    Check-in
                  </button>
                </div>

                <!-- Check-out Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 p-5 flex flex-col justify-between transition-all duration-300 hover:shadow-md">
                  <div>
                    <h3 class="text-lg font-semibold text-[#111827] mb-2 flex items-center">
                      <LogOutIcon class="w-5 h-5 mr-2" />
                      Check-out
                    </h3>
                    <p class="text-sm text-gray-500 mb-3">
                      {{ todayAttendance && todayAttendance.waktu_keluar ? todayAttendance.waktu_keluar : 'Belum check-out' }}
                    </p>
                  </div>
                  <button
                    :disabled="!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)')"
                    @click="goToAbsensi"
                    class="w-full py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02]"
                    :class="{
                      'bg-gray-100 text-gray-400 cursor-not-allowed': !hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'),
                      'bg-[#111827] hover:bg-[#000000] text-white shadow-md': !(!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'))
                    }"
                  >
                    <LogOutIcon v-if="!(!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'))" class="w-5 h-5 mr-2" />
                    Check-out
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Attendance History -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 transition-all duration-300 hover:shadow-xl">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
              <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                <HistoryIcon class="w-6 h-6 text-[#dc2626] mr-2" />
                Riwayat Absensi (1 Minggu Terakhir)
              </h2>
              <button 
                @click="goToAbsensi" 
                class="text-sm px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200 flex items-center"
              >
                <CalendarIcon class="w-4 h-4 mr-2" />
                Lihat Semua
              </button>
            </div>
            <div class="overflow-x-auto rounded-xl border border-gray-200">
              <table class="min-w-full">
                <thead>
                  <tr class="bg-gray-50 text-left text-xs font-semibold tracking-wider text-gray-500 uppercase">
                    <th class="px-6 py-4">Tanggal</th>
                    <th class="px-6 py-4">Masuk</th>
                    <th class="px-6 py-4">Keluar</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Keterangan</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="attendance in attendanceHistory" :key="attendance.id" class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 text-gray-900 text-sm">{{ formatDate(attendance.tanggal) }}</td>
                    <td class="px-6 py-4 text-gray-900 text-sm">{{ attendance.waktu_masuk || '-' }}</td>
                    <td class="px-6 py-4 text-gray-900 text-sm">{{ attendance.waktu_keluar || '-' }}</td>
                    <td class="px-6 py-4">
                      <span :class="getAttendanceStatusClass(attendance)" class="px-3 py-1 rounded-full text-xs font-semibold">
                        {{ getAttendanceStatusText(attendance) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-gray-900 text-sm max-w-xs">{{ attendance.keterangan || '-' }}</td>
                  </tr>
                  <tr v-if="attendanceHistory.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                      <CalendarXIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                      <p class="text-lg font-medium text-gray-500">Tidak ada data absensi</p>
                      <p class="text-gray-400 mt-1">Belum ada riwayat absensi</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { 
  UserIcon, 
  CalendarCheckIcon, 
  LogInIcon, 
  LogOutIcon, 
  HistoryIcon, 
  MapPinIcon,
  CalendarIcon,
  CalendarXIcon
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    todayAttendance: Object,
    todayIzin: Object,
    attendanceHistory: Array,
    systemSettings: Object,
});

// Office location for POLDA TIK
const officeLocation = {
  lat: 0.5240005,
  lng: 123.0604752
};

// Attendance radius in meters
const attendanceRadius = 100;

const mapContainer = ref(null);
const mapInstance = ref(null);
const marker = ref(null);
const circle = ref(null);

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));

let timeInterval;

// Initialize the map when the component is mounted
onMounted(() => {
  initMap();
  
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  }, 60000); // Update every minute
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
  
  // Clean up map instance
  if (mapInstance.value) {
    mapInstance.value.remove();
  }
});

// Initialize Leaflet map
const initMap = () => {
  if (!mapContainer.value) return;
  
  // Create map instance
  mapInstance.value = L.map(mapContainer.value).setView([officeLocation.lat, officeLocation.lng], 15);
  
  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(mapInstance.value);
  
  // Add marker for office location
  marker.value = L.marker([officeLocation.lat, officeLocation.lng], {
    icon: L.divIcon({
      className: 'bg-red-600 rounded-full w-8 h-8 border-2 border-white shadow-lg flex items-center justify-center',
      iconSize: [32, 32],
      iconAnchor: [16, 16],
      html: '<div class="w-6 h-6 bg-white rounded-full flex items-center justify-center"><div class="w-3 h-3 bg-red-600 rounded-full"></div></div>'
    })
  }).addTo(mapInstance.value);
  
  // Add circle for attendance radius
  circle.value = L.circle([officeLocation.lat, officeLocation.lng], {
    color: '#dc2626',
    fillColor: '#dc2626',
    fillOpacity: 0.2,
    radius: attendanceRadius
  }).addTo(mapInstance.value);
  
  // Add popup to marker
  marker.value.bindPopup("Kantor POLRES").openPopup();
};

// Computed properties
const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour >= 5 && hour < 11) {
    return `Selamat pagi, ${props.user.name}! Semangat menjalankan tugas hari ini 💪`;
  } else if (hour >= 11 && hour < 15) {
    return `Selamat siang, ${props.user.name}! Tetap fokus dan produktif 🔥`;
  } else if (hour >= 15 && hour < 20) {
    return `Selamat sore, ${props.user.name}! Terima kasih atas dedikasi hari ini 👏`;
  } else {
    return `Selamat malam, ${props.user.name}! Waktunya beristirahat 🌙`;
  }
});

const currentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const hasCheckedIn = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_masuk;
});

const hasCheckedOut = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_keluar;
});

const todayStatus = computed(() => {
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    return 'Izin (Valid)';
  } else if (props.todayAttendance && props.todayAttendance.status === 'Izin Parsial (Check-in)') {
    return 'Sudah Check-in (Izin Parsial)';
  } else if (hasCheckedOut.value) {
    return 'Selesai';
  } else if (hasCheckedIn.value) {
    return 'Sudah Check-in';
  } else {
    return 'Belum Check-in';
  }
});

// Methods
const getAttendanceStatusText = (attendance) => {
  if (attendance.status === 'Izin (Valid)') {
    return 'Izin';
  } else if (attendance.status === 'Izin Parsial (Check-in)') {
    return 'Izin Parsial (Check-in)';
  } else if (attendance.status === 'Izin Parsial (Selesai)') {
    return 'Izin Parsial (Selesai)';
  } else if (attendance.status === 'terlambat') {
    return 'Terlambat';
  } else if (attendance.waktu_masuk) {
    return 'Tepat Waktu';
  } else {
    return 'Tidak Hadir';
  }
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

const goToAbsensi = () => {
  router.visit('/user/absensi');
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'Belum Check-in':
      return 'bg-gray-100 text-gray-700';
    case 'Sudah Check-in':
      return 'bg-red-100 text-red-700';
    case 'Selesai':
      return 'bg-gray-900 text-white';
    case 'Izin (Valid)':
    case 'Sudah Check-in (Izin Parsial)':
      return 'bg-pink-100 text-pink-700';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};

const getAttendanceStatusClass = (attendance) => {
  if (attendance.status === 'Izin (Valid)' || attendance.status === 'Izin' || attendance.status === 'Izin Parsial (Check-in)' || attendance.status === 'Izin Parsial (Selesai)') {
    return 'bg-gray-100 text-gray-700';
  } else if (attendance.status === 'terlambat') {
    return 'bg-red-100 text-red-700';
  } else if (attendance.waktu_masuk && !attendance.status) {
    return 'bg-green-100 text-green-700';
  } else {
    return 'bg-gray-100 text-gray-700';
  }
};
</script>