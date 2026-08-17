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

// Data wali: nama & pekerjaan defensif terhadap enum yang belum tentu punya label().
$guardianName = $guardian?->name ?? '-';

$occupationEnum = $guardian?->occupation;
$guardianOccupation = $occupationEnum
? (method_exists($occupationEnum, 'label') ? $occupationEnum->label() : $occupationEnum->value)
: '-';

// Alamat wali: fallback ke alamat siswa kalau wali tidak punya alamat sendiri
// (konsisten dengan catatan migration acd_guardians_vault).
$guardianAddress = $guardian?->vault?->address_encrypted ?? $student->vault?->address_encrypted ?? '-';
@endphp

<x-pdf.document title="Surat Keterangan Pindah Sekolah - {{ $school->name }}">
    <x-pdf.letterhead :school="$school" letter-title="SURAT KETERANGAN PINDAH SEKOLAH" :letter-number="$letterNumber" />

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

        <p>Sesuai dengan permohonan Orang Tua/Wali:</p>

        <table class="table-content">
            <tr>
                <td style="width: 30%;">Nama</td>
                <td style="width: 3%;">:</td>
                <td>{{ $guardianName }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $guardianAddress }}</td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td>:</td>
                <td>{{ $guardianOccupation }}</td>
            </tr>
        </table>

        <p>
            Peserta didik yang bersangkutan telah mengajukan permohonan pindah sekolah dari
            <strong>{{ $school->name }}</strong> ke <strong>{{ $mutation->destination_school ?? '-' }}</strong>
            atas permintaan orang tua. Dengan terbitnya surat keterangan ini, peserta didik tersebut
            secara resmi tidak lagi tercatat sebagai peserta didik {{ $school->name }} dan dengan demikian
            tidak dapat kembali menjadi peserta didik di sekolah ini.
        </p>
        <p>
            Demikian surat keterangan ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <x-pdf.signature :school="$school" :signer="$school->headmaster" signer-role="Kepala Sekolah" :date="$letterDate" />
</x-pdf.document>