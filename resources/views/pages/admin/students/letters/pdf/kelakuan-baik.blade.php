@php
$genderEnum = $student->gender;
$genderLabel = $genderEnum
? (method_exists($genderEnum, 'label') ? $genderEnum->label() : ($genderEnum->value === 'L' ? 'Laki-laki' : 'Perempuan'))
: '-';

$religionEnum = $student->vault?->religion;
$religionLabel = $religionEnum
? (method_exists($religionEnum, 'label') ? $religionEnum->label() : $religionEnum->value)
: '-';

$tempatLahir = $student->vault?->pob_encrypted ?? '-';
$tanggalLahir = $student->vault?->dob_encrypted
? \Carbon\Carbon::parse($student->vault->dob_encrypted)->translatedFormat('d F Y')
: '-';
$nisn = $student->vault?->nisn_encrypted ?? '-';

$namaRombel = $classGroup->name ?? '-';
$namaJurusan = $classGroup?->concentration?->name ?? '-';
$tahunAjaran = $classGroup?->semester?->academicYear?->name ?? '-';
@endphp

<x-pdf.document title="Surat Keterangan Berkelakuan Baik - {{ $school->name }}">
    <x-pdf.letterhead :school="$school" letter-title="SURAT KETERANGAN BERKELAKUAN BAIK" :letter-number="$letterNumber" />

    <div class="content">
        <p>Yang bertanda tangan di bawah ini Kepala Sekolah {{ $school->name }}, menerangkan bahwa :</p>

        <table class="table-content">
            <tr>
                <td style="width: 30%;">Nama</td>
                <td style="width: 3%;">:</td>
                <td class="nama"><strong>{{ $student->name }}</strong></td>
            </tr>
            <tr>
                <td>NIS / NISN</td>
                <td>:</td>
                <td>{{ $student->nis }} / {{ $nisn }}</td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td>{{ $tempatLahir }}, {{ $tanggalLahir }}</td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td>{{ $genderLabel }}</td>
            </tr>
            <tr>
                <td>Agama</td>
                <td>:</td>
                <td>{{ $religionLabel }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $namaRombel }}</td>
            </tr>
            <tr>
                <td>Konsentrasi Keahlian</td>
                <td>:</td>
                <td>{{ $namaJurusan }}</td>
            </tr>
            <tr>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td>{{ $tahunAjaran }}</td>
            </tr>
        </table>

        <p>
            Adalah benar siswa yang bersangkutan merupakan peserta didik <strong>{{ $school->name }}.</strong>
            Berdasarkan catatan tata tertib sekolah dan penilaian dari guru maupun pihak sekolah, hingga saat surat ini diterbitkan, yang bersangkutan <strong>berkelakuan baik, tidak pernah terlibat pelanggaran tata tertib, dan menjaga sikap sesuai norma pendidikan.</strong>
        </p>
        <p>
            Demikian surat keterangan ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <x-pdf.signature :school="$school" :signer="$school->headmaster" signer-role="Kepala Sekolah" :date="$letterDate" />
</x-pdf.document>