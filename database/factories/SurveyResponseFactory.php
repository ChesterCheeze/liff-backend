<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    public function definition()
    {
        return [
            'line_id' => null, // Will be set by relationships
            'survey_id' => Survey::factory(),
            'user_id' => null, // Will be set by relationships
            'user_type' => null, // Will be set by relationships
            'form_data' => [
                'answers' => $this->faker->paragraphs(3),
            ],
            'created_at' => $this->faker->dateTimeBetween('-6 months'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
