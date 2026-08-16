<div id="letter-list-container"
    hx-trigger="refreshTable from:body"
    hx-get="{{ route('admin.students.letters.index') }}{{ request('search') ? '?search=' . urlencode(request('search')) : '' }}{{ request('letter_type') ? '&letter_type=' . urlencode(request('letter_type')) : '' }}"
    hx-target="#letter-list-container"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse table-fixed">
            <colgroup>
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:30%">
                <col style="width:10%">
            </colgroup>
            <thead>
                <tr class="border-b border-border bg-gray-50/30">
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | NIS</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Surat
                        <div class="text-[11px] font-normal normal-case">Jenis | Nomor</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Diterbitkan
                        <div class="text-[11px] font-normal normal-case">Oleh | Tanggal</div>
                    </th>
                    <th class="px-4 py-3 text-sm font-bold text-secondary">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $letter)
                @php
                $student = $letter->student;
                @endphp
                <tr id="row-letter-{{ $letter->id }}" class="border-b border-border hover:bg-muted/50 transition-colors">

                    {{-- Kolom Peserta Didik --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-3 group">
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

                    {{-- Kolom Jenis dan Nomor Surat --}}
                    <td class="px-4 py-4 text-sm">
                        <span class="inline-block px-2 py-0.5 text-[11px] font-semibold rounded-md bg-primary/10 text-primary">
                            {{ $letter->letter_type->label() }}
                        </span>
                        <div class="text-xs text-secondary mt-1 truncate">
                            {{ $letter->letter_number ?? '-' }}
                        </div>
                    </td>

                    {{-- Diterbitkan --}}
                    <td class="px-4 py-4 text-sm">

                        <div class="text-foreground font-medium truncate">
                            {{ $letter->author->staff->name ?? $letter->author->username ?? '-' }}
                        </div>
                        <div class="flex text-xs items-center gap-1 text-secondary">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            <span>{{ \Carbon\Carbon::parse($letter->letter_date)->translatedFormat('d F Y') }}</span>
                        </div>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.students.letters.download', $letter->id) }}" target="_blank" title="Unduh / Lihat PDF"
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-secondary hover:bg-muted hover:text-foreground transition-all cursor-pointer">
                                <i data-lucide="file-text" class="size-4 pointer-events-none"></i>
                            </a>
                            <button type="button" title="Hapus"
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
                                class="flex items-center justify-center size-8 rounded-lg border border-border bg-white text-error hover:bg-error/10 hover:border-error/30 transition-all cursor-pointer">
                                <i data-lucide="trash-2" class="size-4 pointer-events-none"></i>
                            </button>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="file-text" class="size-10 text-border"></i>
                            <p class="font-medium text-sm">Belum ada surat yang diterbitkan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-ui.pagination :paginator="$data" hxTarget="#letter-list-container" />

    <script>
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</div>