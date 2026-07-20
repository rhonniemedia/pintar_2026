{{-- File: resources/views/pages/admin/students/data/partials/_table.blade.php --}}
<div id="students-container"
    hx-get="{{ request()->fullUrl() }}"
    hx-trigger="refreshStudentData from:body"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Peserta Didik
                        <div class="text-[11px] font-normal normal-case">Nama | Jenis Kelamin</div>
                    </th>

                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Nomor Induk Siswa
                        <div class="text-[11px] font-normal normal-case">NIS | NISN</div>
                    </th>

                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Rombongan Belajar
                        <div class="text-[11px] font-normal normal-case">Rombel | Jurusan</div>
                    </th>

                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Detil | Edit</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $r)
                @include('pages.admin.students.data.partials._row-student', ['r' => $r])
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada data siswa ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination yang baru dibuat --}}
    <x-ui.pagination :paginator="$students" hxTarget="#students-container" />
</div>