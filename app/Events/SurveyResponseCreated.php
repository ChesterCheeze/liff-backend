<?php

namespace App\Events;

use App\Models\SurveyResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyResponseCreated // implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SurveyResponse $surveyResponse
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('surveys.admin'),
            new PrivateChannel('survey.'.$this->surveyResponse->survey_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'response' => [
                'id' => $this->surveyResponse->id,
                'survey_id' => $this->surveyResponse->survey_id,
                'user_id' => $this->surveyResponse->user_id,
                'user_type' => $this->surveyResponse->user_type,
                'created_at' => $this->surveyResponse->created_at->toISOString(),
            ],
            'survey' => [
                'id' => $this->surveyResponse->survey->id,
                'name' => $this->surveyResponse->survey->name,
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'survey.response.created';
    }
}
