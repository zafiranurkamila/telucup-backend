<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewSelfAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_notes'      => 'nullable|string|max:2000',
            'is_allowed_to_play' => 'required|boolean',
            'pic_confirmed'      => 'sometimes|boolean',
        ];
    }
}