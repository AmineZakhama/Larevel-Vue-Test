<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Submission #{{ $submission->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        h2 {
            color: #1e40af;
            margin-top: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f3f4f6;
        }
        .field {
            margin: 10px 0;
            padding: 8px;
            border-left: 3px solid #2563eb;
            background-color: #f9fafb;
        }
        .field-name {
            font-weight: bold;
            color: #1e40af;
        }
        .field-value {
            margin-top: 5px;
            color: #374151;
        }
    </style>
</head>
<body>
    <h1>{{ $submission->form->name }}</h1>

    <div class="info">
        <strong>Submission ID:</strong> #{{ $submission->id }}<br>
        <strong>Submitted:</strong> {{ $submission->created_at->format('F d, Y \a\t g:i A') }}
    </div>

    @foreach($submission->form->categories as $category)
        <h2>{{ $category->name }}</h2>

        @if($category->description)
            <p><em>{{ $category->description }}</em></p>
        @endif

        @foreach($category->customFields as $field)
            <div class="field">
                <div class="field-name">{{ $field->name }}</div>
                <div class="field-value">
                    {{ $submission->field_values[$field->id] ?? 'N/A' }}
                </div>
            </div>
        @endforeach
    @endforeach
</body>
</html>
