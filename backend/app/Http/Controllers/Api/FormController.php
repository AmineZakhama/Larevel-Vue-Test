<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Http\Resources\FormResource;
use App\Http\Requests\StoreFormRequest;
use App\Http\Requests\UpdateFormRequest;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $forms = Form::with('categories')->get();
        return FormResource::collection($forms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormRequest $request)
    {
        $validated = $request->validated();
        $form = Form::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null
        ]);
        if (!empty($validated['category_ids'])) {
            $form->categories()->attach($validated['category_ids']);
        }
        return new FormResource($form->load('categories.customFields'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $form = Form::with('categories.customFields')->findOrFail($id);
        return new FormResource($form);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFormRequest $request, $id)
    {
        $form = Form::findOrFail($id);
        $validated = $request->validated();
        $form->update([
            'name' => $validated['name'] ?? $form->name,
            'description' => $validated['description'] ?? $form->description
        ]);
        if (isset($validated['category_ids'])) {
            $form->categories()->sync($validated['category_ids']);
        }
        return new FormResource($form->load('categories.customFields'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $form = Form::findOrFail($id);
        $form->delete();
        return response()->json(['message' => 'Form deleted successfully']);
    }
}
