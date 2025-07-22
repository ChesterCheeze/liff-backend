<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyQuestionFactory extends Factory
{
    protected $model = SurveyQuestion::class;

    public function definition()
    {
        $types = ['text', 'multiple_choice', 'rating', 'checkbox', 'radio'];

        return [
            'label' => $this->faker->sentence(3),
            'name' => $this->faker->unique()->slug(2),
            'type' => $this->faker->randomElement($types),
            'required' => $this->faker->boolean(70),
            'survey_id' => Survey::factory(),
        ];
    }
}
