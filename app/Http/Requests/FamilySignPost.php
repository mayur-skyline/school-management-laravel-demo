<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FamilySignPost extends FormRequest
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
            'student_id' => 'required',
            'risk' => 'required',
            'school_action' => 'required',
            //'future_risk_ids' => 'required|array',
            'goals' => 'required|array',
            //'reason_ids' => 'required|array',
            'lead' => 'required',
            'review_date' => 'required|date|after:today'
        ];
    }
}
