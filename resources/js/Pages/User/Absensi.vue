<template>
  <div class="flex min-h-screen h-screen bg-[#F8F9FA]">
    <!-- Sidebar -->
    <Sidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content -->
    <div class="flex flex-col flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header class="z-10"
        :user="user" 
        current-page="Absensi" 
        mobile-breadcrumb="Absensi"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-16 p-4 sm:p-6 md:p-8">
        <div class="max-w-5xl mx-auto">
          <!-- Header Section -->
          <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-[#111827]">Absensi Hari Ini</h1>
            <p class="text-gray-600 mt-1">{{ currentDate }}</p>
          </div>

          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
            {{ $page.props.flash.error }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.prompt_keterangan" class="mb-4 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            {{ $page.props.flash.prompt_keterangan }}
          </div>

          <!-- Full Leave Message -->
          <div v-if="todayIzin && todayIzin.jenis_izin === 'penuh'" class="mb-6 p-4 bg-blue-100 text-blue-800 rounded-lg">
            <div class="flex items-center">
              <BanIcon class="h-5 w-5 mr-2" />
              <span class="font-medium">Anda sedang dalam status izin penuh. Tidak perlu melakukan absensi hari ini.</span>
            </div>
            <p class="mt-1 text-sm">Keterangan: {{ todayIzin.keterangan }}</p>
          </div>

          <!-- Partial Leave Message -->
          <div v-if="todayIzin && todayIzin.jenis_izin === 'parsial'" class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            <div class="flex items-center">
              <ClockIcon class="h-5 w-5 mr-2" />
              <span class="font-medium">Anda memiliki izin parsial hari ini.</span>
            </div>
            <p class="mt-1 text-sm">Keterangan: {{ todayIzin.keterangan }}</p>
            <p class="mt-1 text-sm">Anda diperbolehkan melakukan Check-in, namun tidak wajib melakukan Check-out.</p>
          </div>

          <!-- Late Arrival Explanation Modal -->
          <div v-if="showLateArrivalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
              <h3 class="text-lg font-semibold mb-4">Keterangan Keterlambatan</h3>
              <p class="text-gray-600 mb-4">Anda terlambat hari ini. Mohon berikan alasan keterlambatan Anda:</p>
              <textarea 
                v-model="lateArrivalReason" 
                class="w-full border border-gray-300 rounded-lg p-2 mb-4" 
                rows="3" 
                placeholder="Masukkan alasan keterlambatan..."
              ></textarea>
              <div class="flex justify-end gap-2">
                <button @click="cancelLateArrival" class="px-4 py-2 border border-gray-300 rounded-lg">Batal</button>
                <button @click="submitLateArrival" class="px-4 py-2 bg-red-600 text-white rounded-lg">Kirim</button>
              </div>
            </div>
          </div>

          <!-- Permission Request Modal -->
          <div v-if="showPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
              <h3 class="text-lg font-semibold mb-4">Ajukan Izin</h3>
              <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_izin">
                  Jenis Izin
                </label>
                <select 
                  v-model="permissionType" 
                  class="w-full border border-gray-300 rounded-lg p-2"
                >
                  <option value="penuh">Izin Penuh (Tidak perlu absensi)</option>
                  <option value="parsial">Izin Parsial (Bisa absen masuk saja)</option>
                </select>
              </div>
              <p class="text-gray-600 mb-4">Mohon berikan keterangan izin Anda:</p>
              <textarea 
                v-model="permissionReason" 
                class="w-full border border-gray-300 rounded-lg p-2 mb-4" 
                rows="3" 
                placeholder="Masukkan alasan izin..."
              ></textarea>
              <div class="flex justify-end gap-2">
                <button @click="cancelPermission" class="px-4 py-2 border border-gray-300 rounded-lg">Batal</button>
                <button @click="submitPermission" class="px-4 py-2 bg-red-600 text-white rounded-lg">Ajukan</button>
              </div>
            </div>
          </div>

          <!-- Outside Working Hours Warning -->
          <div v-if="!isWithinWorkingHours" class="mb-6 p-4 bg-orange-100 text-orange-800 rounded-lg">
            <div class="flex items-center">
              <AlertTriangleIcon class="h-5 w-5 mr-2" />
              <span class="font-medium">Peringatan: Di luar jam kerja</span>
            </div>
            <p class="mt-1 text-sm">Absensi saat ini di luar jam kerja yang ditentukan. Jika dilanjutkan, Anda akan ditandai sebagai tidak hadir (alpha).</p>
          </div>

          <!-- Already Completed Attendance Message -->
          <div v-if="hasCheckedIn && hasCheckedOut" class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
            <div class="flex items-center">
              <CheckCircleIcon class="h-5 w-5 mr-2" />
              <span class="font-medium">Absensi hari ini telah tercatat. Tetap semangat menjalankan tugas dan tanggung jawab!</span>
            </div>
            <p class="mt-1 text-sm">Check-in: {{ todayAttendance.waktu_masuk }} | Check-out: {{ todayAttendance.waktu_keluar }}</p>
            <p v-if="todayAttendance.keterangan" class="mt-1 text-sm">Keterangan: {{ todayAttendance.keterangan }}</p>
          </div>

          <!-- Partial Leave Check-in Message -->
          <div v-if="todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'" class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            <div class="flex items-center">
              <InfoIcon class="h-5 w-5 mr-2" />
              <span class="font-medium">Anda telah melakukan check-in izin parsial.</span>
            </div>
            <p class="mt-1 text-sm">Anda tidak wajib melakukan check-out.</p>
            <p v-if="todayAttendance.keterangan" class="mt-1 text-sm">Keterangan: {{ todayAttendance.keterangan }}</p>
          </div>

          <!-- Status Cards Section -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Check-in Card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
              <div>
                <h2 class="text-lg font-semibold text-[#dc2626] mb-2">
                  {{ todayIzin && todayIzin.jenis_izin === 'parsial' ? 'Check-in (Izin Parsial)' : 'Check-in' }}
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                  {{ hasCheckedIn ? 'Sudah melakukan check-in' : 'Belum melakukan check-in' }}
                </p>
              </div>
              <button
                :disabled="hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)')"
                @click="checkIn"
                class="w-full py-2.5 rounded-lg font-semibold text-white transition-all duration-200"
                :class="(hasCheckedIn || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)')) ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-[#dc2626] hover:bg-[#b91c1c]'"
              >
                {{ todayIzin && todayIzin.jenis_izin === 'parsial' ? 'Check-in (Izin Parsial)' : 'Check-in' }}
              </button>
            </div>

            <!-- Check-out Card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
              <div>
                <h2 class="text-lg font-semibold text-[#dc2626] mb-2">Check-out</h2>
                <p class="text-sm text-gray-500 mb-4">
                  {{ hasCheckedOut ? 'Sudah melakukan check-out' : (hasCheckedIn ? 'Belum melakukan check-out' : 'Lakukan check-in terlebih dahulu') }}
                </p>
              </div>
              <button
                :disabled="!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)')"
                @click="checkOut"
                class="w-full py-2.5 rounded-lg font-semibold text-white transition-all duration-200"
                :class="(!hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)') || (todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)')) ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-[#dc2626] hover:bg-[#b91c1c]'"
              >
                Check-out
              </button>
            </div>

            <!-- Permission Card -->
            <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 flex flex-col justify-between">
              <div>
                <h2 class="text-lg font-semibold text-[#dc2626] mb-2">Izin</h2>
                <p class="text-sm text-gray-500 mb-4">
                  {{ todayAttendance && (todayAttendance.status === 'Izin (Valid)' || todayAttendance.status === 'Izin') ? 'Izin telah diajukan' : 'Ajukan izin hari ini' }}
                </p>
              </div>
              <button
                :disabled="hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)')"
                @click="openPermissionModal"
                class="w-full py-2.5 rounded-lg font-semibold text-white transition-all duration-200"
                :class="(hasCheckedIn || hasCheckedOut || (todayIzin && todayIzin.jenis_izin === 'penuh') || (todayAttendance && todayAttendance.status === 'Izin (Valid)')) ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-[#dc2626] hover:bg-[#b91c1c]'"
              >
                Ajukan Izin
              </button>
            </div>
          </div>

          <!-- Employee Location Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Current Location Card -->
            <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5">
              <h3 class="text-[#dc2626] font-semibold mb-3">Lokasi Anda Saat Ini</h3>
              <div class="flex items-start">
                <MapPinIcon class="h-5 w-5 text-[#dc2626] mt-0.5 mr-2 flex-shrink-0" />
                <div>
                  <p v-if="currentLocation" class="text-gray-700">
                    <span class="font-medium">Koordinat:</span> {{ currentLocation.lat.toFixed(6) }}, {{ currentLocation.lng.toFixed(6) }}
                  </p>
                  <p v-else class="text-gray-500 italic">Mendeteksi lokasi...</p>
                  <div v-if="gettingLocation" class="mt-2 flex items-center text-sm text-gray-600">
                    <div class="w-4 h-4 border-t-2 border-[#dc2626] border-solid rounded-full animate-spin mr-2"></div>
                    <span>Mendeteksi lokasi Anda...</span>
                  </div>
                  <p v-if="autoLocationDetected && currentLocation" class="mt-2 text-sm text-green-600">
                    ✓ Lokasi berhasil dideteksi secara otomatis
                  </p>
                  <!-- Warning message when outside office radius -->
                  <div v-if="currentLocation && !isWithinOfficeRadius" class="mt-3 p-3 bg-red-100 text-red-800 rounded-lg">
                    <div class="flex items-center">
                      <AlertTriangleIcon class="h-5 w-5 mr-2 flex-shrink-0" />
                      <span class="font-medium">Peringatan: Di luar radius kantor</span>
                    </div>
                    <p class="mt-1 text-sm">Lokasi Anda saat ini berada di luar radius kantor yang ditentukan. Absensi mungkin tidak dapat diproses.</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Office Location Card -->
             <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-5 ">
                <!-- Baris Judul -->
                <h3 class="text-[#dc2626] font-semibold">Lokasi Kantor</h3>
                <!-- <div class="flex items-center mb-3"> -->
                    <!-- <BuildingIcon class="h-5 w-5 text-[#dc2626] mr-2 flex-shrink-0" /> -->
                    <p class="text-gray-700 text-sm">
                      <span class="font-medium">Koordinat Kantor:</span>
                      {{ systemSettings ? systemSettings.location_latitude : '0.5240005' }},
                      {{ systemSettings ? systemSettings.location_longitude : '123.0604752' }}
                    </p>
                  <!-- </div> -->
                <!-- Paragraf Informasi -->
                <div class="space-y-1.5">
                  <p class="text-gray-700 text-sm">
                    Lokasi absensi harus berada dalam radius
                    <span class="font-semibold text-[#dc2626]">
                      {{ systemSettings ? systemSettings.location_radius : '' }} meter
                    </span>
                    dari kantor POLDA Gorontalo.
                  </p>

                  <p v-if="systemSettings" class="text-gray-600 text-sm">
                    <span class="font-medium">Jam Masuk:</span>
                    {{ systemSettings.jam_masuk }} WITA
                  </p>

                  <p v-if="systemSettings" class="text-gray-600 text-sm">
                    <span class="font-medium">Jam Pulang:</span>
                    {{ systemSettings.jam_pulang }} WITA
                  </p>
                </div>
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
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import { CheckCircleIcon, InfoIcon, BanIcon, ClockIcon, AlertTriangleIcon, MapPinIcon, BuildingIcon } from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    todayAttendance: Object,
    todayIzin: Object,
    systemSettings: Object,
});

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const gettingLocation = ref(false);
const currentLocation = ref(null);
const autoLocationDetected = ref(false);

