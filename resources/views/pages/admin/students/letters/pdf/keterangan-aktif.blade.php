@php
// Label jenis kelamin: defensif terhadap kemungkinan enum Gender belum/tidak punya method label().
$genderEnum = $student->gender;
$genderLabel = $genderEnum
? (method_exists($genderEnum, 'label') ? $genderEnum->label() : ($genderEnum->value === 'L' ? 'Laki-laki' : 'Perempuan'))
: '-';

$tempatLahir = $student->vault?->pob_encrypted ?? '-';
$tanggalLahir = $student->vault?->dob_encrypted
? \Carbon\Carbon::parse($student->vault->dob_encrypted)->translatedFormat('d F Y')
: '-';
$nisn = $student->vault?->nisn_encrypted ?? '-';

$namaRombel = $classGroup->name ?? '-';
$namaJurusan = $classGroup?->concentration?->name ?? '-';
$tahunAjaran = $classGroup?->semester?->academicYear?->name ?? '-';

// Data Kepala Sekolah (mengambil dari relasi headmaster yang ada di core_schools)
$namaKepalaSekolah = $school->headmaster?->name_with_title ?? '-';
$nipKepalaSekolah = $school->headmaster?->vault?->nip ?? '-';
$pangkatGolongan = $school->headmaster?->current_grade_label ?? '-';
@endphp

<x-pdf.document title="Surat Keterangan Aktif - {{ $school->name }}">
    <x-pdf.letterhead :school="$school" letter-title="SURAT KETERANGAN AKTIF" :letter-number="$letterNumber" />

    <div class="content">
        <p>Yang bertanda tangan di bawah ini:</p>

        <table class="table-content" style="margin-bottom: 10px;">
            <tr>
                <td style="width: 30%;">Nama</td>
                <td style="width: 3%;">:</td>
                <td><strong>{{ $namaKepalaSekolah }}</strong></td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $nipKepalaSekolah }}</td>
            </tr>
            <tr>
                <td>Pangkat/Gol.</td>
                <td>:</td>
                <td>{{ $pangkatGolongan }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>Kepala {{ $school->name }}</td>
            </tr>
        </table>

        <p>dengan ini menerangkan bahwa:</p>

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
                <td>Kelas</td>
                <td>:</td>
                <td>{{ $namaRombel }}</td>
            </tr>
            <tr>
                <td>Konsentrasi Keahlian</td>
                <td>:</td>
                <td>{{ $namaJurusan }}</td>
            </tr>
        </table>

        <p>
            Adalah benar yang bersangkutan saat ini <strong>masih terdaftar dan aktif sebagai peserta didik {{ $school->name }}</strong> pada Tahun Ajaran {{ $tahunAjaran }}.
        </p>
        <p>
            Demikian surat keterangan ini dikeluarkan untuk dapat dipergunakan sebagaimana mestinya.
        </p>
    </div>

    <x-pdf.signature :school="$school" :signer="$school->headmaster" signer-role="Kepala Sekolah" :date="$letterDate" />
</x-pdf.document>