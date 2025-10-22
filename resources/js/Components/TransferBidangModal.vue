<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50 font-['Inter']" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                  <ArrowRightLeftIcon class="h-6 w-6 text-blue-600" />
                </div>
                <div class="mt-3 text-center sm:mt-5">
                  <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                    Pindah Bidang
                  </DialogTitle>
                  <div class="mt-2">
                    <p class="text-sm text-gray-500">
                      Pindahkan <span class="font-semibold">{{ admin?.name }}</span> ke bidang baru
                    </p>
                  </div>
                  
                  <div class="mt-6">
                    <InputLabel for="new_bidang_id" value="Bidang Baru" class="text-left block text-sm font-medium text-gray-700" />
                    <select
                      id="new_bidang_id"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828] py-2"
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
                </div>
              </div>
              <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-[#C62828] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#b71c1c] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C62828] transition-all duration-300"
                  @click="transferBidang"
                  :disabled="form.processing"
                >
                  Pindahkan
                </button>
                <button
                  type="button"
                  class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0 transition-all duration-300"
                  @click="closeModal"
                >
                  Batal
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { 
  Dialog, 
  DialogPanel, 
  DialogTitle, 
  TransitionChild, 
  TransitionRoot 
} from '@headlessui/vue';
import { ArrowRightLeftIcon } from 'lucide-vue-next';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  admin: {
    type: Object,
    default: null
  },
  bidangs: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'transfer']);

// Form
const form = useForm({
  bidang_id: ''
});

// Methods
const closeModal = () => {
  form.reset();
  emit('close');
};

const transferBidang = () => {
  form.patch(route('superadmin.admin.transfer', props.admin.id), {
    onSuccess: () => {
      closeModal();
      emit('transfer');
    }
  });
};
</script>