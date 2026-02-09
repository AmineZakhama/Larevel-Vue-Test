<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\FormSubmissionController;


Route::get('custom-fields/available', [CustomFieldController::class, 'getAvailable']);
Route::apiResource('custom-fields', CustomFieldController::class);

Route::get('categories/available', [CategoryController::class, 'getAvailable']);
Route::apiResource('categories', CategoryController::class);

Route::apiResource('forms', FormController::class);

Route::get('forms/{formId}/submissions', [FormSubmissionController::class, 'index']);
Route::post('forms/{formId}/submissions', [FormSubmissionController::class, 'store']);
Route::get('submissions/{id}', [FormSubmissionController::class, 'show']);
Route::delete('submissions/{id}', [FormSubmissionController::class, 'destroy']);

