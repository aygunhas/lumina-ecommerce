<?php
/**
 * Global Toast Bildirim Sistemi (Alpine.js)
 * Tetikleme: window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Metin', type: 'success' } }));
 */
?>
<div x-data="{
    show: false,
    message: '',
    type: 'success',
    _timer: null,
    open(e) {
        this.message = e.detail.message || '';
        this.type = e.detail.type || 'success';
        this.show = true;
        clearTimeout(this._timer);
        this._timer = setTimeout(() => { this.show = false; }, 3000);
    }
}" @notify.window="open($event)" class="fixed bottom-6 right-6 z-[100]" aria-live="polite">
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-250"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-full"
         x-cloak
         class="flex items-center gap-3 bg-[#111] text-white px-4 py-3 rounded shadow-lg min-w-[240px] max-w-[360px]">
        <span class="flex-shrink-0 text-white/90" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </span>
        <p x-text="message" class="text-xs tracking-wide flex-1"></p>
    </div>
</div>
