<?php

namespace Tests\Feature\Admin;

use App\Models\LineOAUser;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportImportTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        Storage::fake('local');
    }

    public function test_admin_can_export_surveys_to_excel()
    {
        $surveys = Survey::factory()->count(3)->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys', [
            'format' => 'xlsx',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition');
    }

    public function test_admin_can_export_surveys_to_csv()
    {
        Survey::factory()->count(2)->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys', [
            'format' => 'csv',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_export_survey_responses()
    {
        $survey = Survey::factory()->create();
        $lineUsers = LineOAUser::factory()->count(3)->create();

        foreach ($lineUsers as $lineUser) {
            SurveyResponse::factory()->create([
                'survey_id' => $survey->id,
                'line_id' => $lineUser->line_id,
            ]);
        }

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys/'.$survey->id.'/responses', [
            'format' => 'xlsx',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_export_all_responses()
    {
        $surveys = Survey::factory()->count(2)->create();

        foreach ($surveys as $survey) {
            SurveyResponse::factory()->count(2)->create([
                'survey_id' => $survey->id,
            ]);
        }

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/responses', [
            'format' => 'csv',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_export_analytics_data()
    {
        $surveys = Survey::factory()->count(2)->create();

        foreach ($surveys as $survey) {
            SurveyResponse::factory()->count(5)->create([
                'survey_id' => $survey->id,
                'completed_at' => now(),
            ]);
        }

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/analytics', [
            'format' => 'xlsx',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_fails_with_invalid_format()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys', [
            'format' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['format']);
    }

    public function test_admin_can_download_import_templates()
    {
        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/admin/import/templates?type=surveys');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_validate_import_file()
    {
        $file = UploadedFile::fake()->createWithContent('surveys.csv',
            "name,description,status\nTest Survey,Test Description,draft");

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/validate', [
            'file' => $file,
            'type' => 'surveys',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'valid',
                    'errors',
                    'preview',
                ],
            ]);
    }

    public function test_admin_can_import_surveys_from_csv()
    {
        $csvContent = "name,description,status\nImported Survey 1,Description 1,draft\nImported Survey 2,Description 2,published";
        $file = UploadedFile::fake()->createWithContent('surveys.csv', $csvContent);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/surveys', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'imported_count',
                    'failed_count',
                    'errors',
                ],
            ]);

        $this->assertDatabaseHas('surveys', [
            'name' => 'Imported Survey 1',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('surveys', [
            'name' => 'Imported Survey 2',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_import_survey_questions()
    {
        $survey = Survey::factory()->create();

        $csvContent = "question_text,question_type,is_required\nWhat is your name?,text,1\nHow old are you?,number,0";
        $file = UploadedFile::fake()->createWithContent('questions.csv', $csvContent);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/surveys/'.$survey->id.'/questions', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'imported_count',
                    'failed_count',
                ],
            ]);

        $this->assertDatabaseHas('survey_questions', [
            'survey_id' => $survey->id,
            'question_text' => 'What is your name?',
            'question_type' => 'text',
            'is_required' => true,
        ]);
    }

    public function test_import_fails_with_invalid_file()
    {
        $file = UploadedFile::fake()->create('invalid.txt', 100);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/surveys', [
            'file' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_import_handles_validation_errors_gracefully()
    {
        $csvContent = "name,description,status\n,Invalid Description,invalid_status\nValid Survey,Valid Description,draft";
        $file = UploadedFile::fake()->createWithContent('surveys.csv', $csvContent);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/surveys', [
            'file' => $file,
        ]);

        $response->assertStatus(200);

        $responseData = $response->json('data');
        $this->assertEquals(1, $responseData['imported_count']);
        $this->assertEquals(1, $responseData['failed_count']);
        $this->assertNotEmpty($responseData['errors']);

        $this->assertDatabaseHas('surveys', [
            'name' => 'Valid Survey',
        ]);
    }

    public function test_non_admin_cannot_access_export_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys');

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_import_endpoints()
    {
        $user = User::factory()->create(['role' => 'user']);
        $token = $user->createToken('test-token')->plainTextToken;

        $file = UploadedFile::fake()->create('surveys.csv');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/import/surveys', [
            'file' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_export_includes_correct_data_structure()
    {
        $survey = Survey::factory()->create([
            'name' => 'Test Export Survey',
            'description' => 'Test Description',
            'status' => 'published',
        ]);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        Excel::fake();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/admin/export/surveys', [
            'format' => 'xlsx',
        ]);

        Excel::assertDownloaded('surveys_export.xlsx', function ($export) use ($survey) {
            return $export->collection()->contains(function ($item) use ($survey) {
                return $item['name'] === $survey->name;
            });
        });
    }
}
