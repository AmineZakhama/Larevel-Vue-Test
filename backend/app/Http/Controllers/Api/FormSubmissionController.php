<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\Form;
use App\Http\Requests\StoreFormSubmissionRequest;
use App\Http\Resources\FormSubmissionResource;

class FormSubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($formId)
    {
        $form = Form::findOrFail($formId);
        $submissions = $form->submissions()->with('form')->latest()->get();
        return FormSubmissionResource::collection($submissions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormSubmissionRequest $request, $formId)
    {
        $form = Form::with('categories.customFields')->findOrFail($formId);

        // Get field_values from request
        $fieldValues = $request->input('field_values');



        $submission = FormSubmission::create([
            'form_id' => $formId,
            'field_values' => $fieldValues
        ]);



        return new FormSubmissionResource($submission->load('form'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $submission = FormSubmission::with('form.categories.customFields')->findOrFail($id);
        return new FormSubmissionResource($submission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $submission = FormSubmission::findOrFail($id);
        $submission->delete();
        return response()->json(['message' => 'Submission deleted successfully']);
    }

    /**
     * Reorder submissions
     */
    public function reorder(Request $request)
    {
        $submissions = $request->input('submissions'); // [{id: 1, order: 0}, {id: 2, order: 1}]

        foreach ($submissions as $item) {
            FormSubmission::where('id', $item['id'])
                ->update(['display_order' => $item['order']]);
        }

        return response()->json(['message' => 'Submissions reordered successfully']);
    }
}
