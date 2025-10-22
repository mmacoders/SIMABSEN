<template>
  <div class="flex min-h-screen bg-[#F5F6FA] font-['Inter']">
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
              <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Admin</h2>
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

      <main class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ $page.props.flash.success }}
          </div>

          <!-- Admin Table Component -->
          <AdminTable 
            :admins="admins.data"
            @open-create-modal="showCreateModal = true"
            @edit-admin="editAdmin"
            @transfer-admin="openTransferModal"
            @toggle-status="openToggleStatusModal"
            @delete-admin="openDeleteModal"
          />
        </div>
      </main>

      <!-- Create/Edit Modal -->
      <Modal :show="showCreateModal || showEditModal" @close="closeModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showEditModal ? 'Edit Admin' : 'Tambah Admin' }}
          </h3>

          <AdminForm 
            :admin="editingAdmin"
            :bidangs="bidangs"
            :is-edit="showEditModal"
            @submit="handleSubmit"
            @cancel="closeModal"
          />
        </div>
      </Modal>

      <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :open="showDeleteModal"
        title="Hapus Admin"
        message="Apakah Anda yakin ingin menghapus admin ini? Tindakan ini tidak dapat dibatalkan."
        type="danger"
        confirm-text="Hapus"
        cancel-text="Batal"
        @close="showDeleteModal = false"
        @confirm="confirmDelete"
      />

      <!-- Toggle Status Confirmation Modal -->
      <ConfirmModal
        :open="showToggleStatusModal"
        :title="adminToToggle?.status === 'active' ? 'Nonaktifkan Admin' : 'Aktifkan Admin'"
        :message="`Apakah Anda yakin ingin ${adminToToggle?.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'} admin ini?`"
        type="info"
        :confirm-text="adminToToggle?.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'"
        cancel-text="Batal"
        @close="showToggleStatusModal = false"
        @confirm="confirmToggleStatus"
      />

      <!-- Transfer Bidang Modal -->
      <TransferBidangModal
        :open="showTransferModal"
        :admin="adminToTransfer"
        :bidangs="bidangs"
        @close="showTransferModal = false"
        @transfer="handleTransfer"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import AdminTable from '@/Components/AdminTable.vue';
import AdminForm from '@/Components/AdminForm.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import TransferBidangModal from '@/Components/TransferBidangModal.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import {
  BellIcon,
  ChevronDownIcon,
  UserIcon,
  LogOutIcon,
  MenuIcon
} from 'lucide-vue-next';

const props = defineProps({
  admins: Object,
  bidangs: Array,
});

const profileMenuOpen = ref(false);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showToggleStatusModal = ref(false);
const showTransferModal = ref(false);

const editingAdmin = ref(null);
const adminToDelete = ref(null);
const adminToToggle = ref(null);
const adminToTransfer = ref(null);

const adminForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  bidang_id: '',
  status: 'active',
});

const createAdmin = (form) => {
  form.post(route('superadmin.admin.store'), {
    onSuccess: () => {
      closeModal();
      form.reset();
    },
  });
};

const updateAdmin = (form) => {
  form.patch(route('superadmin.admin.update', editingAdmin.value.id), {
    onSuccess: () => {
      closeModal();
    },
  });
};

const handleSubmit = (form) => {
  if (showEditModal.value) {
    updateAdmin(form);
  } else {
    createAdmin(form);
  }
};

const editAdmin = (admin) => {
  editingAdmin.value = admin;
  showEditModal.value = true;
};

const openDeleteModal = (admin) => {
  adminToDelete.value = admin;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (adminToDelete.value) {
    router.delete(route('superadmin.admin.destroy', adminToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        adminToDelete.value = null;
      }
    });
  }
};

const openToggleStatusModal = (admin) => {
  adminToToggle.value = admin;
  showToggleStatusModal.value = true;
};

const confirmToggleStatus = () => {
  if (adminToToggle.value) {
    router.patch(route('superadmin.admin.toggle-status', adminToToggle.value.id), {}, {
      onSuccess: () => {
        showToggleStatusModal.value = false;
        adminToToggle.value = null;
      }
    });
  }
};

const openTransferModal = (admin) => {
  adminToTransfer.value = admin;
  showTransferModal.value = true;
};

const handleTransfer = () => {
  showTransferModal.value = false;
  adminToTransfer.value = null;
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingAdmin.value = null;
  adminForm.reset();
  adminForm.clearErrors();
};

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

// Close profile menu when clicking outside
document.addEventListener('click', (event) => {
  if (profileMenuOpen.value && !event.target.closest('.relative')) {
    profileMenuOpen.value = false;
  }
});
</script>