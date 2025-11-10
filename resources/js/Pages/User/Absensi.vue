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
      <Header class="z-10"
        :user="user" 
        current-page="Absensi" 
        mobile-breadcrumb="Absensi"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-16 p-4 sm:p-6">
        <div class="max-w-5xl mx-auto">
          <!-- Header Section -->
          <div class="mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Absensi Hari Ini</h1>
            <p class="text-gray-600 mt-2">{{ currentDate }}</p>
          </div>

          <!-- Temporary Testing Toggle - Only visible in development/testing -->
          <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <AlertTriangleIcon class="h-5 w-5 text-yellow-600 mr-2" />
                <span class="text-yellow-800 font-medium">Mode Testing</span>
              </div>
              <div class="flex items-center">
                <span class="text-sm text-yellow-700 mr-3">Nonaktifkan Radius Lokasi</span>
                <button 
                  @click="toggleLocationValidation"
                  :class="locationValidationDisabled ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-300 hover:bg-gray-400'"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                >
                  <span 
                    :class="locationValidationDisabled ? 'translate-x-6' : 'translate-x-1'"
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  />
                </button>
              </div>
            </div>
            <p class="text-xs text-yellow-600 mt-2">Tombol ini hanya tersedia untuk tujuan testing dan akan dihapus dalam produksi.</p>
          </div>

          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-5 bg-green-50 text-green-700 rounded-2xl border border-green-100 transition-all duration-300 shadow-md">
            <div class="flex items-center">
              <CheckCircleIcon class="h-6 w-6 mr-3 flex-shrink-0" />
              <span class="font-medium">{{ $page.props.flash.success }}</span>
            </div>
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-6 p-5 bg-red-50 text-red-700 rounded-2xl border border-red-100 transition-all duration-300 shadow-md">
            <div class="flex items-center">
              <XCircleIcon class="h-6 w-6 mr-3 flex-shrink-0" />
              <span class="font-medium">{{ $page.props.flash.error }}</span>
            </div>
          </div>
          <div v-if="$page.props.flash && $page.props.flash.prompt_keterangan" class="mb-6 p-5 bg-yellow-50 text-yellow-700 rounded-2xl border border-yellow-100 transition-all duration-300 shadow-md">
            <div class="flex items-center">
              <AlertCircleIcon class="h-6 w-6 mr-3 flex-shrink-0" />
              <span class="font-medium">{{ $page.props.flash.prompt_keterangan }}</span>
            </div>
          </div>

          <!-- Full Leave Message -->
          <div v-if="todayIzin && todayIzin.jenis_izin === 'penuh'" class="mb-8 p-5 bg-blue-50 text-blue-700 rounded-2xl border border-blue-100 transition-all duration-300 shadow-md">
            <div class="flex items-start">
              <BanIcon class="h-6 w-6 mr-3 flex-shrink-0 mt-0.5" />
              <div>
                <span class="font-medium">Anda sedang dalam status izin penuh. Tidak perlu melakukan absensi hari ini.</span>
                <p class="mt-2 text-sm">Keterangan: {{ todayIzin.keterangan }}</p>
              </div>
            </div>
          </div>

          <!-- Partial Leave Message -->
          <div v-if="todayIzin && todayIzin.jenis_izin === 'parsial'" class="mb-8 p-5 bg-yellow-50 text-yellow-700 rounded-2xl border border-yellow-100 transition-all duration-300 shadow-md">
            <div class="flex items-start">
              <ClockIcon class="h-6 w-6 mr-3 flex-shrink-0 mt-0.5" />
              <div>
                <span class="font-medium">Anda memiliki izin parsial hari ini.</span>
                <p class="mt-2 text-sm">Keterangan: {{ todayIzin.keterangan }}</p>
                <p class="mt-1 text-sm">Anda diperbolehkan melakukan Check-in, namun tidak wajib melakukan Check-out.</p>
              </div>
            </div>
          </div>

          <!-- Late Arrival Explanation Modal -->
          <div v-if="showLateArrivalModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-7 w-full max-w-md shadow-2xl transform transition-all duration-300 scale-100">
              <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-semibold text-gray-900">Keterangan Keterlambatan</h3>
                <button @click="cancelLateArrival" class="text-gray-400 hover:text-gray-500 focus:outline-none p-2 rounded-full hover:bg-gray-100">
                  <XIcon class="h-5 w-5" />
                </button>
              </div>
              <p class="text-gray-600 mb-5">Anda terlambat hari ini. Mohon berikan alasan keterlambatan Anda:</p>
              <textarea 
                v-model="lateArrivalReason" 
                class="w-full border border-gray-300 rounded-xl p-4 mb-5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm" 
                rows="4" 
                placeholder="Masukkan alasan keterlambatan..."
              ></textarea>
              <div class="flex justify-end gap-3">
                <button @click="cancelLateArrival" class="px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                  Batal
                </button>
                <button @click="submitLateArrival" class="px-5 py-2.5 bg-[#dc2626] text-white rounded-xl hover:bg-[#b91c1c] transition-colors duration-300 font-medium flex items-center">
                  <CheckIcon class="w-4 h-4 mr-2" />
                  Kirim
                </button>
              </div>
            </div>
          </div>

          <!-- Permission Request Modal -->
          <div v-if="showPermissionModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-7 w-full max-w-md shadow-2xl transform transition-all duration-300 scale-100">
              <div class="flex justify-between items-center mb-5">
                <h3 class="text-xl font-semibold text-gray-900">Ajukan Izin</h3>
                <button @click="cancelPermission" class="text-gray-400 hover:text-gray-500 focus:outline-none p-2 rounded-full hover:bg-gray-100">
                  <XIcon class="h-5 w-5" />
                </button>
              </div>
              <div class="mb-5">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_izin">
                  Jenis Izin
                </label>
                <select 
                  v-model="permissionType" 
                  class="w-full border border-gray-300 rounded-xl p-4 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
                >
                  <option value="penuh">Izin Penuh (Tidak perlu absensi)</option>
                  <option value="parsial">Izin Parsial (Bisa absen masuk saja)</option>
                </select>
              </div>
              <p class="text-gray-600 mb-5">Mohon berikan keterangan izin Anda:</p>
              <textarea 
                v-model="permissionReason" 
                class="w-full border border-gray-300 rounded-xl p-4 mb-5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm" 
                rows="4" 
                placeholder="Masukkan alasan izin..."
              ></textarea>
              <div class="flex justify-end gap-3">
                <button @click="cancelPermission" class="px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium">
                  Batal
                </button>
                <button @click="submitPermission" class="px-5 py-2.5 bg-[#dc2626] text-white rounded-xl hover:bg-[#b91c1c] transition-colors duration-300 font-medium">
                  Ajukan
                </button>
              </div>
            </div>
          </div>

          <!-- Already Completed Attendance Message -->
          <div v-if="hasCheckedIn && hasCheckedOut" class="mb-8 p-5 bg-green-50 text-green-700 rounded-2xl border border-green-100 transition-all duration-300 shadow-md">
            <div class="flex items-start">
              <CheckCircleIcon class="h-6 w-6 mr-3 flex-shrink-0 mt-0.5" />
              <div>
                <span class="font-medium">Absensi hari ini telah tercatat. Tetap semangat menjalankan tugas dan tanggung jawab!</span>
                <p class="mt-2 text-sm">Check-in: {{ todayAttendance.waktu_masuk }} | Check-out: {{ todayAttendance.waktu_keluar }}</p>
                <p v-if="todayAttendance.keterangan" class="mt-1 text-sm">Keterangan: {{ todayAttendance.keterangan }}</p>
              </div>
            </div>
          </div>

          <!-- Partial Leave Check-in Message -->
          <div v-if="todayAttendance && todayAttendance.status === 'Izin Parsial (Check-in)'" class="mb-8 p-5 bg-yellow-50 text-yellow-700 rounded-2xl border border-yellow-100 transition-all duration-300 shadow-md">
            <div class="flex items-start">
              <InfoIcon class="h-6 w-6 mr-3 flex-shrink-0 mt-0.5" />
              <div>
                <span class="font-medium">Anda telah melakukan check-in izin parsial.</span>
                <p class="mt-2 text-sm">Anda tidak wajib melakukan check-out.</p>
                <p v-if="todayAttendance.keterangan" class="mt-1 text-sm">Keterangan: {{ todayAttendance.keterangan }}</p>
              </div>
            </div>
          </div>

          <!-- Attendance and Leave Cards Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Unified Attendance Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
              <div class="flex flex-col h-full">
                <div class="flex items-center mb-4">
                  <div class="p-3 rounded-xl bg-red-50 mr-4">
                    <LogInIcon class="w-6 h-6 text-[#dc2626]" />
                  </div>
                  <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                      {{ getAttendanceCardTitle }}
                    </h2>
                    <p class="text-gray-600 text-sm">
                      {{ getAttendanceCardDescription }}
                    </p>
                  </div>
                </div>
                
                <div v-if="hasCheckedIn && !hasCheckedOut" class="mb-4 p-3 bg-gray-50 rounded-lg">
                  <div class="text-sm text-gray-500">Check-in:</div>
                  <div class="font-medium">{{ todayAttendance.waktu_masuk }}</div>
                </div>
                
                <!-- Attendance Warnings Section -->
                <div class="mb-4 space-y-3">
                  <!-- Location Warning -->
                  <div v-if="currentLocation && !isWithinOfficeRadius && !locationValidationDisabled" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                      <MapPinIcon class="h-4 w-4 text-red-600 mt-0.5 mr-2 flex-shrink-0" />
                      <div class="text-sm text-red-700">
                        <span class="font-medium">Peringatan:</span> Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="mt-auto">
                  <!-- Late arrival reason input - shown when user is late -->
                  <div v-if="isLateArrival && !hasCheckedIn" class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="late_reason">
                      Keterangan Keterlambatan
                    </label>
                    <textarea 
                      v-model="lateArrivalReason" 
                      id="late_reason"
                      class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm" 
                      rows="3" 
                      placeholder="Mohon berikan alasan keterlambatan Anda..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Harap isi keterangan keterlambatan Anda sebelum melakukan check-in.</p>
                  </div>
                  
                  <button
                    :disabled="isAttendanceButtonDisabled"
                    @click="handleAttendanceAction"
                    class="w-full py-4 rounded-xl font-semibold text-white transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02]"
                    :class="getAttendanceButtonClass"
                  >
                    <Loader2Icon v-if="gettingLocation && !isAttendanceButtonDisabled" class="w-5 h-5 mr-2 animate-spin" />
                    {{ getAttendanceButtonText }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Leave Request Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
              <div class="flex flex-col h-full">
                <div class="flex items-center mb-4">
                  <div class="p-3 rounded-xl bg-blue-50 mr-4">
                    <FileTextIcon class="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <h2 class="text-xl font-semibold text-gray-900">Pengajuan Izin</h2>
                    <p class="text-gray-600 text-sm">Ajukan izin jika tidak dapat hadir</p>
                  </div>
                </div>
                
                <div class="mb-4 flex-1">
                  <p class="text-gray-600 text-sm mb-3">Pilih jenis izin yang sesuai dengan kebutuhan Anda:</p>
                  <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-start">
                      <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 mr-2 flex-shrink-0"></div>
                      <span><span class="font-medium">Izin Penuh:</span> Tidak perlu absen hari ini</span>
                    </li>
                    <li class="flex items-start">
                      <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 mr-2 flex-shrink-0"></div>
                      <span><span class="font-medium">Izin Parsial:</span> Hanya perlu check-in</span>
                    </li>
                  </ul>
                </div>
                
                <div class="mt-auto">
                  <button 
                    @click="showPermissionModal = true"
                    class="w-full py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-semibold flex items-center justify-center"
                  >
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Ajukan Izin
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Location Information -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 transition-all duration-300 hover:shadow-xl">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <MapPinIcon class="w-6 h-6 text-[#dc2626] mr-2" />
              Informasi Lokasi
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <p class="text-gray-600 mb-4">Lokasi kantor POLDA TIK:</p>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <MapPinIcon class="w-4 h-4 mr-2" />
                  <span>Latitude: {{ officeLocation.lat }}</span>
                </div>
                <div class="flex items-center text-sm text-gray-500 mb-4">
                  <MapPinIcon class="w-4 h-4 mr-2" />
                  <span>Longitude: {{ officeLocation.lng }}</span>
                </div>
                <div class="flex items-center text-sm text-gray-500 mb-2">
                  <BuildingIcon class="w-4 h-4 mr-2" />
                  <span>Radius absensi: {{ attendanceRadius }} meter</span>
                </div>
              </div>
              <div class="h-48 rounded-xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 relative">
                <div ref="mapContainer" class="w-full h-full"></div>
                <div v-if="!mapLoaded" class="absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-70">
                  <Loader2Icon class="w-6 h-6 animate-spin text-[#dc2626]" />
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
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { 
  CheckCircleIcon, 
  InfoIcon, 
  BanIcon, 
  ClockIcon, 
  AlertTriangleIcon, 
  MapPinIcon, 
  BuildingIcon,
  XIcon,
  XCircleIcon,
  AlertCircleIcon,
  LogInIcon,
  LogOutIcon,
  FileTextIcon,
  PlusIcon,
  Loader2Icon,
  CheckIcon
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    todayAttendance: Object,
    todayIzin: Object,
    systemSettings: Object,
    testingModeDisabled: {
      type: Boolean,
      default: false
    }
});

// Office location with fallback values
const officeLocation = {
  lat: props.systemSettings?.location_latitude || 0.5240005,
  lng: props.systemSettings?.location_longitude || 123.0604752
};

// Attendance radius with fallback value
const attendanceRadius = props.systemSettings?.location_radius || 100;

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const gettingLocation = ref(false);
const currentLocation = ref(null);
const autoLocationDetected = ref(false);
const mapLoaded = ref(false);
const mapContainer = ref(null);

// Testing mode state - initialize from props
const locationValidationDisabled = ref(props.testingModeDisabled || false);

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

// Toggle location validation for testing
const toggleLocationValidation = () => {
  // Call the API to toggle the testing mode
  router.post(route('api.system-settings.toggle-test'), {}, {
    onSuccess: (response) => {
      // Update the local state based on the response
      if (response.testing_mode_disabled !== undefined) {
        locationValidationDisabled.value = response.testing_mode_disabled;
      }
      
      Swal.fire({
        icon: 'success',
        title: locationValidationDisabled.value ? 'Mode Testing Diaktifkan' : 'Mode Testing Dinonaktifkan',
        text: locationValidationDisabled.value 
          ? 'Validasi radius lokasi telah dinonaktifkan untuk testing.' 
          : 'Validasi radius lokasi telah diaktifkan kembali.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Gagal mengubah mode testing.',
      });
    }
  });
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

// Computed properties for unified attendance card
const getAttendanceCardTitle = computed(() => {
  if (hasCheckedIn.value && hasCheckedOut.value) {
    return 'Absensi Selesai'; 
  } else if (hasCheckedIn.value && !hasCheckedOut.value) {
    return 'Sudah Check-in';
  } else {
    return 'Belum Absen';
  }
});

const getAttendanceCardDescription = computed(() => {
  if (hasCheckedIn.value && hasCheckedOut.value) {
    return 'Absensi hari ini telah tercatat. Tetap semangat menjalankan tugas!';
  } else if (hasCheckedIn.value && !hasCheckedOut.value) {
    return 'Anda sudah melakukan check-in. Silakan lakukan check-out.';
  } else {
    return 'Silakan lakukan absensi hari ini.';
  }
});

// Check if user is late based on system settings and action type
const isLateArrival = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamMasuk = props.systemSettings?.jam_masuk || '08:00:00';
  const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';
  
  // Only consider late arrival for check-in actions, not check-out
  if (hasCheckedIn.value && !hasCheckedOut.value) {
    // For check-out, we don't consider it "late arrival"
    return false;
  }
  
  // User is considered late if current time is after jam_masuk but within working hours (before jam_pulang)
  return currentTime > jamMasuk && currentTime < jamPulang;
});

