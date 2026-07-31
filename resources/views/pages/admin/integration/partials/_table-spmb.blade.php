<div id="spmb-table-container">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-border bg-muted/30">
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-secondary whitespace-nowrap">Profil & NIK</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-secondary whitespace-nowrap">No. Registrasi & NISN</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-secondary whitespace-nowrap">Asal Sekolah & Jurusan</th>
                    <th class="px-5 py-3 text-[11px] font-bold uppercase tracking-wider text-secondary whitespace-nowrap">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paginatedData as $siswa)
                @php
                $gender = $siswa['jk'] ?? 'L';
                $name = $siswa['nama_lengkap'] ?? '-';
                $nik = $siswa['nik'] ?? '-';
                $noreg = $siswa['no_registrasi'] ?? '-';
                $nisn = $siswa['nisn'] ?? '-';
                $asalSekolah = $siswa['asal_sekolah'] ?? '-';
                $konsentrasi = $siswa['konsentrasi_keahlian'] ?? '-';
                $status = $siswa['keterangan_status'] ?? 'Terverifikasi';
                @endphp

                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-5 py-4 min-w-[240px]">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$name" :gender="$gender" :index="$loop->index" />
                            <div>
                                <div class="font-semibold text-foreground text-sm uppercase whitespace-nowrap">{{ $name }}</div>
                                <div class="text-xs text-secondary mt-0.5">{{ $nik }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="px-5 py-4 min-w-[190px]">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1.5 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold whitespace-nowrap">{{ $noreg }}</span>
                            <span class="px-3 py-1.5 rounded-md bg-warning/10 text-warning-dark text-xs font-bold whitespace-nowrap">{{ $nisn }}</span>
                        </div>
                    </td>

                    <td class="px-5 py-4 min-w-[160px]">
                        <div class="text-sm font-semibold text-foreground whitespace-nowrap uppercase">{{ $asalSekolah }}</div>
                        <div class="text-xs text-secondary whitespace-nowrap">{{ $konsentrasi }}</div>
                    </td>

                    <td class="px-5 py-4 min-w-[120px]">
                        <span class="px-3 py-1.5 rounded-md bg-success/10 text-success text-xs font-bold whitespace-nowrap flex items-center gap-1.5 w-max">
                            <i data-lucide="check-circle-2" class="size-3.5"></i> {{ $status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-12 text-center text-secondary">
                        <i data-lucide="inbox" class="size-10 mx-auto mb-3 opacity-20"></i>
                        <p class="text-sm font-medium">Tidak ada data ditemukan</p>
                        <p class="text-xs mt-1">Gunakan kata kunci atau filter lain.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Menambahkan hx-boost agar link pagination Laravel dieksekusi via HTMX -->
    @if($paginatedData->hasPages())
    <div class="px-5 py-4 border-t border-border bg-gray-50/50" hx-boost="true" hx-target="#spmb-table-container" hx-swap="outerHTML">
        {{ $paginatedData->links() }}
    </div>
    @endif
</div>