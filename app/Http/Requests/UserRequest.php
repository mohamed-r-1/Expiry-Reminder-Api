<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        return [
            //
            // 'name'=>'required',
            // 'email'=>'required|email|unique:users,email',
            // 'password'=>'required|numeric',
            // 'confirm_password'=>'required|same:password'
            'image' => 'required|image|mimes:png,jpeg,jpg,gif|max:2048',
            'name' => 'required|string|min:5|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8|same:password',


        ];
    }
}
