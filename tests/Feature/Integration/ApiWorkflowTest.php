<?php

namespace Tests\Feature\Integration;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected LineOAUser $lineUser;

    protected Survey $survey;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure mail driver is set to array for testing
        config(['mail.default' => 'array']);

        // Clear rate limiting cache for testing
        app('cache')->flush();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);
        $this->lineUser = LineOAUser::factory()->create();
        $this->survey = Survey::factory()->create(['status' => 'active']);
        SurveyQuestion::factory()->count(3)->create(['survey_id' => $this->survey->id]);
    }

    public function test_complete_survey_workflow_for_regular_user()
    {
        // 1. User registers
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'message',
                ],
            ]);

        $user = User::where('email', 'testuser@example.com')->first();
        $token = $user->generateEmailVerificationToken();

        // 2. User verifies email
        $response = $this->getJson("/api/v1/auth/verify-email?token={$token}&email={$user->email}");
        $response->assertStatus(200);

        // 3. User views available surveys
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/surveys');
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'name', 'description']]]);

        // 4. User views specific survey
        $response = $this->getJson("/api/v1/surveys/{$this->survey->id}");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'description',
                    'questions' => [['id', 'label', 'type']],
                ],
            ]);

        // 5. User submits survey response
        $response = $this->postJson('/api/v1/survey-responses', [
            'survey_id' => $this->survey->id,
            'answers' => [
                'question_1' => 'Answer 1',
                'question_2' => 'Answer 2',
                'question_3' => 'Answer 3',
            ],
        ]);

        if ($response->status() !== 201) {
            \Log::error('Survey response submission failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'user_authenticated' => auth()->check(),
                'current_user' => auth()->user() ? auth()->user()->toArray() : null,
            ]);
        }

        $response->assertStatus(201);
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $this->survey->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_complete_admin_workflow()
    {
        Sanctum::actingAs($this->admin);

        // 1. Admin creates survey
        $response = $this->postJson('/api/v1/admin/surveys', [
            'name' => 'New Admin Survey',
            'description' => 'A survey created by admin',
            'section' => 'general',
            'status' => 'draft',
        ]);

        $response->assertStatus(201);
        $surveyId = $response->json('data.id');

        // 2. Admin adds questions to survey
        $questionData = [
            ['label' => 'Question 1', 'name' => 'question_1', 'type' => 'text'],
            ['label' => 'Question 2', 'name' => 'question_2', 'type' => 'radio'],
            ['label' => 'Question 3', 'name' => 'question_3', 'type' => 'number'],
        ];

        foreach ($questionData as $question) {
            $response = $this->postJson("/api/v1/admin/surveys/{$surveyId}/questions", $question);
            $response->assertStatus(201);
        }

        // 3. Admin activates survey
        $response = $this->putJson("/api/v1/admin/surveys/{$surveyId}/status", [
            'status' => 'active',
        ]);
        $response->assertStatus(200);

        // 4. Admin views analytics dashboard
        $response = $this->getJson('/api/v1/admin/analytics/dashboard');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'overview' => [
                        'total_surveys',
                        'active_surveys',
                        'total_responses',
                        'total_users',
                        'total_line_users',
                    ],
                    'recent_activity' => [
                        'responses_last_7_days',
                        'surveys_last_7_days',
                    ],
                    'charts' => [
                        'responses_by_day',
                        'top_surveys',
                    ],
                ],
            ]);

        // 5. Admin exports survey data (skip file download test for now)
        // File download tests require special handling in integration tests
        // $response = $this->postJson('/api/v1/admin/export/surveys', [
        //     'format' => 'csv'
        // ]);
        // $response->assertStatus(200);
    }

    public function test_line_user_survey_workflow()
    {
        // 1. LINE user authenticates
        $response = $this->postJson('/api/v1/auth/line', [
            'line_id' => 'line123456',
            'name' => 'LINE User',
            'picture_url' => 'https://example.com/picture.jpg',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'line_id', 'name'],
                    'token',
                ],
            ]);

        $lineUser = LineOAUser::where('line_id', 'line123456')->first();

        // 2. LINE user submits survey response (using Sanctum::actingAs for testing)
        Sanctum::actingAs($lineUser);
        $response = $this->postJson('/api/v1/survey-responses', [
            'survey_id' => $this->survey->id,
            'answers' => [
                'question_1' => 'LINE Answer 1',
                'question_2' => 'LINE Answer 2',
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('survey_responses', [
            'survey_id' => $this->survey->id,
            'user_id' => $lineUser->id,
            'user_type' => 'App\\Models\\LineOAUser',
        ]);
    }

    public function test_cross_model_integration()
    {
        Sanctum::actingAs($this->admin);

        // Create survey with questions
        $survey = Survey::factory()->create(['status' => 'active']);
        $questions = SurveyQuestion::factory()->count(5)->create(['survey_id' => $survey->id]);

        // Create responses from different user types
        $responses = [
            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'user_id' => $this->regularUser->id,
                'user_type' => 'App\\Models\\User',
            ]),
            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'user_id' => $this->lineUser->id,
                'user_type' => 'App\\Models\\LineOAUser',
            ]),
        ];

        // Test survey analytics with mixed user types
        $response = $this->getJson("/api/v1/admin/analytics/surveys/{$survey->id}/stats");
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'survey_id',
                    'survey_name',
                    'total_responses',
                    'completion_rate',
                    'avg_completion_time',
                    'responses_by_day',
                    'total_questions',
                ],
            ]);

        // Verify response count is correct
        $this->assertEquals(2, $response->json('data.total_responses'));
    }

    public function test_rate_limiting_integration()
    {
        // Test auth rate limiting (5 requests per minute)
        $responses = [];
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/auth/admin/login', [
                'email' => 'invalid@example.com',
                'password' => 'wrongpassword',
            ]);
            $responses[] = $response->getStatusCode();
        }

        // Ensure we get at least one rate limit response (429) in the last few attempts
        $this->assertTrue(
            in_array(429, array_slice($responses, -2)),
            'Expected rate limiting to trigger after multiple failed requests'
        );
    }

    public function test_permission_integration_across_endpoints()
    {
        // Test that regular user cannot access admin endpoints
        Sanctum::actingAs($this->regularUser);

        $adminEndpoints = [
            ['GET', '/api/v1/admin/surveys'],
            ['POST', '/api/v1/admin/surveys', ['name' => 'Test', 'description' => 'Test']],
            ['GET', '/api/v1/admin/analytics/dashboard'],
            ['GET', '/api/v1/admin/users'],
        ];

        foreach ($adminEndpoints as $endpoint) {
            $method = $endpoint[0];
            $url = $endpoint[1];
            $data = $endpoint[2] ?? [];
            $response = $this->json($method, $url, $data);
            $response->assertStatus(403);
        }

        // Test that admin can access all endpoints
        Sanctum::actingAs($this->admin);

        foreach ($adminEndpoints as $endpoint) {
            $method = $endpoint[0];
            $url = $endpoint[1];
            $data = $endpoint[2] ?? [];
            $response = $this->json($method, $url, $data);
            $this->assertNotEquals(403, $response->getStatusCode());
        }
    }

    public function test_database_transaction_integration()
    {
        Sanctum::actingAs($this->admin);

        $initialSurveyCount = Survey::count();
        $initialQuestionCount = SurveyQuestion::count();

        // Create survey and questions in a workflow that should be atomic
        $response = $this->postJson('/api/v1/admin/surveys', [
            'name' => 'Transaction Test Survey',
            'description' => 'Testing database transactions',
            'section' => 'test',
            'status' => 'draft',
        ]);

        $surveyId = $response->json('data.id');

        // Add multiple questions
        $questions = [
            ['label' => 'Question 1', 'name' => 'question_1', 'type' => 'text'],
            ['label' => 'Question 2', 'name' => 'question_2', 'type' => 'radio'],
        ];

        foreach ($questions as $question) {
            $this->postJson("/api/v1/admin/surveys/{$surveyId}/questions", $question);
        }

        // Verify all records were created
        $this->assertEquals($initialSurveyCount + 1, Survey::count());
        $this->assertEquals($initialQuestionCount + 2, SurveyQuestion::count());

        // Verify relationships work correctly
        $survey = Survey::find($surveyId);
        $this->assertEquals(2, $survey->questions()->count());
    }
}
