<?php

namespace Tests\Feature\Admin;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_dashboard_displays_correct_metrics()
    {
        // Create test data
        $lineUsers = User::factory()->count(3)->create();
        $surveys = Survey::factory()->count(2)->create();

        foreach ($lineUsers as $lineUser) {
            SurveyResponse::factory()->create([
                'line_id' => $lineUser->line_id,
                'survey_id' => $surveys[0]->id,
            ]);
        }
        SurveyResponse::factory()->count(2)->create([
            'line_id' => $lineUsers[0]->line_id,
            'survey_id' => $surveys[1]->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200)
            ->assertViewHas('totalUsers')
            ->assertViewHas('userGrowth')
            ->assertViewHas('totalSurveys')
            ->assertViewHas('surveyGrowth')
            ->assertViewHas('totalResponses')
            ->assertViewHas('responseGrowth')
            ->assertViewHas('recentActivity');

        // Assert the metrics are correct
        $response->assertSee('3'); // Total users (excluding admin)
        $response->assertSee('2'); // Total surveys
        $response->assertSee('5'); // Total responses
    }

    public function test_dashboard_shows_recent_activity()
    {
        $lineUser = User::factory()->create();
        $survey = Survey::factory()->create(['name' => 'Test Survey']);
        SurveyResponse::factory()->create([
            'line_id' => $lineUser->line_id,
            'survey_id' => $survey->id,
        ]);

        $dashboardResponse = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $dashboardResponse->assertStatus(200)
            ->assertSee($lineUser->name)
            ->assertSee('Test Survey');
    }

    public function test_dashboard_shows_correct_growth_metrics()
    {
        // Create old data (more than 30 days ago)
        $this->travel(-40)->days();
        $oldLineUsers = User::factory()->count(2)->create();
        Survey::factory()->count(2)->create();

        // Return to present
        $this->travel(40)->days();

        // Create new data
        $newLineUsers = User::factory()->count(3)->create();
        Survey::factory()->count(1)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Assert we see the growth in the metrics
        // Total users should be 5 (2 old + 3 new)
        $response->assertSee('5');
        // New users should be 3
        $response->assertSee('3');
        // Total surveys should be 3 (2 old + 1 new)
        $response->assertSee('3');
        // New survey should be 1
        $response->assertSee('1');
    }

    public function test_dashboard_layout_components_are_present()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200)
            ->assertViewIs('admin.dashboard')
            ->assertSee('Dashboard')
            ->assertSee('Users')
            ->assertSee('Surveys')
            ->assertSee('Analytics');
    }

    public function test_dashboard_data_is_properly_paginated()
    {
        $lineUser = User::factory()->create();
        $survey = Survey::factory()->create();

        // Create 15 survey responses
        SurveyResponse::factory()->count(15)->create([
            'line_id' => $lineUser->line_id,
            'survey_id' => $survey->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);

        // Check that pagination is working (default 10 items per page)
        $response->assertViewHas('recentActivity', function ($activity) {
            return $activity->count() <= 10;
        });
    }
}
