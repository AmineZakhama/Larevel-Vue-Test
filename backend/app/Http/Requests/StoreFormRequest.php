<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
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
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_ids' => 'nullable|array',
        'category_ids.*' => 'exists:categories,id'
    ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $categoryIds = $this->input('category_ids', []);

            if (empty($categoryIds)) {
                return;
            }

            // Check if any categories are already assigned to other forms
            $usedCategories = \DB::table('category_form')
                ->whereIn('category_id', $categoryIds)
                ->pluck('category_id')
                ->unique();

            if ($usedCategories->isNotEmpty()) {
                $categoryNames = \DB::table('categories')
                    ->whereIn('id', $usedCategories->toArray())
                    ->pluck('name')
                    ->toArray();

                $validator->errors()->add(
                    'category_ids',
                    'The following categories are already used in other forms: ' . implode(', ', $categoryNames)
                );
            }
        });
    }
}
