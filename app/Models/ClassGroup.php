<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Siswa yang statusnya benar-benar masih aktif DI ROMBEL INI:
     * - baris pivot berstatus 'active'
     * - data siswa (acd_students) juga berstatus 'active'
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
     * Yang membuat siswa benar-benar hilang dari rombel hanya perubahan status pivot itu
     * sendiri (mis. jadi 'graduated' saat lulus), konsisten dengan Student::activeClassGroup()
     * dan ClassGroupPromotionController::activeCandidates().
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()
            // Pastikan string 'active' ini sesuai dengan value Enum StudentStatus Anda 
            // (jika Enum Anda menggunakan huruf besar, ubah menjadi 'ACTIVE' atau panggil Enum-nya langsung)
            ->where('acd_students.status', 'active')

            // Logika baru pengganti status pivot: belum ada tanggal keluar / belum dipindah / dimutasi
            ->wherePivotNull('exit_date');
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
