<?php

namespace App\Exports;

use App\Enums\Student\DistanceToSchool;
use App\Enums\Student\Education;
use App\Enums\Student\FamilyRelation;
use App\Enums\Student\Gender;
use App\Enums\Student\Income;
use App\Enums\Student\LivingStatus;
use App\Enums\Student\Profession;
use App\Enums\Student\RegistrationType;
use App\Enums\Student\Religion;
use App\Enums\Student\ResidenceType;
use App\Enums\Student\SpecialCondition;
use App\Enums\Student\Transportation;
use App\Enums\Student\StudentStatus;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithCustomValueBinder
{
    public function __construct(private readonly Collection $students) {}

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            // --- Akademik & Kelas ---
            'No',
            'Status Siswa',
            'Tanggal Status',
            'Kelas Saat Ini',
            'Jurusan',
            'Rombel',

            // --- Identitas Inti & Vault ---
            'Nama Lengkap',
            'Nama Panggilan',
            'NIS',
            'NISN',
            'NIK',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Agama',
            'Golongan Darah',
            'Anak Ke',
            'Jumlah Saudara',

            // --- Kontak & Domisili ---
            'Email',
            'No HP Siswa',
            'Alamat',
            'RT',
            'RW',
            'Desa/Kelurahan',
            'Kecamatan',
            'Kabupaten/Kota',
            'Provinsi',
            'Kode Pos',
            'Jenis Tinggal',
            'Transportasi',
            'Jarak ke Sekolah',

            // --- Sejarah Penerimaan & Sekolah Asal ---
            'Kategori Masuk',
            'Kelas Masuk',
            'Tanggal Masuk',
            'Sekolah Asal',
            'NPSN Sekolah Asal',
            'Status Sekolah Asal',
            'Kota Sekolah Asal',
            'Provinsi Sekolah Asal',
            'No Ijazah',
            'Tahun Lulus',

            // --- Kesehatan & Kebutuhan Khusus ---
            'Tinggi Badan (cm)',
            'Berat Badan (kg)',
            'Riwayat Penyakit',
            'Berkebutuhan Khusus',
            'Jenis Kondisi Khusus',
            'Deskripsi Kondisi',
            'Ada Alergi Makanan',
            'Alergi Makanan',

            // --- Minat & Ekstrakurikuler ---
            'Minat Seni',
            'Minat Olahraga',
            'Minat Organisasi',
            'Pilihan Ekstrakurikuler',
            'Kategori FLS2N',
            'Kategori O2SN',

            // --- Rencana Karir & BKK ---
            'Rencana Setelah Lulus',
            'Minat Kerja',
            'Negara Tujuan',
            'Program Tujuan',
            'Kemampuan Bahasa Asing',
            'Bersedia Pelatihan Bahasa',
            'Siap Seleksi BKK',

            // --- Orang Tua: AYAH ---
            'Nama Ayah',
            'NIK Ayah',
            'Status Ayah',
            'Tahun Lahir Ayah',
            'Pendidikan Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'No HP Ayah',
            'Alamat Ayah',

            // --- Orang Tua: IBU ---
            'Nama Ibu',
            'NIK Ibu',
            'Status Ibu',
            'Tahun Lahir Ibu',
            'Pendidikan Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'No HP Ibu',
            'Alamat Ibu',

            // --- Orang Tua: WALI ---
            'Nama Wali',
            'NIK Wali',
            'Status Wali',
            'Tahun Lahir Wali',
            'Pendidikan Wali',
            'Pekerjaan Wali',
            'Penghasilan Wali',
            'No HP Wali',
            'Alamat Wali',
        ];
    }

    public function map($student): array
    {
        static $no = 0;
        $no++;

        $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];

        // Info Kelas Aktif
        $activeGroup = $student->activeClassGroup instanceof Collection
            ? $student->activeClassGroup->first()
            : $student->activeClassGroup;
        $kelasCurrent = $activeGroup ? ($grades[$activeGroup->grade_level] ?? $activeGroup->grade_level) : '-';
        $rombelCurrent = $activeGroup->name ?? ($activeGroup->group_number ?? '-');

        // Ekstraksi Enum dengan Helper Universal (Bersih & Aman dari Null)
        $studentStatus = $this->getEnumLabel($student->status, StudentStatus::class);
        $gender = $this->getEnumLabel($student->gender, Gender::class);
        $registrationType = $this->getEnumLabel($student->registration_type, RegistrationType::class);
        $residenceType = $this->getEnumLabel($student->residence_type, ResidenceType::class);
        $transportation = $this->getEnumLabel($student->transportation, Transportation::class);
        $distanceToSchool = $this->getEnumLabel($student->distance_to_school, DistanceToSchool::class);
        $specialCondition = $this->getEnumLabel($student->special_condition_type, SpecialCondition::class);
        $religion = $this->getEnumLabel($student->vault?->religion_encrypted, Religion::class);

        // Data Vault Inti
        $v = $student->vault;

        // Data Orang Tua
        $ayah = $this->getGuardianData($student, ['father', 'ayah']);
        $ibu = $this->getGuardianData($student, ['mother', 'ibu']);
        $wali = $this->getGuardianData($student, ['guardian', 'wali']);

        return [
            // --- Akademik & Kelas ---
            $no,
            $studentStatus,
            $student->status_date ?? '-',
            $kelasCurrent,
            $student->concentration->name ?? '-',
            $rombelCurrent,

            // --- Identitas Inti & Vault ---
            $student->name ?? '-',
            $student->nick_name ?? '-',
            $student->nis ?? '-',
            $v?->nisn_encrypted ?? '-',
            $v?->nik_encrypted ?? '-',
            $gender,
            $v?->pob_encrypted ?? '-',
            $v?->dob_encrypted ?? '-',
            $religion,
            $student->blood_type ?? '-',
            $student->child_order ?? '-',
            $student->number_of_siblings ?? '-',

            // --- Kontak & Domisili ---
            $v?->email_encrypted ?? '-',
            $this->formatPhoneNumber($v?->phone_number_encrypted), // Memformat HP Siswa
            $v?->address_encrypted ?? '-',
            $v?->rt_encrypted ?? '-',
            $v?->rw_encrypted ?? '-',
            $v?->village_encrypted ?? '-',
            $v?->district_encrypted ?? '-',
            $v?->regency_encrypted ?? '-',
            $v?->province_encrypted ?? '-',
            $v?->postal_code_encrypted ?? '-',
            $residenceType,
            $transportation,
            $distanceToSchool,

            // --- Sejarah Penerimaan & Sekolah Asal ---
            $registrationType,
            $grades[$student->entry_grade_level] ?? $student->entry_grade_level ?? '-',
            $student->entry_date ?? '-',
            $student->previous_school ?? '-',
            $student->previous_school_npsn ?? '-',
            $student->previous_school_status ?? '-',
            $student->previous_school_city ?? '-',
            $student->previous_school_province ?? '-',
            $student->graduation_certificate_number ?? '-',
            $student->graduation_year ?? '-',

            // --- Kesehatan & Kebutuhan Khusus ---
            $student->height ?? '-',
            $student->weight ?? '-',
            $student->medical_history ?? '-',
            $student->is_special_condition === 'yes' ? 'Ya' : 'Tidak',
            $specialCondition,
            $student->condition_description ?? '-',
            $student->has_food_allergy === 'yes' ? 'Ya' : 'Tidak',
            $student->food_allergy ?? '-',

            // --- Minat & Ekstrakurikuler ---
            $student->interest_art ?? '-',
            $student->interest_sport ?? '-',
            $student->interest_organization ?? '-',
            $student->extracurricular_choice ?? '-',
            $student->fl2sn_category ?? '-',
            $student->o2sn_category ?? '-',

            // --- Rencana Karir & BKK ---
            $student->post_graduation_plan ?? '-',
            $student->work_interest ?? '-',
            $student->target_country ?? '-',
            $student->target_program ?? '-',
            $student->foreign_language_skills ?? '-',
            $student->willing_to_language_train === 'yes' ? 'Ya' : ($student->willing_to_language_train === 'no' ? 'Tidak' : '-'),
            $student->ready_for_bkk_selection === 'yes' ? 'Ya' : ($student->ready_for_bkk_selection === 'no' ? 'Tidak' : '-'),

            // --- Orang Tua: AYAH ---
            $ayah['name'],
            $ayah['nik'],
            $ayah['status'],
            $ayah['birth_year'],
            $ayah['education'],
            $ayah['occupation'],
            $ayah['income'],
            $ayah['phone'],
            $ayah['address'],

            // --- Orang Tua: IBU ---
            $ibu['name'],
            $ibu['nik'],
            $ibu['status'],
            $ibu['birth_year'],
            $ibu['education'],
            $ibu['occupation'],
            $ibu['income'],
            $ibu['phone'],
            $ibu['address'],

            // --- Orang Tua: WALI ---
            $wali['name'],
            $wali['nik'],
            $wali['status'],
            $wali['birth_year'],
            $wali['education'],
            $wali['occupation'],
            $wali['income'],
            $wali['phone'],
            $wali['address'],
        ];
    }

    /**
     * Helper untuk mengambil dan memformat data satu jenis wali/orang tua
     */
    private function getGuardianData($student, array $relationKeys): array
    {
        $guardian = $student->guardians?->first(function ($g) use ($relationKeys) {
            $rel = $g->relationship instanceof FamilyRelation
                ? $g->relationship->value
                : strtolower(trim($g->relationship));
            return in_array($rel, $relationKeys);
        });

        return [
            'name'       => $guardian->name ?? '-',
            'nik'        => $guardian->vault?->nik_encrypted ?? '-',
            'status'     => $this->getEnumLabel($guardian?->living_status, LivingStatus::class),
            'birth_year' => $guardian->birth_year ?? '-',
            'education'  => $this->getEnumLabel($guardian?->education, Education::class),
            'occupation' => $this->getEnumLabel($guardian?->occupation, Profession::class),
            'income'     => $this->getEnumLabel($guardian?->income_range, Income::class),
            'phone'      => $this->formatPhoneNumber($guardian?->vault?->phone_number_encrypted), // Memformat HP Ortu
            'address'    => $guardian->vault?->address_encrypted ?? '-',
        ];
    }

    /**
     * Helper universal untuk mengekstrak label Enum dengan aman.
     */
    private function getEnumLabel($value, string $enumClass): string
    {
        if ($value === null) {
            return '-';
        }

        if ($value instanceof $enumClass) {
            return $value->label();
        }

        if (is_string($value) || is_int($value)) {
            return $enumClass::tryFrom($value)?->label() ?? (string) $value;
        }

        return '-';
    }

    /**
     * Helper untuk memastikan format nomor HP selalu berawalan 08
     */
    private function formatPhoneNumber(?string $phoneNumber): string
    {
        if (empty($phoneNumber) || $phoneNumber === '-') {
            return '-';
        }

        // Hapus karakter apa pun yang bukan angka (seperti '+', '-', spasi)
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Jika diawali dengan '62', ubah menjadi '0'
        if (str_starts_with($cleaned, '62')) {
            $cleaned = '0' . substr($cleaned, 2);
        }
        // Pastikan nomor yang diawali '8' (tanpa 0 atau 62 di depannya) tetap ditambahkan '0'
        elseif (str_starts_with($cleaned, '8')) {
            $cleaned = '0' . $cleaned;
        }

        return $cleaned;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2EFDA']]],
        ];
    }

    /**
     * Memaksa kolom tertentu diekspor murni sebagai teks (String)
     * Menggunakan pencocokan abjad kolom untuk melindungi angka dari Excel.
     */
    public function bindValue(Cell $cell, $value)
    {
        // Daftar abjad kolom yang berisi angka riskan
        $stringColumns = [
            'I',  // NIS
            'J',  // NISN
            'K',  // NIK
            'T',  // No HP Siswa
            'AB', // Kode Pos
            'AJ', // NPSN Sekolah Asal
            'AN', // No Ijazah
            'BL', // NIK Ayah
            'BR', // No HP Ayah
            'BU', // NIK Ibu
            'CA', // No HP Ibu
            'CD', // NIK Wali
            'CJ', // No HP Wali
        ];

        if (in_array($cell->getColumn(), $stringColumns) && $value !== '-') {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
