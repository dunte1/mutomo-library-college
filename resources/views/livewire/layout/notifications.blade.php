<div
    x-data="{ show: false, message: '', type: 'success' }"
    x-on:notify.window="
        show = true;
        message = $event.detail.message;
        type = $event.detail.type || 'success';
        setTimeout(() => show = false, 4000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed bottom-4 right-4 z-50 md:bottom-4"
>
    <div :class="{
        'bg-emerald-500': type === 'success',
        'bg-red-500': type === 'error',
        'bg-amber-500': type === 'warning',
        'bg-sky-500': type === 'info'
    }" class="flex items-center gap-3 px-5 py-3 rounded-2xl text-white shadow-soft-lg text-sm font-medium">
        <template x-if="type === 'success'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </template>
        <template x-if="type === 'error'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </template>
        <span x-text="message"></span>
        <button @click="show = false" class="ml-2 hover:opacity-75">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
