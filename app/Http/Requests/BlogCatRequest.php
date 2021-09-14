<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class BlogCatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category' => 'required|max:50'
        ];
    }

    public function messages() {
      return [
        'category.required' => Config::get('consts.wording.ERROR_REQUIRE_CAT'),
        'category.max' => 'カテゴリは50文字以内で入力してください。'
      ];
    }
}
