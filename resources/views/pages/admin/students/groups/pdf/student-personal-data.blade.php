<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Biodata Peserta Didik - {{ $personalData->full_name }}</title>
    <style>
        /* Margin kertas standar */
        @page {
            margin: 50px 50px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.35;
        }

        /* Warna Utama & Tipografi */
        .text-primary {
            color: #2563eb;
        }

        .text-gray {
            color: #6b7280;
        }

        /* Judul Dokumen */
        .document-title {
            font-size: 22px;
            font-weight: 900;
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .document-title span {
            color: #2563eb;
        }

        /* KUNCI ANTI-TERPOTONG: Bungkus setiap section dengan class ini */
        .section-block {
            page-break-inside: avoid;
            margin-bottom: 25px;
        }

        /* Styling Judul Section */
        .section-title {
            color: #2563eb;
            font-weight: 900;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        /* Styling Tabel Data 1 Kolom */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table td {
            vertical-align: top;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        /* Pengaturan Lebar Kolom (Total 100%) */
        .label {
            width: 30%;
            color: #4b5563;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .colon {
            width: 3%;
            font-weight: bold;
            color: #9ca3af;
        }

        .value {
            width: 67%;
            color: #111;
            font-weight: bold;
        }

        /* Khusus Header & Foto - Full Width tanpa card */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .header-table td {
            border: none;
            padding: 0;
        }

        .header-info .name {
            font-size: 20px;
            font-weight: 900;
            color: #1e3a8a;
            margin-bottom: 2px;
        }

        .header-info .name-sub {
            font-size: 13px;
            color: #6b7280;
            font-weight: 400;
            margin-bottom: 12px;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 8px;
        }

        .header-data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-data-table tr {
            page-break-inside: avoid;
        }

        .header-data-table td {
            vertical-align: top;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .header-data-table .label {
            width: 30%;
            color: #4b5563;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .header-data-table .colon {
            width: 3%;
            font-weight: bold;
            color: #9ca3af;
        }

        .header-data-table .value {
            width: 67%;
            color: #111;
            font-weight: bold;
        }

        .photo-box {
            width: 3.5cm;
            height: 4.5cm;
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            text-align: center;
            color: #9ca3af;
            border-radius: 8px;
            margin-left: auto;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-size: 11px;
        }

        .photo-img {
            width: 3.5cm;
            height: 4.5cm;
            object-fit: cover;
            border-radius: 8px;
            margin-left: auto;
            display: block;
        }

        /* Styling Tabel Rapor & TKA - Horizontal */
        .rapor-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 12px;
        }

        .rapor-table th {
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: bold;
            text-align: center;
            padding: 6px 10px;
            border: 1px solid #1e4fa8;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .rapor-table td {
            text-align: center;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
        }

        .rapor-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .rapor-table .total-row {
            background-color: #eff6ff !important;
            font-weight: bold;
            color: #2563eb;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Judul Tabel Rapor & TKA */
        .table-title {
            font-weight: 700;
            font-size: 12px;
            color: #1e3a8a;
            margin-top: 10px;
            margin-bottom: 3px;
            padding-left: 2px;
        }

        .table-title:first-of-type {
            margin-top: 5px;
        }

        /* TANDA TANGAN - Rata Tengah dan Tanggal di Atas */
        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-top: 1px solid #f3f4f6;
            padding-top: 15px;
            page-break-inside: avoid;
            border-collapse: collapse;
        }

        .ttd-table td {
            width: 50%;
            vertical-align: bottom;
            /* Menjaga agar garis nama tetap sejajar */
            border: none;
            padding: 0;
            text-align: center;
            /* Membuat teks rata tengah */
        }

        .ttd-date {
            color: #4b5563;
            font-size: 12px;
            margin-bottom: 5px;
            /* Jarak antara tanggal dan peran/jabatan */
        }

        .ttd-title {
            font-weight: bold;
            color: #4b5563;
            font-size: 12px;
            margin-bottom: 60px;
            /* Jarak untuk area tanda tangan manual */
        }

        .ttd-name-box {
            display: inline-block;
            border-top: 1px solid #333;
            /* Garis di atas nama */
            min-width: 200px;
            padding-top: 4px;
            font-size: 11px;
            color: #000000;
        }

        .ttd-date {
            color: #4b5563;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <div class="document-title">
        BIODATA <span>PESERTA DIDIK</span>
    </div>

    {{-- IDENTITAS & FOTO - Full Width tanpa card --}}
    <table class="header-table">
        <tr>
            <td style="width: 75%; padding-right: 20px;">
                <div class="header-info">
                    <div class="name">{{ $personalData->full_name }}</div>
                    <div class="name-sub">Peserta Didik Baru Tahun Ajaran {{ $tahunAjaran }}</div>

                    <table class="header-data-table">
                        <tr>
                            <td class="label">Nama Panggilan</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $personalData->nick_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">NIK</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $personalData->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">NISN</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $personalData->nisn ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tempat, Tgl Lahir</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $ttl }}</td>
                        </tr>
                        <tr>
                            <td class="label">Jenis Kelamin</td>
                            <td class="colon">:</td>
                            <td class="value">{{ $personalData->gender === 'L' ? 'Laki-laki (L)' : 'Perempuan (P)' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="width: 25%; text-align: right; vertical-align: top; padding-top: 5px;">
                @if($personalData->photo && file_exists(public_path('storage/' . $personalData->photo)))
                <img class="photo-img" src="{{ public_path('storage/' . $personalData->photo) }}" alt="Pas Foto">
                @else
                <div class="photo-box">
                    <br><br><br>PAS FOTO<br>3x4
                </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- A. DETAIL PRIBADI & KONTAK --}}
    <div class="section-block">
        <div class="section-title">A. DETAIL PRIBADI & KONTAK</div>
        <table class="data-table">
            <tr>
                <td class="label">Agama</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->religion ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Anak Ke</td>
                <td class="colon">:</td>
                <td class="value">
                    @if($personalData->child_order)
                    {{ $personalData->child_order }} dari {{ $personalData->number_of_siblings ?? 0 }} Saudara Kandung
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">No. Telepon / HP</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->phone_number ? '0' . $personalData->phone_number : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->email ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- B. DATA ALAMAT DOMISILI --}}
    <div class="section-block">
        <div class="section-title">B. DATA ALAMAT DOMISILI</div>
        <table class="data-table">
            <tr>
                <td class="label">Alamat Jalan</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->address ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">RT / RW</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->rt ?? '-' }} / {{ $personalData->rw ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Desa / Kelurahan</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->village ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kecamatan</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->district ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kabupaten / Kota</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->regency ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Provinsi & Kode Pos</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->province ?? '-' }} @if($personalData->postal_code) ({{ $personalData->postal_code }}) @endif</td>
            </tr>
            <tr>
                <td class="label">Jenis Tempat Tinggal</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->residence_type ? ucwords(str_replace('_', ' ', $personalData->residence_type)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Transportasi & Jarak</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $personalData->transportation ? ucwords(str_replace('_', ' ', $personalData->transportation)) : '-' }}
                    @if($personalData->distance_to_school) ({{ $personalData->distance_to_school }}) @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- C. PENDIDIKAN SEBELUMNYA --}}
    <div class="section-block">
        <div class="section-title">C. PENDIDIKAN SEBELUMNYA</div>
        <table class="data-table">
            <tr>
                <td class="label">Nama Sekolah Asal</td>
                <td class="colon">:</td>
                <td class="value text-primary">{{ $personalData->previous_school ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status Sekolah</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->previous_school_status ? ucwords(str_replace('_', ' ', $personalData->previous_school_status)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">NPSN</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->previous_school_npsn ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kota & Provinsi</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->previous_school_city ?? '-' }}@if($personalData->previous_school_province), {{ $personalData->previous_school_province }}@endif</td>
            </tr>
            <tr>
                <td class="label">Tahun Lulus</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->graduation_year ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. Ijazah / SKL</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->graduation_certificate_number ?? '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- D. DATA AKADEMIK (NILAI RAPOR & TKA) --}}
    <div class="section-block">
        <div class="section-title" style="color: #065f46; border-bottom-color: #a7f3d0;">D. DATA AKADEMIK (NILAI RAPOR &amp; TKA)</div>

        @if($registration)
        <div class="table-title">Nilai Rapor</div>
        <table class="rapor-table">
            <thead>
                <tr>
                    <th>Smt 1</th>
                    <th>Smt 2</th>
                    <th>Smt 3</th>
                    <th>Smt 4</th>
                    <th>Smt 5</th>
                    <th>Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $registration->report_sem_1 !== null ? number_format($registration->report_sem_1, 2) : '-' }}</td>
                    <td>{{ $registration->report_sem_2 !== null ? number_format($registration->report_sem_2, 2) : '-' }}</td>
                    <td>{{ $registration->report_sem_3 !== null ? number_format($registration->report_sem_3, 2) : '-' }}</td>
                    <td>{{ $registration->report_sem_4 !== null ? number_format($registration->report_sem_4, 2) : '-' }}</td>
                    <td>{{ $registration->report_sem_5 !== null ? number_format($registration->report_sem_5, 2) : '-' }}</td>
                    <td class="total-row">{{ $registration->report_average !== null ? number_format($registration->report_average, 2) : '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="table-title">Tes Kemampuan Akademik (TKA)</div>
        <table class="rapor-table" style="margin-top: 5px;">
            <thead>
                <tr>
                    <th>Matematika</th>
                    <th>Bahasa Indonesia</th>
                    <th>Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $registration->tka_math !== null ? number_format($registration->tka_math, 2) : '-' }}</td>
                    <td>{{ $registration->tka_indonesian !== null ? number_format($registration->tka_indonesian, 2) : '-' }}</td>
                    <td class="total-row">{{ $registration->tka_average !== null ? number_format($registration->tka_average, 2) : '-' }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <p style="color:#6b7280; font-style: italic;">Data pendaftaran (nilai rapor & TKA) belum diisi.</p>
        @endif
    </div>

    {{-- E. KESEHATAN, MINAT & BAKAT --}}
    <div class="section-block">
        <div class="section-title">E. KESEHATAN, MINAT & BAKAT</div>
        <table class="data-table">
            <tr>
                <td class="label">Tinggi / Berat Badan</td>
                <td class="colon">:</td>
                <td class="value">
                    {{ $personalData->height ?? '-' }}{{ $personalData->height ? ' cm' : '' }} / {{ $personalData->weight ?? '-' }}{{ $personalData->weight ? ' kg' : '' }}
                </td>
            </tr>
            <tr>
                <td class="label">Golongan Darah</td>
                <td class="colon">:</td>
                <td class="value">{{ $personalData->blood_type ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Riwayat Penyakit</td>
                <td class="colon">:</td>
                {{-- Format: diabetes -> Diabetes --}}
                <td class="value">{{ $personalData->medical_history ? ucwords(str_replace('_', ' ', $personalData->medical_history)) : 'Tidak ada' }}</td>
            </tr>
            <tr>
                <td class="label">Kondisi Khusus (Disabilitas)</td>
                <td class="colon">:</td>
                <td class="value">
                    @if($personalData->is_special_condition === 'yes')
                    {{ $personalData->special_condition_type ? ucwords(str_replace('_', ' ', $personalData->special_condition_type)) : 'Ada' }}
                    @else
                    Tidak Ada
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Ekstrakurikuler Pilihan</td>
                <td class="colon">:</td>
                {{-- Format: jurnalistik -> Jurnalistik --}}
                <td class="value">{{ $personalData->extracurricular_choice ? ucwords(str_replace('_', ' ', $personalData->extracurricular_choice)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Minat Organisasi</td>
                <td class="colon">:</td>
                {{-- Format: osis -> OSIS (pakai ucwords karena berupa singkatan) --}}
                <td class="value">{{ $personalData->interest_organization ? ucwords(str_replace('_', ' ', $personalData->interest_organization)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kategori Seni (FL2SN)</td>
                <td class="colon">:</td>
                {{-- Format: seni_tari -> Seni Tari --}}
                <td class="value">{{ $personalData->fl2sn_category ? ucwords(str_replace('_', ' ', $personalData->fl2sn_category)) : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kategori Olahraga (O2SN)</td>
                <td class="colon">:</td>
                {{-- Format: atletik -> Atletik --}}
                <td class="value">{{ $personalData->o2sn_category ? ucwords(str_replace('_', ' ', $personalData->o2sn_category)) : '-' }}</td>
            </tr>
        </table>
    </div>

    {{-- F. DATA AYAH KANDUNG --}}
    <div class="section-block">
        <div class="section-title" style="color: #1e3a8a; border-bottom-color: #bfdbfe;">F. DATA AYAH KANDUNG</div>
        <table class="data-table">
            @if($father)
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->name }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->isAlive() ? 'Masih Hidup' : 'Telah Meninggal' }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->birth_year ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->education ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->occupation ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->income_range ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->phone_number ? '0' . $father->phone_number : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $father->address ?? '-' }}</td>
            </tr>
            @else
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- G. DATA IBU KANDUNG --}}
    <div class="section-block">
        <div class="section-title" style="color: #831843; border-bottom-color: #fbcfe8;">G. DATA IBU KANDUNG</div>
        <table class="data-table">
            @if($mother)
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->name }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->isAlive() ? 'Masih Hidup' : 'Telah Meninggal' }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->birth_year ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->education ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->occupation ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->income_range ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->phone_number ? '0' . $mother->phone_number : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $mother->address ?? '-' }}</td>
            </tr>
            @else
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- H. DATA WALI (JIKA ADA) --}}
    <div class="section-block">
        <div class="section-title" style="color: #4c1d95; border-bottom-color: #ddd6fe;">H. DATA WALI (JIKA ADA)</div>
        <table class="data-table">
            @if($guardian)
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->name }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->isAlive() ? 'Masih Hidup' : 'Telah Meninggal' }}</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->nik ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->birth_year ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->education ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->occupation ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->income_range ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->phone_number ? '0' . $guardian->phone_number : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">{{ $guardian->address ?? '-' }}</td>
            </tr>
            @else
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">NIK</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Tahun Lahir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pendidikan Terakhir</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Pekerjaan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Penghasilan per Bulan</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">No. HP</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            <tr>
                <td class="label">Alamat Lengkap</td>
                <td class="colon">:</td>
                <td class="value">-</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- TANDA TANGAN --}}
    {{-- $penandaTangan dikirim langsung dari controller berdasarkan pilihan user di
         modal cetak. Kalau karena suatu sebab tidak dikirim (null), fallback ke
         urutan lama: Ayah > Ibu > Wali yang masih hidup. --}}
    @php
    $penandaTangan = $penandaTangan ?? collect([$father, $mother, $guardian])->first(fn($p) => $p && $p->isAlive());
    @endphp

    <table class="ttd-table">
        <tr>
            <td>
                <div class="ttd-title">Wali Murid</div>
                <div class="ttd-name-box">
                    {{ optional($penandaTangan)->name ?? '......................................' }}
                </div>
            </td>
            <td>
                {{-- Tanggal dipindah ke atas --}}
                <div class="ttd-date">{{ $tanggalCetak }}</div>
                <div class="ttd-title">Calon Peserta Didik</div>
                <div class="ttd-name-box">
                    {{ $personalData->full_name }}
                </div>
            </td>
        </tr>
    </table>
</body>

</html>