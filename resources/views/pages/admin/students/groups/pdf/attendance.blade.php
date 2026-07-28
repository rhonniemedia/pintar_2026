<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir</title>
</head>
<style>
    /* Mengatur margin untuk halaman */
    @page {
        margin: 1.5cm;
    }

    /* --- TAMBAHAN BARU: CSS UNTUK PAGE BREAK --- */
    .page-break {
        page-break-after: always;
    }

    .page-break:last-child {
        page-break-after: auto;
        /* Halaman terakhir tidak perlu page break */
    }

    .table-header {
        font-family: Arial, sans-serif;
        width: 100%;
        text-align: center;
    }

    .department {
        font-size: 1.2rem;
        margin: 0;
    }

    .logo-img {
        height: 90px;
    }

    .sub-department {
        font-size: 1.5rem;
        font-weight: bold;
        margin: 0;
    }

    .address-1,
    .address-2 {
        font-size: 0.7rem;
        margin: 0;
    }

    .table-title {
        font-family: Arial, sans-serif;
        font-size: 0.8rem;
        width: 100%;
    }

    .table-title .title {
        font-weight: bold;
        text-align: center;
        font-size: 1rem;
        padding: 5px;
    }

    .table-content,
    .table-footer {
        font-family: Arial, sans-serif;
        font-size: 0.8rem;
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .table-footer td {
        vertical-align: top;
    }

    .table-content th,
    .table-content td {
        border: 1px solid black;
        padding-left: 4px;
        padding-right: 4px;
        text-align: center;
        height: 1.08rem;
    }

    .table-content td.nama {
        text-align: left;
    }

    .table-rekapitulasi {
        width: 100%;
        border-collapse: collapse;
    }

    .table-rekapitulasi td {
        border: 1px solid black;
        padding-left: 4px;
        padding-right: 4px;
        height: 1.1rem;
    }

    .table-rekapitulasi td:nth-child(1) {
        border-right: none;
    }

    .table-rekapitulasi td:nth-child(2) {
        border-left: none;
    }

    .jml {
        text-align: right;
    }

    .sign {
        padding-left: 100px;
    }

    .table-content th,
    .table-content td,
    .table-footer th,
    .table-footer td {
        width: calc(100% / 10);
    }
</style>

<body>
    {{-- Lakukan looping untuk setiap kelas/rombel yang dikirim dari controller --}}
    @foreach ($pagesData as $page)
    @php
    // Ekstrak data agar pemanggilan variabel di HTML tetap sama seperti kode Anda sebelumnya
    $dataRombel = $page['dataRombel'];
    $tahunAjaran = $page['tahunAjaran'];
    $rombel = $page['rombel'];
    $result = $page['result'];
    @endphp

    {{-- Bungkus setiap rombel dengan class page-break --}}
    <div class="page-break">
        <table class="table-header" style="border-bottom: 2px solid black;">
            <tbody>
                <tr>
                    <td style="width: 17%;"><img src="{{ public_path('assets/images/icons/bengkulu.png') }}" alt="" class="logo-img"></td>
                    <td>
                        <p class="department">PEMERINTAH PROVINSI BENGKULU</p>
                        <p class="sub-department">SMK NEGERI 1 REJANG LEBONG</p>
                        <p class="address-1">Jalan Ahmad Marzuki No. 105, Air Rambai, Curup, Rejang Lebong, Bengkulu 39111</p>
                        <p class="address-2">Telepon 0732 21258, Laman smkn1rl.sch.id, Pos-el mail@smkn1rl.sch.id</p>
                    </td>
                    <td style="width: 17%;"><img src="{{ public_path('assets/images/icons/smk.png') }}" alt="" class="logo-img"></td>
                </tr>
            </tbody>
        </table>

        <table class="table-title">
            <tbody>
                <tr>
                    <td class="title" colspan="3">DAFTAR HADIR PESERTA DIDIK</td>
                </tr>
                <tr>
                    <td style="width: 25%;">KELAS</td>
                    <td style="width: 1.5%;">:</td>
                    <td>{{ $dataRombel->nama_rombel }}</td>
                </tr>
                <tr>
                    <td>JURUSAN</td>
                    <td>:</td>
                    <td>{{ strtoupper($dataRombel->dataJurusan->jurusan) }} </td>
                </tr>
                <tr>
                    <td>WALI KELAS</td>
                    <td>:</td>
                    <td>{{ $dataRombel->dataGuru->nama }}</td>
                </tr>
                <tr>
                    <td>TAHUN PELAJARAN</td>
                    <td>:</td>
                    <td>{{ $tahunAjaran->tahun_ajaran }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table-content">
            <thead>
                <tr>
                    <th style="width: 5%;" rowspan="2">NO</th>
                    <th style="width: 7%;" rowspan="2">NIS</th>
                    <th rowspan="2">NAMA SISWA</th>
                    <th style="width: 12%;" rowspan="2">NISN</th>
                    <th style="width: 4.5%;" rowspan="2">L/P</th>
                    <th style="width: 9%;" colspan="5">KEHADIRAN</th>
                    <th style="width: 6%;" rowspan="2">KET</th>
                </tr>
                <tr>
                    <th style="width: 4%;">1</th>
                    <th style="width: 4%;">2</th>
                    <th style="width: 4%;">3</th>
                    <th style="width: 4%;">4</th>
                    <th style="width: 4%;">5</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rombel as $siswa)
                <tr>
                    <td style="width: 5%;">{{ $loop->iteration }}</td>
                    <td style="width: 7%;">{{ $siswa->dataPesertaDidik->nis ?? '' }}</td>
                    <td class="nama">{{ $siswa->dataPesertaDidik->nama ?? '' }}</td>
                    <td style="width: 12%;">{{ $siswa->dataPesertaDidik->nisn ?? '' }}</td>
                    <td style="width: 4.5%;">{{ $siswa->dataPesertaDidik->jk ?? '' }}</td>
                    <td style="width: 4%;">&nbsp;</td>
                    <td style="width: 4%;">&nbsp;</td>
                    <td style="width: 4%;">&nbsp;</td>
                    <td style="width: 4%;">&nbsp;</td>
                    <td style="width: 4%;">&nbsp;</td>
                    <td style="width: 6%;">&nbsp;</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table-footer">
            <tbody>
                <tr>
                    <td class="rekapitulasi" style="width: 50%;">
                        Rekapitulasi:
                        <table class="table-rekapitulasi">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;">Laki-laki</td>
                                    <td class="jml" style="width: 50%;">{{ $result->lakiLaki }} Orang</td>
                                </tr>
                                <tr>
                                    <td>Perempuan</td>
                                    <td class="jml">{{ $result->perempuan }} Orang</td>
                                </tr>
                                <tr>
                                    <td>Jumlah</td>
                                    <td class="jml">{{ $result->total }} Orang</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td class="sign" style="width: 50%;">
                        Curup, __________ 202__<br />
                        Wali Kelas<br /><br /><br /><br /><br />
                        {{ $dataRombel->dataGuru->nama }}<br />
                        NIP {{ $dataRombel->dataGuru->nip ?? '~' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    @endforeach
</body>

</html>