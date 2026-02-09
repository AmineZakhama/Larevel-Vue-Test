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

    /**
     * Set the field_values attribute.
     * Ensures it's stored as a JSON object, not an array.
     */
    public function setFieldValuesAttribute($value)
    {
        // Force JSON to be stored as object, not array
        $this->attributes['field_values'] = json_encode($value, JSON_FORCE_OBJECT);
    }

    /**
     * Get the field_values attribute.
     * Returns it as an associative array.
     */
    public function getFieldValuesAttribute($value)
    {
        return json_decode($value, true);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
