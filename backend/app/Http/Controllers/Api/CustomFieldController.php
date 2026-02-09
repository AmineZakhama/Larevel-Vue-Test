<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomField;
use App\Http\Resources\CustomFieldResource;
use App\Http\Requests\StoreCustomFieldRequest;
use App\Http\Requests\UpdateCustomFieldRequest;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fields = CustomField::all();
        return CustomFieldResource::collection($fields);
    }

    /**
     * Get available custom fields (not assigned to any category yet)
     */
    public function getAvailable(Request $request)
    {
        $excludeCategoryId = $request->query('exclude_category_id');

        // Get field IDs that are already assigned to categories
        $query = \DB::table('category_custom_field')
            ->select('custom_field_id')
            ->distinct();

        if ($excludeCategoryId) {
            $query->where('category_id', '!=', $excludeCategoryId);
        }

        $usedFieldIds = $query->pluck('custom_field_id')->toArray();

        // Get fields NOT in the used list
        $availableFields = CustomField::whereNotIn('id', $usedFieldIds)->get();

        return CustomFieldResource::collection($availableFields);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomFieldRequest $request)
    {
        $field = CustomField::create($request->validated());
        return new CustomFieldResource($field);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $field = CustomField::findOrFail($id);
        return new CustomFieldResource($field);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomFieldRequest $request, $id)
    {
        $field = CustomField::findOrFail($id);
        $field->update($request->validated());
        return new CustomFieldResource($field);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $field = CustomField::findOrFail($id);
        $field->delete();
        return response()->json(['message' => 'Custom field deleted successfully']);
    }
}
