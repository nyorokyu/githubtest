<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class MaRequest extends FormRequest
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
            'title' => 'required|max:50',
            'prefectures' => 'required|integer|not_in:0',
            'content' => 'required',
            'display' => 'required'
        ];
    }

    public function messages() {
      return [
        'title.required' => Config::get('consts.wording.ERROR_REQUIRE_TITLE'),
        'title.max' => Config::get('consts.wording.ERROR_TITLE_MAX'),
        'prefectures.*' => '都道府県を選択してください。',
        'content.required' => '本文は必須項目です。',
        'display.required' => Config::get('consts.wording.ERROR_REQUIRE_RADIO'),
      ];
    }
}
