<?php

namespace App\Exports;

use App\Enums\Student\Gender;
use App\Enums\Student\FamilyRelation;
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

// Tambahkan extends DefaultValueBinder dan implements WithCustomValueBinder
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
            'No',
            'Nama',
            'NISN',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'No HP Siswa',
            'Nama Ayah',
            'Nama Ibu',
            'No HP Ortu',
            'Kelas',
            'Jurusan',
            'Rombel',
        ];
    }

    public function map($student): array
    {
        static $no = 0;
        $no++;

        $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];

        $activeGroup = $student->activeClassGroup instanceof Collection
            ? $student->activeClassGroup->first()
            : $student->activeClassGroup;

        $gender = $student->gender instanceof Gender
            ? $student->gender->label()
            : (Gender::tryFrom($student->gender)?->label() ?? '-');

        $ayah = $student->guardians?->first(function ($g) {
            // Cek apakah data berupa Enum atau string biasa
            $rel = $g->relationship instanceof FamilyRelation
                ? $g->relationship->value
                : strtolower(trim($g->relationship));

            return in_array($rel, ['father', 'ayah']);
        });

        $ibu = $student->guardians?->first(function ($g) {
            $rel = $g->relationship instanceof FamilyRelation
                ? $g->relationship->value
                : strtolower(trim($g->relationship));

            return in_array($rel, ['mother', 'ibu']);
        });

        $hpOrtu = $ayah?->vault?->phone_number_encrypted
            ?? $ibu?->vault?->phone_number_encrypted
            ?? '-';

        return [
            $no,
            $student->name,
            $student->vault->nisn_encrypted ?? '-',
            $student->vault->nik_encrypted ?? '-',
            $student->vault->pob_encrypted ?? '-',
            $student->vault->dob_encrypted ?? '-',
            $gender,
            $student->vault->phone_number_encrypted ?? '-',
            $ayah->name ?? '-',
            $ibu->name ?? '-',
            $hpOrtu,
            $activeGroup ? ($grades[$activeGroup->grade_level] ?? $activeGroup->grade_level) : '-',
            $student->concentration->name ?? '-',
            $activeGroup->name ?? ($activeGroup->group_number ?? '-'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Memaksa kolom tertentu diekspor murni sebagai teks (String) 
     * untuk mencegah NIK dan No HP menjadi format Scientific (E+12)
     */
    public function bindValue(Cell $cell, $value)
    {
        // Kolom C(NISN), D(NIK), H(HP Siswa), K(HP Ortu)
        $stringColumns = ['C', 'D', 'H', 'K'];

        if (in_array($cell->getColumn(), $stringColumns) && $value !== '-') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Gunakan pemetaan default untuk kolom selain di atas
        return parent::bindValue($cell, $value);
    }
}
