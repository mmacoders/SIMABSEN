<template>
  <div class="flex min-h-screen bg-[#F8F9FA]">
    <!-- Sidebar -->
    <Sidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content -->
    <div class="flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header 
        :user="user" 
        current-page="Perbarui Profil" 
        mobile-breadcrumb="Profil"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-16 p-4 sm:p-6 md:p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Page Title -->
          <div class="text-center mb-8">
            <div class="flex justify-center items-center mb-3">
              <UserCircleIcon class="h-8 w-8 text-gray-900 mr-2" />
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Perbarui Profil</h1>
            </div>
            <p class="text-gray-600">Ubah informasi pribadi dan foto profil Anda di sini.</p>
          </div>

          <div class="space-y-6">
            <!-- Profile Picture Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 flex flex-col items-center border border-gray-100 transition-all duration-300">
              <div class="relative mb-6">
                <div v-if="profilePreview" class="w-32 h-32 rounded-full overflow-hidden ring-2 ring-red-500">
                  <img :src="profilePreview" alt="Profile Preview" class="w-full h-full object-cover transition-all duration-300 ease-in-out">
                </div>
                <div v-else class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center ring-2 ring-red-500">
                  <UserCircleIcon class="h-16 w-16 text-gray-400" />
                </div>
              </div>
              
              <label class="bg-red-600 hover:bg-red-700 text-white rounded-lg px-4 py-2 text-sm transition-all cursor-pointer">
                <span>Ubah Foto</span>
                <input 
                  type="file" 
                  class="hidden" 
                  @change="handleProfilePictureChange"
                  accept="image/jpeg,image/png,image/jpg"
                >
              </label>
              <p class="text-xs text-gray-500 mt-2">Format JPG/PNG, maks. 2MB</p>
            </div>

            <!-- Profile Information Form -->
            <form @submit.prevent="submitProfile" class="bg-white rounded-2xl shadow-md border border-gray-100 p-6 space-y-4 transition-all duration-300">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Nama Lengkap</label>
                  <input
                    type="text"
                    v-model="profileForm.name"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.name }"
                  >
                  <div v-if="profileForm.errors.name" class="text-red-500 text-sm mt-1">{{ profileForm.errors.name }}</div>
                </div>

                <!-- NIP/NRP (Disabled) -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">NIP / NRP</label>
                  <input
                    type="text"
                    :value="profileForm.nip || profileForm.nrp"
                    disabled
                    class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-100 cursor-not-allowed"
                  >
                </div>

                <!-- Email -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Email</label>
                  <input
                    type="email"
                    v-model="profileForm.email"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.email }"
                  >
                  <div v-if="profileForm.errors.email" class="text-red-500 text-sm mt-1">{{ profileForm.errors.email }}</div>
                </div>

                <!-- Phone -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">No. HP</label>
                  <input
                    type="text"
                    v-model="profileForm.no_hp"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.no_hp }"
                  >
                  <div v-if="profileForm.errors.no_hp" class="text-red-500 text-sm mt-1">{{ profileForm.errors.no_hp }}</div>
                </div>

                <!-- Position -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Jabatan</label>
                  <input
                    type="text"
                    v-model="profileForm.jabatan"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.jabatan }"
                  >
                  <div v-if="profileForm.errors.jabatan" class="text-red-500 text-sm mt-1">{{ profileForm.errors.jabatan }}</div>
                </div>

                <!-- Department -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Bidang / Divisi</label>
                  <select
                    v-model="profileForm.bidang_id"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.bidang_id }"
                  >
                    <option value="">Pilih Bidang</option>
                    <option 
                      v-for="bidang in bidangs" 
                      :key="bidang.id" 
                      :value="bidang.id"
                    >
                      {{ bidang.nama_bidang }}
                    </option>
                  </select>
                  <div v-if="profileForm.errors.bidang_id" class="text-red-500 text-sm mt-1">{{ profileForm.errors.bidang_id }}</div>
                </div>

                <!-- Password -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Password Baru</label>
                  <input
                    type="password"
                    v-model="profileForm.password"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.password }"
                  >
                  <div v-if="profileForm.errors.password" class="text-red-500 text-sm mt-1">{{ profileForm.errors.password }}</div>
                </div>

                <!-- Password Confirmation -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Konfirmasi Password</label>
                  <input
                    type="password"
                    v-model="profileForm.password_confirmation"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-red-500 focus:outline-none transition-all"
                    :class="{ 'border-red-500': profileForm.errors.password_confirmation }"
                  >
                  <div v-if="profileForm.errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ profileForm.errors.password_confirmation }}</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row justify-end gap-4 pt-6">
                <button
                  type="button"
                  @click="cancelChanges"
                  class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-lg transition-all duration-200"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-200 flex items-center justify-center"
                  :class="{ 'opacity-60 cursor-not-allowed': isSubmitting }"
                >
                  <span v-if="isSubmitting" class="mr-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </span>
                  <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { UserCircleIcon } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    user: Object,
    bidangs: Array,
});

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

