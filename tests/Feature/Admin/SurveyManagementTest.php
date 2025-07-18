<?php

namespace Tests\Feature\Admin;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_surveys_list()
    {
        $survey = Survey::factory()->create([
            'name' => 'Test Survey',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.index'));

        $response->assertStatus(200)
            ->assertViewIs('admin.surveys.index')
            ->assertSee('Test Survey')
            ->assertSee('published');
    }

    public function test_admin_can_search_surveys()
    {
        Survey::factory()->create(['name' => 'Customer Feedback']);
        Survey::factory()->create(['name' => 'Employee Survey']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.index', ['search' => 'Customer']));

        $response->assertStatus(200)
            ->assertSee('Customer Feedback')
            ->assertDontSee('Employee Survey');
    }

    public function test_admin_can_filter_surveys_by_status()
    {
        Survey::factory()->create([
            'name' => 'Published Survey',
            'status' => 'published',
        ]);
        Survey::factory()->create([
            'name' => 'Draft Survey',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.index', ['status' => 'published']));

        $response->assertStatus(200)
            ->assertSee('Published Survey')
            ->assertDontSee('Draft Survey');
    }

    public function test_admin_can_view_survey_details()
    {
        $survey = Survey::factory()->create([
            'name' => 'Detailed Survey',
            'description' => 'Survey Description',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.show', $survey));

        $response->assertStatus(200)
            ->assertViewIs('admin.surveys.show')
            ->assertSee('Detailed Survey')
            ->assertSee('Survey Description');
    }

    public function test_admin_can_view_survey_responses()
    {
        $survey = Survey::factory()->create();
        $lineUser = User::factory()->create();
        $response = SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'line_id' => $lineUser->line_id,
        ]);

        $adminResponse = $this->actingAs($this->admin)
            ->get(route('admin.surveys.responses', $survey));

        $adminResponse->assertStatus(200)
            ->assertViewIs('admin.surveys.responses')
            ->assertSee($lineUser->name);
    }

    public function test_admin_can_delete_survey()
    {
        $survey = Survey::factory()->create();
        $responses = SurveyResponse::factory()->count(3)->create([
            'survey_id' => $survey->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.surveys.index'))
            ->withToken(csrf_token())
            ->delete(route('admin.surveys.destroy', $survey));

        $response->assertRedirect(route('admin.surveys.index'));

        // Verify survey and its responses are deleted
        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
        foreach ($responses as $surveyResponse) {
            $this->assertDatabaseMissing('survey_responses', ['id' => $surveyResponse->id]);
        }
    }

    public function test_survey_statistics_are_accurate()
    {
        $survey = Survey::factory()->create();

        // Create some completed responses
        SurveyResponse::factory()->count(3)->create([
            'survey_id' => $survey->id,
            'completed_at' => now(),
        ]);

        // Create some incomplete responses
        SurveyResponse::factory()->count(2)->create([
            'survey_id' => $survey->id,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.show', $survey));

        $response->assertStatus(200);

        // Check response statistics
        $responseStats = $response->viewData('responseStats');
        $this->assertEquals(5, $responseStats['total']); // Total responses
        $this->assertEquals(60, $responseStats['completion_rate']); // 3 out of 5 = 60%
    }

    public function test_survey_responses_are_paginated()
    {
        $survey = Survey::factory()->create();
        SurveyResponse::factory()->count(15)->create([
            'survey_id' => $survey->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.responses', $survey));

        $response->assertStatus(200);

        // Verify pagination (default 10 per page)
        $this->assertEquals(10, $response->viewData('responses')->count());
    }

    public function test_survey_list_shows_response_counts()
    {
        $survey = Survey::factory()->create();
        SurveyResponse::factory()->count(5)->create([
            'survey_id' => $survey->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.surveys.index'));

        $response->assertStatus(200)
            ->assertSee('5'); // Should show 5 responses
    }
}
