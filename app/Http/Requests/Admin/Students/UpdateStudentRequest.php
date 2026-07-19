<?php

namespace App\Http\Requests\Admin\Students;

use App\Enums\Student\DistanceToSchool;
use App\Enums\Student\Gender;
use App\Enums\Student\Religion;
use App\Enums\Student\ResidenceType;
use App\Enums\Student\SpecialCondition;
use App\Enums\Student\Transportation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Sesuaikan dengan policy/permission project (mis. Gate::allows('update-student'))
        return true;
    }

    public function rules(): array
    {
        return [
            // Identitas
            'name'               => ['required', 'string', 'max:255'],
            'nick_name'          => ['nullable', 'string', 'max:100'],
            'gender'             => ['required', 'in:' . $this->enumValues(Gender::class)],
            'pob'                => ['nullable', 'string', 'max:255'],
            'dob'                => ['nullable', 'date'],
            'religion'           => ['nullable', 'in:' . $this->enumValues(Religion::class)],
            'nik'                => ['nullable', 'string', 'max:32'],
            'child_order'        => ['nullable', 'integer', 'min:1'],
            'number_of_siblings' => ['nullable', 'integer', 'min:0'],

            // Akademik (non-rombel, non-jurusan — dikelola lewat alur Pindah Kelas/Kenaikan Kelas)
            'previous_school'               => ['nullable', 'string', 'max:255'],
            'previous_school_npsn'          => ['nullable', 'string', 'max:20'],
            'previous_school_city'          => ['nullable', 'string', 'max:100'],
            'previous_school_province'      => ['nullable', 'string', 'max:100'],
            'graduation_certificate_number' => ['nullable', 'string', 'max:100'],
            'graduation_year'               => ['nullable', 'digits:4'],

            // Kontak & Alamat
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

            // Kesehatan & Minat
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
        ];
    }

    /**
     * Bangun daftar value enum (mis. "L,P") untuk rule 'in:...' tanpa bergantung
     * pada method statis tambahan di enum — cukup butuh enum itu backed by string.
     */
    private function enumValues(string $enumClass): string
    {
        return implode(',', array_column($enumClass::cases(), 'value'));
    }
}
