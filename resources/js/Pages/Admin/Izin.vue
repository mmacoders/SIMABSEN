<template>
    <AdminLayout page-title="Izin & Cuti" mobile-page-title="Izin">
        <div class="p-6 bg-gray-50">
            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Izin & Cuti</h2>
                <p class="text-gray-600 mt-1">Kelola permintaan izin dan cuti pegawai</p>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Permintaan</h3>
                <form @submit.prevent="filterRequests" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <InputLabel for="start_date" value="Tanggal Mulai" />
                        <TextInput
                            id="start_date"
                            type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            v-model="filterForm.start_date"
                        />
                    </div>
                    <div>
                        <InputLabel for="end_date" value="Tanggal Selesai" />
                        <TextInput
                            id="end_date"
                            type="date"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            v-model="filterForm.end_date"
                        />
                    </div>
                    <div>
                        <InputLabel for="search" value="Cari Pegawai" />
                        <TextInput
                            id="search"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                            placeholder="Nama pegawai..."
                            v-model="filterForm.search"
                        />
                    </div>
                    <div class="flex items-end space-x-2">
                        <PrimaryButton class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                            Filter
                        </PrimaryButton>
                        <button
                            @click="resetFilters"
                            type="button"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Leave Requests Table -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Izin</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="izin in izins.data" :key="izin.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ izin.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ izin.user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDateRange(izin.tanggal_mulai, izin.tanggal_selesai) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="izin.jenis_izin === 'penuh' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                                    >
                                        {{ izin.jenis_izin === 'penuh' ? 'Izin Penuh' : 'Izin Parsial' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" :title="izin.keterangan">
                                        {{ izin.keterangan }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusClass(izin.status)"
                                    >
                                        {{ getStatusText(izin.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button
                                            v-if="izin.status === 'pending'"
                                            @click="updateStatus(izin.id, 'approved')"
                                            class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                            title="Setujui"
                                        >
                                            <CheckIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            v-if="izin.status === 'pending'"
                                            @click="updateStatus(izin.id, 'rejected')"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                            title="Tolak"
                                        >
                                            <XIcon class="w-5 h-5" />
                                        </button>
                                        <span v-if="izin.status !== 'pending'" class="text-gray-400">
                                            —
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="izins.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada permintaan izin
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ izins.from || 0 }} sampai {{ izins.to || 0 }} dari {{ izins.total || 0 }} permintaan
                    </div>
                    <div class="flex space-x-2">
                        <button
                            v-if="izins.prev_page_url"
                            @click="fetchPage(izins.prev_page_url)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Sebelumnya
                        </button>
                        <button
                            v-if="izins.next_page_url"
                            @click="fetchPage(izins.next_page_url)"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { CheckIcon, XIcon } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    izins: Object,
    filters: Object,
});

const filterForm = useForm({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
});

const filterRequests = () => {
    router.get(route('admin.izin'), filterForm.data());
};

const resetFilters = () => {
    filterForm.reset();
    router.get(route('admin.izin'));
};

const fetchPage = (url) => {
    router.get(url);
};

const updateStatus = (izinId, status) => {
    if (confirm(`Apakah Anda yakin ingin ${status === 'approved' ? 'menyetujui' : 'menolak'} permintaan izin ini?`)) {
        router.patch(route('admin.izin.update', izinId), { status }, {
            onSuccess: () => {
                // Refresh the page to show updated status
                router.reload();
            }
        });
    }
};

const formatDateRange = (startDate, endDate) => {
    if (startDate === endDate) {
        return new Date(startDate).toLocaleDateString('id-ID');
    }
    return `${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`;
};

const getStatusClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
        default:
            return 'bg-yellow-100 text-yellow-800';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'pending':
        default:
            return 'Menunggu';
    }
};
</script>