// Modal states
const showLateArrivalModal = ref(false);
const showPermissionModal = ref(false);
const lateArrivalReason = ref('');
const permissionReason = ref('');
const permissionType = ref('penuh');

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

// Computed properties for check-in/check-out status
const hasCheckedIn = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_masuk;
});

const hasCheckedOut = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_keluar;
});

// Check if current time is within working hours
const isWithinWorkingHours = computed(() => {
  if (!props.systemSettings) return true;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamMasuk = props.systemSettings.jam_masuk || '08:00:00';
  const jamPulang = props.systemSettings.jam_pulang || '17:00:00';
  
  // Allow check-in from jam_masuk until 1 hour after jam_pulang
  const endCheckInTime = new Date(`1970-01-01T${jamPulang}`);
  endCheckInTime.setHours(endCheckInTime.getHours() + 1);
  const endCheckInTimeString = endCheckInTime.toTimeString().substring(0, 8);
  
  return currentTime >= jamMasuk && currentTime <= endCheckInTimeString;
});

// Check if current location is within office radius
const isWithinOfficeRadius = computed(() => {
  if (!currentLocation.value || !props.systemSettings) return true;
  
  const lat1 = currentLocation.value.lat;
  const lng1 = currentLocation.value.lng;
  const lat2 = props.systemSettings.location_latitude;
  const lng2 = props.systemSettings.location_longitude;
  const radius = props.systemSettings.location_radius;
  
  // Convert to radians
  const radLat1 = (Math.PI * lat1) / 180;
  const radLng1 = (Math.PI * lng1) / 180;
  const radLat2 = (Math.PI * lat2) / 180;
  const radLng2 = (Math.PI * lng2) / 180;
  
  // Haversine formula
  const deltaLat = radLat2 - radLat1;
  const deltaLng = radLng2 - radLng1;
  
  const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
            Math.cos(radLat1) * Math.cos(radLat2) *
            Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
  
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  
  // Earth's radius in meters
  const earthRadius = 6371000;
  
  // Calculate distance
  const distance = earthRadius * c;
  
  // Valid if within specified radius
  return distance <= radius;
});

