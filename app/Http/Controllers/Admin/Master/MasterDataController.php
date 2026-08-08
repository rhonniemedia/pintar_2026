<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\CoreAcademicYear;
use App\Models\CoreConcentration;
use App\Models\CoreSemester;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasterDataController extends Controller
{
    /* ============================================================
     * INDEX (Tab-based listing dengan HTMX)
     * ========================================================== */

    public function index(Request $request): View|string
    {
        $activeTab = $request->query('tab', 'academic-year');
        $search    = $request->query('search', '');

        [$data, $viewPartial] = match ($activeTab) {
            'academic-year' => [
                CoreAcademicYear::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orderBy('start_date', 'desc')
                    ->paginate(10),
                'pages.admin.master.partials._table-academic-year',
            ],
            'semester' => [
                CoreSemester::with('academicYear')
                    ->when($search, fn($q) => $q->where('code', 'like', "%{$search}%"))
                    ->orderBy('start_date', 'desc')
                    ->paginate(10),
                'pages.admin.master.partials._table-semester',
            ],
            'concentration' => [
                CoreConcentration::when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orderBy('name', 'asc')
                    ->paginate(10),
                'pages.admin.master.partials._table-concentration',
            ],
            default => abort(404),
        };

        if ($request->header('HX-Request')) {
            return view($viewPartial, compact('data'))->render();
        }

        return view('pages.admin.master.index', compact('activeTab', 'data', 'search', 'viewPartial'));
    }

    /* ============================================================
     * ACADEMIC YEAR
     * ========================================================== */

    public function createAcademicYear(): View
    {
        return view('pages.admin.master.partials._modal-academic-year');
    }

    public function storeAcademicYear(Request $request): View|Response
    {
        $validator = $this->academicYearValidator($request);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-academic-year',
                $validator
            );
        }

        DB::transaction(function () use ($request) {
            // Cari tahun ajaran yang sedang aktif saat ini
            $activeYear = CoreAcademicYear::where('status', 'active')->first();

            // Buat data tahun ajaran baru
            $newYear = CoreAcademicYear::create([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'status'     => $request->status ?? 'inactive',
            ]);

            // Isi next_id pada tahun ajaran yang aktif sebelumnya dengan ID yang baru dibuat
            if ($activeYear) {
                $activeYear->update(['next_id' => $newYear->id]);
            }

            // Jika user langsung membuat tahun ajaran ini sebagai 'Aktif', nonaktifkan yang lain
            if ($request->status === 'active') {
                CoreAcademicYear::where('id', '!=', $newYear->id)
                    ->update(['status' => 'inactive']);
            }
        });

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Tahun ajaran baru berhasil ditambahkan.',
            refreshTable: true,
            closeModal: true
        );
    }

    /**
     * Tampilkan form edit (modal) Tahun Ajaran. Menggunakan view yang sama dengan create.
     */
    public function editAcademicYear(string $id): View
    {
        $academicYear = CoreAcademicYear::findOrFail($id);

        return view('pages.admin.master.partials._modal-academic-year', compact('academicYear'));
    }

    /**
     * Update data Tahun Ajaran.
     */
    public function updateAcademicYear(Request $request, string $id): View|Response
    {
        $academicYear = CoreAcademicYear::findOrFail($id);

        $validator = $this->academicYearValidator($request, $academicYear->id);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-academic-year',
                $validator,
                ['academicYear' => $academicYear]
            );
        }

        DB::transaction(function () use ($request, $academicYear) {
            // Update data tahun ajaran
            $academicYear->update([
                'name'       => $request->name,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'status'     => $request->status ?? 'inactive',
            ]);

            // Jika status diubah menjadi 'Aktif', pastikan tahun ajaran lain dinonaktifkan
            if ($request->status === 'active') {
                CoreAcademicYear::where('id', '!=', $academicYear->id)
                    ->update(['status' => 'inactive']);
            }
        });

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data tahun ajaran berhasil diperbarui.',
            refreshTable: true,
            closeModal: true
        );
    }

    /**
     * Hapus data Tahun Ajaran.
     */
    public function destroyAcademicYear(string $id): Response
    {
        $academicYear = CoreAcademicYear::find($id);

        if (! $academicYear) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Gagal!',
                text: 'Data tahun ajaran tidak ditemukan atau sudah dihapus sebelumnya.'
            );
        }

        try {
            $academicYear->delete();
        } catch (QueryException $e) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Tidak Bisa Dihapus',
                text: 'Tahun ajaran ini masih terhubung dengan data lain (semester/rombel). Hapus data terkait terlebih dahulu.'
            );
        }

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data tahun ajaran berhasil dihapus.',
            refreshTable: true
        );
    }

    /**
     * Aturan & pesan validasi untuk form Tahun Ajaran (dipakai store & update).
     */
    private function academicYearValidator(Request $request, ?string $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('core_academic_years', 'name')->ignore($ignoreId),
            ],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'nullable|in:active,inactive',
        ], [
            'name.required'       => 'Nama tahun ajaran wajib diisi.',
            'name.max'             => 'Nama tahun ajaran maksimal 50 karakter.',
            'name.unique'          => 'Nama tahun ajaran sudah terdaftar, gunakan nama lain.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date'     => 'Format tanggal mulai tidak valid.',
            'end_date.required'   => 'Tanggal selesai wajib diisi.',
            'end_date.date'       => 'Format tanggal selesai tidak valid.',
            'end_date.after'      => 'Tanggal selesai harus setelah tanggal mulai.',
            'status.in' => 'Status yang dipilih tidak valid.',
        ]);
    }

    /* ============================================================
     * SEMESTER
     * ========================================================== */

    public function createSemester(): View
    {
        return view('pages.admin.master.partials._modal-semester', [
            'academicYears' => $this->getAcademicYearsOptions(),
            'semesters'     => $this->getSemestersOptions(),
        ]);
    }

    public function storeSemester(Request $request): View|Response
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id'   => 'required|exists:core_academic_years,id',
            'type'               => 'required|in:odd,even',
            'code'               => 'required|string|max:20|unique:core_semesters,code',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after:start_date',
            'status'             => 'nullable|in:active,inactive', // Tambahan validasi status
            'duplikat_rombel'    => 'nullable|boolean',
            // 'sumber_semester_id' wajib diisi jika 'duplikat_rombel' dicentang
            'sumber_semester_id' => 'nullable|uuid|required_if:duplikat_rombel,1|exists:core_semesters,id',
        ], [
            'sumber_semester_id.required_if' => 'Pilih semester sumber untuk duplikasi rombel.',
        ]);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-semester',
                $validator,
                [
                    'academicYears' => $this->getAcademicYearsOptions(),
                    'semesters'     => $this->getSemestersOptions(),
                ]
            );
        }

        $pesan = 'Semester baru berhasil ditambahkan.';

        DB::transaction(function () use ($request, &$pesan) {
            $activeSemester = CoreSemester::where('status', 'active')->first();

            $newSemester = CoreSemester::create([
                'academic_year_id' => $request->academic_year_id,
                'type'             => $request->type,
                'code'             => $request->code,
                'start_date'       => $request->start_date,
                'end_date'         => $request->end_date,
                'status'           => $request->status ?? 'inactive',
            ]);

            // Isi next_id pada semester yang aktif sebelumnya
            if ($activeSemester) {
                $activeSemester->update(['next_id' => $newSemester->id]);
            }

            // Nonaktifkan semester lain jika yang baru ini diset Aktif
            if ($request->status === 'active') {
                CoreSemester::where('id', '!=', $newSemester->id)
                    ->update(['status' => 'inactive']);
            }

            // Duplikasi rombongan belajar (ClassGroup) dari semester sebelumnya, jika diminta.
            // Logika kenaikan tingkat kelas (10 -> 11 -> 12) mengikuti fitur duplikasi
            // pada Tahun Ajaran di aplikasi sebelumnya, sekarang diturunkan ke level Semester.
            if ($request->boolean('duplikat_rombel') && $request->sumber_semester_id) {
                $this->duplikatRombelDariSemester($request->sumber_semester_id, $newSemester->id);
                $pesan = 'Semester baru dan duplikasi rombel berhasil dibuat.';
            }
        });

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: $pesan,
            refreshTable: true,
            closeModal: true
        );
    }

    /**
     * Duplikasi rombongan belajar (ClassGroup) dari semester sumber ke semester baru,
     * sekaligus menerapkan kenaikan tingkat kelas (10 -> 11 -> 12).
     *
     * Catatan skema: `grade_level` pada acd_class_groups adalah string angka ('10','11','12'),
     * bukan relasi ke tabel Kelas terpisah. Namun field `name` (contoh: 'X RPL 1') masih
     * memakai penomoran romawi, sehingga penggantian nama rombel tetap memakai romawi.
     */
    private function duplikatRombelDariSemester(string $sumberSemesterId, string $semesterBaruId): void
    {
        // Mapping kenaikan tingkat (grade_level). Tingkat 12 tidak naik (lulus).
        $kenaikanGrade = [
            '10' => '11',
            '11' => '12',
        ];

        // Mapping grade_level -> romawi, dipakai untuk mengganti penomoran pada nama rombel.
        $romawiGrade = [
            '10' => 'X',
            '11' => 'XI',
            '12' => 'XII',
        ];

        $rombelsLama = ClassGroup::where('semester_id', $sumberSemesterId)->get();

        foreach ($rombelsLama as $rombel) {
            $gradeLama = $rombel->grade_level;
            $gradeBaru = $kenaikanGrade[$gradeLama] ?? null;

            // Jika ada tingkat berikutnya (bukan lulusan dari grade 12), buat rombel baru
            if ($gradeBaru) {
                $namaBaru = $rombel->name
                    ? Str::replaceFirst($romawiGrade[$gradeLama], $romawiGrade[$gradeBaru], $rombel->name)
                    : null;

                ClassGroup::create([
                    'semester_id'         => $semesterBaruId,
                    'concentration_id'    => $rombel->concentration_id,
                    // Wali kelas diikutsertakan (naik bersama kelasnya)
                    'homeroom_teacher_id' => $rombel->homeroom_teacher_id,
                    'grade_level'         => $gradeBaru,
                    'name'                => $namaBaru,
                    'group_number'        => $rombel->group_number,
                ]);
            }

            // Untuk rombel tingkat 10, buat ulang rombel tingkat 10 yang sama di semester baru
            // (menampung siswa baru / siswa yang tinggal kelas).
            if ($gradeLama === '10') {
                ClassGroup::firstOrCreate(
                    [
                        'semester_id'      => $semesterBaruId,
                        'concentration_id' => $rombel->concentration_id,
                        'grade_level'      => '10',
                        'group_number'     => $rombel->group_number,
                    ],
                    [
                        // Wali kelas dikosongkan untuk menghindari bentrok/duplikat 
                        // dengan kelas 11 yang baru saja membawa wali kelas ini
                        'homeroom_teacher_id' => null,
                        'name'                => $rombel->name,
                    ]
                );
            }
        }
    }

    /**
     * Tampilkan form edit (modal) Semester.
     */
    public function editSemester(string $id): View
    {
        $semester = CoreSemester::findOrFail($id);

        return view('pages.admin.master.partials._modal-semester', [
            'semester'      => $semester,
            'academicYears' => $this->getAcademicYearsOptions(),
        ]);
    }

    /**
     * Update data Semester.
     */
    public function updateSemester(Request $request, string $id): View|Response
    {
        $semester = CoreSemester::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:core_academic_years,id',
            'type'             => 'required|in:odd,even',
            'code'             => 'required|string|max:20|unique:core_semesters,code,' . $semester->id,
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'status'           => 'nullable|in:active,inactive', // Tambahan validasi status
        ]);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-semester-edit',
                $validator,
                [
                    'semester'      => $semester,
                    'academicYears' => $this->getAcademicYearsOptions(),
                ]
            );
        }

        DB::transaction(function () use ($request, $semester) {
            $semester->update([
                'academic_year_id' => $request->academic_year_id,
                'type'             => $request->type,
                'code'             => $request->code,
                'start_date'       => $request->start_date,
                'end_date'         => $request->end_date,
                'status'           => $request->status ?? 'inactive',
            ]);

            // Nonaktifkan semester lain jika status diubah menjadi Aktif
            if ($request->status === 'active') {
                CoreSemester::where('id', '!=', $semester->id)
                    ->update(['status' => 'inactive']);
            }
        });

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data semester berhasil diperbarui.',
            refreshTable: true,
            closeModal: true
        );
    }

    /**
     * Hapus data Semester.
     */
    public function destroySemester(string $id): Response
    {
        $semester = CoreSemester::find($id);

        if (! $semester) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Gagal!',
                text: 'Data semester tidak ditemukan atau sudah dihapus sebelumnya.'
            );
        }

        try {
            $semester->delete();
        } catch (QueryException $e) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Tidak Bisa Dihapus',
                text: 'Semester ini masih terhubung dengan data lain. Hapus data terkait terlebih dahulu.'
            );
        }

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data semester berhasil dihapus.',
            refreshTable: true
        );
    }

    /* ============================================================
     * CONCENTRATION (JURUSAN)
     * ========================================================== */

    public function createConcentration(): View
    {
        return view('pages.admin.master.partials._modal-concentration');
    }

    public function storeConcentration(Request $request): View|Response
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'alias'       => 'required|string|max:50|unique:core_concentrations,alias',
            'code'        => 'required|string|max:50|unique:core_concentrations,code',
            'icon'        => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'status'      => 'nullable|in:active,archived', // Tambahan Validasi Status
        ]);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-concentration',
                $validator
            );
        }

        CoreConcentration::create([
            'name'        => $request->name,
            'alias'       => $request->alias,
            'code'        => $request->code,
            'icon'        => $request->icon,
            'description' => $request->description,
            'status'      => $request->status ?? 'active',
        ]);

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Jurusan baru berhasil ditambahkan.',
            refreshTable: true,
            closeModal: true
        );
    }

    public function editConcentration(string $id): View
    {
        $concentration = CoreConcentration::findOrFail($id);

        return view('pages.admin.master.partials._modal-concentration', compact('concentration'));
    }

    public function updateConcentration(Request $request, string $id): View|Response
    {
        $concentration = CoreConcentration::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'alias'       => 'required|string|max:50|unique:core_concentrations,alias,' . $concentration->id,
            'code'        => 'required|string|max:50|unique:core_concentrations,code,' . $concentration->id,
            'icon'        => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'status'      => 'nullable|in:active,archived',
        ]);

        if ($validator->fails()) {
            return $this->renderModalWithErrors(
                'pages.admin.master.partials._modal-concentration',
                $validator,
                ['concentration' => $concentration]
            );
        }

        $concentration->update([
            'name'        => $request->name,
            'alias'       => $request->alias,
            'code'        => $request->code,
            'icon'        => $request->icon,
            'description' => $request->description,
            'status'      => $request->status ?? 'active',
        ]);

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data jurusan berhasil diperbarui.',
            refreshTable: true,
            closeModal: true
        );
    }

    public function destroyConcentration(string $id): Response
    {
        $concentration = CoreConcentration::find($id);

        if (! $concentration) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Gagal!',
                text: 'Data jurusan tidak ditemukan.'
            );
        }

        try {
            $concentration->delete();
        } catch (QueryException $e) {
            return $this->htmxAlertResponse(
                icon: 'error',
                title: 'Tidak Bisa Dihapus',
                text: 'Jurusan ini masih terhubung dengan data siswa atau rombel.'
            );
        }

        return $this->htmxAlertResponse(
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data jurusan berhasil dihapus.',
            refreshTable: true
        );
    }

    /* ============================================================
     * PRIVATE HELPERS
     * ========================================================== */

    /**
     * Render modal form beserta error validasi & old input (HTMX-friendly).
     */
    private function renderModalWithErrors(string $view, $validator, array $data = []): View
    {
        $view = view($view, $data);

        $view->with('errors', $validator->errors());
        $view->with('input', $validator->getData());

        return $view;
    }

    /**
     * Response HTMX generik yang men-trigger SweetAlert (via event 'showAlert'),
     * opsional refresh tabel & tutup modal.
     *
     * @param  string  $icon   'success' | 'error' | 'warning' | 'info'
     */
    private function htmxAlertResponse(
        string $icon,
        string $title,
        string $text = '',
        bool $refreshTable = false,
        bool $closeModal = false
    ): Response {
        $trigger = [
            'showAlert' => [
                'icon'  => $icon,
                'title' => $title,
                'text'  => $text,
            ],
        ];

        if ($refreshTable) {
            $trigger['refreshTable'] = true;
        }

        if ($closeModal) {
            $trigger['closeModal'] = true;
        }

        return response('')
            ->header('HX-Trigger', json_encode($trigger, JSON_THROW_ON_ERROR));
    }

    /**
     * Ambil daftar tahun ajaran untuk dropdown (sorted by start_date desc).
     */
    private function getAcademicYearsOptions()
    {
        return CoreAcademicYear::orderBy('start_date', 'desc')->get();
    }

    /**
     * Ambil daftar semester untuk dropdown sumber duplikasi rombel (sorted by start_date desc).
     */
    private function getSemestersOptions()
    {
        return CoreSemester::with('academicYear')->orderBy('start_date', 'desc')->get();
    }
}
