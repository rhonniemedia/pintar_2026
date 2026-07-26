{{-- File: resources/views/pages/admin/students/rombel/partials/_table.blade.php --}}
<div id="class-groups-container">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[46%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Rombel
                        <div class="text-[11px] font-normal normal-case">Nama Rombel | Jurusan</div>
                    </th>

                    <th class="w-[46%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Wali Kelas
                        <div class="text-[11px] font-normal normal-case">Wali Kelas | Jumlah Siswa</div>
                    </th>

                    <th class="w-[8%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Aksi
                        <div class="text-[11px] font-normal normal-case">Edit | Hapus</div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classGroups as $g)
                @include('pages.admin.students.groups.partials._row-class-group', ['g' => $g])
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-16 text-center text-secondary">
                        <div class="flex flex-col items-center gap-3">
                            <i data-lucide="inbox" class="size-10 text-border"></i>
                            <p class="font-medium">Tidak ada data rombel ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Memanggil komponen pagination yang baru dibuat --}}
    <x-ui.pagination :paginator="$classGroups" hxTarget="#class-groups-container" />
</div>