const getAttendanceButtonText = computed(() => {
  if (hasCheckedIn.value && !hasCheckedOut.value) {
    return 'Check-out';
  } else {
    return 'Check-in';
  }
});

const isAttendanceButtonDisabled = computed(() => {
  // Disable if user has full leave
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    return true;
  }
  
  // Disable if user has valid izin
  if (props.todayAttendance && props.todayAttendance.status === 'Izin (Valid)') {
    return true;
  }
  
  // Disable if user has partial leave and already checked in
  if (props.todayIzin && props.todayIzin.jenis_izin === 'parsial' && 
      props.todayAttendance && props.todayAttendance.status === 'Izin Parsial (Check-in)') {
    return true;
  }
  
  // Disable check-out button if not checked in yet
  if (!hasCheckedIn.value && hasCheckedOut.value) {
    return true;
  }
  
  // Disable if already completed both check-in and check-out
  if (hasCheckedIn.value && hasCheckedOut.value) {
    return true;
  }
  
  return false;
});

const getAttendanceButtonClass = computed(() => {
  if (isAttendanceButtonDisabled.value) {
    return 'bg-gray-100 text-gray-400 cursor-not-allowed';
  } else if (hasCheckedIn.value && !hasCheckedOut.value) {
    // Check-out button
    return 'bg-gray-900 hover:bg-black';
  } else {
    // Check-in button
    return 'bg-[#dc2626] hover:bg-[#b91c1c]';
  }
});

