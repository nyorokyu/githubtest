<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class ContactRequest extends FormRequest
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
            'name' => ['required'],
            'email' => ['required', 'string', 'email'],
            'tel' => ['digits_between:10,11'],
            'message' => ['required']
        ];
    }

    public function messages() {
      return [
        'name.required' => Config::get('consts.wording.ERROR_REQUIRE_TITLE'),
        'email.required' => 'メールアドレスは必須項目です。',
        'email.*' => 'メールアドレスを正しく入力してください。',
        'tel.disits_between' => '電話番号を正しく入力してください。',
        'message.required' => 'お問い合わせ内容は必須項目です。'
      ];
    }
}
