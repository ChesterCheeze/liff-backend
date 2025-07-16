<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SurveyResponseTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testStore()
    {
        $user = \App\Models\LineOAUser::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;
        $data = [
            'lineId' => $user->line_id,
            'survey_id' => '1',
            'form_data' => '{
                "q1": 7,
                "q2": 8
            }',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/survey/response', $data);

        if ($response->status() !== 201) {
            fwrite(STDERR, $response->getContent());
        }
        $response->assertStatus(201);
        $response->assertJson(['message' => 'Data created successfully']);
    }
}
