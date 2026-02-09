<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        'name' => 'sometimes|string|max:255',
        'description' => 'nullable|string',
        'custom_field_ids' => 'nullable|array',
        'custom_field_ids.*' => 'exists:custom_fields,id'
    ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fieldIds = $this->input('custom_field_ids', []);

            if (empty($fieldIds)) {
                return;
            }

            $currentCategoryId = $this->route('category');

            // Check if any fields are already assigned to OTHER categories
            $usedFields = \DB::table('category_custom_field')
                ->whereIn('custom_field_id', $fieldIds)
                ->where('category_id', '!=', $currentCategoryId)
                ->pluck('custom_field_id')
                ->unique();

            if ($usedFields->isNotEmpty()) {
                $fieldNames = \DB::table('custom_fields')
                    ->whereIn('id', $usedFields->toArray())
                    ->pluck('name')
                    ->toArray();

                $validator->errors()->add(
                    'custom_field_ids',
                    'The following fields are already assigned to other categories: ' . implode(', ', $fieldNames)
                );
            }
        });
    }
}
