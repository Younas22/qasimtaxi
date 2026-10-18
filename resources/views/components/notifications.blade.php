{{--
    Global toast/notification area. Auto-displays session flash messages
    (`status` = success, `error` = failure — same convention as the admin
    panel) and can be pushed to from anywhere via:
        window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: 'Saved!' } }))
--}}

<div x-data="notificationCenter()"
    x-init="@if (session('status')) push('success', {{ Illuminate\Support\Js::from(session('status')) }}); @endif
            @if (session('error')) push('error', {{ Illuminate\Support\Js::from(session('error')) }}); @endif"
    @notify.window="push($event.detail.type ?? 'success', $event.detail.message)"
    class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:end-4 sm:items-end">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-3 sm:translate-y-0 sm:translate-x-6"
            x-transition:enter-end="opacity-100 translate-y-0 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-y-3 sm:translate-y-0 sm:translate-x-6"
            class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl border shadow-xl backdrop-blur"
            :class="{
                'border-emerald-500/30 bg-luxury-charcoal/95': toast.type === 'success',
                'border-red-500/30 bg-red-500/10': toast.type === 'error',
                'border-amber-500/30 bg-luxury-charcoal/95': toast.type === 'warning',
                'border-luxury-gold/30 bg-luxury-charcoal/95': toast.type === 'info',
            }">
            <div class="flex items-start gap-3 px-4 py-3.5">
                <x-icon name="check-circle" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" x-show="toast.type === 'success'" />
                <x-icon name="close" class="mt-0.5 h-5 w-5 shrink-0 text-red-400" x-show="toast.type === 'error'" />
                <x-icon name="shield" class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" x-show="toast.type === 'warning'" />
                <x-icon name="bell" class="mt-0.5 h-5 w-5 shrink-0 text-luxury-gold" x-show="toast.type === 'info'" />
                <p class="flex-1 text-sm text-luxury-white" x-text="toast.message"></p>
                <button type="button" @click="dismiss(toast.id)" class="shrink-0 text-luxury-muted hover:text-luxury-white" aria-label="{{ __('Dismiss') }}">
                    <x-icon name="close" class="h-4 w-4" />
                </button>
            </div>
            <div class="h-0.5 w-full bg-white/5">
                <div class="h-full w-full"
                    :class="toast.type === 'error' ? 'bg-red-400' : (toast.type === 'warning' ? 'bg-amber-400' : 'bg-luxury-gold')"
                    x-init="$el.style.transition = 'width 4.7s linear'; requestAnimationFrame(() => requestAnimationFrame(() => $el.style.width = '0%'))"></div>
            </div>
        </div>
    </template>
</div>

<script>
    function notificationCenter() {
        return {
            toasts: [],
            push(type, message) {
                if (!message) return;
                const id = Date.now() + Math.random();
                this.toasts.push({ id, type, message, visible: true });
                setTimeout(() => this.dismiss(id), 5000);
            },
            dismiss(id) {
                const toast = this.toasts.find((t) => t.id === id);
                if (toast) toast.visible = false;
                setTimeout(() => { this.toasts = this.toasts.filter((t) => t.id !== id); }, 300);
            },
        };
    }
</script>
