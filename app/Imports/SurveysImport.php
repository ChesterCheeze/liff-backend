<?php

namespace App\Imports;

use App\Models\Survey;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SurveysImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected array $importedSurveys = [];

    protected array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                $survey = Survey::create([
                    'name' => $row['title'],
                    'description' => $row['description'] ?? '',
                    'section' => $row['section'] ?? 'general',
                    'status' => $row['status'] ?? 'draft',
                ]);

                $this->importedSurveys[] = $survey;
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section' => 'nullable|string|max:100',
            'status' => ['nullable', Rule::in(['draft', 'active', 'inactive'])],
        ];
    }

    public function getImportedSurveys(): array
    {
        return $this->importedSurveys;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportSummary(): array
    {
        return [
            'total_imported' => count($this->importedSurveys),
            'total_errors' => count($this->errors),
            'imported_surveys' => $this->importedSurveys,
            'errors' => $this->errors,
        ];
    }
}
