@props(['title' => 'Dokumen'])
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        /* Mengatur margin untuk halaman */
        @page {
            margin: 1.7cm;
        }

        .table-header {
            font-family: Arial, sans-serif;
            width: 100%;
            text-align: center;
        }

        .department {
            font-size: 1.3rem;
            margin: 0;
        }

        .logo-img {
            height: 90px;
        }

        .sub-department {
            font-size: 1.7rem;
            font-weight: bold;
            margin: 0;
        }

        .address-1,
        .address-2 {
            font-size: 0.75rem;
            margin: 0;
        }

        .table-title {
            font-family: Arial, sans-serif;
            font-size: 0.8rem;
            width: 100%;
            margin-top: 15px;
        }

        .table-title .title {
            font-weight: bold;
            text-align: center;
            font-size: 1.1rem;
            padding: 2px 0;
            line-height: 1;
        }

        .table-title .subtitle {
            font-weight: normal;
            text-align: center;
            font-size: 1rem;
            padding: 0;
            line-height: 1;
        }

        .content {
            font-family: Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.5rem;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            text-align: justify;
        }

        .table-content,
        .table-footer {
            font-family: Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.8rem;
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table-footer td {
            vertical-align: top;
        }

        .table-content th,
        .table-content td {
            border: none;
            padding-left: 0px;
            padding-right: 4px;
            text-align: justify;
            height: 1.1rem;
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
            padding-left: 30px;
            line-height: 1.4;
        }

        .table-content th,
        .table-content td {
            width: calc(100% / 10);
        }
    </style>
</head>

<body>
    {{ $slot }}
</body>

</html>