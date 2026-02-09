<?php

namespace App\Exports;

use App\Models\FormSubmission;
use App\Models\Form;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SubmissionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $formId;
    protected $allFields = [];
    protected $fieldsByCategory = [];

    public function __construct($formId = null)
    {
        $this->formId = $formId;
        $this->loadFieldStructure();
    }

    protected function loadFieldStructure()
    {
        // Get all forms (or specific form)
        $query = Form::with('categories.customFields');

        if ($this->formId) {
            $query->where('id', $this->formId);
        }

        $forms = $query->get();

        // Collect all unique fields organized by category
        foreach ($forms as $form) {
            foreach ($form->categories as $category) {
                foreach ($category->customFields as $field) {
                    $key = $field->id;
                    if (!isset($this->allFields[$key])) {
                        $this->allFields[$key] = [
                            'id' => $field->id,
                            'name' => $field->name,
                            'type' => $field->field_type,
                            'category' => $category->name
                        ];

                        // Group by category
                        if (!isset($this->fieldsByCategory[$category->name])) {
                            $this->fieldsByCategory[$category->name] = [];
                        }
                        $this->fieldsByCategory[$category->name][] = $this->allFields[$key];
                    }
                }
            }
        }
    }

    public function collection()
    {
        $query = FormSubmission::with('form.categories.customFields')->orderBy('display_order');

        if ($this->formId) {
            $query->where('form_id', $this->formId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        $headings = [
            'Submission ID',
            'Form Name',
        ];

        // Add headers organized by category
        foreach ($this->fieldsByCategory as $categoryName => $fields) {
            foreach ($fields as $field) {
                $headings[] = $categoryName . ' - ' . $field['name'];
            }
        }

        $headings[] = 'Submitted At';

        return $headings;
    }

    public function map($submission): array
    {
        $row = [
            $submission->id,
            $submission->form->name,
        ];

        // Add each field value in the same order as headings
        foreach ($this->fieldsByCategory as $categoryName => $fields) {
            foreach ($fields as $field) {
                $value = $submission->field_values[$field['id']] ?? '-';

                // Format based on field type
                if ($field['type'] === 'checkbox') {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($field['type'] === 'date' && $value !== '-') {
                    $value = date('Y-m-d', strtotime($value));
                }

                $row[] = $value;
            }
        }

        $row[] = $submission->created_at->format('Y-m-d H:i:s');

        return $row;
    }
}
