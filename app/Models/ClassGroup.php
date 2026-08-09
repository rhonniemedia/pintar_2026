<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Student\MutationStatus;
use App\Enums\Student\StudentStatus;

class ClassGroup extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'acd_class_groups';

    protected $guarded = ['id'];

    // NOTE: Ditambahkan karena kolom di migration adalah `concentration_id`
    // (bukan `major_id`), sehingga relasi ini yang dipakai oleh fitur Rombongan Belajar.
    public function concentration(): BelongsTo
    {
        return $this->belongsTo(CoreConcentration::class);
    }

    // Di dalam file App\Models\ClassGroup.php
    public function homeroomTeacher()
    {
        // Pastikan ini mengarah ke model Data, bukan User
        return $this->belongsTo(Data::class, 'homeroom_teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'acd_class_group_students')
            // Hapus 'status', sesuaikan dengan struktur baru jika perlu
            ->withPivot('entry_date', 'exit_date', 'exit_reason', 'mutation_id')
            ->withTimestamps();
    }

    /**
     * Siswa yang statusnya benar-benar masih anggota rombel ini SELAGI
     * SEMESTER/TAHUN AJARAN INI MASIH AKTIF:
     * - data siswa (acd_students) berstatus 'active' ATAU 'graduated'
     * - baris pivot belum ditutup oleh perpindahan kelas (exit_reason)
     * - baris pivot belum ditutup oleh mutasi APAPUN, KECUALI mutasi
     *   kelulusan (GRADUATED) — siswa lulus tetap dihitung sebagai
     *   anggota rombel terakhirnya sampai semester benar-benar berganti.
     *
     * PENTING: sengaja TIDAK memfilter exit_date. Saat proses kenaikan kelas/kelulusan
     * (lihat ClassGroupPromotionController::promote()/graduate()), exit_date pada baris
     * lama diisi lebih dulu sebagai JADWAL pindah ke semester berikutnya — bukan berarti
     * siswa sudah keluar dari rombel ini SEKARANG. Semester aktif di sistem belum tentu
     * langsung berganti begitu proses kenaikan kelas dijalankan, jadi siswa masih resmi
     * jadi anggota rombel ini sampai semester benar-benar berganti. Kalau exit_date ikut
     * difilter, siswa akan langsung "hilang" dari tampilan rombel meski data aslinya
     * belum berubah — itu bug yang pernah terjadi sebelumnya.
     *
     * Yang membuat siswa benar-benar hilang dari rombel adalah mutasi non-kelulusan
     * (dropout, resign, pindah sekolah, dll.) yang mengisi mutation_id, atau
     * perpindahan kelas (exit_reason). Konsisten dengan Student::activeClassGroup()
     * dan ClassGroupPromotionController::activeCandidates().
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()
            // FIX: sebelumnya hanya mengizinkan status 'active', sehingga siswa yang
            // sudah lulus (status cache berubah jadi 'graduated' via
            // MutationStatus::resultingStudentStatus()) ikut ter-exclude — padahal
            // selagi tahun ajaran masih berjalan, siswa graduated tetap harus dihitung.
            ->whereIn('acd_students.status', [
                StudentStatus::ACTIVE->value,
                StudentStatus::GRADUATED->value,
            ])

            // FIX: sebelumnya pakai wherePivotNull('exit_date'), padahal exit_date
            // diisi lebih dulu sebagai JADWAL pindah saat proses kenaikan kelas/
            // kelulusan dijalankan (lihat komentar di atas method ini) — sehingga
            // siswa yang sebenarnya masih aktif ikut ter-exclude dan laporan
            // menampilkan 0. Logika yang benar (konsisten dengan
            // Student::activeClassGroup()) adalah cek exit_reason + mutation_id,
            // bukan exit_date.
            ->whereNull('acd_class_group_students.exit_reason')
            ->where(function ($q) {
                $q->whereNull('acd_class_group_students.mutation_id')
                    ->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')
                            ->from('acd_student_mutations')
                            ->whereColumn('acd_student_mutations.id', 'acd_class_group_students.mutation_id')
                            ->where('acd_student_mutations.status', MutationStatus::GRADUATED->value);
                    });
            });
    }

    public function classGroupTeachers(): HasMany
    {
        return $this->hasMany(ClassGroupTeacher::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(StudentMutation::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(CoreSemester::class, 'semester_id');
    }
}
