<?php

namespace App\Http\Controllers\Integration;

use App\Enums\Student\DistanceToSchool;
use App\Enums\Student\Education;
use App\Enums\Student\FamilyRelation;
use App\Enums\Student\Gender;
use App\Enums\Student\Income;
use App\Enums\Student\LivingStatus;
use App\Enums\Student\Profession;
use App\Enums\Student\Religion;
use App\Enums\Student\ResidenceType;
use App\Enums\Student\SpecialCondition;
use App\Enums\Student\StudentStatus;
use App\Enums\Student\Transportation;
use App\Http\Controllers\Controller;
use App\Models\CoreConcentration;
use App\Models\Guardian;
use App\Models\GuardianVault;
use App\Models\Student; // Sesuaikan dengan nama model Anda
use App\Models\StudentVault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpmbSyncController extends Controller
{
    /**
     * Mengambil ringkasan statistik dari API SPMB untuk ditampilkan di Modal
     */
    public function checkInfo()
    {
        $url = config('services.spmb.url');
        $token = config('services.spmb.token');

        try {
            // Menggunakan cache agar tidak membebani server SPMB jika tombol sering diklik
            $apiData = Cache::remember('spmb_api_info', 300, function () use ($url, $token) {
                $response = Http::withToken($token)->acceptJson()->get($url);

                if (!$response->successful()) {
                    throw new \Exception('Gagal menghubungi server SPMB.');
                }
                return $response->json();
            });

            // Ambil data statistik dari 'meta' response API Pintar
            $statistik = $apiData['meta']['statistik_jurusan'] ?? [];
            $totalData = $apiData['meta']['total_data'] ?? 0;

            return view('pages.admin.students.data.partials._sync-spmb-info', compact('statistik', 'totalData'));
        } catch (\Exception $e) {
            return response('<div class="p-4 text-center text-sm font-medium text-error bg-error/10 rounded-xl border border-error/20">Gagal memuat informasi API: ' . $e->getMessage() . '</div>');
        }
    }

    public function store(Request $request)
    {
        $url = config('services.spmb.url');
        $token = config('services.spmb.token');

        // 1. Tangkap jurusan yang sedang diproses oleh antrean frontend
        $targetJurusan = $request->input('jurusan');

        try {
            $response = Http::withToken($token)->acceptJson()->get($url);

            if ($response->successful()) {
                $dataSiswaUtuh = $response->json()['data'] ?? [];

                // 2. Filter data dari API SPMB HANYA untuk jurusan yang sedang di-request
                if (!empty($targetJurusan)) {
                    $dataSiswa = array_filter($dataSiswaUtuh, function ($siswa) use ($targetJurusan) {
                        return strtolower(trim($siswa['konsentrasi_keahlian'] ?? '')) === strtolower(trim($targetJurusan));
                    });
                } else {
                    $dataSiswa = $dataSiswaUtuh;
                }

                foreach ($dataSiswa as $siswa) {

                    $nisnRaw = trim($siswa['nisn']);
                    $nisnHash = hash('sha256', $nisnRaw);

                    $vaultMatch = StudentVault::where('nisn_hash', $nisnHash)->first();
                    $concentration = CoreConcentration::where('name', 'LIKE', '%' . trim(preg_replace('/\s+/', ' ', $siswa['konsentrasi_keahlian'] ?? '')) . '%')->first();

                    if (!$concentration) {
                        Log::warning("Gagal sinkronisasi: Jurusan '{$siswa['konsentrasi_keahlian']}' untuk siswa {$siswa['nama_lengkap']} tidak ditemukan di Master Data.");
                        continue;
                    }

                    if ($vaultMatch) {
                        $student = $vaultMatch->student;
                    } else {
                        $student = new Student();
                        $student->status = StudentStatus::ACTIVE->value;
                    }

                    // 1. DATA INDUK
                    $student->name = $siswa['nama_lengkap'];
                    $student->nick_name = $siswa['nama_panggilan'] ?? null;
                    $student->gender = $this->mapGender($siswa['jk'] ?? '');
                    $student->child_order = $siswa['anak_ke'] ?? null;
                    $student->number_of_siblings = $siswa['jumlah_saudara'] ?? null;
                    $student->blood_type = $siswa['golongan_darah'] ?? null;

                    $student->entry_date = $siswa['tanggal_daftar_ulang'] ?? now();
                    $student->registration_type = 'new';
                    $student->entry_grade_level = '10';
                    $student->concentration_id = $concentration->id;

                    $student->residence_type = $this->mapResidence($siswa['jenis_tinggal'] ?? '');
                    $student->transportation = $this->mapTransportation($siswa['alat_transportasi'] ?? '');
                    $student->distance_to_school = $this->mapDistance($siswa['jarak_ke_sekolah'] ?? '');

                    $student->previous_school = $siswa['asal_sekolah'] ?? null;
                    $student->previous_school_npsn = $siswa['npsn_asal_sekolah'] ?? null;
                    $student->previous_school_status = $siswa['status_asal_sekolah'] ?? null;
                    $student->previous_school_city = $siswa['kota_asal_sekolah'] ?? null;
                    $student->previous_school_province = $siswa['provinsi_asal_sekolah'] ?? null;
                    $student->graduation_certificate_number = $siswa['no_seri_ijazah'] ?? null;
                    $student->graduation_year = $siswa['tahun_lulus'] ?? null;

                    $student->is_special_condition = ($siswa['berkebutuhan_khusus'] === 'no' || $siswa['berkebutuhan_khusus'] === 'tidak') ? 'no' : 'yes';
                    $student->special_condition_type = $this->mapSpecialNeeds($siswa['berkebutuhan_khusus'] ?? '');
                    $student->condition_description = $siswa['deskripsi_kebutuhan'] ?? null;
                    $student->height = $siswa['tinggi_badan'] ?? null;
                    $student->weight = $siswa['berat_badan'] ?? null;
                    $student->medical_history = $siswa['riwayat_penyakit'] ?? null;

                    $student->interest_art = $siswa['minat_seni'] ?? null;
                    $student->interest_sport = $siswa['minat_olahraga'] ?? null;
                    $student->interest_organization = $siswa['minat_organisasi'] ?? null;
                    $student->extracurricular_choice = $siswa['pilihan_ekskul'] ?? null;
                    $student->fl2sn_category = $siswa['kategori_fl2sn'] ?? null;
                    $student->o2sn_category = $siswa['kategori_o2sn'] ?? null;
                    $student->photo = $siswa['foto_profil'] ?? null;

                    $student->save();

                    // 2. DATA VAULT
                    $vault = $student->vault ?: new StudentVault();
                    $vault->student_id = $student->id;

                    $vault->nisn_encrypted = $nisnRaw;
                    $vault->nisn_hash = $nisnHash;

                    if (!empty($siswa['nik'])) {
                        $vault->nik_encrypted = trim($siswa['nik']);
                        $vault->nik_hash = hash('sha256', trim($siswa['nik']));
                    }

                    $vault->pob_encrypted = $siswa['tempat_lahir'] ?? null;

                    if (!empty($siswa['tanggal_lahir'])) {
                        $vault->dob_encrypted = trim($siswa['tanggal_lahir']);
                        $vault->dob_hash = hash('sha256', trim($siswa['tanggal_lahir']));
                    }

                    if (!empty($siswa['agama'])) {
                        $agamaMapped = $this->mapReligion($siswa['agama']);
                        $vault->religion_encrypted = $agamaMapped;
                        $vault->religion_hash = hash('sha256', strtolower($agamaMapped));
                    }

                    if (!empty($siswa['email'])) {
                        $vault->email_encrypted = trim($siswa['email']);
                        $vault->email_hash = hash('sha256', strtolower(trim($siswa['email'])));
                    }
                    if (!empty($siswa['no_hp_siswa'])) {
                        $vault->phone_number_encrypted = trim($siswa['no_hp_siswa']);
                        $vault->phone_number_hash = hash('sha256', trim($siswa['no_hp_siswa']));
                    }

                    $vault->address_encrypted = $siswa['alamat_siswa'] ?? null;
                    $vault->rt_encrypted = $siswa['rt'] ?? null;
                    $vault->rw_encrypted = $siswa['rw'] ?? null;
                    $vault->village_encrypted = $siswa['desa_kelurahan'] ?? null;

                    if (!empty($siswa['kecamatan'])) {
                        $vault->district_encrypted = trim($siswa['kecamatan']);
                        $vault->district_hash = hash('sha256', strtolower(trim($siswa['kecamatan'])));
                    }

                    $vault->regency_encrypted = $siswa['kabupaten_kota'] ?? null;
                    $vault->province_encrypted = $siswa['provinsi'] ?? null;
                    $vault->postal_code_encrypted = $siswa['kode_pos'] ?? null;

                    $vault->save();

                    // 3. DATA ORANG TUA
                    if (!empty($siswa['orang_tua']) && is_array($siswa['orang_tua'])) {
                        foreach ($siswa['orang_tua'] as $ortu) {
                            $relation = $this->mapFamilyRelation($ortu['status_hubungan'] ?? '');
                            $guardian = Guardian::firstOrNew([
                                'student_id' => $student->id,
                                'relationship' => $relation
                            ]);

                            $guardian->name = $ortu['nama'];
                            $guardian->living_status = $this->mapLivingStatus($ortu['status_hidup'] ?? '');
                            $guardian->birth_year = $ortu['tahun_lahir'] ?? null;
                            $guardian->occupation = $this->mapProfession($ortu['pekerjaan'] ?? '');
                            $guardian->education = $this->mapEducation($ortu['pendidikan'] ?? '');
                            $guardian->income_range = $this->mapIncome($ortu['penghasilan'] ?? '');

                            $guardian->save();

                            $guardianVault = $guardian->vault ?: new GuardianVault();
                            $guardianVault->guardian_id = $guardian->id;

                            if (!empty($ortu['nik'])) {
                                $guardianVault->nik_encrypted = trim($ortu['nik']);
                                $guardianVault->nik_hash = hash('sha256', trim($ortu['nik']));
                            }
                            if (!empty($ortu['no_hp'])) {
                                $guardianVault->phone_number_encrypted = trim($ortu['no_hp']);
                                $guardianVault->phone_number_hash = hash('sha256', trim($ortu['no_hp']));
                            }
                            $guardianVault->address_encrypted = $ortu['alamat'] ?? null;

                            $guardianVault->save();
                        }
                    }
                }

                Cache::forget('spmb_verified_students');

                // 3. Menghapus respons SweetAlert dan mengubahnya menjadi JSON
                return response()->json([
                    'status' => 'success',
                    'message' => "Data jurusan {$targetJurusan} berhasil disinkronkan."
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung dengan server SPMB.'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error Simpan API SPMB: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat sinkronisasi data.'
            ], 500);
        }
    }

    /**
     * ======================================================================
     * FUNGSI BANTUAN PEMETAAN (MAPPING) DATA API KE ENUM
     * ======================================================================
     */

    // --- ENUM SISWA ---

    private function mapGender(string $value): string
    {
        return match (strtoupper($value)) {
            'L' => Gender::LAKI_LAKI->value,
            'P' => Gender::PEREMPUAN->value,
            default => Gender::LAKI_LAKI->value,
        };
    }

    private function mapReligion(string $value): string
    {
        return match (strtolower($value)) {
            'islam' => Religion::ISLAM->value,
            'kristen' => Religion::KRISTEN->value,
            'katolik' => Religion::KATOLIK->value,
            'hindu' => Religion::HINDU->value,
            'buddha' => Religion::BUDDHA->value,
            'konghucu' => Religion::KONGHUCU->value,
            default => Religion::LAINNYA->value,
        };
    }

    private function mapResidence(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'orang') && str_contains($val, 'tua')) return ResidenceType::ORANG_TUA->value;
        if (str_contains($val, 'wali')) return ResidenceType::WALI->value;
        if (str_contains($val, 'kos')) return ResidenceType::KOS->value;
        if (str_contains($val, 'asrama')) return ResidenceType::ASRAMA->value;
        return ResidenceType::LAINNYA->value;
    }

    private function mapTransportation(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'jalan')) return Transportation::JALAN_KAKI->value;
        if (str_contains($val, 'sepeda')) return Transportation::SEPEDA->value;
        if (str_contains($val, 'pribadi')) return Transportation::KENDARAAN_PRIBADI->value;
        if (str_contains($val, 'umum')) return Transportation::KENDARAAN_UMUM->value;
        if (str_contains($val, 'antar')) return Transportation::ANTAR_JEMPUT->value;
        return Transportation::LAINNYA->value;
    }

    private function mapDistance(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'kurang')) return DistanceToSchool::KURANG_1_KM->value;
        if (str_contains($val, '1') && str_contains($val, '3')) return DistanceToSchool::ANTARA_1_3_KM->value;
        if (str_contains($val, '3') && str_contains($val, '5')) return DistanceToSchool::ANTARA_3_5_KM->value;
        if (str_contains($val, '5') && str_contains($val, '10')) return DistanceToSchool::ANTARA_5_10_KM->value;
        if (str_contains($val, 'lebih')) return DistanceToSchool::LEBIH_10_KM->value;

        return DistanceToSchool::ANTARA_3_5_KM->value;
    }

    private function mapSpecialNeeds(string $value): string
    {
        return match (strtolower($value)) {
            'no', 'tidak', 'tidak ada' => SpecialCondition::TIDAK_ADA->value,
            default => SpecialCondition::LAINNYA->value,
        };
    }

    // --- ENUM ORANG TUA ---

    private function mapFamilyRelation(string $value): string
    {
        return match (strtolower($value)) {
            'ayah' => FamilyRelation::AYAH->value,
            'ibu' => FamilyRelation::IBU->value,
            default => FamilyRelation::WALI->value,
        };
    }

    private function mapLivingStatus(string $value): string
    {
        return match (strtolower($value)) {
            'alive', 'hidup' => LivingStatus::ALIVE->value,
            'deceased', 'meninggal' => LivingStatus::DECEASED->value,
            default => LivingStatus::ALIVE->value,
        };
    }

    private function mapProfession(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'petani')) return Profession::PETANI->value;
        if (str_contains($val, 'ibu rumah tangga') || str_contains($val, 'irt')) return Profession::IBU_RUMAH_TANGGA->value;
        if (str_contains($val, 'nelayan')) return Profession::NELAYAN->value;
        if (str_contains($val, 'pns')) return Profession::PNS->value;
        if (str_contains($val, 'tni')) return Profession::TNI->value;
        if (str_contains($val, 'polri')) return Profession::POLRI->value;
        if (str_contains($val, 'wiraswasta') || str_contains($val, 'wirausaha') || str_contains($val, 'pedagang')) return Profession::WIRASWASTA->value;
        if (str_contains($val, 'buruh')) return Profession::BURUH->value;
        if (str_contains($val, 'karyawan swasta')) return Profession::KARYAWAN_SWASTA->value;
        if (str_contains($val, 'tidak bekerja')) return Profession::TIDAK_BEKERJA->value;

        return Profession::LAINNYA->value;
    }

    private function mapEducation(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'sd')) return Education::SD->value;
        if (str_contains($val, 'smp') || str_contains($val, 'mts')) return Education::SMP->value;
        if (str_contains($val, 'sma') || str_contains($val, 'smk') || str_contains($val, 'ma')) return Education::SMA->value;
        if (str_contains($val, 'd1') || str_contains($val, 'diploma 1')) return Education::DIPLOMA_1->value;
        if (str_contains($val, 'd2') || str_contains($val, 'diploma 2')) return Education::DIPLOMA_2->value;
        if (str_contains($val, 'd3') || str_contains($val, 'diploma 3')) return Education::DIPLOMA_3->value;
        if (str_contains($val, 's1') || str_contains($val, 'd4') || str_contains($val, 'sarjana')) return Education::SARJANA->value;
        if (str_contains($val, 's2') || str_contains($val, 'magister')) return Education::MAGISTER->value;
        if (str_contains($val, 's3') || str_contains($val, 'doktor')) return Education::DOKTOR->value;

        return Education::TIDAK_SEKOLAH->value;
    }

    private function mapIncome(string $value): string
    {
        $val = strtolower($value);
        if (str_contains($val, 'kurang')) return Income::KURANG_1_JT->value;
        if (str_contains($val, '1.000.000') && str_contains($val, '1.999.999')) return Income::RANGE_1_2_JT->value;
        if (str_contains($val, '2.000.000') && str_contains($val, '2.999.999')) return Income::RANGE_2_3_JT->value;
        if (str_contains($val, '3.000.000') && str_contains($val, '4.999.999')) return Income::RANGE_3_5_JT->value;
        if (str_contains($val, '5.000.000') && str_contains($val, '9.999.999')) return Income::RANGE_5_10_JT->value;
        if (str_contains($val, 'lebih') && str_contains($val, '10')) return Income::LEBIH_10_JT->value;

        return Income::TANPA_PENGHASILAN->value;
    }
}
