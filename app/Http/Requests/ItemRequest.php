<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
            'id' => 'required|max:11',
            'title' => 'required|min:5|max:255',
            'description' => 'nullable|min:10|max:255',
            'item_image' => 'required|image|mimes:png,jpeg,jpg,gif|max:2048',
            "pro_date" => 'required|date',
            "exp_date" => 'required|date',
            "start_reminder" => 'required',
            "code" => 'required|integer|digits:5|unique:item',
            "category_id" => 'required|integer|max:255|exists:subcategories,id'

        ];
    }
}
