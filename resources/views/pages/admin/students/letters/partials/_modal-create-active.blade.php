{{-- File: resources/views/pages/admin/students/letters/partials/_modal-create-active.blade.php --}}
<div x-data="{
        open: false,
        closeModal() {
            this.open = false;
            setTimeout(() => {
                const container = document.getElementById('modal-form-container');
                if (container) container.innerHTML = '';
            }, 200);
        }
    }"
    x-init="setTimeout(() => open = true, 10); $watch('open', value => { if (!value) closeModal() })"
    @close-modal.window="closeModal()">

    <x-ui.modal show="open" maxWidth="md">

        {{-- Modal Header --}}
        <div class="flex items-start sm:items-center justify-between gap-3 px-4 sm:px-6 py-4 sm:py-5 border-b border-border bg-slate-50/50 shrink-0">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="size-11 sm:size-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="file-check-2" class="size-5 sm:size-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-foreground text-base sm:text-lg leading-tight truncate">Keterangan Aktif</h3>
                    <p class="text-xs sm:text-sm text-secondary mt-0.5 truncate">Isi data untuk menerbitkan surat</p>
                </div>
            </div>
            <button type="button" @click="closeModal()"
                class="size-8 sm:size-9 flex items-center justify-center rounded-lg border border-border bg-white text-secondary hover:bg-error/10 hover:text-error hover:border-error/30 transition-colors cursor-pointer shrink-0">
                <i data-lucide="x" class="size-4 pointer-events-none"></i>
            </button>
        </div>

        {{-- Form HTMX dengan Validasi Alpine.js --}}
        <form id="create-letter-active-form"
            hx-post="{{ route('admin.students.letters.store-active') }}"
            hx-target="#modal-form-container"
            hx-swap="innerHTML"
            novalidate
            x-data="{
                saving: false,
                errors: {},
                isValid: false,

                // Cek semua field wajib, isi this.errors, dan kembalikan objek error tsb
                checkForm() {
                    const formEl = document.getElementById('create-letter-active-form');
                    const formData = new FormData(formEl);
                    const errs = {};

                    if (!formData.get('student_id')) {
                        errs.student_id = 'Peserta didik wajib dipilih.';
                    }
                    if (!formData.get('letter_number') || formData.get('letter_number').trim() === '') {
                        errs.letter_number = 'Nomor surat wajib diisi.';
                    }
                    if (!formData.get('letter_date')) {
                        errs.letter_date = 'Tanggal surat wajib diisi.';
                    }

                    this.isValid = Object.keys(errs).length === 0;
                    return errs;
                },

                // Dipanggil tiap kali ada perubahan input, supaya tombol enable/disable real-time
                revalidate() {
                    this.checkForm();
                },

                // Dipanggil saat htmx AKAN mengirim request (event ini bisa dibatalkan)
                validateBeforeSubmit(evt) {
                    this.errors = this.checkForm();

                    if (Object.keys(this.errors).length > 0) {
                        // Batalkan request HTMX sepenuhnya
                        evt.preventDefault();
                        return;
                    }

                    this.saving = true;
                }
            }"
            x-init="checkForm()"
            @input="revalidate()"
            @change="revalidate()"
            @htmx:confirm="validateBeforeSubmit($event)"
            @htmx:after-request="saving = false"
            class="flex flex-col flex-1 min-h-0">
            @csrf

            @php
            $inputClass = 'w-full bg-white border border-border rounded-xl px-3.5 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary transition-colors';
            $labelClass = 'block text-[10px] font-semibold text-secondary uppercase tracking-wider mb-1.5';
            $errorClass = 'border-error ring-1 ring-error/30';
            @endphp

            <div class="block p-4 sm:p-6 overflow-visible bg-slate-50/30 flex-1 space-y-4">
                @php
                $studentOptions = $students->map(function($s) {
                return [
                'value' => $s->id,
                'label' => $s->name . ($s->nis ? ' (' . $s->nis . ')' : '')
                ];
                })->toArray();
                @endphp
                <div>
                    <label class="{{ $labelClass }}">Peserta Didik <span class="text-error">*</span></label>

                    {{-- Memanggil Komponen Searchable Select --}}
                    <x-ui.searchable-select
                        name="student_id"
                        :options="$studentOptions"
                        placeholder="-- Cari dan Pilih Peserta Didik --" />

                    @error('student_id') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Nomor Surat <span class="text-error">*</span></label>
                    <input type="text" name="letter_number" value="{{ old('letter_number') }}" placeholder="421.5/045/O/SMKN1RL/{{ now()->year }}"
                        class="{{ $inputClass }} @error('letter_number') {{ $errorClass }} @enderror"
                        required>
                    @error('letter_number') <span class="text-error text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Tanggal Surat <span class="text-error">*</span></label>
                    <input type="date" name="letter_date" value="{{ old('letter_date', now()->format('Y-m-d')) }}"
                        class="{{ $inputClass }} @error('letter_date') {{ $errorClass }} @enderror"
                        required>
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
                    class="flex items-center justify-center min-w-[160px] px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 shadow-sm shadow-primary/30 transition-all cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed">

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

    </x-ui.modal>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</div>