<?php

namespace Tests\Feature\Realtime;

use App\Events\SurveyResponseCreated;
use App\Events\SurveyStatusChanged;
use App\Events\SurveyUpdated;
use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WebSocketBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    private $lineUser;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->lineUser = LineOAUser::factory()->create();
    }

    public function test_survey_updated_event_dispatched_on_create()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $surveyData = [
            'name' => 'New Survey',
            'description' => 'Survey Description',
            'status' => 'draft',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/surveys', $surveyData);

        $response->assertStatus(201);

        Event::assertDispatched(SurveyUpdated::class, function ($event) {
            return $event->survey->name === 'New Survey' &&
                   $event->action === 'created';
        });
    }

    public function test_survey_updated_event_dispatched_on_update()
    {
        $survey = Survey::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'name' => 'Updated Survey Name',
            'description' => 'Updated Description',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson('/api/v1/admin/surveys/'.$survey->id, $updateData);

        $response->assertStatus(200);

        Event::assertDispatched(SurveyUpdated::class, function ($event) use ($survey) {
            return $event->survey->id === $survey->id &&
                   $event->action === 'updated';
        });
    }

    public function test_survey_updated_event_dispatched_on_delete()
    {
        $survey = Survey::factory()->create();
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->deleteJson('/api/v1/admin/surveys/'.$survey->id);

        $response->assertStatus(200);

        Event::assertDispatched(SurveyUpdated::class, function ($event) use ($survey) {
            return $event->survey->id === $survey->id &&
                   $event->action === 'deleted';
        });
    }

    public function test_survey_status_changed_event_dispatched()
    {
        $survey = Survey::factory()->create(['status' => 'draft']);
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson('/api/v1/admin/surveys/'.$survey->id.'/status', [
            'status' => 'published',
        ]);

        $response->assertStatus(200);

        Event::assertDispatched(SurveyStatusChanged::class, function ($event) use ($survey) {
            return $event->survey->id === $survey->id &&
                   $event->oldStatus === 'draft' &&
                   $event->newStatus === 'published';
        });
    }

    public function test_survey_response_created_event_dispatched()
    {
        $survey = Survey::factory()->create(['status' => 'published']);
        $token = $this->lineUser->createToken('test-token')->plainTextToken;

        $responseData = [
            'survey_id' => $survey->id,
            'answers' => json_encode(['q1' => 'answer1', 'q2' => 'answer2']),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/survey-responses', $responseData);

        $response->assertStatus(201);

        Event::assertDispatched(SurveyResponseCreated::class, function ($event) use ($survey) {
            return $event->surveyResponse->survey_id === $survey->id;
        });
    }

    public function test_broadcast_authentication_endpoint_works()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-surveys',
        ]);

        $response->assertStatus(200);
    }

    public function test_broadcast_authentication_fails_for_unauthorized_user()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-admin.surveys',
        ]);

        $response->assertStatus(403);
    }

    public function test_survey_updated_event_contains_correct_data()
    {
        Event::fake([SurveyUpdated::class]);

        $survey = Survey::factory()->create([
            'name' => 'Test Survey',
            'status' => 'draft',
        ]);

        $event = new SurveyUpdated($survey, 'created');

        $this->assertEquals($survey->id, $event->survey->id);
        $this->assertEquals('created', $event->action);
        $this->assertEquals('Test Survey', $event->survey->name);
    }

    public function test_survey_status_changed_event_contains_correct_data()
    {
        Event::fake([SurveyStatusChanged::class]);

        $survey = Survey::factory()->create(['status' => 'published']);

        $event = new SurveyStatusChanged($survey, 'draft', 'published');

        $this->assertEquals($survey->id, $event->survey->id);
        $this->assertEquals('draft', $event->oldStatus);
        $this->assertEquals('published', $event->newStatus);
    }

    public function test_survey_response_created_event_contains_correct_data()
    {
        Event::fake([SurveyResponseCreated::class]);

        $survey = Survey::factory()->create();
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'line_id' => $this->lineUser->line_id,
        ]);

        $event = new SurveyResponseCreated($response);

        $this->assertEquals($response->id, $event->surveyResponse->id);
        $this->assertEquals($survey->id, $event->surveyResponse->survey_id);
        $this->assertEquals($this->lineUser->line_id, $event->surveyResponse->line_id);
    }

    public function test_events_are_not_dispatched_when_operation_fails()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $invalidSurveyData = [
            'name' => '',
            'description' => '',
            'status' => 'invalid_status',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/surveys', $invalidSurveyData);

        $response->assertStatus(422);

        Event::assertNotDispatched(SurveyUpdated::class);
    }

    public function test_multiple_events_can_be_dispatched_in_sequence()
    {
        $survey = Survey::factory()->create(['status' => 'draft']);
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $updateData = ['name' => 'Updated Name'];
        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->putJson('/api/v1/admin/surveys/'.$survey->id, $updateData);

        $statusData = ['status' => 'published'];
        $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->putJson('/api/v1/admin/surveys/'.$survey->id.'/status', $statusData);

        Event::assertDispatched(SurveyUpdated::class);
        Event::assertDispatched(SurveyStatusChanged::class);

        Event::assertDispatchedTimes(SurveyUpdated::class, 1);
        Event::assertDispatchedTimes(SurveyStatusChanged::class, 1);
    }

    public function test_broadcasting_channels_are_properly_configured()
    {
        $survey = Survey::factory()->create();
        $event = new SurveyUpdated($survey, 'created');

        $channels = $event->broadcastOn();

        $this->assertIsArray($channels);
        $this->assertNotEmpty($channels);
    }

    public function test_survey_response_event_includes_survey_relationship()
    {
        $survey = Survey::factory()->create(['name' => 'Test Survey']);
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'line_id' => $this->lineUser->line_id,
        ]);

        $event = new SurveyResponseCreated($response);

        $this->assertNotNull($event->surveyResponse->survey);
        $this->assertEquals('Test Survey', $event->surveyResponse->survey->name);
    }
}
