<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class SyncVenueAmenitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amenity_ids'   => ['array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'amenity_ids.array'        => 'Danh sách amenity IDs phải là mảng.',
            'amenity_ids.*.integer'    => 'Mỗi amenity ID phải là số nguyên.',
            'amenity_ids.*.exists'     => 'Amenity ID không tồn tại.',
        ];
    }
}
