<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Onboarding extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if($this->user()->level == 1)
            return true;
        abort(400, 'Access Denied for all User except Students');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'avatar' => 'required|numeric',
            'vehicle' => 'required'
        ];
    }
}
