<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'field_values',
    ];

    protected $casts = [
        'field_values' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
