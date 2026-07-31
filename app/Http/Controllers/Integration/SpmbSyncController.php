<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\Student; // Sesuaikan dengan nama model Anda
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpmbSyncController extends Controller
{
    /**
     * 1. Menampilkan Pratinjau Data (dengan Cache, Search & Filter)
     */
    public function preview(Request $request)
    {
        $url = config('services.spmb.url');
        $token = config('services.spmb.token');

        try {
            // CACHE: Simpan data dari SPMB selama 10 menit.
            $dataSiswaUtuh = Cache::remember('spmb_verified_students', 600, function () use ($url, $token) {
                $response = Http::withToken($token)->acceptJson()->get($url);

                // Lemparkan exception jika gagal, agar langsung ditangkap oleh blok catch di bawah
                if (!$response->successful()) {
                    throw new \Exception('Gagal mengambil data dari server SPMB. Status: ' . $response->status());
                }

                return $response->json()['data'] ?? [];
            });

            // Jadikan Collection
            $dataSiswa = collect($dataSiswaUtuh);

            // 2. Terapkan Pencarian (Search)
            $search = $request->query('search');
            if (!empty($search)) {
                $search = strtolower($search);
                $dataSiswa = $dataSiswa->filter(function ($item) use ($search) {
                    return str_contains(strtolower($item['nama_lengkap'] ?? ''), $search) ||
                        str_contains(strtolower($item['no_registrasi'] ?? ''), $search) ||
                        str_contains(strtolower($item['nisn'] ?? ''), $search);
                });
            }

            // 3. Terapkan Filter Jenis Kelamin
            $filterGender = $request->query('filter_gender');
            if (!empty($filterGender)) {
                $dataSiswa = $dataSiswa->where('jk', $filterGender);
            }

            // 4. Terapkan Filter Jurusan
            $filterConcentration = $request->query('filter_concentration');
            if (!empty($filterConcentration)) {
                $dataSiswa = $dataSiswa->filter(function ($item) use ($filterConcentration) {
                    return str_contains(strtolower($item['konsentrasi_keahlian'] ?? ''), strtolower($filterConcentration));
                });
            }

            // Daftar unik jurusan untuk dropdown filter (Ambil dari data utuh)
            $concentrationOptions = collect($dataSiswaUtuh)->pluck('konsentrasi_keahlian')->filter()->unique();

            // 5. Pagination Manual
            $page = $request->query('page', 1);
            $perPage = 10;
            $paginatedData = new LengthAwarePaginator(
                $dataSiswa->forPage($page, $perPage),
                $dataSiswa->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // 6. Cek apakah ini request dari HTMX
            if ($request->header('HX-Request')) {
                return view('pages.admin.integration.partials._table-spmb', compact('paginatedData'));
            }

            return view('pages.admin.integration.spmb-preview', compact('paginatedData', 'search', 'filterGender', 'filterConcentration', 'concentrationOptions'));
        } catch (\Exception $e) {
            Log::error('Error API SPMB (Preview): ' . $e->getMessage());
            return redirect()->route('admin.students.data.index')->with('error', $e->getMessage());
        }
    }

    /**
     * 2. Mengeksekusi Penyimpanan ke Database
     */
    public function store(Request $request)
    {
        $url = config('services.spmb.url');
        $token = config('services.spmb.token');

        try {
            // Tarik ulang data untuk memastikan keamanan dan kesegaran data
            $response = Http::withToken($token)->acceptJson()->get($url);

            if ($response->successful()) {
                $dataSiswa = $response->json()['data'] ?? [];
                $jumlahBerhasil = 0;

                foreach ($dataSiswa as $siswa) {
                    DataPelajar::updateOrCreate(
                        ['no_registrasi' => $siswa['no_registrasi']],
                        [
                            'nama_lengkap' => $siswa['nama_lengkap'],
                            'nisn' => $siswa['nisn'],
                            'nik' => $siswa['nik'],
                            'jk' => $siswa['jk'], // Disesuaikan dengan JSON SPMB
                            'tempat_lahir' => $siswa['tempat_lahir'],
                            'tanggal_lahir' => $siswa['tanggal_lahir'],
                            'agama' => $siswa['agama'],
                            'email' => $siswa['email'],
                            'no_hp_siswa' => $siswa['no_hp_siswa'],
                            'alamat_siswa' => $siswa['alamat_siswa'],
                            'asal_sekolah' => $siswa['asal_sekolah'],
                            'konsentrasi_keahlian' => $siswa['konsentrasi_keahlian'],
                        ]
                    );
                    $jumlahBerhasil++;
                }

                // Setelah berhasil, hapus cache agar preview selanjutnya mengambil data terbaru
                Cache::forget('spmb_verified_students');

                // Kembalikan ke halaman master data siswa
                return redirect()->route('admin.students.data.index')
                    ->with('success', "Sinkronisasi selesai! {$jumlahBerhasil} data siswa berhasil disimpan.");
            }

            return redirect()->back()->with('error', 'Gagal menyimpan data saat menghubungi SPMB.');
        } catch (\Exception $e) {
            Log::error('Error Simpan API SPMB: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data.');
        }
    }
}
