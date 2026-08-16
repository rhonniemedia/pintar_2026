{{-- File: resources/views/pages/admin/students/letters/partials/_modal-create.blade.php --}}
<div x-data="{
        open: true,
        closeModal() {
            this.open = false;
            setTimeout(() => {
                const container = document.getElementById('modal-form-container');
                if (container) container.innerHTML = '';
            }, 200);
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0 bg-black/60"
    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    @click.self="closeModal()"
    @close-modal.window="closeModal()">

    <div class="bg-white sm:rounded-2xl w-full sm:max-w-md h-full sm:h-auto sm:max-h-[85vh] flex flex-col overflow-hidden shadow-2xl">

        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="file-plus-2" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Buat Surat</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Pilih jenis surat yang mau diterbitkan</p>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-2">
            @foreach ($letterTypes as $type)
            @php $isAvailable = array_key_exists($type->value, $createRoutes); @endphp

            @if ($isAvailable)
            <button type="button"
                hx-get="{{ route($createRoutes[$type->value]) }}"
                hx-target="#modal-form-container"
                hx-swap="innerHTML"
                class="w-full flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl border border-border bg-white hover:border-primary/40 hover:bg-primary/5 transition-colors cursor-pointer text-left">
                <span class="text-sm font-medium text-foreground">{{ $type->label() }}</span>
                <i data-lucide="chevron-right" class="size-4 text-secondary"></i>
            </button>
            @else
            <div class="w-full flex items-center justify-between gap-3 px-3.5 py-3 rounded-xl border border-border bg-slate-50 opacity-60 cursor-not-allowed">
                <span class="text-sm font-medium text-secondary">{{ $type->label() }}</span>
                <span class="text-[10px] font-semibold text-secondary bg-white border border-border rounded-full px-2 py-0.5">Segera Hadir</span>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>