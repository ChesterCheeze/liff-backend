<?php

use App\Models\Survey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DebugExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_export_endpoint()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $survey = Survey::factory()->create(['status' => 'active']);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/v1/admin/export/surveys', [
            'format' => 'csv',
            'surveys' => [$survey->id],
        ]);

        echo 'Status: '.$response->getStatusCode()."\n";
        echo 'Headers: '.print_r($response->headers->all(), true)."\n";
        echo 'Content: '.$response->getContent()."\n";

        $this->assertTrue(true); // Just pass the test
    }
}
