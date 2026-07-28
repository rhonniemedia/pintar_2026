<?php

namespace App\Models;

use App\Models\CoreConcentration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