// Handle attendance action (check-in or check-out)
const handleAttendanceAction = () => {
  if (hasCheckedIn.value && !hasCheckedOut.value) {
    checkOut();
  } else {
    checkIn();
  }
};

// Check if current time is within valid check-in window
const isValidCheckInTime = computed(() => {
  if (!props.systemSettings) return true;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamMasuk = props.systemSettings?.jam_masuk || '08:00:00';
  const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';
  
  // Allow check-in from 1 hour before jam_masuk until jam_pulang
  const startCheckInTime = new Date(`1970-01-01T${jamMasuk}`);
  startCheckInTime.setHours(startCheckInTime.getHours() - 1);
  const startCheckInTimeString = startCheckInTime.toTimeString().substring(0, 8);
  
  // Users should not be allowed to check in after jam_pulang
  // They should be marked as absent if they try to check in after jam_pulang
  return currentTime >= startCheckInTimeString && currentTime < jamPulang;
});

// Check if current location is within office radius
const isWithinOfficeRadius = computed(() => {
  // If location validation is disabled for testing, always return true
  if (locationValidationDisabled.value) return true;
  
  if (!currentLocation.value || !props.systemSettings) return true;
  
  // Ensure we have valid coordinates
  const officeLat = props.systemSettings?.location_latitude;
  const officeLng = props.systemSettings?.location_longitude;
  
  if (officeLat === undefined || officeLng === undefined) return true;
  
  const lat1 = currentLocation.value.lat;
  const lng1 = currentLocation.value.lng;
  const lat2 = officeLat;
  const lng2 = officeLng;
  const radius = props.systemSettings?.location_radius || 100;
  
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
  // Initialize map
  initMap();
  
  // Always auto-detect location when page loads
  // Small delay to ensure page is fully loaded
  setTimeout(() => {
    getCurrentLocation();
  }, 1000);
});

