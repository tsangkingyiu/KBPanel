<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:projects|regex:/^[a-z0-9.-]+$/',
            'type' => 'required|in:laravel,wordpress',
            'laravel_version' => 'required_if:type,laravel|in:8,9,10,11,12',
            'php_version' => 'required|in:7.4,8.0,8.1,8.2,8.3',
            'webserver' => 'required|in:nginx,apache',
        ];
    }
}
