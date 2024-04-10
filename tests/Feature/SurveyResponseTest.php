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
        $token = '1|ETshJlxmPmmvuPkZ4Zeuwu12qamc58XlDYcwM4Gz51ff4e65';
        $data = [
            'lineId' => '123456789',
            'survey_id' => '1',
            'form_data' => '{
                "q1": 7,
                "q2": 8
            }',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/survey/response', $data);
       
        $response->assertStatus(201);

        $response->assertJson(['message' => 'Data created successfully']);
    }
}