// Initialize map
const initMap = () => {
  // Small delay to ensure DOM is ready
  setTimeout(() => {
    if (mapContainer.value) {
      try {
        // Create map instance
        const map = L.map(mapContainer.value).setView([officeLocation.lat, officeLocation.lng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add marker for office location
        const marker = L.marker([officeLocation.lat, officeLocation.lng]).addTo(map);
        
        // Add circle for attendance radius
        const circle = L.circle([officeLocation.lat, officeLocation.lng], {
          color: '#dc2626',
          fillColor: '#dc2626',
          fillOpacity: 0.2,
          radius: attendanceRadius
        }).addTo(map);
        
        // Add popup to marker
        marker.bindPopup("Kantor POLDA TIK").openPopup();
        
        // Set map as loaded
        mapLoaded.value = true;
      } catch (error) {
        console.error('Error initializing map:', error);
        // Set map as loaded even if there's an error to hide the loading spinner
        mapLoaded.value = true;
      }
    }
  }, 100);
};

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
  
  // Check if user is within office radius (unless disabled for testing)
  if (currentLocation.value && !isWithinOfficeRadius.value && !locationValidationDisabled.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.',
    });
    return;
  }
  
  // Check if user is trying to check in outside of valid working hours
  if (!isValidCheckInTime.value) {
    const jamMasuk = props.systemSettings?.jam_masuk || '08:00';
    const jamPulang = props.systemSettings?.jam_pulang || '17:00';
    
    // Calculate the valid check-in window for better messaging
    const startCheckInTime = new Date(`1970-01-01T${jamMasuk}`);
    startCheckInTime.setHours(startCheckInTime.getHours() - 1);
    const startCheckInTimeString = startCheckInTime.toTimeString().substring(0, 5);
    
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: `Jam check-in yang diperbolehkan: ${startCheckInTimeString} - ${jamPulang}. Check-in di luar jam tersebut tidak diperbolehkan.`,
      showCancelButton: false,
      confirmButtonText: 'OK'
    });
    return;
  }
  
  // If user is late and hasn't provided a reason, show error
  // Only apply this validation when actually checking in (not when already checked in)
  // Skip this validation if user is outside working hours
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';
  const isOutsideWorkingHours = currentTime >= jamPulang;

  if (isLateArrival.value && !lateArrivalReason.value.trim() && !hasCheckedIn.value && !isOutsideWorkingHours) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Anda terlambat. Mohon isi keterangan keterlambatan sebelum melakukan check-in.',
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
  // Prepare data for check-in request
  const requestData = {
    lat: lat,
    lng: lng,
  };
  
  // Add late arrival reason if user is late
  if (isLateArrival.value && lateArrivalReason.value.trim()) {
    requestData.keterangan = lateArrivalReason.value;
  }
  
  // Add flag to indicate if user is checking in outside of working hours
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';

  if (currentTime > jamPulang) {
    requestData.outside_working_hours = true;
  }
  
  router.post(route('user.absensi.checkin'), requestData, {
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
        // Clear the late arrival reason after successful check-in
        lateArrivalReason.value = '';
        
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
  
  // Check if user is within office radius (unless disabled for testing)
  if (currentLocation.value && !isWithinOfficeRadius.value && !locationValidationDisabled.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.',
    });
    return;
  }
  
  // Remove working hours validation for check-out
  // const isValidCheckOutTime = computed(() => {
  //   if (!props.systemSettings) return true;
  //   
  //   const now = new Date();
  //   const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  //   const jamMasuk = props.systemSettings?.jam_masuk || '08:00:00';
  //   const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';
  //   
  //   // Allow check-out from jam_masuk onwards until 2 hours after jam_pulang
  //   const endCheckOutTime = new Date(`1970-01-01T${jamPulang}`);
  //   endCheckOutTime.setHours(endCheckOutTime.getHours() + 2);
  //   const endCheckOutTimeString = endCheckOutTime.toTimeString().substring(0, 8);
  //   
  //   return currentTime >= jamMasuk && currentTime <= endCheckOutTimeString;
  // });
  
  // Warn if outside working hours for check-out
  // if (!isValidCheckOutTime.value) {
  //   const jamMasuk = props.systemSettings?.jam_masuk || '08:00';
  //   const jamPulang = props.systemSettings?.jam_pulang || '17:00';
  //   
  //   // Calculate the valid check-out window for better messaging
  //   const endCheckOutTime = new new Date(`1970-01-01T${jamPulang}`);
  //   endCheckOutTime.setHours(endCheckOutTime.getHours() + 2);
  //   const endCheckOutTimeString = endCheckOutTime.toTimeString().substring(0, 5);
  //   
  //   Swal.fire({
  //     icon: 'warning',
  //     title: 'Peringatan!',
  //     text: `Jam check-out yang diperbolehkan: ${jamMasuk} - ${endCheckOutTimeString}. Jika dilanjutkan, Anda akan ditandai sebagai tidak hadir (alpha).`,
  //     showCancelButton: true,
  //     confirmButtonText: 'Lanjutkan',
  //     cancelButtonText: 'Batal'
  //   }).then((result) => {
  //     if (result.isConfirmed) {
  //       performCheckOut();
  //     }
  //   });
  //   return;
  // }
  
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
  
  // Get today's date in YYYY-MM-DD format
  const today = new Date().toISOString().split('T')[0];
  
  router.post(route('user.absensi.permission'), {
    keterangan: permissionReason.value,
    jenis_izin: permissionType.value,
    tanggal_mulai: today,
    tanggal_selesai: today,
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
  
  // Check if current time is after checkout time
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamPulang = props.systemSettings?.jam_pulang || '17:00:00';
  
  const requestData = {
    lat: currentLocation.value?.lat || 0, // Use current location or default to 0
    lng: currentLocation.value?.lng || 0,
  };
  
  // If user is checking in after work hours, mark as absent regardless of late arrival reason
  if (currentTime > jamPulang) {
    requestData.outside_working_hours = true;
    requestData.keterangan = 'Check-in dilakukan di luar jam kerja';
  } else {
    requestData.keterangan = lateArrivalReason.value;
  }
  
  router.post(route('user.absensi.checkin'), requestData, {
    onSuccess: () => {
      showLateArrivalModal.value = false;
      lateArrivalReason.value = '';
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: currentTime > jamPulang 
          ? 'Check-in berhasil dicatat. Anda telah ditandai sebagai tidak hadir (alpha) karena check-in dilakukan di luar jam kerja.'
          : 'Check-in berhasil dicatat dengan keterangan keterlambatan.',
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

// Close modals when pressing Escape key
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (showLateArrivalModal.value) {
      cancelLateArrival();
    }
    if (showPermissionModal.value) {
      cancelPermission();
    }
  }
});
</script>