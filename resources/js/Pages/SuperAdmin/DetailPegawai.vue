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
                            <Link :href="route('superadmin.pegawai')" class="font-semibold text-xl text-gray-800 leading-tight hover:text-red-600">
                                ← Kembali
                            </Link>
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
                    <!-- Success/Error Messages -->
                    <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ $page.props.flash.error }}
                    </div>

                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2 mb-2">
                            <UserIcon class="text-red-600" />
                            Detail Pegawai
                        </h1>
                        <p class="text-gray-600">Informasi lengkap pegawai POLDA TIK.</p>
                    </div>

                    <!-- Pegawai Detail Card -->
                    <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                        <div class="flex flex-col md:flex-row gap-8">
                            <!-- Profile Picture -->
                            <div class="flex-shrink-0">
                                <img 
                                    :src="pegawai.profile_pict || '/images/default-profile.png'" 
                                    :alt="pegawai.name"
                                    class="w-32 h-32 rounded-full mx-auto ring-2 ring-red-500 object-cover"
                                    @error="handleImageError"
                                />
                            </div>
                            
                            <!-- User Info -->
                            <div class="flex-grow">
                                <h2 class="text-center md:text-left text-xl font-semibold text-gray-900">{{ pegawai.name }}</h2>
                                <p class="text-center md:text-left text-gray-500">{{ pegawai.email }}</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                    <div>
                                        <p class="text-sm text-gray-500">Bidang</p>
                                        <p class="font-medium">{{ pegawai.bidang?.nama_bidang || '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Jabatan</p>
                                        <p class="font-medium">{{ pegawai.jabatan || '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Pangkat</p>
                                        <p class="font-medium">{{ pegawai.pangkat || '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">NIP/NRP</p>
                                        <p class="font-medium">{{ pegawai.nip || pegawai.nrp || '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Nomor HP</p>
                                        <p class="font-medium">{{ pegawai.no_hp || '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p class="font-medium">
                                            <span 
                                                v-if="pegawai.status === 'aktif'"
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700"
                                            >
                                                Aktif
                                            </span>
                                            <span 
                                                v-else
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-600"
                                            >
                                                Nonaktif
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="flex flex-col gap-3">
                                <button 
                                    @click="editUser"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                >
                                    <EditIcon class="h-4 w-4 inline mr-2" />
                                    Edit
                                </button>
                                
                                <button 
                                    @click="toggleUserStatus"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition"
                                    :class="pegawai.status === 'aktif' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                                >
                                    <BanIcon v-if="pegawai.status === 'aktif'" class="h-4 w-4 inline mr-2" />
                                    <CheckIcon v-else class="h-4 w-4 inline mr-2" />
                                    {{ pegawai.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                
                                <button 
                                    @click="resetUserPassword"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                                >
                                    <RefreshCwIcon class="h-4 w-4 inline mr-2" />
                                    Reset Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Statistics -->
                    <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Absensi Bulan Ini</h3>
                        
                        <div class="bg-gray-50 p-4 rounded-lg text-center">
                            <p class="text-gray-700 text-sm font-medium">Total Absensi Bulan Ini</p>
                            <div class="flex justify-center gap-8 mt-4">
                                <div>
                                    <p class="text-2xl font-bold text-red-600">{{ stats.hadir }}</p>
                                    <span class="text-xs text-gray-500">Hadir</span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-blue-600">{{ stats.izin }}</p>
                                    <span class="text-xs text-gray-500">Izin</span>
                                </div>
                                <div>
                                    <p class="text-2xl font-bold text-yellow-600">{{ stats.terlambat }}</p>
                                    <span class="text-xs text-gray-500">Terlambat</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance Records -->
                    <div class="bg-white rounded-2xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Absensi Terbaru</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keluar</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="attendance in attendances" :key="attendance.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(attendance.tanggal) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ attendance.waktu_masuk || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ attendance.waktu_keluar || '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="getStatusClass(attendance)"
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            >
                                                {{ getStatusText(attendance) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ attendance.keterangan || '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data absensi
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Edit User Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center backdrop-blur-sm z-50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-fadeIn">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Edit Pegawai</h3>
                    <button @click="closeEditModal" class="text-gray-500 hover:text-gray-700">
                        <XIcon class="h-5 w-5" />
                    </button>
                </div>
                
                <form @submit.prevent="updateUser">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input
                                v-model="editForm.name"
                                type="text"
                                class="w-full border border-gray-300 rounded-lg p-2"
                                required
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                v-model="editForm.email"
                                type="email"
                                class="w-full border border-gray-300 rounded-lg p-2"
                                required
                            />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                                <input
                                    v-model="editForm.no_hp"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat</label>
                                <input
                                    v-model="editForm.pangkat"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NIP/NRP</label>
                            <input
                                v-model="nipOrNrp"
                                type="text"
                                class="w-full border border-gray-300 rounded-lg p-2"
                                :placeholder="hasNip ? 'NIP' : 'NRP'"
                            />
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bidang</label>
                                <select
                                    v-model="editForm.bidang_id"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                    required
                                >
                                    <option value="">Pilih Bidang</option>
                                    <option v-for="bidang in bidangs" :key="bidang.id" :value="bidang.id">
                                        {{ bidang.nama_bidang }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input
                                    v-model="editForm.jabatan"
                                    type="text"
                                    class="w-full border border-gray-300 rounded-lg p-2"
                                />
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="editForm.status"
                                class="w-full border border-gray-300 rounded-lg p-2"
                                required
                            >
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex gap-3">
                        <button
                            type="button"
                            @click="closeEditModal"
                            class="flex-1 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="flex-1 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import {
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogOutIcon,
    MenuIcon,
    EditIcon,
    BanIcon,
    RefreshCwIcon,
    XIcon,
    CheckIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    pegawai: Object,
    stats: Object,
    attendances: Array,
    bidangs: Array,
});

// State
const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const showEditModal = ref(false);

// Edit form
const editForm = useForm({
    id: props.pegawai.id,
    name: props.pegawai.name,
    email: props.pegawai.email,
    no_hp: props.pegawai.no_hp || '',
    pangkat: props.pegawai.pangkat || '',
    nrp: props.pegawai.nrp || '',
    nip: props.pegawai.nip || '',
    bidang_id: props.pegawai.bidang_id,
    jabatan: props.pegawai.jabatan || '',
    status: props.pegawai.status,
});

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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const getStatusClass = (attendance) => {
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'bg-blue-100 text-blue-800';
        case 'sakit':
            return 'bg-yellow-100 text-yellow-800';
        case 'terlambat':
        case 'Terlambat':
            return 'bg-orange-100 text-orange-800';
        case 'hadir':
        case 'Hadir':
            return attendance.waktu_masuk ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        case 'Izin Parsial (Check-in)':
        case 'Izin Parsial (Selesai)':
            return 'bg-purple-100 text-purple-800';
        default:
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'bg-green-100 text-green-800';
            } else if (attendance.waktu_masuk) {
                return 'bg-blue-100 text-blue-800';
            } else {
                return 'bg-gray-100 text-gray-800';
            }
    }
};

const getStatusText = (attendance) => {
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'Izin';
        case 'sakit':
            return 'Sakit';
        case 'terlambat':
        case 'Terlambat':
            return 'Terlambat';
        case 'hadir':
        case 'Hadir':
            return attendance.waktu_masuk ? 'Hadir' : 'Belum Absen';
        case 'Izin Parsial (Check-in)':
            return 'Izin Parsial (Check-in)';
        case 'Izin Parsial (Selesai)':
            return 'Izin Parsial (Selesai)';
        default:
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'Hadir';
            } else if (attendance.waktu_masuk) {
                return 'Sudah Check-in';
            } else {
                return attendance.status || 'Belum Absen';
            }
    }
};

const editUser = () => {
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const updateUser = () => {
    editForm.patch(route('superadmin.pegawai.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
            // Reload the page to show updated data
            router.reload();
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
};

const toggleUserStatus = () => {
    if (confirm(`Apakah Anda yakin ingin ${props.pegawai.status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan'} pegawai ini?`)) {
        router.patch(route('superadmin.pegawai.toggle-status', props.pegawai), {}, {
            onSuccess: () => {
                // Reload the page to show updated data
                router.reload();
            }
        });
    }
};

const resetUserPassword = () => {
    if (confirm('Apakah Anda yakin ingin mereset password pegawai ini?')) {
        router.post(route('superadmin.pegawai.reset-password', props.pegawai), {}, {
            onSuccess: () => {
                // Password reset success message will be shown via flash message
            }
        });
    }
};

const handleImageError = (event) => {
    event.target.src = '/images/default-profile.png';
};

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
    if (profileMenuOpen.value && !event.target.closest('.relative')) {
        profileMenuOpen.value = false;
    }
});

// Computed property to determine if user has NIP or NRP
const hasNip = computed(() => {
    return props.pegawai && props.pegawai.nip && props.pegawai.nip.trim() !== '';
});

// Computed property for NIP/NRP value
const nipOrNrp = computed({
    get() {
        if (!props.pegawai) return '';
        return hasNip.value ? props.pegawai.nip : props.pegawai.nrp;
    },
    set(value) {
        if (!props.pegawai) return;
        if (hasNip.value) {
            editForm.nip = value;
            editForm.nrp = '';
        } else {
            editForm.nrp = value;
            editForm.nip = '';
        }
    }
});

</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>