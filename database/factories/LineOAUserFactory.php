<?php

namespace Database\Factories;

use App\Models\LineOAUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class LineOAUserFactory extends Factory
{
    protected $model = LineOAUser::class;

    public function definition()
    {
        return [
            'line_id' => (string) $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'name' => $this->faker->name(),
            'picture_url' => $this->faker->imageUrl(200, 200, 'people'),
        ];
    }
}
