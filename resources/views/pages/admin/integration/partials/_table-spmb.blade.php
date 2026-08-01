<div id="spmb-table-container"
    hx-get="{{ request()->fullUrl() }}"
    hx-swap="outerHTML">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-border">
                    <th class="w-[32%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Profil Peserta
                        <div class="text-[11px] font-normal normal-case">Nama | No. Registrasi</div>
                    </th>

                    <th class="w-[30%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Registrasi
                        <div class="text-[11px] font-normal normal-case">NIK | NISN</div>
                    </th>

                    <th class="w-[28%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Asal Sekolah
                        <div class="text-[11px] font-normal normal-case">Sekolah | Pilihan Jurusan</div>
                    </th>

                    <th class="w-[10%] px-4 py-3 text-sm font-bold text-secondary tracking-wider">
                        Status
                        <div class="text-[11px] font-normal normal-case">Verifikasi SPMB</div>
                    </th>
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
                // KODE BARU: Memotong teks menggunakan explode agar teks di dalam kurung hilang
                $statusRaw = $siswa['keterangan_status'] ?? 'Terverifikasi';
                $status = explode(' (', $statusRaw)[0];
                @endphp

                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                    <td class="px-4 py-4 min-w-[240px]">
                        <div class="flex items-center gap-3">
                            <x-ui.avatar :name="$name" :gender="$gender" :index="$loop->index" />
                            <div>
                                <div class="font-semibold text-foreground text-sm uppercase whitespace-nowrap">{{ $name }}</div>
                                <div class="flex items-center gap-1.5 text-xs text-secondary mt-0.5">
                                    {{ $noreg }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-4 min-w-[190px]">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-3 py-1.5 rounded-md bg-teal-500/10 text-teal-700 text-xs font-bold whitespace-nowrap">{{ $nik }}</span>
                            <span class="px-3 py-1.5 rounded-md bg-warning/10 text-warning-dark text-xs font-bold whitespace-nowrap">{{ $nisn }}</span>
                        </div>
                    </td>

                    <td class="px-4 py-4 min-w-[160px]">
                        <div class="text-sm font-semibold text-foreground whitespace-nowrap uppercase">{{ $asalSekolah }}</div>
                        <div class="text-xs text-secondary whitespace-nowrap">{{ $konsentrasi }}</div>
                    </td>

                    <td class="px-4 py-4 min-w-[120px]">
                        <span class="px-3 py-1.5 rounded-md bg-success/10 text-success text-xs font-bold whitespace-nowrap flex items-center gap-1.5 w-max">
                            <i data-lucide="check-circle-2" class="size-3.5"></i> {{ $status }}
                        </span>
                    </td>
                </tr>
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

    {{-- Memanggil komponen pagination yang diletakkan sejajar tanpa kotak pembungkus tambahan --}}
    @if($paginatedData->hasPages())
    <x-ui.pagination :paginator="$paginatedData" hxTarget="#spmb-table-container" />
    @endif
</div>