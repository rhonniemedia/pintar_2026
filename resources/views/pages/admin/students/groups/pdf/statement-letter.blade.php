@php
$charsPerLine = 60;
$estimateLines = function ($text) use ($charsPerLine) {
$len = mb_strlen((string) $text);
return max(1, (int) ceil($len / $charsPerLine));
};

$alamatSiswaLines = $estimateLines($alamatLengkapSiswa ?? '');
$alamatWaliLines = $estimateLines(($waliPembuat->address ?? $alamatLengkapSiswa) ?? '');

$extraLines = max(0, $alamatSiswaLines - 1) + max(0, $alamatWaliLines - 1);

if ($extraLines >= 3) {
$densityClass = 'density-2';
} elseif ($extraLines >= 1) {
$densityClass = 'density-1';
} else {
$densityClass = '';
}
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pernyataan Peserta Didik Baru - {{ $personalData->full_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 1cm 1.2cm;
        }

        /* FONT DIPERBESAR MENJADI 9.5pt */
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9.5pt !important;
            line-height: 1.15;
            color: #1a1a1a;
        }

        .document-title {
            text-align: center;
            font-size: 11.5pt !important;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #1e3a5f;
            margin-bottom: 4px;
        }

        .section-title {
            font-weight: bold;
            font-size: 9.5pt !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #1e3a5f;
            border-bottom: 1px solid #c9d6e3;
            padding-bottom: 1px;
            margin: 4px 0 2px 0;
        }

        p {
            text-align: justify;
            margin: 3px 0;
        }

        /* KHUSUS TABEL DATA: line-height dikurangi agar tidak terlalu lebar */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .form-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .col-label {
            width: 30%;
            font-weight: bold;
            color: #333;
        }

        .col-colon {
            width: 3%;
            text-align: center;
            color: #888;
        }

        .col-value {
            width: 67%;
        }

        .garis-bawah {
            display: block;
            width: 100%;
            border-bottom: 1px dotted #999;
            min-height: 10px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .list-wrapper {
            margin: 3px 0 4px 0;
        }

        .cols-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .cols-table td {
            vertical-align: top;
            width: 50%;
        }

        .cols-table .col-left {
            padding-right: 12px;
        }

        .cols-table .col-right {
            padding-left: 12px;
        }

        ol.list-items {
            font-size: 9.5pt !important;
            margin: 0;
            padding-left: 18px;
            text-align: justify;
        }

        ol.list-items li {
            margin-bottom: 1px;
            padding-left: 0;
        }

        ol.list-items li strong {
            color: #1e3a5f;
        }

        /* TABEL TANDA TANGAN: Menggunakan tabel/tabulasi rata kiri */
        .signature-table {
            width: 100%;
            text-align: left;
            margin-top: 6px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            vertical-align: top;
            padding-left: 80px;
            padding-right: 15px;
        }

        .sig-role {
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .materai-note {
            font-size: 8pt !important;
            font-style: italic;
            color: #6b7280;
            display: block;
            margin-top: 1px;
        }

        /* RUANG TANDA TANGAN: Diperlebar secara signifikan */
        .ttd-space {
            height: 55px;
        }

        .ttd-name {
            display: inline-block;
            border-top: 1px solid #333;
            min-width: 85%;
            padding-top: 2px;
            font-size: 9.5pt !important;
        }

        /* Penyesuaian density otomatis jika teks memanjang */
        body.density-1 .form-table {
            line-height: 1.3;
        }

        body.density-1 .ttd-space {
            height: 45px;
        }

        body.density-2 .form-table {
            line-height: 1.2;
        }

        body.density-2 .ttd-space {
            height: 35px;
        }

        body.density-2 p {
            margin: 1px 0;
        }
    </style>
</head>

<body class="{{ $densityClass }}">

    <div class="document-title">Surat Pernyataan Peserta Didik Baru</div>

    <p>Yang bertanda tangan di bawah ini:</p>

    <div class="section-title">A. Peserta Didik</div>
    <table class="form-table">
        <tr>
            <td class="col-label">Nama Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $personalData->full_name }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Tempat, tanggal Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $ttl }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $personalData->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Agama</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $personalData->religion ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Alamat Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $alamatLengkapSiswa }}</span></td>
        </tr>
        <tr>
            <td class="col-label">No. HP/Telepon</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $personalData->phone_number ? '0' . $personalData->phone_number : '-' }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Asal Sekolah</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $personalData->previous_school ?? '-' }}</span></td>
        </tr>
    </table>

    <div class="section-title">B. Orang Tua / Wali</div>
    <table class="form-table">
        @if($waliPembuat)
        <tr>
            <td class="col-label">Nama Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $waliPembuat->name }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $waliPembuat->occupation ?? '-' }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Alamat Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $waliPembuat->address ?? $alamatLengkapSiswa }}</span></td>
        </tr>
        <tr>
            <td class="col-label">No. HP/Telepon</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $waliPembuat->phone_number ? '0' . $waliPembuat->phone_number : '-' }}</span></td>
        </tr>
        <tr>
            <td class="col-label">Hubungan Keluarga</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">{{ $waliPembuat->relationship_label }}</span></td>
        </tr>
        @else
        <tr>
            <td class="col-label">Nama Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">-</span></td>
        </tr>
        <tr>
            <td class="col-label">Pekerjaan</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">-</span></td>
        </tr>
        <tr>
            <td class="col-label">Alamat Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">-</span></td>
        </tr>
        <tr>
            <td class="col-label">No. HP/Telepon</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">-</span></td>
        </tr>
        <tr>
            <td class="col-label">Hubungan Keluarga</td>
            <td class="col-colon">:</td>
            <td class="col-value"><span class="garis-bawah">-</span></td>
        </tr>
        @endif
    </table>

    <p>Dengan sesungguhnya dan penuh kesadaran menyatakan bahwa selama menjadi peserta didik di <strong>{{ $namaSekolah }}</strong>, saya bersedia:</p>

    <div class="list-wrapper">
        <table class="cols-table">
            <tr>
                <td class="col-left">
                    <ol class="list-items" type="1">
                        <li><strong>Menaati</strong> pelaksanaan Wawasan Wiyata Mandala serta seluruh peraturan dan tata tertib sekolah yang berlaku dengan penuh tanggung jawab.</li>
                        <li><strong>Mengikuti</strong> pendidikan agama sesuai dengan agama dan keyakinan yang saya anut serta menghormati keyakinan orang lain.</li>
                        <li><strong>Mengikuti</strong> kegiatan ekstrakurikuler wajib maupun pilihan yang telah ditetapkan sekolah dengan penuh tanggung jawab.</li>
                        <li><strong>Menjaga</strong> nama baik diri sendiri, keluarga, dan {{ $namaSekolah }}, baik di dalam maupun di luar lingkungan sekolah.</li>
                        <li><strong>Mematuhi</strong> ketentuan penggunaan telepon seluler (HP) di lingkungan sekolah sesuai dengan prosedur yang berlaku dan ditetapkan sekolah.</li>
                    </ol>
                </td>
                <td class="col-right">
                    <ol class="list-items" type="1" start="6">
                        <li><strong>Tidak membawa</strong> kendaraan bermotor ke lingkungan sekolah, baik memiliki maupun tidak memiliki Surat Izin Mengemudi (SIM).</li>
                        <li><strong>Tidak mengajukan</strong> permohonan pindah konsentrasi keahlian (jurusan) selama menempuh pendidikan di sekolah.</li>
                        <li><strong>Membebaskan</strong> sekolah dari segala tuntutan dan tanggung jawab atas kehilangan barang berharga milik pribadi di lingkungan sekolah.</li>
                        <li><strong>Mengenakan</strong> seragam sekolah sesuai jadwal dan ketentuan yang berlaku, serta senantiasa rapi dan sopan.</li>
                        <li><strong>Tidak terlibat</strong> dalam tindakan melanggar hukum, seperti kriminal, narkoba, merokok, minuman keras, tawuran, dan bullying.</li>
                    </ol>
                </td>
            </tr>
        </table>
    </div>

    <p>Apabila di kemudian hari saya terbukti tidak menaati dan melanggar tata tertib yang telah ditetapkan dalam pernyataan di atas, maka saya siap menerima sanksi berupa:</p>

    <div class="list-wrapper">
        <ol class="list-items" type="a">
            <li><strong>Larangan</strong> mengikuti kegiatan belajar mengajar di sekolah (skorsing) selama maksimal 7 (tujuh) hari.</li>
            <li><strong>Pengembalian</strong> status peserta didik kepada orang tua/wali (dikeluarkan dari sekolah).</li>
        </ol>
    </div>

    <p>Demikian surat pernyataan ini saya buat dengan sebenarnya, tanpa ada paksaan dari pihak mana pun, serta diketahui dan disetujui oleh orang tua/wali.</p>

    <table class="signature-table">
        <tr>
            <td style="padding-bottom: 2px;"></td>
            <td style="padding-bottom: 2px;">Rejang Lebong, {{ $tanggalCetak }}</td>
        </tr>
        <tr>
            <td>
                Mengetahui/Menyetujui,<br>
                <span class="sig-role">Orang Tua/Wali</span>
            </td>
            <td>
                <span class="sig-role">Yang Membuat Pernyataan,</span><br><br>
                <span class="materai-note">(*Materai Rp 10.000)</span>
            </td>
        </tr>
        <tr>
            <td class="ttd-space"></td>
            <td class="ttd-space"></td>
        </tr>
        <tr>
            <td><span class="ttd-name">{{ optional($waliPembuat)->name ?? '......................................' }}</span></td>
            <td><span class="ttd-name">{{ $personalData->full_name }}</span></td>
        </tr>
    </table>

</body>

</html>