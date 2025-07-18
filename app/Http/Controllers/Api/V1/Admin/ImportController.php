<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Imports\SurveyQuestionsImport;
use App\Imports\SurveysImport;
use App\Models\Survey;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends BaseApiController
{
    public function surveys(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new SurveysImport();
            Excel::import($import, $request->file('file'));

            $summary = $import->getImportSummary();

            return $this->successResponse($summary, 'Surveys imported successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Import failed: '.$e->getMessage(), 500);
        }
    }

    public function surveyQuestions(Request $request, Survey $survey)
    {
        $this->requireAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new SurveyQuestionsImport($survey);
            Excel::import($import, $request->file('file'));

            $summary = $import->getImportSummary();

            return $this->successResponse($summary, 'Survey questions imported successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Import failed: '.$e->getMessage(), 500);
        }
    }

    public function validateFile(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:10240',
            'type' => 'required|in:surveys,questions',
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            // Basic file validation
            $validation = [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $extension,
                'valid' => true,
                'errors' => [],
            ];

            // Check file structure based on type
            if ($request->type === 'surveys') {
                $validation = array_merge($validation, $this->validateSurveysFile($file));
            } elseif ($request->type === 'questions') {
                $validation = array_merge($validation, $this->validateQuestionsFile($file));
            }

            return $this->successResponse($validation, 'File validation completed');
        } catch (\Exception $e) {
            return $this->errorResponse('File validation failed: '.$e->getMessage(), 500);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $this->requireAdmin();

        $request->validate([
            'type' => 'required|in:surveys,questions',
            'format' => 'required|in:xlsx,csv',
        ]);

        try {
            $filename = $request->type.'_template.'.$request->format;

            if ($request->type === 'surveys') {
                return $this->generateSurveysTemplate($request->format, $filename);
            } elseif ($request->type === 'questions') {
                return $this->generateQuestionsTemplate($request->format, $filename);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Template generation failed: '.$e->getMessage(), 500);
        }
    }

    private function validateSurveysFile($file): array
    {
        try {
            $data = Excel::toArray(new SurveysImport(), $file);
            $rows = $data[0] ?? [];

            $requiredColumns = ['title'];
            $optionalColumns = ['description', 'section', 'status'];
            $allColumns = array_merge($requiredColumns, $optionalColumns);

            $validation = [
                'rows_count' => count($rows) - 1, // Excluding header
                'columns' => [],
                'preview' => array_slice($rows, 0, 5), // First 5 rows for preview
            ];

            if (! empty($rows)) {
                $headers = array_keys($rows[0]);
                $validation['columns'] = $headers;

                // Check required columns
                $missingColumns = array_diff($requiredColumns, $headers);
                if (! empty($missingColumns)) {
                    $validation['valid'] = false;
                    $validation['errors'][] = 'Missing required columns: '.implode(', ', $missingColumns);
                }
            }

            return $validation;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['File structure validation failed: '.$e->getMessage()],
            ];
        }
    }

    private function validateQuestionsFile($file): array
    {
        try {
            $data = Excel::toArray(new SurveyQuestionsImport(new Survey()), $file);
            $rows = $data[0] ?? [];

            $requiredColumns = ['question_text'];
            $optionalColumns = ['question_type', 'required'];

            $validation = [
                'rows_count' => count($rows) - 1,
                'columns' => [],
                'preview' => array_slice($rows, 0, 5),
            ];

            if (! empty($rows)) {
                $headers = array_keys($rows[0]);
                $validation['columns'] = $headers;

                $missingColumns = array_diff($requiredColumns, $headers);
                if (! empty($missingColumns)) {
                    $validation['valid'] = false;
                    $validation['errors'][] = 'Missing required columns: '.implode(', ', $missingColumns);
                }
            }

            return $validation;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['File structure validation failed: '.$e->getMessage()],
            ];
        }
    }

    private function generateSurveysTemplate(string $format, string $filename)
    {
        $headers = ['title', 'description', 'section', 'status'];
        $sampleData = [
            ['Sample Survey 1', 'This is a sample survey description', 'general', 'draft'],
            ['Sample Survey 2', 'Another survey example', 'feedback', 'active'],
        ];

        return $this->generateTemplateFile($headers, $sampleData, $format, $filename);
    }

    private function generateQuestionsTemplate(string $format, string $filename)
    {
        $headers = ['question_text', 'question_type', 'required'];
        $sampleData = [
            ['What is your name?', 'text', 'true'],
            ['How would you rate our service?', 'radio', 'true'],
            ['Additional comments', 'textarea', 'false'],
        ];

        return $this->generateTemplateFile($headers, $sampleData, $format, $filename);
    }

    private function generateTemplateFile(array $headers, array $sampleData, string $format, string $filename)
    {
        $data = array_merge([$headers], $sampleData);

        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray
        {
            protected array $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        };

        return Excel::download($export, $filename);
    }
}
