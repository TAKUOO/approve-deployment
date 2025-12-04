<script setup>
defineProps({
    showLogo: {
        type: Boolean,
        default: false,
    },
    colorScheme: {
        type: String,
        default: 'gray', // 'gray' or 'slate'
    },
    padding: {
        type: String,
        default: 'default', // 'default' or 'large'
    },
});
</script>

<template>
    <footer :class="[
        padding === 'large' ? 'px-6 py-10' : 'space-y-2',
        'text-xs text-center',
        colorScheme === 'slate' ? 'text-slate-500' : 'text-gray-500'
    ]">
        <!-- サービスロゴ（オプション） -->
        <div v-if="showLogo" class="pt-8 mt-12 border-t border-gray-200">
            <div class="flex justify-center mb-10">
                <a href="/">
                    <img src="/images/logo.png" alt="AutoRelease" class="h-12 object-contain" />
                </a>
            </div>
        </div>
        
        <div :class="[
            'flex flex-wrap gap-4 justify-center text-sm',
            colorScheme === 'slate' ? 'text-slate-600' : 'text-gray-600'
        ]">
            <a href="https://rubydesign.jp/" target="_blank" rel="noopener noreferrer" 
               :class="[
                   'underline',
                   colorScheme === 'slate' ? 'hover:text-slate-900' : 'hover:text-gray-900'
               ]">
                運営元
            </a>
            <a href="/terms" 
               :class="[
                   'underline',
                   colorScheme === 'slate' ? 'hover:text-slate-900' : 'hover:text-gray-900'
               ]">
                利用規約
            </a>
            <a href="/privacy" 
               :class="[
                   'underline',
                   colorScheme === 'slate' ? 'hover:text-slate-900' : 'hover:text-gray-900'
               ]">
                プライバシーポリシー
            </a>
        </div>
        <div>&copy; {{ new Date().getFullYear() }} AutoRelease. All rights reserved.</div>
    </footer>
</template>

