<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * @param Collection $students Koleksi siswa yang SUDAH difilter oleh
     *                              controller (bukan query mentah), supaya
     *                              class ini tidak perlu tahu logic filter.
     */
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
            'Jenis Kelamin',
            'Kelas',
            'Jurusan',
            'Rombel',
        ];
    }

    public function map($student): array
    {
        static $no = 0;
        $no++;

        // TODO: sesuaikan pemetaan field di bawah ini dengan nama kolom/relasi
        // yang sebenarnya ada di model Student & relasinya (vault, concentration,
        // activeClassGroup) kalau berbeda dari asumsi berikut.
        $grades = ['10' => 'X', '11' => 'XI', '12' => 'XII', '13' => 'XIII'];

        // activeClassGroup di-eager-load sebagai relasi hasOne/hasMany yang
        // sudah di-scope ke semester aktif (lihat buildBaseQuery di controller).
        $activeGroup = $student->activeClassGroup instanceof Collection
            ? $student->activeClassGroup->first()
            : $student->activeClassGroup;

        return [
            $no,
            $student->name,
            $student->vault->nisn_encrypted ?? '-',
            $student->gender === 'L' ? 'Laki-laki' : 'Perempuan',
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
}
