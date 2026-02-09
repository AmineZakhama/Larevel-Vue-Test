<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'field_type',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function categories()
    {
       return $this->belongsToMany(Category::class, 'category_custom_field')->withTimestamps();
    }
}
