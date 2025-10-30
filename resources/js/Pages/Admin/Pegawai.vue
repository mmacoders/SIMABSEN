<template>
    <AdminLayout page-title="Data Pegawai" mobile-page-title="Pegawai">
        <div class="p-4 sm:p-6 bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Data Pegawai</h1>
                <p class="text-gray-600 mt-2">Kelola data pegawai POLDA TIK.</p>
            </div>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                {{ $page.props.flash.error }}
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-8 transition-all duration-300 hover:shadow-xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-5 flex items-center">
                    <SearchIcon class="w-5 h-5 text-[#dc2626] mr-2" /> Filter Pegawai
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Pegawai</label>
                        <div class="relative">
                            <input
                                type="text"
                                v-model="filters.search"
                                placeholder="Nama atau bidang..."
                                class="w-full border border-gray-300 rounded-lg p-2 pl-10"
                                @input="debounceSearch"
                            />
                            <SearchIcon class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" />
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bidang</label>
                        <select
                            v-model="filters.bidang_id"
                            class="w-full border border-gray-300 rounded-lg p-2"
                            @change="applyFilters"
                        >
                            <option value="">Semua Bidang</option>
                            <option v-for="bidang in bidangs" :key="bidang.id" :value="bidang.id">
                                {{ bidang.nama_bidang }}
                            </option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="filters.status"
                            class="w-full border border-gray-300 rounded-lg p-2"
                            @change="applyFilters"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button
                            @click="resetFilters"
                            class="w-full border border-red-500 text-red-500 px-4 py-2 rounded-lg hover:bg-red-50 transition-all duration-200 ease-in-out"
                        >
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pegawai Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div 
                    v-for="user in users.data" 
                    :key="user.id"
                    class="relative bg-white rounded-2xl shadow-md p-5 border border-gray-100 hover:shadow-xl transition-all duration-300 ease-in-out cursor-pointer"
                    :class="user.status === 'nonaktif' ? 'grayscale' : ''"
                    @click="viewDetail(user)"
                >
                    <!-- Status Indicator -->
                    <div class="absolute top-3 right-3">
                        <div 
                            v-if="user.status === 'aktif'" 
                            class="w-3 h-3 rounded-full bg-red-500"
                            title="Aktif"
                        ></div>
                        <div 
                            v-else 
                            class="w-3 h-3 rounded-full bg-gray-300"
                            title="Nonaktif"
                        ></div>
                    </div>
                    
                    <!-- Profile Picture -->
                    <div class="flex justify-center mb-4">
                        <img 
                            :src="user.profile_pict_url || '/images/profile.png'" 
                            :alt="user.name"
                            class="w-20 h-20 rounded-full ring-2 ring-red-500 object-cover"
                            @error="handleImageError"
                        />
                    </div>
                    
                    <!-- User Info -->
                    <div class="text-center">
                        <h3 class="font-semibold text-gray-900 truncate">{{ user.name }}</h3>
                        <p class="text-sm text-gray-600 truncate">{{ user.bidang?.nama_bidang || '-' }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ user.jabatan || '-' }}</p>
                        
                        <!-- Status Badge -->
                        <div class="mt-3">
                            <span 
                                v-if="user.status === 'aktif'"
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
                        </div>
                    </div>
                    
                    <!-- Overlay Actions -->
                    <div 
                        class="absolute inset-0 bg-black/60 opacity-0 hover:opacity-100 flex items-center justify-center gap-3 transition-all duration-300 rounded-2xl"
                        @click.stop
                    >
                        <button 
                            @click="editUser(user)"
                            class="bg-white text-red-600 px-3 py-1 rounded-lg font-medium hover:bg-red-100 transition"
                            title="Edit Profil"
                        >
                            <EditIcon class="h-4 w-4" />
                        </button>
                        <button 
                            @click="toggleUserStatus(user)"
                            class="bg-red-600 text-white px-3 py-1 rounded-lg font-medium hover:bg-red-700 transition"
                            :title="user.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'"
                        >
                            <BanIcon v-if="user.status === 'aktif'" class="h-4 w-4" />
                            <CheckIcon v-else class="h-4 w-4" />
                        </button>
                        <button 
                            @click="resetUserPassword(user)"
                            class="bg-gray-100 text-gray-700 px-3 py-1 rounded-lg font-medium hover:bg-gray-200 transition"
                            title="Reset Password"
                        >
                            <RefreshCwIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="users.data.length === 0" class="col-span-full text-center py-12">
                    <UsersIcon class="h-12 w-12 text-gray-400 mx-auto" />
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak ada data pegawai</h3>
                    <p class="mt-1 text-gray-500">Coba ubah filter pencarian Anda.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <template v-for="(link, index) in users.links" :key="index">
                        <span 
                            v-if="link.url === null" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed rounded-lg"
                        >
                            {{ link.label }}
                        </span>
                        <a 
                            v-else-if="link.label === 'pagination.previous'" 
                            :href="link.url"
                            class="relative inline-flex items-center px-2 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg"
                            :class="link.active ? 'z-10 bg-red-600 text-white border-red-600' : ''"
                        >
                            <ChevronLeftIcon class="h-5 w-5" />
                        </a>
                        <a 
                            v-else-if="link.label === 'pagination.next'" 
                            :href="link.url"
                            class="relative inline-flex items-center px-2 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg"
                            :class="link.active ? 'z-10 bg-red-600 text-white border-red-600' : ''"
                        >
                            <ChevronRightIcon class="h-5 w-5" />
                        </a>
                        <a 
                            v-else 
                            :href="link.url"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg"
                            :class="link.active ? 'z-10 bg-red-600 text-white border-red-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-100'"
                        >
                            {{ link.label }}
                        </a>
                    </template>
                </nav>
            </div>
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
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, reactive, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import {
    SearchIcon,
    UsersIcon,
    EditIcon,
    BanIcon,
    RefreshCwIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    XIcon,
    CheckIcon
} from 'lucide-vue-next';

