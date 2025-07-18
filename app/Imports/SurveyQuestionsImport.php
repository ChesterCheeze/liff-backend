<?php

namespace App\Imports;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SurveyQuestionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected Survey $survey;

    protected array $importedQuestions = [];

    protected array $errors = [];

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $options = null;
                if (isset($row['options']) && ! empty($row['options'])) {
                    $options = is_string($row['options'])
                        ? explode(',', $row['options'])
                        : $row['options'];
                }

                $question = SurveyQuestion::create([
                    'survey_id' => $this->survey->id,
                    'label' => $row['question_text'],
                    'name' => \Illuminate\Support\Str::slug($row['question_text']),
                    'type' => $row['question_type'] ?? 'text',
                    'required' => filter_var($row['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ]);

                $this->importedQuestions[] = $question;
            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $row->toArray(),
                    'error' => $e->getMessage(),
                ];
            }
        }
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string',
            'question_type' => ['nullable', Rule::in(['text', 'textarea', 'radio', 'checkbox', 'select', 'number', 'email', 'date'])],
            'required' => 'nullable|boolean',
        ];
    }

    public function getImportedQuestions(): array
    {
        return $this->importedQuestions;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportSummary(): array
    {
        return [
            'survey_id' => $this->survey->id,
            'total_imported' => count($this->importedQuestions),
            'total_errors' => count($this->errors),
            'imported_questions' => $this->importedQuestions,
            'errors' => $this->errors,
        ];
    }
}
