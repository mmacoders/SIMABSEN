<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Pegawai</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-6 flex justify-between items-center">
                            <h3 class="text-lg font-medium">Daftar Pegawai Bidang {{ users[0]?.bidang?.nama_bidang || '' }}</h3>
                            <div class="relative">
                                <input
                                    type="text"
                                    placeholder="Cari pegawai..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    v-model="searchTerm"
                                />
                                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in filteredUsers" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ user.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <Link :href="route('admin.pegawai.show', user)" class="text-blue-600 hover:text-blue-900">
                                                Detail
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredUsers.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data pegawai
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: Array,
});

const searchTerm = ref('');

const filteredUsers = computed(() => {
    if (!searchTerm.value) {
        return props.users;
    }
    
    const term = searchTerm.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(term) || 
        user.email.toLowerCase().includes(term)
    );
});
</script>