// Store original data for comparison
const originalData = {
    name: props.user.name || '',
    email: props.user.email || '',
    no_hp: props.user.no_hp || '',
    jabatan: props.user.jabatan || '',
    bidang_id: props.user.bidang_id || '',
    // profile_pict: null,
    nip: props.user.nip || '',
    nrp: props.user.nrp || '',
    pangkat: props.user.pangkat || '',
};

const profileForm = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    no_hp: props.user.no_hp || '',
    jabatan: props.user.jabatan || '',
    bidang_id: props.user.bidang_id || '',
    profile_pict: null,
    password: '',
    password_confirmation: '',
    nip: props.user.nip || '',
    nrp: props.user.nrp || '',
    pangkat: props.user.pangkat || '',
});

const profilePreview = ref(props.user.profile_pict_url || null);
const isSubmitting = ref(false);

const handleProfilePictureChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'File tidak valid',
                text: 'Hanya file JPG/PNG yang diperbolehkan',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File terlalu besar',
                text: 'Ukuran file maksimal 2MB',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        profileForm.profile_pict = file;
        // Create preview
        profilePreview.value = URL.createObjectURL(file);
        // Removed auto-submit logic - user must click "Simpan Perubahan" manually
    }
};

const submitProfile = () => {
    // Check if no changes were made
    const currentData = {
        name: profileForm.name,
        email: profileForm.email,
        no_hp: profileForm.no_hp || '',
        jabatan: profileForm.jabatan || '',
        bidang_id: profileForm.bidang_id || '',
        nip: profileForm.nip || '',
        nrp: profileForm.nrp || '',
        pangkat: profileForm.pangkat || '',
    };
    
    if (JSON.stringify(currentData) === JSON.stringify(originalData) && !profileForm.profile_pict && !profileForm.password) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak ada perubahan',
            text: 'Tidak ada perubahan untuk disimpan.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }
    
    isSubmitting.value = true;
    
    // Use post method with forceFormData to ensure all fields are properly sent
    profileForm.post(route('user.profil.update'), {
        forceFormData: true,
        onSuccess: () => {
            isSubmitting.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui!',
                confirmButtonColor: '#dc2626'
            });
            // Refresh user data
            router.reload({ only: ['user'] });
        },
        onError: () => {
            isSubmitting.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memperbarui profil',
                confirmButtonColor: '#dc2626'
            });
        }
    });
};

const cancelChanges = () => {
    // Reset form to original values
    profileForm.name = props.user.name || '';
    profileForm.email = props.user.email || '';
    profileForm.no_hp = props.user.no_hp || '';
    profileForm.jabatan = props.user.jabatan || '';
    profileForm.bidang_id = props.user.bidang_id || '';
    profileForm.nip = props.user.nip || '';
    profileForm.nrp = props.user.nrp || '';
    profileForm.pangkat = props.user.pangkat || '';
    profileForm.password = '';
    profileForm.password_confirmation = '';
    profileForm.profile_pict = null; // Also reset profile picture
    profilePreview.value = props.user.profile_pict_url || null;
};
</script>