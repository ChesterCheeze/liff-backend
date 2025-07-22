<?php

namespace App\Exports;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SurveyResponsesExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Survey $survey;

    public function __construct(Survey $survey)
    {
        $this->survey = $survey;
    }

    public function collection()
    {
        return SurveyResponse::with(['user', 'lineOaUser', 'survey.questions'])
            ->where('survey_id', $this->survey->id)
            ->get();
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Response ID',
            'Survey Title',
            'User ID',
            'User Name',
            'User Email',
            'Submitted At',
            'Status',
        ];

        $questionHeadings = $this->survey->questions()
            ->pluck('label')
            ->map(function ($question) {
                return "Q: {$question}";
            })
            ->toArray();

        return array_merge($baseHeadings, $questionHeadings);
    }

    public function map($response): array
    {
        // Try to get user info from polymorphic user or LINE user
        $userName = '';
        $userEmail = '';

        if ($response->user) {
            $userName = $response->user->name ?? $response->user->display_name ?? 'Unknown';
            $userEmail = $response->user->email ?? '';
        } elseif ($response->lineOaUser) {
            $userName = $response->lineOaUser->display_name ?? 'Unknown';
            $userEmail = $response->lineOaUser->email ?? '';
        }

        $baseData = [
            $response->id,
            $response->survey->name,
            $response->user_id,
            $userName,
            $userEmail,
            $response->completed_at?->format('Y-m-d H:i:s') ?? '',
            $response->completed_at ? 'Completed' : 'In Progress',
        ];

        $formData = is_array($response->form_data) ? $response->form_data : json_decode($response->form_data, true) ?? [];

        $questionAnswers = $this->survey->questions()
            ->get()
            ->map(function ($question) use ($formData) {
                return $formData[$question->name] ?? '';
            })
            ->toArray();

        return array_merge($baseData, $questionAnswers);
    }

    public function title(): string
    {
        return 'Survey Responses';
    }
}
