<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
public function rules(): array
{
    $userId = $this->route('id');

    return [
        'Username'  => "sometimes|string|max:50|unique:users,Username,$userId,User_ID",
        'Password'  => 'sometimes|string|min:6',
        'Role'      => 'sometimes|in:Admin,Receptionist,Technician,Billing,Inventory',
        'Full_Name' => 'sometimes|string|max:100',
        'Email'     => "sometimes|email|unique:users,Email,$userId,User_ID",
        'Phone'     => 'sometimes|nullable|string|max:20',
        'Department'=> 'sometimes|nullable|string|max:30',
        'Hire_Date' => 'sometimes|nullable|date',
        'Is_Active' => 'sometimes|nullable|boolean',
    ];
}
}
