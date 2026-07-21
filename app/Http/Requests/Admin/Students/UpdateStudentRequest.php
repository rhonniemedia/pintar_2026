<?php

namespace App\Http\Requests\Admin\Students;

use App\Enums\Student\DistanceToSchool;
use App\Enums\Student\Gender;
use App\Enums\Student\Religion;
use App\Enums\Student\ResidenceType;
use App\Enums\Student\SpecialCondition;
use App\Enums\Student\Transportation;
use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $step = (int) $this->query('step', 1);

        return match ($step) {
            1 => [ // Identitas
                'name'               => ['required', 'string', 'max:255'],
                'nick_name'          => ['nullable', 'string', 'max:100'],
                'gender'             => ['required', 'in:' . $this->enumValues(Gender::class)],
                'pob'                => ['nullable', 'string', 'max:255'],
                'dob'                => ['nullable', 'date'],
                'religion'           => ['nullable', 'in:' . $this->enumValues(Religion::class)],
                'nik'                => ['nullable', 'digits:16'],
                'child_order'        => ['nullable', 'integer', 'min:1'],
                'number_of_siblings' => ['nullable', 'integer', 'min:0'],
            ],
            2 => [ // Kontak & Alamat
                'phone_number'       => ['nullable', 'string', 'max:20'],
                'email'              => ['nullable', 'email', 'max:255'],
                'residence_type'     => ['nullable', 'in:' . $this->enumValues(ResidenceType::class)],
                'transportation'     => ['nullable', 'in:' . $this->enumValues(Transportation::class)],
                'distance_to_school' => ['nullable', 'in:' . $this->enumValues(DistanceToSchool::class)],
                'address'            => ['nullable', 'string'],
                'rt'                 => ['nullable', 'string', 'max:5'],
                'rw'                 => ['nullable', 'string', 'max:5'],
                'village'            => ['nullable', 'string', 'max:100'],
                'district'           => ['nullable', 'string', 'max:100'],
                'regency'            => ['nullable', 'string', 'max:100'],
                'province'           => ['nullable', 'string', 'max:100'],
                'postal_code'        => ['nullable', 'string', 'max:10'],
            ],
            3 => [ // Orangtua / Wali
                'guardians' => ['required', 'array'],

                // --- Validasi Ayah (Wajib) ---
                'guardians.father.name'          => ['required', 'string', 'max:255'],
                'guardians.father.living_status' => ['required', 'in:alive,deceased'],
                'guardians.father.birth_year'    => ['nullable', 'numeric', 'digits:4'],
                'guardians.father.occupation'    => ['nullable', 'string', 'max:255'],
                'guardians.father.education'     => ['nullable', 'string', 'max:255'],
                'guardians.father.income_range'  => ['nullable', 'string', 'max:255'],
                'guardians.father.nik'           => ['nullable', 'string', 'max:32'],
                'guardians.father.phone_number'  => ['nullable', 'string', 'max:20'],
                'guardians.father.address'       => ['nullable', 'string'],

                // --- Validasi Ibu (Wajib) ---
                'guardians.mother.name'          => ['required', 'string', 'max:255'],
                'guardians.mother.living_status' => ['required', 'in:alive,deceased'],
                'guardians.mother.birth_year'    => ['nullable', 'numeric', 'digits:4'],
                'guardians.mother.occupation'    => ['nullable', 'string', 'max:255'],
                'guardians.mother.education'     => ['nullable', 'string', 'max:255'],
                'guardians.mother.income_range'  => ['nullable', 'string', 'max:255'],
                'guardians.mother.nik'           => ['nullable', 'string', 'max:32'],
                'guardians.mother.phone_number'  => ['nullable', 'string', 'max:20'],
                'guardians.mother.address'       => ['nullable', 'string'],

                // --- Validasi Wali (Opsional) ---
                'guardians.guardian.name'          => ['nullable', 'string', 'max:255'],
                'guardians.guardian.living_status' => ['nullable', 'in:alive,deceased'],
                'guardians.guardian.birth_year'    => ['nullable', 'numeric', 'digits:4'],
                'guardians.guardian.occupation'    => ['nullable', 'string', 'max:255'],
                'guardians.guardian.education'     => ['nullable', 'string', 'max:255'],
                'guardians.guardian.income_range'  => ['nullable', 'string', 'max:255'],
                'guardians.guardian.nik'           => ['nullable', 'string', 'max:32'],
                'guardians.guardian.phone_number'  => ['nullable', 'string', 'max:20'],
                'guardians.guardian.address'       => ['nullable', 'string'],
            ],
            4 => [ // Akademik
                'previous_school'               => ['nullable', 'string', 'max:255'],
                'previous_school_npsn'          => ['nullable', 'string', 'max:20'],
                'previous_school_city'          => ['nullable', 'string', 'max:100'],
                'previous_school_province'      => ['nullable', 'string', 'max:100'],
                'graduation_certificate_number' => ['nullable', 'string', 'max:100'],
                'graduation_year'               => ['nullable', 'digits:4'],
            ],
            5 => [ // Kesehatan & Minat
                'height'                 => ['nullable', 'numeric', 'min:0'],
                'weight'                 => ['nullable', 'numeric', 'min:0'],
                'blood_type'             => ['nullable', 'string', 'max:5'],
                'is_special_condition'   => ['nullable', 'in:yes,no'],
                'special_condition_type' => ['nullable', 'in:' . $this->enumValues(SpecialCondition::class)],
                'condition_description'  => ['nullable', 'string'],
                'medical_history'        => ['nullable', 'string'],
                'interest_art'           => ['nullable', 'string', 'max:255'],
                'interest_sport'         => ['nullable', 'string', 'max:255'],
                'interest_organization'  => ['nullable', 'string', 'max:255'],
                'extracurricular_choice' => ['nullable', 'string', 'max:255'],
            ],
            default => [],
        };
    }

    private function enumValues(string $enumClass): string
    {
        return implode(',', array_column($enumClass::cases(), 'value'));
    }

    protected function failedValidation(Validator $validator)
    {
        $id = $this->route('id');
        $step = (int) $this->query('step', 1);

        $this->flash();

        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);

        /** @var \Illuminate\View\View $view */
        $view = view('pages.admin.students.data.partials._edit-modal', [
            'student'     => $student,
            'currentStep' => $step
        ]);

        $view->withErrors($validator);

        throw new HttpResponseException(response($view));
    }

    public function messages(): array
    {
        return [
            'name.required'                          => 'Nama lengkap wajib diisi.',
            'gender.required'                        => 'Jenis kelamin wajib dipilih.',
            'address.required'                       => 'Alamat wajib diisi.',

            // Pesan error spesifik untuk relasi Orangtua
            'guardians.father.name.required'         => 'Nama ayah wajib diisi.',
            'guardians.father.living_status.required' => 'Status kehidupan ayah wajib dipilih.',
            'guardians.mother.name.required'         => 'Nama ibu wajib diisi.',
            'guardians.mother.living_status.required' => 'Status kehidupan ibu wajib dipilih.',

            'required'       => ':attribute wajib diisi.',
            'string'         => ':attribute harus berupa teks.',
            'max'            => ':attribute maksimal :max karakter.',
            'min'            => ':attribute minimal :min karakter.',
            'in'             => 'Pilihan :attribute tidak valid.',
            'numeric'        => ':attribute hanya boleh berisi angka.',
            'digits'         => ':attribute harus berisi tepat :digits angka.',
            'digits_between' => ':attribute harus berisi antara :min sampai :max angka.',
            'email'          => 'Format :attribute tidak valid.',
            'date'           => 'Format tanggal tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name'                  => 'Nama Lengkap',
            'nick_name'             => 'Nama Panggilan',
            'gender'                => 'Jenis Kelamin',
            'pob'                   => 'Tempat Lahir',
            'dob'                   => 'Tanggal Lahir',
            'religion'              => 'Agama',
            'nik'                   => 'NIK',
            'child_order'           => 'Anak Ke',
            'number_of_siblings'    => 'Jumlah Saudara',

            'phone_number'          => 'Nomor Telepon',
            'email'                 => 'Email',
            'residence_type'        => 'Jenis Tempat Tinggal',
            'transportation'        => 'Moda Transportasi',
            'distance_to_school'    => 'Jarak ke Sekolah',
            'address'               => 'Alamat Lengkap',

            // Alias untuk atribut Ayah
            'guardians.father.name'         => 'Nama Ayah',
            'guardians.father.birth_year'   => 'Tahun Lahir Ayah',
            'guardians.father.occupation'   => 'Pekerjaan Ayah',
            'guardians.father.education'    => 'Pendidikan Terakhir Ayah',
            'guardians.father.income_range' => 'Rentang Penghasilan Ayah',
            'guardians.father.nik'          => 'NIK Ayah',
            'guardians.father.phone_number' => 'No Telepon Ayah',
            'guardians.father.address'      => 'Alamat Ayah',

            // Alias untuk atribut Ibu
            'guardians.mother.name'         => 'Nama Ibu',
            'guardians.mother.birth_year'   => 'Tahun Lahir Ibu',
            'guardians.mother.occupation'   => 'Pekerjaan Ibu',
            'guardians.mother.education'    => 'Pendidikan Terakhir Ibu',
            'guardians.mother.income_range' => 'Rentang Penghasilan Ibu',
            'guardians.mother.nik'          => 'NIK Ibu',
            'guardians.mother.phone_number' => 'No Telepon Ibu',
            'guardians.mother.address'      => 'Alamat Ibu',

            // Alias untuk atribut Wali
            'guardians.guardian.name'         => 'Nama Wali',
            'guardians.guardian.birth_year'   => 'Tahun Lahir Wali',
            'guardians.guardian.occupation'   => 'Pekerjaan Wali',
            'guardians.guardian.education'    => 'Pendidikan Terakhir Wali',
            'guardians.guardian.income_range' => 'Rentang Penghasilan Wali',
            'guardians.guardian.nik'          => 'NIK Wali',
            'guardians.guardian.phone_number' => 'No Telepon Wali',
            'guardians.guardian.address'      => 'Alamat Wali',

            'previous_school'       => 'Sekolah Asal',
            'previous_school_npsn'  => 'NPSN Sekolah Asal',
        ];
    }
}
