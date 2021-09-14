<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Config;

class MovieRequest extends FormRequest
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
            'category_id' => 'required|integer|not_in:0',
            'movie_file' => 'nullable|mimes:mp4,webm,qt,x-msvideo',
            'display' => 'required'
        ];
    }

    public function messages() {
      return [
        'title.required' => Config::get('consts.wording.ERROR_REQUIRE_TITLE'),
        'title.max' => Config::get('consts.wording.ERROR_TITLE_MAX'),
        'category_id.*' => Config::get('consts.wording.ERROR_REQUIRE_CAT'),
        'movie_file.mimes' => '動画ファイルは mp4, webm, mov, aviのみアップロード可能です。',
        'display.required' => Config::get('consts.wording.ERROR_REQUIRE_RADIO'),
      ];
    }
}
