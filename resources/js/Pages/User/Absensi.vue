<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <Sidebar 
      :open="sidebarOpen" 
      :collapsed="sidebarCollapsed"
      @toggle="toggleSidebar"
      @collapse="handleSidebarCollapse"
    />
    
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Header -->
      <Header 
        :sidebar-open="sidebarOpen" 
        @toggle-sidebar="toggleSidebar"
      />
      
      <!-- Main Content -->
      <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
          <!-- Page Title -->
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Absensi Harian</h1>
            <p class="text-gray-600 mt-2">{{ currentDate }}</p>
          </div>

          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
            {{ $page.props.flash.error }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.prompt_keterangan" class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            {{ $page.props.flash.prompt_keterangan }}
          </div>

          <!-- Attendance Card -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 transition-all duration-300 hover:shadow-xl">
            <div class="flex flex-col h-full">
              <div class="flex items-center mb-4">
                <div class="p-3 rounded-xl bg-red-50 mr-4">
                  <ClockIcon class="w-6 h-6 text-[#dc2626]" />
                </div>
                <div>
                  <h2 class="text-xl font-semibold text-gray-900">Absensi</h2>
                  <p class="text-gray-600 text-sm">{{ getAttendanceCardTitle }}</p>
                </div>
              </div>
              
              <div class="mb-4 flex-1">
                <p class="text-gray-600 text-sm">
                  {{ getAttendanceCardDescription }}
                </p>
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
      </main>
    </div>
  </div>
  
  <!-- Permission Modal -->
  <div v-if="showPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-900">Ajukan Izin</h3>
          <button @click="showPermissionModal = false" class="text-gray-500 hover:text-gray-700">
            <XIcon class="h-6 w-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitPermissionRequest" class="space-y-4">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_type">
              Jenis Izin
            </label>
            <select
              id="permission_type"
              v-model="permissionType"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
              required
            >
              <option value="penuh">Izin Penuh</option>
              <option value="parsial">Izin Parsial</option>
            </select>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                Tanggal Mulai
              </label>
              <input
                id="start_date"
                type="date"
                v-model="permissionStartDate"
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
                required
              />
            </div>
            
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
                Tanggal Selesai
              </label>
              <input
                id="end_date"
                type="date"
                v-model="permissionEndDate"
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
                required
              />
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_reason">
              Keterangan
            </label>
            <textarea
              id="permission_reason"
              v-model="permissionReason"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
              rows="3"
              placeholder="Jelaskan alasan izin Anda..."
              required
            ></textarea>
          </div>
          
          <!-- File Upload for Full Leave Requests -->
          <div v-if="permissionType === 'penuh'">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_file">
              Dokumen Pendukung (PDF/JPG/PNG)
            </label>
            <input
              id="permission_file"
              type="file"
              @change="handleFileChange"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
            />
            <p class="text-xs text-gray-500 mt-1">Unggah dokumen pendukung untuk izin cuti atau sakit (maks. 2MB)</p>
          </div>
          
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showPermissionModal = false"
              class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submittingPermission"
              class="flex-1 py-3 bg-[#dc2626] text-white rounded-xl hover:bg-[#b91c1c] transition-colors duration-200 flex items-center justify-center"
              :class="{ 'opacity-50 cursor-not-allowed': submittingPermission }"
            >
              <Loader2Icon v-if="submittingPermission" class="w-5 h-5 mr-2 animate-spin" />
              Ajukan
            </button>
          </div>
        </form>
      </div>
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
const showPermissionModal = ref(false);
const lateArrivalReason = ref('');
const permissionReason = ref('');
const permissionType = ref('penuh');
const permissionStartDate = ref(new Date().toISOString().split('T')[0]);
const permissionEndDate = ref(new Date().toISOString().split('T')[0]);
const permissionFile = ref(null);
const submittingPermission = ref(false);

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
  const data = {
    lat: lat,
    lng: lng
  };
  
  // Add late arrival reason if provided
  if (lateArrivalReason.value.trim()) {
    data.keterangan = lateArrivalReason.value.trim();
  }
  
  router.post(route('user.absensi.checkin'), data, {
    onStart: () => {
      gettingLocation.value = true;
    },
    onFinish: () => {
      gettingLocation.value = false;
      lateArrivalReason.value = ''; // Clear the late arrival reason after submission
    },
    onSuccess: () => {
      // Reload the page to update the attendance status
      router.reload();
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat melakukan check-in. Silakan coba lagi.',
      });
    }
  });
};

// Check-out function
const checkOut = () => {
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
    lng: lng
  }, {
    onStart: () => {
      gettingLocation.value = true;
    },
    onFinish: () => {
      gettingLocation.value = false;
    },
    onSuccess: () => {
      // Reload the page to update the attendance status
      router.reload();
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat melakukan check-out. Silakan coba lagi.',
      });
    }
  });
};

// Handle file change for permission request
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file type and size
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
    
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'File harus berupa PDF, JPG, JPEG, atau PNG.',
      });
      event.target.value = ''; // Clear the input
      return;
    }
    
    if (file.size > maxSize) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Ukuran file maksimal 2MB.',
      });
      event.target.value = ''; // Clear the input
      return;
    }
    
    permissionFile.value = file;
  }
};

// Submit permission request
const submitPermissionRequest = () => {
  submittingPermission.value = true;
  
  const formData = new FormData();
  formData.append('keterangan', permissionReason.value);
  formData.append('jenis_izin', permissionType.value);
  formData.append('tanggal_mulai', permissionStartDate.value);
  formData.append('tanggal_selesai', permissionEndDate.value);
  
  // Add file if provided and it's a full leave request
  if (permissionFile.value && permissionType.value === 'penuh') {
    formData.append('file', permissionFile.value);
  }
  
  router.post(route('user.absensi.permission'), formData, {
    onSuccess: () => {
      submittingPermission.value = false;
      showPermissionModal.value = false;
      permissionReason.value = '';
      permissionType.value = 'penuh';
      permissionStartDate.value = new Date().toISOString().split('T')[0];
      permissionEndDate.value = new Date().toISOString().split('T')[0];
      permissionFile.value = null;
      
      // Reload the page to update the izin status
      router.reload();
    },
    onError: (errors) => {
      submittingPermission.value = false;
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat mengajukan izin. Silakan coba lagi.',
      });
    }
  });
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
</script>