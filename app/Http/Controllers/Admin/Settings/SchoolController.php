<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSchoolRequest;
use App\Models\CoreSchool;
use App\Models\Data;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    /**
     * Tampilkan halaman Data Sekolah.
     */
    public function index()
    {
        return view('pages.admin.settings.school.index', [
            'schoolData' => $this->transform($this->school()),
        ]);
    }

    /**
     * Tampilkan modal form edit (dipanggil via hx-get).
     */
    public function edit()
    {
        return view('pages.admin.settings.school.partials._modal-edit', [
            'school'       => $this->school(),
            'staffOptions' => $this->staffOptions(),
        ]);
    }

    /**
     * Simpan perubahan data sekolah.
     * Karena data ini singleton, "create" terjadi otomatis pada
     * penyimpanan pertama kali (belum pernah ada record sebelumnya).
     */
    public function update(UpdateSchoolRequest $request): Response
    {
        $school = $this->school();
        $data   = $request->safe()->except('logo');
        $logo   = $request->file('logo');

        if ($logo instanceof UploadedFile) {
            $this->removeOldLogo($school);
            $data['logo_path'] = $logo->store('schools', 'public');
        }

        $school->fill($data)->save();

        return $this->htmxUpdated($school);
    }

    /**
     * Opsi staf/pegawai untuk select di form, urut nama.
     */
    private function staffOptions(): array
    {
        return Data::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();
    }

    /**
     * Record singleton sekolah: record pertama, atau instance kosong
     * jika belum pernah disimpan. Return type non-nullable membuat
     * Intelephense tidak protes saat hasilnya dipakai di transform()/view.
     */
    private function school(): CoreSchool
    {
        return CoreSchool::current() ?? new CoreSchool();
    }

    /**
     * Disk 'public' untuk aset ber-URL (logo sekolah).
     * Tipe konkret FilesystemAdapter agar Intelephense mengenali
     * method delete()/url() — kontrak Filesystem tidak punya url().
     */
    private function disk(): FilesystemAdapter
    {
        return Storage::disk('public');
    }

    /**
     * Hapus file logo lama saat logo baru di-upload.
     */
    private function removeOldLogo(CoreSchool $school): void
    {
        if ($school->logo_path) {
            $this->disk()->delete($school->logo_path);
        }
    }

    /**
     * Response 200 + event HX-Trigger 'school-updated' untuk frontend.
     */
    private function htmxUpdated(CoreSchool $school): Response
    {
        return response('', 200)->header('HX-Trigger', json_encode([
            'school-updated' => [
                'message' => 'Data sekolah berhasil diperbarui.',
                'school'  => $this->transform($school),
            ],
        ]));
    }

    /**
     * Transform model menjadi array untuk view/frontend.
     */
    private function transform(CoreSchool $school): array
    {
        return [
            'name'                        => $school->name,
            'status'                      => $school->status,
            'npsn'                        => $school->npsn,
            'nss'                         => $school->nss,
            'establishment_decree_number' => $school->establishment_decree_number,
            'establishment_date'          => $school->establishment_date?->format('d-m-Y'),
            'address'                     => $school->address,
            'rt'                          => $school->rt,
            'rw'                          => $school->rw,
            'village'                     => $school->village,
            'district'                    => $school->district,
            'regency'                     => $school->regency,
            'province'                    => $school->province,
            'postal_code'                 => $school->postal_code,
            'phone'                       => $school->phone,
            'email'                       => $school->email,
            'website'                     => $school->website,
            'logo'                        => $school->logo_path ? $this->disk()->url($school->logo_path) : null,
            'supervising_office_status'   => $school->supervising_office_status,
            'parent_institution'          => $school->parent_institution,

            ...$this->officerFields('headmaster', $school->headmaster_staff_id, $school->headmaster),
            ...$this->officerFields('student_affairs_deputy', $school->student_affairs_deputy_staff_id, $school->studentAffairsDeputy),
            ...$this->officerFields('administration_coordinator', $school->administration_coordinator_staff_id, $school->administrationCoordinator),
        ];
    }

    /**
     * Pasang pasangan key *_staff_id dan *_name untuk satu pejabat.
     */
    private function officerFields(string $prefix, mixed $staffId, ?Model $officer): array
    {
        return [
            "{$prefix}_staff_id" => $staffId,
            "{$prefix}_name"     => $officer?->name,
        ];
    }
}
