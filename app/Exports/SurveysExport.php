<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SurveysExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Survey::with(['questions', 'responses']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['section'])) {
            $query->where('section', $this->filters['section']);
        }

        if (isset($this->filters['date_from'])) {
            $query->where('created_at', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->where('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Survey ID',
            'Name',
            'Description',
            'Section',
            'Status',
            'Total Questions',
            'Total Responses',
            'Completed Responses',
            'Created At',
            'Updated At',
        ];
    }

    public function map($survey): array
    {
        $completedResponses = $survey->responses()
            ->whereNotNull('completed_at')
            ->count();

        return [
            $survey->id,
            $survey->name,
            $survey->description,
            $survey->section,
            $survey->status,
            $survey->questions->count(),
            $survey->responses->count(),
            $completedResponses,
            $survey->created_at->format('Y-m-d H:i:s'),
            $survey->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function title(): string
    {
        return 'Surveys';
    }
}
