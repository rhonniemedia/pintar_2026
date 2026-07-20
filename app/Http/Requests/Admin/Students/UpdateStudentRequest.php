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
        // Ambil info step dari URL (?step=...)
        $step = (int) $this->query('step', 1);

        // Hanya jalankan validasi untuk step yang sedang aktif
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
            2 => [ // Kontak & Alamat (Posisi baru di Step 2)
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
            3 => [ // Orangtua / Wali (Rule tambahan)
                'guardian_name'          => ['required', 'string', 'max:255'],
                'guardian_relationship'  => ['required', 'in:father,mother,guardian'],
                'guardian_living_status' => ['required', 'in:alive,deceased'],
                'guardian_birth_year'    => ['nullable', 'numeric', 'digits:4'],
                'guardian_occupation'    => ['nullable', 'string', 'max:255'],
                'guardian_education'     => ['nullable', 'string', 'max:255'],
                'guardian_income_range'  => ['nullable', 'string', 'max:255'],
                'guardian_nik'           => ['nullable', 'string', 'max:32'],
                'guardian_phone_number'  => ['nullable', 'string', 'max:20'],
                'guardian_address'       => ['nullable', 'string'],
            ],
            4 => [ // Akademik (Posisi baru di Step 4)
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

    /**
     * Helper milikmu yang dipertahankan.
     */
    private function enumValues(string $enumClass): string
    {
        return implode(',', array_column($enumClass::cases(), 'value'));
    }

    /**
     * Kustomisasi error khusus untuk HTMX agar mereturn Modal kembali, bukan melakukan redirect.
     */
    protected function failedValidation(Validator $validator)
    {
        $id = $this->route('id');
        $step = (int) $this->query('step', 1);

        // Menggunakan method bawaan Request (Lebih clean dan tidak dimarahi Intelephense)
        $this->flash();

        $student = Student::with(['vault', 'guardians.vault'])->findOrFail($id);

        // Deklarasikan tipe class secara eksplisit untuk menghilangkan error 'withErrors'
        /** @var \Illuminate\View\View $view */
        $view = view('pages.admin.students.data.partials._edit-modal', [
            'student'     => $student,
            'currentStep' => $step
        ]);

        // Suntikkan pesan error validasi ke dalam view
        $view->withErrors($validator);

        throw new HttpResponseException(response($view));
    }

    /**
     * Kustomisasi pesan error (Custom Messages)
     */
    public function messages(): array
    {
        return [
            // Pesan spesifik untuk field tertentu
            'name.required'          => 'Nama lengkap wajib diisi.',
            'gender.required'        => 'Jenis kelamin wajib dipilih.',
            'address.required'       => 'Alamat wajib diisi.',

            'guardian_name.required' => 'Nama wali wajib diisi.',
            'guardian_relationship.required'  => 'Status relasi wajib dipilih.',
            'guardian_living_status.required' => 'Status kehidupan wajib dipilih.',

            // Pesan umum (fallback) untuk semua field yang menggunakan rule ini
            'required'    => ':attribute wajib diisi.',
            'string'      => ':attribute harus berupa teks.',
            'max'         => ':attribute maksimal :max karakter.',
            'min'         => ':attribute minimal :min karakter.',
            'in'          => 'Pilihan :attribute tidak valid.',
            'numeric'     => ':attribute hanya boleh berisi angka.',
            'digits'      => ':attribute harus berisi tepat :digits angka.',
            'digits_between' => ':attribute harus berisi antara :min sampai :max angka.',
            'email'       => 'Format :attribute tidak valid.',
            'date'        => 'Format tanggal tidak valid.',
        ];
    }

    /**
     * Kustomisasi nama alias kolom (Custom Attributes)
     * Ini digunakan untuk mengganti kata ":attribute" pada pesan fallback di atas.
     */
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

            'guardian_name'          => 'Nama Wali',
            'guardian_relationship'  => 'Status Relasi',
            'guardian_living_status' => 'Status Kehidupan',
            'guardian_birth_year'    => 'Tahun Lahir Wali',
            'guardian_occupation'    => 'Pekerjaan Wali',
            'guardian_education'     => 'Pendidikan Terakhir Wali',
            'guardian_income_range'  => 'Rentang Penghasilan Wali',
            'guardian_nik'           => 'NIK Wali',
            'guardian_phone_number'  => 'No Telepon Wali',

            'previous_school'       => 'Sekolah Asal',
            'previous_school_npsn'  => 'NPSN Sekolah Asal',
        ];
    }
}