// Get current location
const getCurrentLocation = () => {
  gettingLocation.value = true;
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        gettingLocation.value = false;
        currentLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        autoLocationDetected.value = true;
        
        // Show success message only when manually triggered or on initial load
        // Skip showing message on automatic check-in/check-out to avoid interrupting user
        if (!hasCheckedIn.value && !hasCheckedOut.value) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Lokasi Anda berhasil dideteksi.',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
          });
        }
      },
      (error) => {
        gettingLocation.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal mendapatkan lokasi: ' + error.message,
        });
      }
    );
  } else {
    gettingLocation.value = false;
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Geolocation tidak didukung oleh browser Anda.',
    });
  }
};

// Automatically get location when page loads
onMounted(() => {
  // Always auto-detect location when page loads
  // Small delay to ensure page is fully loaded
  setTimeout(() => {
    getCurrentLocation();
  }, 1000);
});

// Check-in function with automatic location
const checkIn = () => {
  // Check if user has full leave
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Anda sedang dalam status izin penuh. Tidak perlu melakukan absensi hari ini.',
    });
    return;
  }
  
  // Check if user is within office radius
  if (currentLocation.value && !isWithinOfficeRadius.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.',
    });
    return;
  }
  
  // Warn if outside working hours
  if (!isWithinWorkingHours.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: `Anda melakukan absensi di luar jam kerja (${props.systemSettings?.jam_masuk} - ${props.systemSettings?.jam_pulang}). Jika dilanjutkan, Anda akan ditandai sebagai tidak hadir (alpha).`,
      showCancelButton: true,
      confirmButtonText: 'Lanjutkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        performCheckIn();
      }
    });
    return;
  }
  
  performCheckIn();
};

