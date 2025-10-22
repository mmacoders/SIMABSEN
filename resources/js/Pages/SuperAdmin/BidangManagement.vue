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
                            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manajemen Bidang</h2>
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
                                        <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <UserIcon class="h-4 w-4 inline mr-2" />
                                            Profil
                                        </a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" @click.prevent="logout">
                                            <LogOutIcon class="h-4 w-4 inline mr-2" />
                                            Keluar
                                        </a>
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
                    <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ $page.props.flash.error }}
                    </div>

                    <div class="mb-6 flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Manajemen Bidang</h1>
                            <p class="text-gray-600">Kelola bidang dalam sistem absensi</p>
                        </div>
                        <button
                            @click="showCreateModal = true"
                            class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition flex items-center"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" />
                            Tambah Bidang
                        </button>
                    </div>

                    <!-- Bidang Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div
                            v-for="bidang in bidangs"
                            :key="bidang.id"
                            class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition"
                        >
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-lg bg-red-50">
                                        <BuildingIcon class="h-6 w-6 text-[#C62828]" />
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ bidang.nama_bidang }}</h3>
                                        <p class="text-sm text-gray-600">{{ bidang.description }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button 
                                        @click="editBidang(bidang)"
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
                                    >
                                        <EditIcon class="h-4 w-4" />
                                    </button>
                                    <button 
                                        @click="deleteBidang(bidang)"
                                        class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg"
                                    >
                                        <TrashIcon class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Admin:</span>
                                    <span class="font-medium">{{ bidang.admin_count }}</span>
                                </div>
                                <div class="flex justify-between text-sm mt-1">
                                    <span class="text-gray-600">Pegawai:</span>
                                    <span class="font-medium">{{ bidang.user_count }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="showCreateModal || showEditModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ showEditModal ? 'Edit Bidang' : 'Tambah Bidang' }}
                </h3>

                <form @submit.prevent="showEditModal ? updateBidang() : createBidang()">
                    <div class="mb-4">
                        <InputLabel for="nama_bidang" value="Nama Bidang" />
                        <TextInput
                            id="nama_bidang"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="bidangForm.nama_bidang"
                            required
                            autofocus
                        />
                        <InputError class="mt-2" :message="bidangForm.errors.nama_bidang" />
                    </div>

                    <div class="mb-4">
                        <InputLabel for="description" value="Deskripsi" />
                        <textarea
                            id="description"
                            class="mt-1 block w-full border-gray-300 focus:border-[#C62828] focus:ring-[#C62828] rounded-md shadow-sm"
                            v-model="bidangForm.description"
                            rows="3"
                        ></textarea>
                        <InputError class="mt-2" :message="bidangForm.errors.description" />
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <SecondaryButton @click="closeModal">Batal</SecondaryButton>
                        <PrimaryButton :disabled="bidangForm.processing" class="bg-[#C62828] hover:bg-[#b71c1c]">
                            {{ showEditModal ? 'Update' : 'Simpan' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import {
    BellIcon,
    ChevronDownIcon,
    UserIcon,
    LogOutIcon,
    BuildingIcon,
    PlusIcon,
    EditIcon,
    TrashIcon,
    MenuIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    bidangs: Array,
});

// State
const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingBidang = ref(null);

// Form
const bidangForm = useForm({
    nama_bidang: '',
    description: '',
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

const createBidang = () => {
    bidangForm.post(route('superadmin.bidang.store'), {
        onSuccess: () => {
            closeModal();
            bidangForm.reset();
        },
    });
};

const editBidang = (bidang) => {
    editingBidang.value = bidang;
    bidangForm.nama_bidang = bidang.nama_bidang;
    bidangForm.description = bidang.description;
    showEditModal.value = true;
};

const updateBidang = () => {
    bidangForm.patch(route('superadmin.bidang.update', editingBidang.value.id), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const deleteBidang = (bidang) => {
    if (confirm('Apakah Anda yakin ingin menghapus bidang ini? Tindakan ini akan menghapus semua data terkait.')) {
        router.delete(route('superadmin.bidang.destroy', bidang.id));
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    editingBidang.value = null;
    bidangForm.reset();
    bidangForm.clearErrors();
};

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
    if (profileMenuOpen.value && !event.target.closest('.relative')) {
        profileMenuOpen.value = false;
    }
});
</script>