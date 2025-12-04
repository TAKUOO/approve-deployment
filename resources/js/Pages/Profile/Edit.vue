<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppFooter from '@/Components/AppFooter.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="プロフィール - AutoRelease" />

    <AuthenticatedLayout>
        <div class="py-12 min-h-screen bg-indigo-50">
            <div class="mx-auto max-w-3xl bg-white rounded-2xl">
                <div
                    class="p-8 border-b border-gray-200"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w"
                    />
                </div>

                <div
                    class="p-8"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
            
            <!-- フッター -->
            <div class="mt-8">
                <AppFooter />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
