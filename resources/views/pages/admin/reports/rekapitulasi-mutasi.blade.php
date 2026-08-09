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
        }

        .header h3 {
            text-align: center;
            margin: 5px 0;
            font-weight: bold;
            font-size: 14pt;
        }

        .title h5 {
            text-align: left;
            margin: 2px 0 10px 0;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .table-content {
            width: 100%;
            margin: 0;
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
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .table-content table td:nth-child(3),
        .table-content table td:nth-child(5),
        .table-content table td:nth-child(6),
        .table-content table td:nth-child(10) {
            text-align: left;
        }

        .table-footer table {
            width: 100%;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .table-footer table th,
        .table-footer table td {
            border: none;
            text-align: left;
        }

        .table-footer table td:nth-child(1) {
            width: 40%;
            padding-left: 150px;
        }

        .table-footer table td:nth-child(2) {
            width: 35%;
            padding-left: 50px;
        }

        .table-footer table td:nth-child(3) {
            width: 25%;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>DATA REKAPITULASI MUTASI PESERTA DIDIK PERIODE {{ $bulan }}</h3>
    </div>

    <div class="title">
        <h5>MASUK</h5>
    </div>
    <figure class="table-content">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width:3%">NO</th>
                    <th rowspan="2" style="width:7%">NIS</th>
                    <th rowspan="2" style="width:20%;">NAMA LENGKAP</th>
                    <th rowspan="2" style="width:3%">LP</th>
                    <th rowspan="2" style="width:16%;">TEMPAT TGL LAHIR</th>
                    <th colspan="2" style="width:24%">ORANG TUA/WALI</th>
                    <th colspan="2" style="width:14%">DITERIMA</th>
                    <th rowspan="2" style="width:13%;">KETERANGAN</th>
                </tr>
                <tr>
                    <th style="width:14%;">NAMA</th>
                    <th style="width:10%;">PEKERJAAN</th>
                    <th style="width:7%;">TANGGAL</th>
                    <th style="width:7%;">KELAS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mutasiMasuk as $masuk)
                @php
                $vault = $masuk->student->vault ?? null;
                $guardian = $masuk->student->guardians->first() ?? null;

                $tempatLahir = $vault->pob_encrypted ?? '-';
                $tglLahir = $vault->dob_encrypted ? \Carbon\Carbon::parse($vault->dob_encrypted)->format('d-m-Y') : '-';
                $namaWali = $guardian->name ?? '-';
                $pekerjaanWali = $guardian?->occupation?->label() ?? '-';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $masuk->student->nis ?? '-' }}</td>
                    <td>{{ strtoupper($masuk->student->name) }}</td>
                    <td>{{ $masuk->student->gender }}</td>
                    <td>{{ ucwords(strtolower($tempatLahir)) }}, {{ $tglLahir }}</td>
                    <td>{{ strtoupper($namaWali) }}</td>
                    <td>{{ ucwords(strtolower($pekerjaanWali)) }}</td>
                    <td>{{ \Carbon\Carbon::parse($masuk->mutation_date)->format('d-m-Y') }}</td>
                    <td>{{ $masuk->classGroup->name ?? '-' }}</td>
                    <td>Dari: {{ $masuk->origin_school ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">NIHIL</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </figure>

    <div class="title" style="margin-top: 15px;">
        <h5>KELUAR</h5>
    </div>
    <figure class="table-content">
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width:3%">NO</th>
                    <th rowspan="2" style="width:7%">NIS</th>
                    <th rowspan="2" style="width:20%;">NAMA LENGKAP</th>
                    <th rowspan="2" style="width:3%">LP</th>
                    <th rowspan="2" style="width:16%;">TEMPAT TGL LAHIR</th>
                    <th colspan="2" style="width:24%">ORANG TUA/WALI</th>
                    <th colspan="2" style="width:14%">KELUAR</th>
                    <th rowspan="2" style="width:13%;">KETERANGAN</th>
                </tr>
                <tr>
                    <th style="width:14%;">NAMA</th>
                    <th style="width:10%;">PEKERJAAN</th>
                    <th style="width:7%;">TANGGAL</th>
                    <th style="width:7%;">KELAS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mutasiKeluar as $keluar)
                @php
                $vault = $keluar->student->vault ?? null;
                $guardian = $keluar->student->guardians->first() ?? null;

                $tempatLahir = $vault->pob_encrypted ?? '-';
                $tglLahir = $vault->dob_encrypted ? \Carbon\Carbon::parse($vault->dob_encrypted)->format('d-m-Y') : '-';
                $namaWali = $guardian->name ?? '-';

                // FIX: occupation di-cast ke enum Profession, bukan string —
                // ambil ->label() (atau ->value kalau enum-nya tidak punya label()) dulu.
                $pekerjaanWali = $guardian?->occupation?->label() ?? '-';

                // FIX: gender juga enum (Gender), sama-sama perlu ->label()/->value
                // sebelum dicetak, kalau tidak Blade akan gagal convert object ke string.
                $gender = $keluar->student->gender ?? '-';
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $keluar->student->nis ?? '-' }}</td>
                    <td>{{ strtoupper($keluar->student->name) }}</td>
                    <td>{{ $gender }}</td>
                    <td>{{ ucwords(strtolower($tempatLahir)) }}, {{ $tglLahir }}</td>
                    <td>{{ strtoupper($namaWali) }}</td>
                    <td>{{ ucwords(strtolower($pekerjaanWali)) }}</td>
                    <td>{{ \Carbon\Carbon::parse($keluar->mutation_date)->format('d-m-Y') }}</td>
                    <td>{{ $keluar->classGroup->name ?? '-' }}</td>
                    <td>
                        {{ $keluar->status->label() }}
                        @if($keluar->destination_school)
                        <br>Ke: {{ $keluar->destination_school }}
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">NIHIL</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </figure>

    <figure class="table-footer">
        <table>
            <tbody>
                <tr>
                    <td>Mengetahui,
                        <br>Kepala Sekolah
                        <br><br><br><br> {{ $sekolah->kepalaSekolah->nama ?? '' }}
                        <br>NIP {{ $sekolah->kepalaSekolah->nip ?? '' }}
                    </td>
                    <td>
                        <br>Waka Kesiswaan
                        <br><br><br><br>{{ $sekolah->kesiswaan->nama ?? '' }}
                        <br>NIP {{ $sekolah->kesiswaan->nip ?? '' }}
                    </td>
                    <td>Curup, {{ $tgl_validasi }}
                        <br>Koordinator Tata Usaha
                        <br><br><br><br>{{ $sekolah->kaTu->nama ?? '' }}
                        <br>NIP {{ $sekolah->kaTu->nip ?? '' }}
                    </td>
                </tr>
            </tbody>
        </table>
    </figure>
</body>

</html>