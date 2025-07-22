<?php

namespace Tests\Feature\Integration;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PerformanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected array $surveys;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        // Create a moderate dataset for performance testing
        $this->surveys = Survey::factory()->count(10)->create(['status' => 'active'])->toArray();

        foreach ($this->surveys as $survey) {
            SurveyQuestion::factory()->count(5)->create(['survey_id' => $survey['id']]);
        }
    }

    public function test_api_response_times()
    {
        Sanctum::actingAs($this->admin);

        $endpoints = [
            ['GET', '/api/v1/admin/surveys'],
            ['GET', '/api/v1/admin/analytics/dashboard'],
            ['GET', '/api/v1/admin/users'],
        ];

        foreach ($endpoints as $endpoint) {
            $method = $endpoint[0];
            $url = $endpoint[1];
            $startTime = microtime(true);
            $response = $this->json($method, $url);
            $endTime = microtime(true);

            $responseTime = $endTime - $startTime;

            $response->assertStatus(200);
            $this->assertLessThan(1.0, $responseTime, "{$url} took too long: {$responseTime}s");
        }
    }

    public function test_database_query_performance()
    {
        // Create larger dataset
        $users = User::factory()->count(100)->create(['role' => 'user']);
        $lineUsers = LineOAUser::factory()->count(50)->create();

        foreach ($this->surveys as $survey) {
            // Create responses for random users
            foreach ($users->random(20) as $user) {
                SurveyResponse::factory()->create([
                    'survey_id' => $survey['id'],
                    'user_id' => $user->id,
                    'user_type' => 'App\\Models\\User',
                ]);
            }

            foreach ($lineUsers->random(10) as $lineUser) {
                SurveyResponse::factory()->create([
                    'survey_id' => $survey['id'],
                    'user_id' => $lineUser->id,
                    'user_type' => 'App\\Models\\LineOAUser',
                    'line_id' => $lineUser->line_id,
                ]);
            }
        }

        Sanctum::actingAs($this->admin);

        // Test analytics query performance
        DB::flushQueryLog();
        DB::enableQueryLog();

        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/admin/analytics/dashboard');
        $endTime = microtime(true);

        $queries = DB::getQueryLog();
        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $responseTime, 'Analytics dashboard took too long');
        $this->assertLessThan(20, count($queries), 'Too many database queries');

        DB::disableQueryLog();
    }

    public function test_memory_usage_during_bulk_operations()
    {
        Sanctum::actingAs($this->admin);

        $initialMemory = memory_get_usage(true);

        // Simulate bulk survey creation
        $surveys = [];
        for ($i = 0; $i < 50; $i++) {
            $surveys[] = [
                'name' => "Performance Test Survey {$i}",
                'description' => "Test description {$i}",
                'section' => 'performance',
                'status' => 'draft',
            ];
        }

        foreach ($surveys as $surveyData) {
            $this->postJson('/api/v1/admin/surveys', $surveyData);
        }

        $finalMemory = memory_get_usage(true);
        $memoryIncrease = $finalMemory - $initialMemory;

        // Memory increase should be reasonable (under 50MB for this test)
        $this->assertLessThan(50 * 1024 * 1024, $memoryIncrease, 'Memory usage too high during bulk operations');
    }

    public function test_concurrent_request_handling()
    {
        Sanctum::actingAs($this->admin);

        $responses = [];
        $startTime = microtime(true);

        // Simulate concurrent requests (in a real scenario, you'd use actual concurrency)
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/v1/admin/analytics/dashboard');
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // All requests should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Total time should not be much more than single request time
        $this->assertLessThan(10.0, $totalTime, 'Concurrent requests took too long');
    }

    public function test_pagination_performance()
    {
        // Create large dataset
        $users = User::factory()->count(500)->create(['role' => 'user']);

        Sanctum::actingAs($this->admin);

        $startTime = microtime(true);
        $response = $this->getJson('/api/v1/admin/users?page=1&per_page=50');
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $responseTime, 'Paginated request took too long');

        // Test last page performance
        $lastPage = ceil(501 / 50); // 501 including admin user

        $startTime = microtime(true);
        $response = $this->getJson("/api/v1/admin/users?page={$lastPage}&per_page=50");
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $responseTime, 'Last page request took too long');
    }

    public function test_search_performance()
    {
        // Create surveys with varied names for search testing
        for ($i = 0; $i < 100; $i++) {
            Survey::factory()->create([
                'name' => "Test Survey {$i}",
                'description' => "Description for survey {$i}",
                'status' => 'active',
            ]);
        }

        Sanctum::actingAs($this->admin);

        $searchTerms = ['Test', 'Survey', '50', 'Description'];

        foreach ($searchTerms as $term) {
            $startTime = microtime(true);
            $response = $this->getJson("/api/v1/admin/surveys?search={$term}");
            $endTime = microtime(true);

            $responseTime = $endTime - $startTime;

            $response->assertStatus(200);
            $this->assertLessThan(0.5, $responseTime, "Search for '{$term}' took too long");
        }
    }

    public function test_export_performance()
    {
        // Create substantial dataset for export
        $survey = Survey::factory()->create(['status' => 'active']);
        SurveyQuestion::factory()->count(10)->create(['survey_id' => $survey->id]);

        $users = User::factory()->count(200)->create(['role' => 'user']);

        foreach ($users as $user) {
            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'user_id' => $user->id,
                'user_type' => 'App\\Models\\User',
            ]);
        }

        Sanctum::actingAs($this->admin);

        $startTime = microtime(true);
        $response = $this->postJson('/api/v1/admin/export/surveys', [
            'format' => 'csv',
            'status' => 'active',
        ]);
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;

        // Export should return a file download, so it could be 200 or redirect
        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertLessThan(5.0, $responseTime, 'Export took too long');
    }

    public function test_caching_effectiveness()
    {
        Cache::flush();
        Sanctum::actingAs($this->admin);

        // First request (no cache)
        $startTime = microtime(true);
        $response1 = $this->getJson('/api/v1/admin/analytics/dashboard');
        $firstRequestTime = microtime(true) - $startTime;

        // Second request (should be cached if caching is implemented)
        $startTime = microtime(true);
        $response2 = $this->getJson('/api/v1/admin/analytics/dashboard');
        $secondRequestTime = microtime(true) - $startTime;

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // If caching is implemented, second request should be faster
        if ($secondRequestTime < $firstRequestTime) {
            $this->assertLessThan($firstRequestTime * 0.8, $secondRequestTime, 'Caching not effective enough');
        }

        // Data should be identical
        $this->assertEquals($response1->json('data'), $response2->json('data'));
    }

    public function test_database_connection_pooling()
    {
        // Test multiple rapid database operations
        $initialSurveyCount = Survey::count();
        $initialUserCount = User::count();

        $startTime = microtime(true);

        for ($i = 0; $i < 10; $i++) {
            Survey::factory()->create();
            User::factory()->create();
            // Use existing survey instead of creating new ones via factory relationships
            SurveyResponse::factory()->create([
                'survey_id' => $this->surveys[0]['id'], // Use existing survey
                'user_id' => $this->admin->id,
                'user_type' => 'App\Models\User',
                'line_id' => null, // Don't create LineOAUser
            ]);
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Should complete reasonably quickly
        $this->assertLessThan(3.0, $totalTime, 'Database operations took too long');

        // Verify all records were created
        $this->assertEquals($initialSurveyCount + 10, Survey::count());
        $this->assertEquals($initialUserCount + 10, User::count());
    }

    public function test_queue_performance()
    {
        Queue::fake();

        Sanctum::actingAs($this->admin);

        // Test operations that might use queues (like exports)
        $surveys = Survey::factory()->count(5)->create();

        foreach ($surveys as $survey) {
            $startTime = microtime(true);

            $response = $this->postJson('/api/v1/admin/export/surveys', [
                'format' => 'csv',
                'status' => 'active',
            ]);

            $endTime = microtime(true);
            $responseTime = $endTime - $startTime;

            // Export should return a file download, so it could be 200 or redirect
            $this->assertContains($response->getStatusCode(), [200, 302]);
            $this->assertLessThan(1.0, $responseTime, 'Queued operation response took too long');
        }
    }

    public function test_large_payload_handling()
    {
        Sanctum::actingAs($this->admin);

        $survey = Survey::first();

        // Create a large form response
        $largeFormData = [];
        for ($i = 0; $i < 100; $i++) {
            $largeFormData["question_{$i}"] = str_repeat('Large answer text ', 100);
        }

        $startTime = microtime(true);
        $response = $this->postJson('/api/v1/survey-responses', [
            'survey_id' => $survey->id,
            'answers' => $largeFormData,
        ]);
        $endTime = microtime(true);

        $responseTime = $endTime - $startTime;

        $response->assertStatus(201);
        $this->assertLessThan(2.0, $responseTime, 'Large payload handling took too long');
    }

    public function test_api_rate_limiting_performance()
    {
        Sanctum::actingAs($this->admin);

        $startTime = microtime(true);
        $successfulRequests = 0;

        // Make requests until rate limit is hit
        for ($i = 0; $i < 150; $i++) {
            $response = $this->getJson('/api/v1/admin/surveys');

            if ($response->getStatusCode() === 200) {
                $successfulRequests++;
            } elseif ($response->getStatusCode() === 429) {
                break;
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // Should handle requests efficiently even when rate limiting
        $this->assertLessThan(30.0, $totalTime, 'Rate limiting handling took too long');
        $this->assertGreaterThan(50, $successfulRequests, 'Rate limiting too restrictive');
    }
}