// Perform check-in with current location
const performCheckIn = () => {
  // Always get fresh location for check-in
  gettingLocation.value = true;
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        gettingLocation.value = false;
        currentLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        autoLocationDetected.value = true;
        
        // Send check-in request with the newly obtained location
        sendCheckInRequest(position.coords.latitude, position.coords.longitude);
      },
      (error) => {
        gettingLocation.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal mendapatkan lokasi: ' + error.message + '. Silakan coba lagi.',
        });
      }
    );
  } else {
    gettingLocation.value = false;
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Geolocation tidak didukung oleh browser Anda.',
    });
  }
};

// Send check-in request to server
const sendCheckInRequest = (lat, lng) => {
  router.post(route('user.absensi.checkin'), {
    lat: lat,
    lng: lng,
  }, {
    onStart: () => {
      gettingLocation.value = true;
    },
    onFinish: () => {
      gettingLocation.value = false;
    },
    onSuccess: (response) => {
      // Check if we need to show the late arrival modal
      if (response.props.flash && response.props.flash.prompt_keterangan) {
        showLateArrivalModal.value = true;
      } else {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: response.props.flash && response.props.flash.success || 'Check-in berhasil dicatat.',
          showConfirmButton: false,
          timer: 1500,
          timerProgressBar: true,
        });
      }
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: errors.message || errors.default || 'Terjadi kesalahan saat check-in.',
      });
    }
  });
};

