<?php

namespace App\Http\Requests\Admin\Students;

use App\Enums\Student\DistanceToSchool;
use App\Enums\Student\Education;
use App\Enums\Student\FamilyRelation;
use App\Enums\Student\Gender;
use App\Enums\Student\Income;
use App\Enums\Student\Profession;
use App\Enums\Student\Religion;
use App\Enums\Student\ResidenceType;
use App\Enums\Student\Transportation;
use App\Models\ClassGroup;
use App\Models\CoreSemester;
use App\Models\StudentVault;
use App\Traits\HasBlindIndex;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMutationInRequest extends FormRequest
{
    use HasBlindIndex;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Ambil current_step dari input (default 5 jika ini adalah final submit)
        $step = (int) $this->input('current_step', 5);
        $rules = [];

        if ($step >= 1) {
            $rules = array_merge($rules, [
                'name'               => ['required', 'string', 'max:255'],
                'nisn'               => ['required', 'digits:10'],
                'nik'                => ['required', 'digits:16'],
                'pob'                => ['required', 'string', 'max:255'],
                'dob'                => ['required', 'date'],
                'gender'             => ['required', 'in:' . $this->enumValues(Gender::class)],
                'religion'           => ['required', 'in:' . $this->enumValues(Religion::class)],
                'child_order'        => ['nullable', 'integer', 'min:1'],
                'number_of_siblings' => ['nullable', 'integer', 'min:0'],
            ]);
        }

        if ($step >= 2) {
            $rules = array_merge($rules, [
                'phone_number'       => ['nullable', 'string', 'max:20'],
                'email'              => ['nullable', 'email', 'max:255'],
                'residence_type'     => ['required', 'in:' . $this->enumValues(ResidenceType::class)],
                'transportation'     => ['required', 'in:' . $this->enumValues(Transportation::class)],
                'distance_to_school' => ['nullable', 'in:' . $this->enumValues(DistanceToSchool::class)],
                'address'            => ['required', 'string'],
                'rt'                 => ['nullable', 'string', 'max:5'],
                'rw'                 => ['nullable', 'string', 'max:5'],
                'village'            => ['required', 'string', 'max:100'],
                'district'           => ['required', 'string', 'max:100'],
                'regency'            => ['required', 'string', 'max:100'],
                'province'           => ['required', 'string', 'max:100'],
                'postal_code'        => ['nullable', 'string', 'max:10'],
            ]);
        }

        if ($step >= 3) {
            $rules = array_merge($rules, [
                'guardian_name'         => ['required', 'string', 'max:255'],
                'guardian_relationship' => ['required', 'in:' . $this->enumValues(FamilyRelation::class)],
                'guardian_nik'          => ['nullable', 'digits:16'],
                'guardian_birth_year'   => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
                'guardian_education'    => ['nullable', 'in:' . $this->enumValues(Education::class)],
                'guardian_occupation'   => ['nullable', 'in:' . $this->enumValues(Profession::class)],
                'guardian_income_range' => ['nullable', 'in:' . $this->enumValues(Income::class)],
                'guardian_phone'        => ['nullable', 'string', 'max:20'],
                'guardian_address'      => ['nullable', 'string'],
            ]);
        }

        if ($step >= 4) {
            $rules = array_merge($rules, [
                'origin_school'          => ['required', 'string', 'max:255'],
                'origin_school_npsn'     => ['nullable', 'string', 'max:20'],
                'origin_school_city'     => ['nullable', 'string', 'max:100'],
                'origin_school_province' => ['nullable', 'string', 'max:100'],
                'previous_school'        => ['nullable', 'string', 'max:255'],
                'previous_school_npsn'   => ['nullable', 'string', 'max:20'],
                'previous_school_status' => ['nullable', 'in:negeri,swasta'],
                'previous_school_city'   => ['nullable', 'string', 'max:100'],
                'previous_school_province' => ['nullable', 'string', 'max:100'],
                'graduation_certificate_number' => ['nullable', 'string', 'max:100'],
                'graduation_year'        => ['nullable', 'digits:4'],
            ]);
        }

        if ($step >= 5) {
            $rules = array_merge($rules, [
                'class_group_id' => ['required', 'uuid', 'exists:acd_class_groups,id'],
                'entry_date'     => ['required', 'date'],
                'notes'          => ['nullable', 'string'],
            ]);
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($this->filled('nisn')) {
                $hash = $this->blindIndexHash($this->input('nisn'));
                if (StudentVault::where('nisn_hash', $hash)->exists()) {
                    $v->errors()->add('nisn', 'NISN sudah terdaftar pada siswa lain.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        $this->flash();

        /** @var \Illuminate\View\View $view */
        $view = view('pages.admin.students.transfers.in.partials._modal-create', [
            'classGroups' => $this->activeClassGroups(),
            'currentStep' => (int) $this->input('current_step', 1),
        ]);

        throw new HttpResponseException(response($view->withErrors($validator)));
    }

    public function messages(): array
    {
        return [
            'required'  => ':attribute wajib diisi.',
            'string'    => ':attribute harus berupa teks.',
            'max'       => ':attribute maksimal :max karakter.',
            'min'       => ':attribute minimal :min.',
            'in'        => 'Pilihan :attribute tidak valid.',
            'digits'    => ':attribute harus berisi tepat :digits angka.',
            'email'     => 'Format :attribute tidak valid.',
            'date'      => 'Format tanggal :attribute tidak valid.',
            'exists'    => ':attribute yang dipilih tidak ditemukan.',
            'uuid'      => ':attribute tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            // Atribut Step 1 & Global
            'class_group_id'        => 'Rombongan Belajar Tujuan',
            'entry_date'            => 'Tanggal Mutasi Masuk',
            'name'                  => 'Nama Lengkap Siswa',
            'gender'                => 'Jenis Kelamin',
            'religion'              => 'Agama',
            'nisn'                  => 'NISN',
            'nik'                   => 'NIK',
            'pob'                   => 'Tempat Lahir',
            'dob'                   => 'Tanggal Lahir',

            // Atribut Step 2 (Kontak & Alamat)
            'residence_type'        => 'Jenis Tempat Tinggal',
            'transportation'        => 'Moda Transportasi',
            'address'               => 'Alamat Lengkap',
            'village'               => 'Kelurahan/Desa',
            'district'              => 'Kecamatan',
            'regency'               => 'Kabupaten/Kota',
            'province'              => 'Provinsi',

            // Atribut Step 3 & 4
            'guardian_name'         => 'Nama Orang Tua/Wali',
            'guardian_relationship' => 'Hubungan Keluarga',
            'origin_school'         => 'Nama Sekolah Asal Pindahan',
        ];
    }

    private function enumValues(string $enumClass): string
    {
        return implode(',', array_column($enumClass::cases(), 'value'));
    }

    private function activeClassGroups()
    {
        $semesterAktif = CoreSemester::where('status', 'active')->first();

        return ClassGroup::with('concentration')
            ->when($semesterAktif, fn($q) => $q->where('semester_id', $semesterAktif->id))
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }
}
