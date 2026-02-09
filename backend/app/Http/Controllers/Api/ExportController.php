<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Exports\SubmissionsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    /**
     * Export all submissions to CSV/Excel/PDF
     */
    public function exportSubmissions(Request $request)
    {
        $format = $request->input('format', 'excel'); // excel, csv, pdf
        $formId = $request->input('form_id');

        $fileName = 'submissions_' . now()->format('Y-m-d_His');

        switch ($format) {
            case 'csv':
                return Excel::download(new SubmissionsExport($formId), $fileName . '.csv');
            case 'pdf':
                return $this->exportToPdf($formId, $fileName);
            case 'excel':
            default:
                return Excel::download(new SubmissionsExport($formId), $fileName . '.xlsx');
        }
    }

    /**
     * Export single submission as PDF
     */
    public function exportSubmissionPdf($id)
    {
        $submission = FormSubmission::with(['form.categories.customFields'])->findOrFail($id);

        $pdf = Pdf::loadView('exports.submission-pdf', [
            'submission' => $submission,
        ]);

        return $pdf->download('submission_' . $submission->id . '.pdf');
    }

    /**
     * Export all submissions to PDF
     */
    protected function exportToPdf($formId, $fileName)
    {
        $query = FormSubmission::with('form')->orderBy('display_order');

        if ($formId) {
            $query->where('form_id', $formId);
        }

        $submissions = $query->get();

        $pdf = Pdf::loadView('exports.submissions-pdf', [
            'submissions' => $submissions,
        ]);

        return $pdf->download($fileName . '.pdf');
    }
}
