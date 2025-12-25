<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackupProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'backup_type' => 'required|in:full,database,files',
            'description' => 'nullable|string|max:255',
        ];
    }
}
