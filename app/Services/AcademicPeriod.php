<?php

namespace App\Services;

use App\Models\CoreSemester;
use Illuminate\Support\Facades\Session;

/**
 * Menentukan "semester yang sedang dilihat" (viewing context) di seluruh
 * halaman admin, terpisah dari status semester aktif di Data Master.
 *
 * - Kalau admin sudah memilih semester lewat dropdown topbar -> pakai itu
 *   (disimpan di session, berlaku lintas halaman selama sesi berjalan).
 * - Kalau belum pernah memilih -> fallback ke semester yang statusnya
 *   'active' di Data Master (perilaku default/lama, tidak berubah).
 *
 * PENTING: service ini HANYA untuk konteks tampilan/filter data (index,
 * export, pencarian, statcard, dsb). Proses yang sifatnya transaksional dan
 * memang harus terikat ke semester/tahun ajaran RESMI di Data Master
 * (misal generate NIS, kenaikan kelas, kelulusan) TIDAK boleh memakai
 * service ini -- tetap query CoreSemester::where('status', 'active')
 * langsung, supaya operasi tersebut tidak ikut "salah semester" gara-gara
 * admin sedang melihat-lihat semester lain.
 */
class AcademicPeriod
{
    private const SESSION_KEY = 'academic_period.semester_id';

    private ?CoreSemester $resolved = null;
    private bool $isResolved = false;

    /**
     * Semester yang sedang dilihat (pilihan user, atau fallback ke aktif).
     */
    public function current(): ?CoreSemester
    {
        if ($this->isResolved) {
            return $this->resolved;
        }

        $this->isResolved = true;

        $selectedId = Session::get(self::SESSION_KEY);

        if ($selectedId) {
            $semester = CoreSemester::find($selectedId);

            if ($semester) {
                return $this->resolved = $semester;
            }

            // Pilihan lama sudah tidak valid lagi (mis. semester dihapus) -> bersihkan.
            Session::forget(self::SESSION_KEY);
        }

        return $this->resolved = CoreSemester::where('status', 'active')->first();
    }

    public function currentId(): ?string
    {
        return $this->current()?->id;
    }

    /**
     * True kalau admin sedang melihat semester LAIN (bukan semester aktif
     * sesungguhnya di Data Master). Berguna untuk menampilkan
     * banner/indikator peringatan di UI supaya admin sadar sedang tidak
     * melihat data semester berjalan.
     */
    public function isOverridden(): bool
    {
        $current = $this->current();
        $active = CoreSemester::where('status', 'active')->first();

        return $current && $active && $current->id !== $active->id;
    }

    public function setSelected(string $semesterId): void
    {
        Session::put(self::SESSION_KEY, $semesterId);
        $this->resolved = null;
        $this->isResolved = false;
    }

    public function clearSelected(): void
    {
        Session::forget(self::SESSION_KEY);
        $this->resolved = null;
        $this->isResolved = false;
    }

    /**
     * Daftar semester untuk dropdown pemilih di topbar, urut dari terbaru.
     */
    public function options()
    {
        return CoreSemester::with('academicYear')
            // Ganti 'kode_semester' dengan nama kolom asli di tabel Anda
            // Jika ingin tetap pakai waktu pembuatan, biarkan 'created_at'
            ->orderByDesc('code')
            ->get();
    }
}
