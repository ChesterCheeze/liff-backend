<?php

namespace Database\Factories;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    public function definition()
    {
        return [
            'line_id' => LineOAUser::factory(),
            'survey_id' => Survey::factory(),
            'form_data' => json_encode([
                'answers' => $this->faker->paragraphs(3),
            ]),
            'created_at' => $this->faker->dateTimeBetween('-6 months'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month'),
        ];
    }
}
