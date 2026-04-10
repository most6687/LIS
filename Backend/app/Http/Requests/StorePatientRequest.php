<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // هنسيبها true لأن الحماية بالـ middleware
    }

    public function rules(): array
    {
        return [
            'Full_Name' => 'required|string|max:100',
            'Gender' => 'nullable|in:M,F',
            'Date_of_Birth' => 'nullable|date',
            'Phone' => 'nullable|string|max:20',
            'Address' => 'nullable|string|max:200',
            'Email' => 'nullable|email|max:100',
            'Insurance_Info' => 'nullable|string|max:100',
        ];
    }
}