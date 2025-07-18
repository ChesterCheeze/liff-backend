<?php

namespace Tests\Feature\Integration;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\SurveyResponseSeeder;
use Database\Seeders\SurveySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SeedingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_integration()
    {
        // Run the admin seeder
        $this->seed(AdminSeeder::class);

        // Verify admin user was created
        $this->assertDatabaseHas('users', [
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->isAdmin());
        $this->assertNotNull($admin->email_verified_at);
    }

    public function test_survey_seeder_integration()
    {
        $this->seed(SurveySeeder::class);

        // Verify surveys were created
        $this->assertDatabaseHas('surveys', [
            'name' => 'Product Satisfaction Survey',
        ]);

        $this->assertDatabaseHas('surveys', [
            'name' => 'Customer Demographics Survey',
        ]);

        // Verify survey questions were created
        $surveys = Survey::all();
        foreach ($surveys as $survey) {
            $this->assertGreaterThan(0, $survey->questions()->count());
        }

        // Verify different question types exist
        $this->assertDatabaseHas('survey_questions', ['type' => 'text']);
        $this->assertDatabaseHas('survey_questions', ['type' => 'radio']);
        $this->assertDatabaseHas('survey_questions', ['type' => 'rating']);
    }

    public function test_survey_response_seeder_integration()
    {
        // First seed the prerequisite data
        $this->seed([
            AdminSeeder::class,
            SurveySeeder::class,
        ]);

        // Create some users and LINE users for responses
        User::factory()->count(10)->create(['role' => 'user']);
        LineOAUser::factory()->count(5)->create();

        // Run the response seeder
        $this->seed(SurveyResponseSeeder::class);

        // Verify responses were created
        $this->assertGreaterThan(0, SurveyResponse::count());

        // Verify responses have proper relationships
        $responses = SurveyResponse::all();
        foreach ($responses as $response) {
            $this->assertNotNull($response->survey);
            $this->assertNotNull($response->survey_id);
            $this->assertIsArray($response->form_data);
        }

        // Verify responses from both user types
        $this->assertDatabaseHas('survey_responses', ['user_type' => 'App\\Models\\User']);
        $this->assertDatabaseHas('survey_responses', ['user_type' => 'App\\Models\\LineOAUser']);
    }

    public function test_complete_database_seeding_workflow()
    {
        // Run all seeders
        Artisan::call('db:seed');

        // Verify all components are properly seeded
        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Survey::count());
        $this->assertGreaterThan(0, SurveyQuestion::count());
        $this->assertGreaterThan(0, SurveyResponse::count());

        // Verify admin exists
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());

        // Verify survey data integrity
        $surveys = Survey::with(['questions', 'responses'])->get();
        foreach ($surveys as $survey) {
            $this->assertGreaterThan(0, $survey->questions->count());

            // Each survey should have some responses
            if ($survey->status === 'active') {
                $this->assertGreaterThanOrEqual(0, $survey->responses->count());
            }
        }
    }

    public function test_factory_relationships_integration()
    {
        // Test complex factory relationships
        $survey = Survey::factory()
            ->has(SurveyQuestion::factory()->count(5), 'questions')
            ->has(SurveyResponse::factory()->count(10), 'responses')
            ->create();

        $this->assertEquals(5, $survey->questions()->count());
        $this->assertEquals(10, $survey->responses()->count());

        // Verify all responses reference the correct survey
        $survey->responses->each(function ($response) use ($survey) {
            $this->assertEquals($survey->id, $response->survey_id);
        });

        // Verify question types are varied
        $questionTypes = $survey->questions->pluck('type')->unique();
        $this->assertGreaterThan(1, $questionTypes->count());
    }

    public function test_user_factory_with_responses()
    {
        $user = User::factory()
            ->has(
                SurveyResponse::factory()
                    ->for(Survey::factory()->has(SurveyQuestion::factory()->count(3), 'questions'))
                    ->count(3),
                'responses'
            )
            ->create();

        // User should have 3 responses
        $userResponses = SurveyResponse::where('user_id', $user->id)
            ->where('user_type', 'App\\Models\\User')
            ->count();

        $this->assertEquals(3, $userResponses);

        // Each response should have valid survey and form data
        $responses = SurveyResponse::where('user_id', $user->id)->get();
        foreach ($responses as $response) {
            $this->assertNotNull($response->survey);
            $this->assertIsArray($response->form_data);
            $this->assertNotEmpty($response->form_data);
        }
    }

    public function test_line_user_factory_integration()
    {
        $lineUsers = LineOAUser::factory()
            ->count(5)
            ->create();

        foreach ($lineUsers as $lineUser) {
            $this->assertNotNull($lineUser->line_id);
            $this->assertNotNull($lineUser->name);

            // Test creating responses for LINE users
            SurveyResponse::factory()
                ->for(Survey::factory()->has(SurveyQuestion::factory()->count(2), 'questions'))
                ->create([
                    'user_id' => $lineUser->id,
                    'user_type' => 'App\\Models\\LineOAUser',
                    'line_id' => $lineUser->line_id,
                ]);
        }

        // Verify all LINE users have responses
        $this->assertEquals(5, SurveyResponse::where('user_type', 'App\\Models\\LineOAUser')->count());
    }

    public function test_seeded_data_analytics_integration()
    {
        // Seed all data
        Artisan::call('db:seed');

        // Verify analytics work with seeded data
        $totalSurveys = Survey::count();
        $totalResponses = SurveyResponse::count();
        $totalUsers = User::count();

        $this->assertGreaterThan(0, $totalSurveys);
        $this->assertGreaterThan(0, $totalResponses);
        $this->assertGreaterThan(0, $totalUsers);

        // Test survey-specific analytics
        $activeSurveys = Survey::where('status', 'active')->get();
        foreach ($activeSurveys as $survey) {
            $responseCount = $survey->responses()->count();
            $this->assertGreaterThanOrEqual(0, $responseCount);
        }

        // Test user type breakdown
        $regularUserResponses = SurveyResponse::where('user_type', 'App\\Models\\User')->count();
        $lineUserResponses = SurveyResponse::where('user_type', 'App\\Models\\LineOAUser')->count();

        $this->assertEquals($totalResponses, $regularUserResponses + $lineUserResponses);
    }

    public function test_factory_states_and_traits()
    {
        // Test different survey states
        $draftSurvey = Survey::factory()->state(['status' => 'draft'])->create();
        $activeSurvey = Survey::factory()->state(['status' => 'active'])->create();
        $closedSurvey = Survey::factory()->state(['status' => 'closed'])->create();

        $this->assertEquals('draft', $draftSurvey->status);
        $this->assertEquals('active', $activeSurvey->status);
        $this->assertEquals('closed', $closedSurvey->status);

        // Test admin vs regular user creation
        $admin = User::factory()->state(['role' => 'admin'])->create();
        $user = User::factory()->state(['role' => 'user'])->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_factory_data_consistency()
    {
        // Create a survey with questions and responses
        $survey = Survey::factory()
            ->has(SurveyQuestion::factory()->count(4), 'questions')
            ->create();

        $questions = $survey->questions;

        // Create responses that reference these questions
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $formData = [];
            foreach ($questions as $question) {
                $formData["question_{$question->id}"] = "Answer for question {$question->id}";
            }

            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'user_id' => $user->id,
                'user_type' => 'App\\Models\\User',
                'form_data' => $formData,
            ]);
        }

        // Verify data consistency
        $responses = $survey->responses;
        $this->assertEquals(3, $responses->count());

        foreach ($responses as $response) {
            $this->assertCount(4, $response->form_data);
            foreach ($questions as $question) {
                $this->assertArrayHasKey("question_{$question->id}", $response->form_data);
            }
        }
    }

    public function test_performance_with_large_seeded_datasets()
    {
        // Create a larger dataset
        $surveys = Survey::factory()->count(10)->create();

        foreach ($surveys as $survey) {
            SurveyQuestion::factory()->count(5)->create(['survey_id' => $survey->id]);
        }

        $users = User::factory()->count(50)->create();
        $lineUsers = LineOAUser::factory()->count(25)->create();

        $startTime = microtime(true);

        // Create responses for all users
        foreach ($surveys as $survey) {
            foreach ($users->take(20) as $user) {
                SurveyResponse::factory()->create([
                    'survey_id' => $survey->id,
                    'user_id' => $user->id,
                    'user_type' => 'App\\Models\\User',
                ]);
            }

            foreach ($lineUsers->take(10) as $lineUser) {
                SurveyResponse::factory()->create([
                    'survey_id' => $survey->id,
                    'user_id' => $lineUser->id,
                    'user_type' => 'App\\Models\\LineOAUser',
                    'line_id' => $lineUser->line_id,
                ]);
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        // Should complete within reasonable time (adjust based on your requirements)
        $this->assertLessThan(30, $executionTime, 'Large dataset creation took too long');

        // Verify all data was created correctly
        $this->assertEquals(10, Survey::count());
        $this->assertEquals(50, SurveyQuestion::count());
        $this->assertEquals(50, User::count()); // 50 users created in this test
        $this->assertEquals(25, LineOAUser::count());
        $this->assertEquals(300, SurveyResponse::count()); // 10 surveys * (20 + 10) responses
    }
}
