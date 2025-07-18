<?php

namespace Tests\Feature\Admin;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected $seeder = null;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user first
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_analytics_dashboard()
    {
        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $response->assertStatus(200)
            ->assertViewIs('admin.analytics.index');
    }

    public function test_analytics_shows_correct_overall_stats()
    {
        // Create 3 LineOA users with user role
        $users = LineOAUser::factory()->count(3)->create(['role' => 'user']);

        // Create 1 survey and 5 responses using existing users
        $survey = Survey::factory()->create();

        foreach (range(1, 5) as $i) {
            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'line_id' => $users->random()->line_id,
            ]);
        }

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $stats = $response->viewData('stats');

        $this->assertEquals(3, $stats['total_users']);
        $this->assertEquals(1, $stats['total_surveys']);
        $this->assertEquals(5, $stats['total_responses']);
    }

    public function test_analytics_shows_correct_period_stats()
    {
        $now = now();

        // Create old data (more than 30 days ago)
        LineOAUser::factory()->count(2)->create([
            'role' => 'user',
            'created_at' => $now->copy()->subDays(40),
        ]);
        Survey::factory()->count(2)->create([
            'created_at' => $now->copy()->subDays(40),
        ]);

        // Create new data
        LineOAUser::factory()->count(3)->create([
            'role' => 'user',
            'created_at' => $now->copy()->subDays(15),
        ]);
        Survey::factory()->create([
            'created_at' => $now->copy()->subDays(15),
        ]);

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics', ['period' => '30']));

        $stats = $response->viewData('stats');

        $this->assertEquals(3, $stats['new_users']);
        $this->assertEquals(1, $stats['new_surveys']);
    }

    public function test_response_trends_are_correctly_calculated()
    {
        $survey = Survey::factory()->create();
        $user = LineOAUser::factory()->create(['role' => 'user']);

        $now = now();

        // Create responses over multiple days
        foreach (range(1, 3) as $day) {
            SurveyResponse::factory()->count(2)->create([
                'survey_id' => $survey->id,
                'line_id' => $user->line_id,
                'created_at' => $now->copy()->subDays($day),
            ]);
        }

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $trends = $response->viewData('responseTrends');

        // Should have 3 days of data
        $this->assertEquals(3, $trends->count());
        // Each day should have 2 responses
        $this->assertEquals(2, $trends->first()['count']);
    }

    public function test_survey_completion_rates_are_accurate()
    {
        $survey = Survey::factory()->create();
        $user = LineOAUser::factory()->create(['role' => 'user']);

        // Create 3 completed and 2 incomplete responses
        SurveyResponse::factory()->count(3)->create([
            'survey_id' => $survey->id,
            'line_id' => $user->line_id,
            'completed_at' => now(),
        ]);
        SurveyResponse::factory()->count(2)->create([
            'survey_id' => $survey->id,
            'line_id' => $user->line_id,
            'completed_at' => null,
        ]);

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $completionRates = $response->viewData('completionRates');
        $surveyRate = $completionRates->first();

        $this->assertEquals(60, $surveyRate['rate']); // 3 out of 5 = 60%
    }

    public function test_user_engagement_metrics_are_correct()
    {
        $users = LineOAUser::factory()->count(3)->create(['role' => 'user']);
        $survey = Survey::factory()->create();

        $now = now();

        // Create responses for each user
        foreach ($users as $user) {
            SurveyResponse::factory()->count(2)->create([
                'line_id' => $user->line_id,
                'survey_id' => $survey->id,
                'created_at' => $now->copy()->subDays(15),
                'completed_at' => $now->copy()->subDays(15)->addMinutes(30),
            ]);
        }

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $engagement = $response->viewData('userEngagement');

        $this->assertEquals(3, $engagement['active_users']);
        $this->assertEquals(2.0, $engagement['avg_responses']);
        $this->assertEquals(30, $engagement['avg_completion_time']);
    }

    public function test_popular_surveys_are_correctly_ranked()
    {
        $surveys = Survey::factory()->count(3)->create();
        $user = LineOAUser::factory()->create(['role' => 'user']);

        // Create different numbers of responses for each survey
        SurveyResponse::factory()->count(5)->create([
            'survey_id' => $surveys[0]->id,
            'line_id' => $user->line_id,
        ]);
        SurveyResponse::factory()->count(3)->create([
            'survey_id' => $surveys[1]->id,
            'line_id' => $user->line_id,
        ]);
        SurveyResponse::factory()->count(1)->create([
            'survey_id' => $surveys[2]->id,
            'line_id' => $user->line_id,
        ]);

        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));

        $popularSurveys = $response->viewData('popularSurveys');

        // First survey should have the most responses
        $this->assertEquals(5, $popularSurveys->first()['responses']);
        // Surveys should be in descending order by response count
        $this->assertEquals(3, $popularSurveys[1]['responses']);
        $this->assertEquals(1, $popularSurveys[2]['responses']);
    }

    public function test_analytics_period_filter_works()
    {
        $periods = [7, 30, 90];

        foreach ($periods as $period) {
            $this->withoutVite();
            $response = $this->actingAs($this->admin, 'admin')
                ->get(route('admin.analytics', ['period' => $period]));

            $response->assertStatus(200)
                ->assertViewHas('period', $period);
        }
    }

    public function test_analytics_data_is_updated_in_real_time()
    {
        // Create initial data
        $users = LineOAUser::factory()->count(2)->create([
            'role' => 'user',
        ]);

        $survey = Survey::factory()->create();
        SurveyResponse::factory()->create([
            'survey_id' => $survey->id,
            'line_id' => $users[0]->line_id,
        ]);

        // Get initial stats
        $this->withoutVite();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));
        $initialStats = $response->viewData('stats');

        // Create one more user, survey and response
        LineOAUser::factory()->create(['role' => 'user']);
        $newSurvey = Survey::factory()->create();
        SurveyResponse::factory()->create([
            'survey_id' => $newSurvey->id,
            'line_id' => $users[1]->line_id,
        ]);

        // Get updated stats
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.analytics'));
        $updatedStats = $response->viewData('stats');

        // Verify stats have increased by exactly one
        $this->assertEquals($initialStats['total_users'] + 1, $updatedStats['total_users'], 'User count should increase by 1');
        $this->assertEquals($initialStats['total_surveys'] + 1, $updatedStats['total_surveys'], 'Survey count should increase by 1');
        $this->assertEquals($initialStats['total_responses'] + 1, $updatedStats['total_responses'], 'Response count should increase by 1');
    }
}
