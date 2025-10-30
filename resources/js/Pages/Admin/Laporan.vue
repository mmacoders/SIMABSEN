<template>
    <AdminLayout page-title="Laporan Absensi" mobile-page-title="Laporan">
        <div class="p-6 bg-gray-50">
            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Laporan Absensi</h2>
                <p class="text-gray-600 mt-1">Kelola dan pantau data absensi pegawai</p>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Laporan</h3>
                <form @submit.prevent="filterReport" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                    <div class="flex items-end">
                        <PrimaryButton class="bg-red-600 hover:bg-red-700 focus:ring-red-500">
                            Filter
                        </PrimaryButton>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="exportReport"
                            type="button"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                        >
                            Export
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="attendance in attendances" :key="attendance.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ attendance.user.name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ attendance.waktu_masuk || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data absensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    attendances: Array,
    startDate: String,
    endDate: String,
});

const filterForm = useForm({
    start_date: props.startDate,
    end_date: props.endDate,
});

const filterReport = () => {
    router.get(route('admin.laporan'), filterForm.data());
};

const exportReport = () => {
    router.post(route('admin.laporan.export'), filterForm.data());
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Get status class based on attendance status
const getStatusClass = (attendance) => {
    // Use the actual status values from the database
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
            // For 'hadir' status, check if the user has actually checked in
            return attendance.waktu_masuk ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        case 'Izin Parsial (Check-in)':
        case 'Izin Parsial (Selesai)':
            return 'bg-purple-100 text-purple-800';
        default:
            // Handle any other status values or null status
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'bg-green-100 text-green-800'; // Completed attendance
            } else if (attendance.waktu_masuk) {
                return 'bg-blue-100 text-blue-800'; // Check-in only
            } else {
                return 'bg-gray-100 text-gray-800'; // No attendance
            }
    }
};

// Get status text based on attendance status
const getStatusText = (attendance) => {
    // Use the actual status values from the database
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
            // For 'hadir' status, check if the user has actually checked in
            return attendance.waktu_masuk ? 'Hadir' : 'Belum Absen';
        case 'Izin Parsial (Check-in)':
            return 'Izin Parsial (Check-in)';
        case 'Izin Parsial (Selesai)':
            return 'Izin Parsial (Selesai)';
        default:
            // Handle any other status values or null status
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'Hadir';
            } else if (attendance.waktu_masuk) {
                return 'Sudah Check-in';
            } else {
                return attendance.status || 'Belum Absen';
            }
    }
};
</script>