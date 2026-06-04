<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi submission self-assessment.
 *
 * Hanya melakukan validasi struktural — validasi logis (apakah jawaban
 * sesuai dengan opsi yang tersedia di kuesioner versi terkini) di-delegasi
 * ke ScoringService karena bergantung pada QuestionBankService.
 */
class StoreSelfAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization dilakukan via middleware role di route.
        return true;
    }

    public function rules(): array
    {
        return [
            // player_id optional — kalau tidak dikirim, ambil dari user yang login.
            'player_id'      => 'nullable|integer|exists:players,id',
            'answers'        => 'required|array|min:1',
            'answers.*'      => 'nullable',

            // Field demografis wajib (sebagai sanity check duplikat dari kuesioner)
            'answers.demo_age'           => 'required|integer|min:10|max:80',
            'answers.demo_height_cm'     => 'required|numeric|min:100|max:230',
            'answers.demo_weight_kg'     => 'required|numeric|min:25|max:200',
            'answers.demo_activity_level'=> 'required|string',

            // Pertanyaan red flag minimal wajib ada (boolean)
            'answers.A1_heart_condition_diagnosed' => 'required|boolean',
            'answers.A2_chest_pain_during_exercise'=> 'required|boolean',
            'answers.A3_unexplained_fainting'      => 'required|boolean',
            'answers.A4_serious_medical_condition' => 'required|boolean',
            'answers.A5_family_cardiac_death'      => 'required|boolean',
            'answers.B1_currently_recovering'      => 'required|boolean',
            'answers.B2_current_pain_worsens'      => 'required|boolean',
            'answers.B3_pain_score'                => 'required|integer|min:0|max:10',
            'answers.C4_using_glasses'             => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban kuesioner wajib diisi.',
            'answers.array'    => 'Format jawaban harus berupa object/map.',
            'answers.demo_age.required' => 'Usia wajib diisi.',
            'answers.demo_height_cm.required' => 'Tinggi badan wajib diisi.',
            'answers.demo_weight_kg.required' => 'Berat badan wajib diisi.',
        ];
    }
}