const props = defineProps({
    users: Object,
    bidangs: Array,
    filters: Object,
});

// State
const showEditModal = ref(false);
const selectedUser = ref(null);

// Filters
const filters = reactive({
    search: props.filters.search || '',
    bidang_id: props.filters.bidang_id || '',
    status: props.filters.status || '',
});

// Edit form
const editForm = useForm({
    id: null,
    name: '',
    email: '',
    no_hp: '',
    pangkat: '',
    nrp: '',
    nip: '',
    bidang_id: '',
    jabatan: '',
    status: 'aktif',
});

let searchTimeout;

// Methods
const applyFilters = () => {
    router.get(route('admin.pegawai'), {
        ...filters
    }, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.bidang_id = '';
    filters.status = '';
    applyFilters();
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const viewDetail = (user) => {
    router.visit(route('admin.pegawai.show', user));
};

const editUser = (user) => {
    selectedUser.value = user;
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.no_hp = user.no_hp || '';
    editForm.pangkat = user.pangkat || '';
    editForm.nrp = user.nrp || '';
    editForm.nip = user.nip || '';
    editForm.bidang_id = user.bidang_id;
    editForm.jabatan = user.jabatan || '';
    editForm.status = user.status;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
};

const updateUser = () => {
    editForm.patch(route('admin.pegawai.update', editForm.id), {
        onSuccess: () => {
            closeEditModal();
            // Reload the page to show updated data
            router.reload({ only: ['users'] });
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
        }
    });
};

const toggleUserStatus = (user) => {
    if (confirm(`Apakah Anda yakin ingin ${user.status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan'} pegawai ini?`)) {
        router.patch(route('admin.pegawai.toggle-status', user), {}, {
            onSuccess: () => {
                // Reload the page to show updated data
                router.reload({ only: ['users'] });
            }
        });
    }
};

const resetUserPassword = (user) => {
    if (confirm('Apakah Anda yakin ingin mereset password pegawai ini?')) {
        router.post(route('admin.pegawai.reset-password', user), {}, {
            onSuccess: () => {
                // Password reset success message will be shown via flash message
            }
        });
    }
};

const handleImageError = (event) => {
    event.target.src = '/images/profile.png';
};

// Computed property to determine if user has NIP or NRP
const hasNip = computed(() => {
    return selectedUser.value && selectedUser.value.nip && selectedUser.value.nip.trim() !== '';
});

// Computed property for NIP/NRP value
const nipOrNrp = computed({
    get() {
        if (!selectedUser.value) return '';
        return hasNip.value ? selectedUser.value.nip : selectedUser.value.nrp;
    },
    set(value) {
        if (!selectedUser.value) return;
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