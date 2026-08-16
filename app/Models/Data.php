<?php

namespace App\Models;

use App\Models\CoreConcentration;
use App\Models\GradeHistory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Data extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'staff_data';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

    /**
     * Opsi 1: Format Normal (Untuk tampilan web/UI standar)
     * Contoh: Budi Santoso, S.Pd., M.Kom.
     */
    public function getNameWithTitleAttribute()
    {
        $front = $this->front_title ? $this->front_title . ' ' : '';
        $back = $this->back_title ? ', ' . $this->back_title : '';

        return $front . $this->name . $back;
    }

    /**
     * Opsi 2: Format Nama Kapital (Khusus untuk cetak PDF/Laporan resmi)
     * Contoh: BUDI SANTOSO, S.Pd., M.Kom.
     */
    public function getNameCapitalWithTitleAttribute()
    {
        $front = $this->front_title ? $this->front_title . ' ' : '';
        $back = $this->back_title ? ', ' . $this->back_title : '';

        return $front . strtoupper($this->name) . $back;
    }

    /**
     * Label pangkat & golongan TERKINI, format siap pakai untuk tanda tangan surat.
     * Contoh: "Pembina Utama Muda (IV/c)". Null kalau belum ada histori golongan sama sekali.
     *
     * Catatan performa: mengakses ini tanpa eager load akan memicu query per pemanggilan.
     * Saat dipakai untuk banyak staf sekaligus, eager load dengan:
     * ->with('currentGrade.grade')
     */
    public function getCurrentGradeLabelAttribute(): ?string
    {
        $grade = $this->currentGrade?->grade;

        if (! $grade) {
            return null;
        }

        return "{$grade->grade_name} ({$grade->grade_code})";
    }

    // Relasi ke tabel Vault (1-to-1)
    public function vault()
    {
        return $this->hasOne(DataVault::class, 'staff_id');
    }

    // Relasi ke tabel Konsentrasi / Jurusan yang dipegang staf ini
    public function authorizedConcentrations()
    {
        return $this->belongsToMany(
            CoreConcentration::class,
            'staff_concentration_authorizations',
            'staff_id',
            'concentration_id'
        );
    }

    public function personnelType()
    {
        // Sesuaikan 'personel_type_id' dengan nama kolom foreign key yang ada di tabel staff_data
        return $this->belongsTo(PersonnelType::class, 'personnel_id');
    }

    /**
     * Seluruh riwayat golongan/pangkat staf ini (tiap kali ada SK kenaikan pangkat).
     */
    public function gradeHistories(): HasMany
    {
        return $this->hasMany(GradeHistory::class, 'staff_id');
    }

    /**
     * Golongan/pangkat yang berlaku SAAT INI, diambil dari baris histori dengan
     * effective_date (TMT) paling akhir. Dipakai untuk keperluan cetak surat,
     * bukan status_effective_date di staff_data (itu untuk status kepegawaian, beda konsep).
     */
    public function currentGrade(): HasOne
    {
        return $this->hasOne(GradeHistory::class, 'staff_id')->latestOfMany('effective_date');
    }
}
