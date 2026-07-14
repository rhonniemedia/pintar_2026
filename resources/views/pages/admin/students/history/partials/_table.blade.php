<div id="students-history-container">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | Jenis Kelamin</div>
                    </th>

                    <th class="w-[26%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Nomor Induk Siswa
                        <div class="text-[11px] font-normal normal-case">NIS | NISN</div>
                    </th>

                    <th class="w-[26%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Rombongan Belajar
                        <div class="text-[11px] font-normal normal-case">Rombel | Jurusan</div>
                    </th>

                    <th class="w-[16%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Keluar
                        <div class="text-[11px] font-normal normal-case">Alasan | Tanggal</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @include('pages.admin.students.history.partials._row-student', ['r' => $r])
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada riwayat siswa ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('pages.admin.students.history.partials._pagination', ['students' => $students])
</div>