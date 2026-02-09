<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('customFields')->get();
        return CategoryResource::collection($categories);
    }

    /**
     * Get available categories (not used in any form yet)
     */
    public function getAvailable(Request $request)
    {
        $excludeFormId = $request->query('exclude_form_id');

        // Get category IDs that are already assigned to forms
        $query = \DB::table('category_form')
            ->select('category_id')
            ->distinct();

        if ($excludeFormId) {
            $query->where('form_id', '!=', $excludeFormId);
        }

        $usedCategoryIds = $query->pluck('category_id')->toArray();

        // Get categories NOT in the used list
        $availableCategories = Category::with('customFields')
            ->whereNotIn('id', $usedCategoryIds)
            ->get();

        return CategoryResource::collection($availableCategories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $category = Category::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null
        ]);
        if (!empty($validated['custom_field_ids'])) {
            $category->customFields()->attach($validated['custom_field_ids']);
        }
        return new CategoryResource($category->load('customFields'));
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::with('customFields')->findOrFail($id);
        return new CategoryResource($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validated();
        $category->update([
            'name' => $validated['name'] ?? $category->name,
            'description' => $validated['description'] ?? $category->description
        ]);
        if (isset($validated['custom_field_ids'])) {
            $category->customFields()->sync($validated['custom_field_ids']);
        }
        return new CategoryResource($category->load('customFields'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
