<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .header p {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin: 0rem;
            line-height: 1.5rem;
        }

        .table-content {
            width: 100%;
            margin: 1rem 0 0 0;
            padding: 0;
            font-size: 0.8rem;
        }

        .table-content table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-content table th,
        .table-content table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        .table-content table td:nth-child(1) {
            width: 5% !important;
        }

        .table-content table td:nth-child(2) {
            text-align: left;
        }

        .table-content table td:nth-child(n+3) {
            width: 6% !important;
        }

        .table-footer {
            width: 100%;
            margin: 0;
            padding: 0;
            font-size: 0.9rem;
        }

        .table-footer table {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }

        .table-footer table th,
        .table-footer table td {
            border: none;
            text-align: left;
        }

        .table-footer table td:nth-child(1) {
            width: 50%;
            padding: 0 70px;
        }

        .table-footer table td:nth-child(2) {
            width: 50%;
            padding-left: 100px;
        }

        .table-footer table td.head div {
            margin-left: 40%;
        }
    </style>
</head>

<body>
    <div class="header">
        <p>DATA PESERTA DIDIK {{ strtoupper($sekolah->sekolah ?? '') }}</p>
        <p>TAHUN AJARAN {{ strtoupper($tahunAjaran->name ?? '') }}</p>
        <p>PERIODE {{ $bulan }}</p>
    </div>

    <figure class="table-content">
        @foreach ($laporan as $jurusanData)
        @if (count($jurusanData['rombels']) > 0)
        <table>
            <thead>
                <tr>
                    <th colspan="14" style="padding:20px 0 5px 0; text-align:left; font-size:15px; border:none; background:none;">
                        Kompetensi Keahlian {{ $jurusanData['jurusan']->name ?? '-' }}
                    </th>
                </tr>
                <tr>
                    <th rowspan="2">NO</th>
                    <th rowspan="2">KELAS</th>
                    <th colspan="3">KEADAAN AWAL</th>
                    <th colspan="3">MASUK</th>
                    <th colspan="3">KELUAR</th>
                    <th colspan="3">KEADAAN AKHIR</th>
                </tr>
                <tr>
                    <th>L</th>
                    <th>P</th>
                    <th>JML</th>
                    <th>L</th>
                    <th>P</th>
                    <th>JML</th>
                    <th>L</th>
                    <th>P</th>
                    <th>JML</th>
                    <th>L</th>
                    <th>P</th>
                    <th>JML</th>
                </tr>
            </thead>
            <tbody>
                @php
                $subTotalAwalL = $subTotalAwalP = $subTotalMasukL = $subTotalMasukP = 0;
                $subTotalKeluarL = $subTotalKeluarP = $subTotalAkhirL = $subTotalAkhirP = 0;
                @endphp

                @foreach ($jurusanData['rombels'] as $rombelData)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $rombelData['rombel']->name ?? '-' }}</td>

                    <td>{{ $rombelData['awal']['L'] }}</td>
                    <td>{{ $rombelData['awal']['P'] }}</td>
                    <td>{{ $rombelData['awal']['J'] }}</td>

                    <td>{{ $rombelData['masuk']['L'] }}</td>
                    <td>{{ $rombelData['masuk']['P'] }}</td>
                    <td>{{ $rombelData['masuk']['J'] }}</td>

                    <td>{{ $rombelData['keluar']['L'] }}</td>
                    <td>{{ $rombelData['keluar']['P'] }}</td>
                    <td>{{ $rombelData['keluar']['J'] }}</td>

                    <td>{{ $rombelData['akhir']['L'] }}</td>
                    <td>{{ $rombelData['akhir']['P'] }}</td>
                    <td>{{ $rombelData['akhir']['J'] }}</td>
                </tr>
                @php
                $subTotalAwalL += $rombelData['awal']['L']; $subTotalAwalP += $rombelData['awal']['P'];
                $subTotalMasukL += $rombelData['masuk']['L']; $subTotalMasukP += $rombelData['masuk']['P'];
                $subTotalKeluarL += $rombelData['keluar']['L']; $subTotalKeluarP += $rombelData['keluar']['P'];
                $subTotalAkhirL += $rombelData['akhir']['L']; $subTotalAkhirP += $rombelData['akhir']['P'];
                @endphp
                @endforeach

                <tr style="font-weight: bold;">
                    <td colspan="2">JUMLAH</td>
                    <td>{{ $subTotalAwalL }}</td>
                    <td>{{ $subTotalAwalP }}</td>
                    <td>{{ $subTotalAwalL + $subTotalAwalP }}</td>

                    <td>{{ $subTotalMasukL }}</td>
                    <td>{{ $subTotalMasukP }}</td>
                    <td>{{ $subTotalMasukL + $subTotalMasukP }}</td>

                    <td>{{ $subTotalKeluarL }}</td>
                    <td>{{ $subTotalKeluarP }}</td>
                    <td>{{ $subTotalKeluarL + $subTotalKeluarP }}</td>

                    <td>{{ $subTotalAkhirL }}</td>
                    <td>{{ $subTotalAkhirP }}</td>
                    <td>{{ $subTotalAkhirL + $subTotalAkhirP }}</td>
                </tr>
            </tbody>
        </table>
        @endif
        @endforeach
    </figure>

    <figure class="table-footer">
        <table>
            <tbody>
                <tr>
                    <td>
                        <br><br>Waka Kesiswaan
                        <br><br><br><br><br>
                        {{ $sekolah->kesiswaan->nama ?? '' }}
                        <br>NIP {{ $sekolah->kesiswaan->nip ?? '' }}
                    </td>
                    <td>
                        <br>Rejang Lebong, {{ $tglValidasi }}
                        <br>Koordinator Tata Usaha
                        <br><br><br><br><br>
                        {{ $sekolah->kaTu->nama ?? '' }}
                        <br>NIP {{ $sekolah->kaTu->nip ?? '' }}
                    </td>
                </tr>
                <tr>
                    <td class="head" colspan="2">
                        <div>
                            <br><br>Mengetahui,
                            <br>Kepala Sekolah
                            <br><br><br><br><br>
                            {{ $sekolah->kepalaSekolah->nama ?? '' }}
                            <br>NIP {{ $sekolah->kepalaSekolah->nip ?? '' }}
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </figure>
</body>

</html>