{{-- File: resources/views/pages/admin/students/letters/partials/_modal-create-poor-family.blade.php --}}
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

    <div class="bg-white sm:rounded-2xl w-full sm:max-w-md h-full sm:h-auto sm:max-h-[85vh] flex flex-col shadow-2xl">

        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0 sm:rounded-t-2xl">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="hand-heart" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Keterangan Tidak Mampu</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Isi data untuk menerbitkan surat</p>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX --}}
        <form id="create-letter-poor-family-form"
            hx-post="{{ route('admin.students.letters.store-poor-family') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            x-data="{
                saving: false,
                studentId: '',
                letterNumber: '',
                letterDate: '{{ old('letter_date', now()->format('Y-m-d')) }}',
                get isValid() {
                    return this.studentId !== '' && this.letterNumber.trim() !== '' && this.letterDate !== '';
                }
            }"
            @submit="saving = true"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 min-h-0">
            @csrf

            @php
            $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors';
            $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
            $errorClass = 'border-error ring-1 ring-error/30';
            @endphp

            <div class="block p-4 sm:p-6 overflow-y-auto bg-slate-50/30 flex-1 space-y-4">

                <div>
                    <label class="{{ $labelClass }}">Peserta Didik <span class="text-error">*</span></label>
                    <select name="student_id" x-model="studentId" required
                        class="{{ $inputClass }} @error('student_id') {{ $errorClass }} @enderror">
                        <option value="">-- Pilih Peserta Didik --</option>
                        @foreach ($students as $s)
                        <option value="{{ $s->id }}" @selected(old('student_id')===$s->id)>
                            {{ $s->name }} @if($s->nis) ({{ $s->nis }}) @endif
                        </option>
                        @endforeach
                    </select>
                    @error('student_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Nomor Surat <span class="text-error">*</span></label>
                    <input type="text" name="letter_number" x-model="letterNumber" required
                        placeholder="421.5/045/O/SMKN1RL/{{ now()->year }}"
                        class="{{ $inputClass }} @error('letter_number') {{ $errorClass }} @enderror">
                    @error('letter_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Tanggal Surat <span class="text-error">*</span></label>
                    <input type="date" name="letter_date" x-model="letterDate" required
                        class="{{ $inputClass }} @error('letter_date') {{ $errorClass }} @enderror">
                    @error('letter_date') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Footer Modal --}}
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-t border-border bg-slate-50/50 flex items-center justify-end gap-2 shrink-0">
                <button type="button" @click="closeModal()"
                    class="px-5 py-2.5 rounded-xl border border-border bg-white text-secondary text-sm font-semibold hover:bg-muted hover:border-gray-300 transition-all cursor-pointer">
                    Batal
                </button>

                <button type="submit" :disabled="saving || !isValid"
                    class="flex items-center justify-center min-w-[160px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">

                    <div x-show="!saving" class="flex items-center gap-1.5">
                        <i data-lucide="file-check-2" class="size-4"></i>
                        <span>Terbitkan Surat</span>
                    </div>

                    <div x-show="saving" x-cloak class="flex items-center gap-1.5">
                        <i data-lucide="loader-2" stroke-width="3" class="size-4 animate-spin"></i>
                        <span>Memproses...</span>
                    </div>
                </button>
            </div>
        </form>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>