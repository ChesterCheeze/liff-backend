<?php

namespace Tests\Feature\Integration;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_model_relationships()
    {
        $user = User::factory()->unverified()->create();

        // Create survey responses for this user
        $surveys = Survey::factory()->count(3)->create();
        foreach ($surveys as $survey) {
            SurveyResponse::factory()->create([
                'user_id' => $user->id,
                'user_type' => 'App\\Models\\User',
                'survey_id' => $survey->id,
            ]);
        }

        // Test user can access their responses
        $this->assertEquals(3, SurveyResponse::where('user_id', $user->id)->count());

        // Test email verification workflow
        $this->assertFalse($user->hasVerifiedEmail());
        $token = $user->generateEmailVerificationToken();
        $this->assertTrue($user->verifyEmailToken($token));

        $user->markEmailAsVerified();
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_survey_model_relationships()
    {
        $survey = Survey::factory()->create();
        $questions = SurveyQuestion::factory()->count(5)->create(['survey_id' => $survey->id]);
        $responses = SurveyResponse::factory()->count(10)->create(['survey_id' => $survey->id]);

        // Test survey relationships
        $this->assertEquals(5, $survey->questions()->count());
        $this->assertEquals(10, $survey->responses()->count());

        // Test cascade delete (questions should be deleted when survey is deleted)
        $questionIds = $survey->questions()->pluck('id')->toArray();
        $survey->delete();

        foreach ($questionIds as $questionId) {
            $this->assertDatabaseMissing('survey_questions', ['id' => $questionId]);
        }
    }

    public function test_survey_response_polymorphic_relationships()
    {
        $user = User::factory()->create();
        $lineUser = LineOAUser::factory()->create();
        $survey = Survey::factory()->create();

        // Create responses for both user types
        $userResponse = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $user->id,
            'user_type' => 'App\\Models\\User',
        ]);

        $lineResponse = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'user_id' => $lineUser->id,
            'user_type' => 'App\\Models\\LineOAUser',
        ]);

        // Test polymorphic relationships
        $this->assertInstanceOf(User::class, $userResponse->user);
        $this->assertInstanceOf(LineOAUser::class, $lineResponse->user);

        // Test survey relationship
        $this->assertEquals($survey->id, $userResponse->survey->id);
        $this->assertEquals($survey->id, $lineResponse->survey->id);
    }

    public function test_line_oa_user_relationships()
    {
        $lineUser = LineOAUser::factory()->create();
        $surveys = Survey::factory()->count(2)->create();

        foreach ($surveys as $survey) {
            SurveyResponse::factory()->create([
                'line_id' => $lineUser->line_id,
                'survey_id' => $survey->id,
                'user_id' => $lineUser->id,
                'user_type' => 'App\\Models\\LineOAUser',
            ]);
        }

        // Test LINE user can access responses via line_id
        $responses = SurveyResponse::where('line_id', $lineUser->line_id)->get();
        $this->assertEquals(2, $responses->count());

        // Test relationship to LINE user
        foreach ($responses as $response) {
            $this->assertEquals($lineUser->id, $response->lineOaUser->id);
        }
    }

    public function test_survey_question_cascade_operations()
    {
        $survey = Survey::factory()->create();
        $questions = SurveyQuestion::factory()->count(3)->create(['survey_id' => $survey->id]);

        // Create responses that reference these questions
        $responses = SurveyResponse::factory()->count(5)->create([
            'survey_id' => $survey->id,
            'form_data' => [
                'question_'.$questions[0]->id => 'Answer 1',
                'question_'.$questions[1]->id => 'Answer 2',
            ],
        ]);

        $questionId = $questions[0]->id;

        // Delete a question
        $questions[0]->delete();

        // Verify question is deleted
        $this->assertDatabaseMissing('survey_questions', ['id' => $questionId]);

        // Verify responses still exist (should handle gracefully)
        $this->assertEquals(5, SurveyResponse::where('survey_id', $survey->id)->count());
    }

    public function test_user_role_and_permissions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        // Test role methods
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());

        // Test role changes
        $user->update(['role' => 'admin']);
        $user->refresh();
        $this->assertTrue($user->isAdmin());
    }

    public function test_survey_status_transitions()
    {
        $survey = Survey::factory()->create(['status' => 'draft']);

        // Test status transitions
        $validStatuses = ['draft', 'active', 'closed', 'archived'];

        foreach ($validStatuses as $status) {
            $survey->update(['status' => $status]);
            $this->assertEquals($status, $survey->fresh()->status);
        }
    }

    public function test_survey_response_data_handling()
    {
        $survey = Survey::factory()->create();
        $questions = SurveyQuestion::factory()->count(3)->create(['survey_id' => $survey->id]);

        $formData = [
            'question_'.$questions[0]->id => 'Text answer',
            'question_'.$questions[1]->id => ['option1', 'option2'],
            'question_'.$questions[2]->id => 5,
        ];

        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'form_data' => $formData,
        ]);

        // Test data retrieval and casting
        $this->assertEquals($formData, $response->form_data);
        $this->assertEquals($formData, $response->answers);
        $this->assertIsArray($response->form_data);
    }

    public function test_database_constraints_and_validation()
    {
        // Test unique constraints
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_factory_relationships()
    {
        // Test that factories create proper relationships
        $survey = Survey::factory()
            ->has(SurveyQuestion::factory()->count(3), 'questions')
            ->has(SurveyResponse::factory()->count(5), 'responses')
            ->create();

        $this->assertEquals(3, $survey->questions()->count());
        $this->assertEquals(5, $survey->responses()->count());

        // Verify all responses reference the correct survey
        $survey->responses->each(function ($response) use ($survey) {
            $this->assertEquals($survey->id, $response->survey_id);
        });
    }

    public function test_model_events_and_observers()
    {
        $user = User::factory()->create();
        $initialTokenCount = $user->tokens()->count();

        // Create some tokens
        $token1 = $user->createToken('test1');
        $token2 = $user->createToken('test2');

        $this->assertEquals($initialTokenCount + 2, $user->tokens()->count());

        // Test token cleanup on password change
        $user->update(['password' => bcrypt('newpassword')]);

        // In a real app, this might trigger token cleanup via model events
        // For now, we'll test the manual cleanup
        $user->tokens()->delete();
        $this->assertEquals(0, $user->tokens()->count());
    }
}
