<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class UserRequest extends FormRequest
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
            'name' => ['required', 'max:225'],
            'email' => ['required', 'string', 'email:strict,dns,spoof', 'max:225'],
            'address' => ['required', 'max:100'],
            'tel' => ['required', 'max:15'],
        ];
    }

    public function messages() {
      return [
        'name.required' => Config::get('consts.wording.ERROR_REQUIRE_TITLE'),
        'name.max' => '氏名は255文字以内で入力してください。',
        'email.required' => 'メールアドレスは必須項目です。',
        'email.max' => 'メールアドレスは255文字以内で入力してください。',
        'email.*' => 'メールアドレスを正しく入力してください。',
        'address.required' => '住所は必須項目です。',
        'address.max' => '住所は100文字以内で入力してください。',
        'tel.required' => '電話番号は必須項目です。',
        'tel.max' => '電話番号は15文字以内で入力してください。'
      ];
    }
}
