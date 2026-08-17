<div id="students-history-container">
    {{-- Wrapper responsif untuk scrolling horizontal di layar sempit --}}
    <div class="overflow-x-auto pb-4 custom-scrollbar">
        {{-- Table auto dengan batas minimal lebar --}}
        <table class="w-full text-left min-w-[800px]">
            <thead>
                <tr class="border-b border-border bg-slate-50/50">
                    <th class="w-[32%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Nama | Jenis Kelamin</div>
                    </th>

                    <th class="w-[26%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Nomor Induk Siswa
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">NIS | NISN</div>
                    </th>

                    <th class="w-[26%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Rombongan Belajar
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Rombel | Jurusan</div>
                    </th>

                    <th class="w-[16%] px-5 py-3.5 text-sm font-bold text-secondary tracking-wider">
                        Keluar
                        <div class="text-[11px] font-normal normal-case mt-0.5 opacity-80">Alasan | Tanggal</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @include('pages.admin.students.history.partials._row-student', ['r' => $r])
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center justify-center gap-3 text-secondary">
                            <div class="size-16 rounded-full bg-slate-100 flex items-center justify-center">
                                <i data-lucide="inbox" class="size-8 text-slate-400"></i>
                            </div>
                            <div>
                                <p class="font-bold text-foreground text-base">Riwayat Kosong</p>
                                <p class="text-sm mt-0.5">Tidak ada riwayat peserta didik yang ditemukan.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Area Pagination --}}
    <div class="mt-4">
        <x-ui.pagination :paginator="$students" hxTarget="#students-history-container" />
    </div>
</div>