// Check-out function with automatic location
const checkOut = () => {
  // Check if user has full leave
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Anda sedang dalam status izin penuh. Tidak perlu melakukan absensi hari ini.',
    });
    return;
  }
  
  // Check if user has partial leave and already checked in
  if (props.todayIzin && props.todayIzin.jenis_izin === 'parsial' && 
      props.todayAttendance && props.todayAttendance.status === 'Izin Parsial (Check-in)') {
    Swal.fire({
      icon: 'info',
      title: 'Informasi',
      text: 'Anda tidak wajib melakukan check-out untuk izin parsial.',
    });
    return;
  }
  
  if (!hasCheckedIn.value || hasCheckedOut.value) return;
  
  // Check if user is within office radius
  if (currentLocation.value && !isWithinOfficeRadius.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.',
    });
    return;
  }
  
  // Warn if outside working hours
  if (!isWithinWorkingHours.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: `Anda melakukan absensi di luar jam kerja (${props.systemSettings?.jam_masuk} - ${props.systemSettings?.jam_pulang}). Jika dilanjutkan, Anda akan ditandai sebagai tidak hadir (alpha).`,
      showCancelButton: true,
      confirmButtonText: 'Lanjutkan',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        performCheckOut();
      }
    });
    return;
  }
  
  performCheckOut();
};

// Perform check-out with current location
const performCheckOut = () => {
  // Always get fresh location for check-out
  gettingLocation.value = true;
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        gettingLocation.value = false;
        currentLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        autoLocationDetected.value = true;
        
        // Send check-out request with the newly obtained location
        sendCheckOutRequest(position.coords.latitude, position.coords.longitude);
      },
      (error) => {
        gettingLocation.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal mendapatkan lokasi: ' + error.message + '. Silakan coba lagi.',
        });
      }
    );
  } else {
    gettingLocation.value = false;
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Geolocation tidak didukung oleh browser Anda.',
    });
  }
};

// Send check-out request to server
const sendCheckOutRequest = (lat, lng) => {
  router.post(route('user.absensi.checkout'), {
    lat: lat,
    lng: lng,
  }, {
    onStart: () => {
      gettingLocation.value = true;
    },
    onFinish: () => {
      gettingLocation.value = false;
    },
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Check-out berhasil dicatat.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: errors.message || errors.default || 'Terjadi kesalahan saat check-out.',
      });
    }
  });
};

// Permission functions
const openPermissionModal = () => {
  showPermissionModal.value = true;
};

const cancelPermission = () => {
  showPermissionModal.value = false;
  permissionReason.value = '';
  permissionType.value = 'penuh';
};

const submitPermission = () => {
  if (!permissionReason.value.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Keterangan izin tidak boleh kosong.',
    });
    return;
  }
  
  router.post(route('user.absensi.permission'), {
    keterangan: permissionReason.value,
    jenis_izin: permissionType.value,
  }, {
    onSuccess: () => {
      showPermissionModal.value = false;
      permissionReason.value = '';
      permissionType.value = 'penuh';
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Permintaan izin berhasil diajukan.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: errors.message || 'Terjadi kesalahan saat mengajukan izin.',
      });
    }
  });
};

// Late arrival functions
const cancelLateArrival = () => {
  showLateArrivalModal.value = false;
  lateArrivalReason.value = '';
};

const submitLateArrival = () => {
  if (!lateArrivalReason.value.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Keterangan keterlambatan tidak boleh kosong.',
    });
    return;
  }
  
  router.post(route('user.absensi.checkin'), {
    lat: null, // We already have location from previous check-in
    lng: null,
    keterangan: lateArrivalReason.value,
  }, {
    onSuccess: () => {
      showLateArrivalModal.value = false;
      lateArrivalReason.value = '';
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Check-in berhasil dicatat dengan keterangan keterlambatan.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: errors.message || 'Terjadi kesalahan saat menyimpan keterangan.',
      });
    }
  });
};
</script>