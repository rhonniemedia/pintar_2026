<?php

namespace App\Http\Requests\Admin\Settings;

use App\Models\CoreSchool;
use App\Models\Data;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Data sekolah singleton: cari id record yang sudah ada (jika ada),
        // supaya rule unique NPSN tidak bentrok dengan data miliknya sendiri.
        $currentId = CoreSchool::query()->value('id');

        return [
            'name'                           => ['required', 'string', 'max:255'],
            'status'                         => ['required', 'in:negeri,swasta'],
            'npsn'                           => [
                'required',
                'digits:8',
                Rule::unique('core_schools', 'npsn')->ignore($currentId),
            ],
            'nss'                            => ['nullable', 'string', 'max:20'],
            'establishment_decree_number'    => ['nullable', 'string', 'max:100'],
            'establishment_date'             => ['nullable', 'date'],

            'address'                        => ['required', 'string'],
            'rt'                             => ['nullable', 'string', 'max:5'],
            'rw'                             => ['nullable', 'string', 'max:5'],
            'village'                        => ['required', 'string', 'max:100'],
            'district'                       => ['required', 'string', 'max:100'],
            'regency'                        => ['required', 'string', 'max:100'],
            'province'                       => ['required', 'string', 'max:100'],
            'postal_code'                    => ['nullable', 'string', 'max:10'],

            'phone'                          => ['required', 'string', 'max:20'],
            'email'                          => ['required', 'email', 'max:255'],
            'website'                        => ['nullable', 'max:255'],
            'logo'                           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            'supervising_office_status'      => ['nullable', 'string', 'max:100'],
            'parent_institution'             => ['nullable', 'string', 'max:100'],

            'headmaster_staff_id'                    => ['nullable', 'uuid', 'exists:staff_data,id'],
            'student_affairs_deputy_staff_id'        => ['nullable', 'uuid', 'exists:staff_data,id'],
            'administration_coordinator_staff_id'    => ['nullable', 'uuid', 'exists:staff_data,id'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->flash();

        $school = CoreSchool::current();

        $staffOptions = Data::orderBy('name')
            ->get()
            ->map(fn($s) => ['value' => $s->id, 'label' => $s->name])
            ->toArray();

        /** @var \Illuminate\View\View $view */
        $view = view('pages.admin.settings.school.partials._modal-edit', [
            'school'       => $school,
            'staffOptions' => $staffOptions,
        ]);

        throw new HttpResponseException(response($view->withErrors($validator)));
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string'   => ':attribute harus berupa teks.',
            'max'      => ':attribute maksimal :max karakter.',
            'in'       => 'Pilihan :attribute tidak valid.',
            'digits'   => ':attribute harus berisi tepat :digits angka.',
            'email'    => 'Format :attribute tidak valid.',
            'url'      => 'Format :attribute tidak valid (harus diawali http:// atau https://).',
            'date'     => 'Format :attribute tidak valid.',
            'unique'   => ':attribute sudah digunakan.',
            'exists'   => ':attribute yang dipilih tidak ditemukan.',
            'uuid'     => ':attribute tidak valid.',
            'image'    => ':attribute harus berupa gambar.',
            'mimes'    => ':attribute harus berformat: :values.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                        => 'Nama Sekolah',
            'status'                      => 'Status Sekolah',
            'npsn'                        => 'NPSN',
            'nss'                         => 'NSS',
            'establishment_decree_number' => 'Nomor SK Pendirian',
            'establishment_date'          => 'Tanggal Pendirian',
            'address'                     => 'Alamat Sekolah',
            'village'                     => 'Kelurahan/Desa',
            'district'                    => 'Kecamatan',
            'regency'                     => 'Kabupaten/Kota',
            'province'                    => 'Provinsi',
            'postal_code'                 => 'Kode Pos',
            'phone'                       => 'Telepon',
            'email'                       => 'Email',
            'website'                     => 'Website',
            'logo'                        => 'Logo Sekolah',
            'supervising_office_status'   => 'Status di Bawah Dinas',
            'parent_institution'          => 'Instansi Induk',
            'headmaster_staff_id'                 => 'Kepala Sekolah',
            'student_affairs_deputy_staff_id'     => 'Wakil Kepala Bidang Kesiswaan',
            'administration_coordinator_staff_id' => 'Koordinator Tata Usaha',
        ];
    }
}
