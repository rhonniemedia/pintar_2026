<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        /* Mengatur font Arial untuk keseluruhan dokumen */
        body {
            font-family: Arial, sans-serif;
        }

        /* Styling untuk judul header */
        .header h3 {
            text-align: center;
            margin: 5px 0;
            font-family: Arial, sans-serif;
            font-weight: bold;
            font-size: 14pt;
        }

        /* Lebar tabel 100% */
        .table-content {
            font-family: Arial, sans-serif;
            font-size: 0.9rem;
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
        }

        .table-content table td:nth-child(1) {
            width: 4.5% !important;
        }

        .table-content table td:nth-child(2) {
            text-align: left;
        }

        .table-content table td:nth-child(3),
        .table-content table td:nth-child(4),
        .table-content table td:nth-child(5),
        .table-content table td:nth-child(6),
        .table-content table td:nth-child(7),
        .table-content table td:nth-child(8),
        .table-content table td:nth-child(9),
        .table-content table td:nth-child(10),
        .table-content table td:nth-child(11) {
            width: 4.5% !important;
        }

        .table-content table td:nth-child(12) {
            width: 8% !important;
        }

        /* Table footer styling */
        .table-footer table {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }

        /* Styling border pada tabel */
        .table-footer table th,
        .table-footer table td {
            border: none;
            text-align: left;
        }

        /* Lebar kolom pada table footer */
        .table-footer table td:nth-child(1) {
            width: 40%;
            padding-left: 80px;
        }

        .table-footer table td:nth-child(2) {
            width: 35%;
            padding-left: 40px;
        }

        .table-footer table td:nth-child(3) {
            width: 25%;
        }
    </style>

</head>

<body>
    <div class="header">
        <!-- Menggunakan $tahunAjaran->name dengan asumsi nama kolomnya 'name' pada model CoreAcademicYear -->
        <h3>DATA JURUSAN TAHUN AJARAN {{ strtoupper($tahunAjaran->name ?? '') }}</h3>
        <h3>{{ strtoupper($sekolah->sekolah ?? '') }}</h3>
        <h3>BULAN {{ $bulan }}</h3>
    </div>

    <figure class="table-content">
        <table>
            <tbody>
                <tr>
                    <th rowspan="2">NO</th>
                    <th rowspan="2">KOMPETENSI KEAHLIAN</th>
                    <th colspan="3">KELAS X</th>
                    <th colspan="3">KELAS XI</th>
                    <th colspan="3">KELAS XII</th>
                    <th rowspan="2">TOTAL<br>JML</th>
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
                </tr>

                @php $no = 1; @endphp
                @foreach ($reportData as $data)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $data['name'] }}</td>

                    <!-- Kelas X -->
                    <td>{{ $data['grades']['10']['L'] }}</td>
                    <td>{{ $data['grades']['10']['P'] }}</td>
                    <td>{{ $data['grades']['10']['JML'] }}</td>

                    <!-- Kelas XI -->
                    <td>{{ $data['grades']['11']['L'] }}</td>
                    <td>{{ $data['grades']['11']['P'] }}</td>
                    <td>{{ $data['grades']['11']['JML'] }}</td>

                    <!-- Kelas XII -->
                    <td>{{ $data['grades']['12']['L'] }}</td>
                    <td>{{ $data['grades']['12']['P'] }}</td>
                    <td>{{ $data['grades']['12']['JML'] }}</td>

                    <!-- Total Per Jurusan -->
                    <td>{{ $data['total_jurusan'] }}</td>
                </tr>
                @endforeach

                <!-- Baris Total Keseluruhan -->
                <tr>
                    <td colspan="2" style="font-weight: bold;">TOTAL</td>

                    <!-- Total Kelas X -->
                    <td style="font-weight: bold;">{{ $grandTotal['10']['L'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['10']['P'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['10']['JML'] }}</td>

                    <!-- Total Kelas XI -->
                    <td style="font-weight: bold;">{{ $grandTotal['11']['L'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['11']['P'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['11']['JML'] }}</td>

                    <!-- Total Kelas XII -->
                    <td style="font-weight: bold;">{{ $grandTotal['12']['L'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['12']['P'] }}</td>
                    <td style="font-weight: bold;">{{ $grandTotal['12']['JML'] }}</td>

                    <!-- Total Semua Jurusan -->
                    <td style="font-weight: bold;">{{ $grandTotal['total'] }}</td>
                </tr>
            </tbody>
        </table>
    </figure>

    <figure class="table-footer">
        <table>
            <tbody>
                <tr>
                    <td>
                        <br>Kepala Sekolah
                        <br><br><br><br><br> {{ $sekolah->kepalaSekolah->nama ?? '' }}
                        <br>NIP {{ $sekolah->kepalaSekolah->nip ?? '' }}
                    </td>
                    <td>
                        <br>Waka Kesiswaan
                        <br><br><br><br><br>{{ $sekolah->kesiswaan->nama ?? '' }}
                        <br>NIP {{ $sekolah->kesiswaan->nip ?? '' }}
                    </td>
                    <td>Curup, {{ $tglValidasi }}
                        <br>Koordinator Tata Usaha
                        <br><br><br><br><br>{{ $sekolah->kaTu->nama ?? '' }}
                        <br>NIP {{ $sekolah->kaTu->nip ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </figure>
</body>

</html>