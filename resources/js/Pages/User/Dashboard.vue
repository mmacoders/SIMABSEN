<template>
  <div class="flex min-h-screen bg-[#F8F9FA]">
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
      <main class="pt-16 p-4 sm:p-6 md:p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Greeting Section -->
          <div class="mb-6">
            <h4 class="text-2xl md:text-3xl font-bold text-[#111827]">{{ greeting }}</h4>
            <p class="text-gray-600 mt-1">{{ currentDate }}</p>
          </div>

          <!-- Info Cards Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- User Info Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
              <h2 class="text-lg font-semibold text-[#111827] mb-4">Informasi Pegawai</h2>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Nama</span>
                  <span class="text-gray-900 font-medium">{{ user.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Bidang</span>
                  <span class="text-gray-900 font-medium">{{ user.bidang?.nama_bidang || '-' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Pangkat/NIP/NRP</span>
                  <span class="text-gray-900 font-medium">{{ user.pangkat }} / {{ user.nip || user.nrp }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Jam</span>
                  <span class="text-gray-900 font-medium">{{ currentTime }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Status Hari Ini</span>
                  <span :class="getStatusClass(todayStatus)" class="px-3 py-1 rounded-full text-xs font-semibold">
                    {{ todayStatus }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Attendance Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
              <h2 class="text-lg font-semibold text-[#111827] mb-4">Absensi Hari Ini</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Check-in Card -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 flex flex-col justify-between">
                  <div>
                    <h3 class="text-md font-semibold text-[#dc2626] mb-2">Check-in</h3>
                    <p class="text-sm text-gray-500 mb-3">
                      {{ todayAttendance && todayAttendance.waktu_masuk ? todayAttendance.waktu_masuk : 'Belum check-in' }}
                    </p>
                  </div>
                  <button
                    :disabled="hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh')"
                    @click="goToAbsensi"
                    class="w-full py-2 rounded-lg font-semibold transition-all duration-200"
                    :class="{
                      'bg-gray-200 text-gray-500 cursor-not-allowed': hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh'),
                      'bg-[#dc2626] hover:bg-[#b91c1c] text-white hover:scale-[1.02]': !(hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh'))
                    }"
                  >
                    Check-in
                  </button>
                </div>

                <!-- Check-out Card -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-4 flex flex-col justify-between">
                  <div>
                    <h3 class="text-md font-semibold text-[#111827] mb-2">Check-out</h3>
                    <p class="text-sm text-gray-500 mb-3">
                      {{ todayAttendance && todayAttendance.waktu_keluar ? todayAttendance.waktu_keluar : 'Belum check-out' }}
                    </p>
                  </div>
                  <button
                    :disabled="!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)')"
                    @click="goToAbsensi"
                    class="w-full py-2 rounded-lg font-semibold transition-all duration-200"
                    :class="{
                      'bg-gray-200 text-gray-500 cursor-not-allowed': !hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'),
                      'bg-[#111827] hover:bg-[#000000] text-white hover:scale-[1.02]': !(!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'))
                    }"
                  >
                    Check-out
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Attendance History -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 mb-8">
          <h2 class="text-lg font-semibold text-[#111827] mb-4">Riwayat Absensi (1 Minggu Terakhir)</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full">
                <thead>
                  <tr class="bg-[#dc2626] text-white text-left uppercase text-xs font-semibold tracking-wider">
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Masuk</th>
                    <th class="px-4 py-3">Keluar</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="attendance in attendanceHistory" :key="attendance.id" class="border-b hover:bg-red-50 transition-all">
                    <td class="px-4 py-3 text-gray-900">{{ formatDate(attendance.tanggal) }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ attendance.waktu_masuk || '-' }}</td>
                    <td class="px-4 py-3 text-gray-900">{{ attendance.waktu_keluar || '-' }}</td>
                    <td class="px-4 py-3">
                      <span :class="getAttendanceStatusClass(attendance)" class="px-3 py-1 rounded-full text-xs font-semibold">
                        {{ getAttendanceStatusText(attendance) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-gray-900">{{ attendance.keterangan || '-' }}</td>
                  </tr>
                  <tr v-if="attendanceHistory.length === 0">
                    <td colspan="5" class="px-4 py-3 text-center text-gray-500">Tidak ada data absensi</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        <!-- Map Location -->
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
          <h2 class="text-lg font-semibold text-[#111827] mb-4">📍 Lokasi Kantor POLRES TIK</h2>
          <div class="h-64 w-full rounded-xl overflow-hidden mt-2 bg-gray-100">
            <div ref="mapContainer" class="w-full h-full"></div>
          </div>
          <div class="mt-2 text-sm text-gray-600">
            <p>Koordinat: {{ officeLocation.lat }}, {{ officeLocation.lng }}</p>
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
      className: 'bg-red-600 rounded-full w-6 h-6 border-2 border-white shadow-lg',
      iconSize: [24, 24],
      iconAnchor: [12, 12]
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
      return 'bg-gray-200 text-gray-700';
    case 'Sudah Check-in':
      return 'bg-red-100 text-red-700';
    case 'Selesai':
      return 'bg-black text-white';
    case 'Izin (Valid)':
    case 'Sudah Check-in (Izin Parsial)':
      return 'bg-pink-100 text-pink-700';
    default:
      return 'bg-gray-200 text-gray-700';
  }
};

const getAttendanceStatusClass = (attendance) => {
  if (attendance.status === 'Izin (Valid)' || attendance.status === 'Izin' || attendance.status === 'Izin Parsial (Check-in)' || attendance.status === 'Izin Parsial (Selesai)') {
    return 'bg-gray-200 text-gray-700';
  } else if (attendance.status === 'terlambat') {
    return 'bg-red-100 text-red-700';
  } else if (attendance.waktu_masuk && !attendance.status) {
    return 'bg-green-100 text-green-700';
  } else {
    return 'bg-gray-200 text-gray-700';
  }
};
</script>
