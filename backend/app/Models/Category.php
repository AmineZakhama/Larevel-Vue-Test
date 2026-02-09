<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function customFields()
    {
        return $this->belongsToMany(CustomField::class, 'category_custom_field')->withTimestamps();
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class, 'form_category')->withTimestamps();
    }
}
