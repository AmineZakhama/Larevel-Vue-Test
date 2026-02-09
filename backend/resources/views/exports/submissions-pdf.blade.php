<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Submissions Export</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333;
        }
        h1 {
            color: #2563eb;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .metadata {
            font-size: 10px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #1e40af;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px 5px;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .submission-block {
            page-break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            padding: 10px;
        }
        .submission-header {
            background-color: #f3f4f6;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #2563eb;
        }
        .category-section {
            margin: 10px 0;
        }
        .category-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
            font-size: 10px;
        }
        .field-row {
            display: flex;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .field-name {
            width: 40%;
            font-weight: bold;
            color: #374151;
        }
        .field-value {
            width: 60%;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>Submissions Export</h1>
    <div class="metadata">
        <strong>Generated:</strong> {{ now()->format('F d, Y \a\t g:i A') }}<br>
        <strong>Total Submissions:</strong> {{ $submissions->count() }}
    </div>

    @foreach($submissions as $submission)
        <div class="submission-block">
            <div class="submission-header">
                <strong>Submission #{{ $submission->id }}</strong> - {{ $submission->form->name }}<br>
                <small>Submitted: {{ $submission->created_at->format('M d, Y g:i A') }}</small>
            </div>

            @if($submission->form->categories)
                @foreach($submission->form->categories as $category)
                    <div class="category-section">
                        <div class="category-title">{{ $category->name }}</div>

                        @foreach($category->customFields as $field)
                            <div class="field-row">
                                <div class="field-name">{{ $field->name }}:</div>
                                <div class="field-value">
                                    @if(isset($submission->field_values[$field->id]))
                                        @if($field->field_type === 'checkbox')
                                            {{ $submission->field_values[$field->id] ? 'Yes' : 'No' }}
                                        @else
                                            {{ $submission->field_values[$field->id] }}
                                        @endif
                                    @else
                                        <em>N/A</em>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="metadata" style="margin-top: 20px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        End of Report - {{ $submissions->count() }} submission(s)
    </div>
</body>
</html>
