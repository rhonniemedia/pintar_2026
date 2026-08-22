<div id="letter-list-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.letters.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}{{ request('letter_type') ? '&letter_type=' . urlencode(request('letter_type')) : '' }}"
    hx-target="#letter-list-container"
    hx-swap="outerHTML">

    {{-- ============ 1. DESKTOP TABLE ============ --}}
    <div class="hidden lg:block overflow-x-auto pb-4 custom-scrollbar">
        <table class="w-full text-left min-w-[800px]">
            <thead>
                <tr class="border-b border-border bg-slate-50/50">
                    <th class="w-[30%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Nama | NIS</div>
                    </th>
                    <th class="w-[30%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                        Surat
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Jenis | Nomor</div>
                    </th>
                    <th class="w-[30%] px-5 py-3 text-sm font-bold text-secondary tracking-wider">
                        Diterbitkan
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Oleh | Tanggal</div>
                    </th>
                    <th class="w-[10%] px-5 py-3 text-sm font-bold text-secondary tracking-wider whitespace-nowrap">
                        Aksi
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Detail | Delete</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $letter)
                @php
                $student = $letter->student;

                $typeValue = $letter->letter_type instanceof \UnitEnum ? $letter->letter_type->value : $letter->letter_type;
                $isProtected = in_array($typeValue, [
                \App\Enums\Student\LetterType::TRANSFER->value,
                \App\Enums\Student\LetterType::DISMISSED->value,
                \App\Enums\Student\LetterType::RESIGNED->value
                ]);
                @endphp
                <tr id="row-letter-{{ $letter->id }}" class="border-b border-border hover:bg-slate-50/80 transition-colors group">

                    <td class="px-5 py-4 min-w-[240px]">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />
                            <div class="min-w-0">
                                <div class="font-semibold text-foreground text-sm group-hover:text-primary transition-colors truncate">
                                    {{ $student->name ?? '-' }}
                                </div>
                                <div class="text-xs text-secondary mt-0.5 truncate">
                                    {{ $student->nis ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 min-w-[200px]">
                        <span title="{{ $letter->letter_type->label() }}" class="inline-block max-w-full truncate px-2 py-0.5 text-[11px] font-semibold rounded-md bg-primary/10 text-primary mb-1.5 align-middle">
                            {{ $letter->letter_type->label() }}
                        </span>
                        <div class="text-xs text-secondary truncate" title="{{ $letter->letter_number }}">
                            {{ $letter->letter_number ?? '-' }}
                        </div>
                    </td>

                    <td class="px-5 py-4 min-w-[160px]">
                        <div class="text-sm font-semibold text-foreground truncate">
                            {{ $letter->author->staff->name ?? $letter->author->username ?? '-' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs mt-0.5 text-secondary">
                            <i data-lucide="calendar" class="size-3.5 shrink-0"></i>
                            <span class="truncate">{{ \Carbon\Carbon::parse($letter->letter_date)->translatedFormat('d F Y') }}</span>
                        </div>
                    </td>

                    <td class="px-5 py-4 min-w-[120px]">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.students.letters.download', $letter->id) }}" target="_blank" title="Lihat PDF"
                                class="flex items-center justify-center size-8 shrink-0 rounded-lg border border-border bg-white text-secondary hover:bg-slate-100 hover:text-primary transition-all cursor-pointer shadow-sm">
                                <i data-lucide="file-text" class="size-4 pointer-events-none"></i>
                            </a>

                            @if($isProtected)
                            <button type="button" title="Surat mutasi tidak dapat dihapus melalui menu ini" disabled
                                class="flex items-center justify-center size-8 shrink-0 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed opacity-60">
                                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
                            </button>
                            @else
                            <button type="button" title="Hapus Surat"
                                hx-delete="{{ route('admin.students.letters.destroy', $letter->id) }}"
                                hx-trigger="confirmed"
                                hx-target="#letter-list-container"
                                hx-swap="none"
                                @click="
                                    ShowConfirm({
                                        title: 'Hapus Surat?',
                                        message: 'Hapus riwayat surat ini? File PDF yang sudah diunduh sebelumnya tidak terpengaruh.',
                                        confirmText: 'Ya, Hapus',
                                        cancelText: 'Batal',
                                    }, () => {
                                        $dispatch('confirmed');
                                    })
                                "
                                class="flex items-center justify-center size-8 shrink-0 rounded-lg border border-border bg-white text-error hover:bg-error/10 hover:border-error/30 transition-all cursor-pointer shadow-sm">
                                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
                            </button>
                            @endif
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center justify-center gap-3 text-secondary">
                            <div class="size-16 rounded-full bg-slate-100 flex items-center justify-center">
                                <i data-lucide="file-text" class="size-8 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-foreground text-base">Belum Ada Surat</p>
                                <p class="text-sm mt-0.5">Surat keterangan yang diterbitkan akan muncul di sini.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ 2. MOBILE CARDS ============ --}}
    <div class="lg:hidden divide-y divide-border border-y border-border bg-white -mx-5 mb-5 mt-2">
        @forelse ($data as $letter)
        @php
        $student = $letter->student;
        $typeValue = $letter->letter_type instanceof \UnitEnum ? $letter->letter_type->value : $letter->letter_type;
        $isProtected = in_array($typeValue, [
        \App\Enums\Student\LetterType::TRANSFER->value,
        \App\Enums\Student\LetterType::DISMISSED->value,
        \App\Enums\Student\LetterType::RESIGNED->value
        ]);

        $authorName = $letter->author->staff->name ?? $letter->author->username ?? '-';
        $letterDate = \Carbon\Carbon::parse($letter->letter_date)->translatedFormat('d F Y');
        @endphp

        <div id="card-letter-{{ $letter->id }}" class="px-5 py-4 border-border hover:bg-slate-50/80 transition-colors">

            <div class="flex items-start gap-3">
                <x-ui.avatar :name="$student->name ?? '-'" :gender="optional($student)->gender" :index="$loop->index" />

                <div class="min-w-0 flex-1">
                    {{-- Bagian Atas: Nama, NIS & Jenis Surat --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-foreground text-sm uppercase truncate">
                                {{ $student->name ?? '-' }}
                            </p>
                            <p class="text-xs text-secondary mt-0.5 truncate" title="NIS">
                                {{ $student->nis ?? '-' }}
                            </p>
                        </div>

                        {{-- Label Jenis Surat --}}
                        <span class="shrink-0 inline-flex px-2 py-1 rounded-md text-[9px] font-bold uppercase tracking-wider bg-primary/10 text-primary">
                            {{ $letter->letter_type->label() }}
                        </span>
                    </div>

                    {{-- Bagian Tengah: Detail Surat --}}
                    <div class="mt-3 border-t border-border divide-y divide-border text-xs">

                        {{-- Nomor Surat --}}
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="hash" class="size-3 text-slate-400"></i>
                                No. Surat
                            </p>
                            <p class="text-foreground text-right font-mono truncate">
                                {{ $letter->letter_number ?? '-' }}
                            </p>
                        </div>

                        {{-- Diterbitkan Oleh --}}
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="user-check" class="size-3 text-slate-400"></i>
                                Penerbit
                            </p>
                            <p class="text-foreground font-medium text-right truncate">
                                {{ $authorName }}
                            </p>
                        </div>

                        {{-- Tanggal Surat --}}
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <p class="text-secondary flex items-center gap-1.5 shrink-0">
                                <i data-lucide="calendar" class="size-3 text-slate-400"></i>
                                Tanggal
                            </p>
                            <p class="text-foreground text-right truncate">
                                {{ $letterDate }}
                            </p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Bagian Bawah: Aksi --}}
            <div class="mt-3 flex justify-end gap-2" x-data="{}">
                <a href="{{ route('admin.students.letters.download', $letter->id) }}" target="_blank" title="Lihat PDF"
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-white text-secondary hover:bg-slate-100 hover:text-primary transition-all cursor-pointer shadow-sm">
                    <i data-lucide="file-text" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Lihat</span>
                </a>

                @if($isProtected)
                <button type="button" title="Surat mutasi tidak dapat dihapus melalui menu ini" disabled
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-border bg-slate-50 text-slate-300 cursor-not-allowed opacity-60">
                    <i data-lucide="trash-2" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Hapus</span>
                </button>
                @else
                <button type="button" title="Hapus Surat"
                    hx-delete="{{ route('admin.students.letters.destroy', $letter->id) }}"
                    hx-trigger="confirmed"
                    hx-target="#letter-list-container"
                    hx-swap="none"
                    @click="
                        ShowConfirm({
                            title: 'Hapus Surat?',
                            message: 'Hapus riwayat surat ini? File PDF yang sudah diunduh sebelumnya tidak terpengaruh.',
                            confirmText: 'Ya, Hapus',
                            cancelText: 'Batal',
                        }, () => {
                            $dispatch('confirmed');
                        })
                    "
                    class="flex items-center justify-center gap-1.5 h-8 px-3 rounded-lg border border-error/20 bg-white text-error hover:bg-error/10 transition-all cursor-pointer shadow-sm">
                    <i data-lucide="trash-2" class="size-3.5 pointer-events-none"></i>
                    <span class="text-xs font-medium">Hapus</span>
                </button>
                @endif
            </div>

        </div>
        @empty
        <div class="px-4 py-16 text-center">
            <div class="flex flex-col items-center justify-center gap-3 text-secondary">
                <div class="size-16 rounded-full bg-slate-100 flex items-center justify-center">
                    <i data-lucide="file-text" class="size-8 text-slate-400"></i>
                </div>
                <div>
                    <p class="font-bold text-foreground text-base">Belum Ada Surat</p>
                    <p class="text-sm mt-0.5">Surat keterangan yang diterbitkan akan muncul di sini.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        <x-ui.pagination :paginator="$data" hxTarget="#letter-list-container" />
    </div>

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>