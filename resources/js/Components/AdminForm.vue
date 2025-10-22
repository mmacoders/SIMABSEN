<template>
  <form @submit.prevent="submitForm" class="space-y-6 font-['Inter']">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <InputLabel for="name" value="Nama Lengkap" class="text-gray-700 font-medium" />
        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.name"
          required
          autofocus
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <InputLabel for="email" value="Email" class="text-gray-700 font-medium" />
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.email"
          required
        />
        <InputError class="mt-2" :message="form.errors.email" />
      </div>
    </div>

    <div v-if="!isEdit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <InputLabel for="password" value="Password" class="text-gray-700 font-medium" />
        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.password"
          required
        />
        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div>
        <InputLabel for="password_confirmation" value="Konfirmasi Password" class="text-gray-700 font-medium" />
        <TextInput
          id="password_confirmation"
          type="password"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.password_confirmation"
          required
        />
        <InputError class="mt-2" :message="form.errors.password_confirmation" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <InputLabel for="bidang_id" value="Bidang" class="text-gray-700 font-medium" />
        <select
          id="bidang_id"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.bidang_id"
          required
        >
          <option value="">Pilih Bidang</option>
          <option v-for="bidang in bidangs" :key="bidang.id" :value="bidang.id">
            {{ bidang.nama_bidang }}
          </option>
        </select>
        <InputError class="mt-2" :message="form.errors.bidang_id" />
      </div>

      <div v-if="isEdit">
        <InputLabel for="status" value="Status" class="text-gray-700 font-medium" />
        <select
          id="status"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.status"
          required
        >
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
        <InputError class="mt-2" :message="form.errors.status" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-4 pt-4">
      <SecondaryButton 
        @click="emit('cancel')" 
        type="button"
        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-all duration-300"
      >
        Batal
      </SecondaryButton>
      <PrimaryButton 
        :disabled="form.processing"
        class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300"
      >
        {{ isEdit ? 'Update' : 'Simpan' }}
      </PrimaryButton>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  admin: {
    type: Object,
    default: null
  },
  bidangs: {
    type: Array,
    required: true
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

// Initialize form
const form = useForm({
  name: props.admin?.name || '',
  email: props.admin?.email || '',
  password: '',
  password_confirmation: '',
  bidang_id: props.admin?.bidang_id || '',
  status: props.admin?.status || 'active'
});

// Methods
const submitForm = () => {
  emit('submit', form);
};
</script>