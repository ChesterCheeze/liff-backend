<?php

namespace App\Events;

use App\Models\Survey;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Survey $survey,
        public string $action = 'updated'
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('surveys'),
            new PrivateChannel('surveys.admin'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'survey' => [
                'id' => $this->survey->id,
                'title' => $this->survey->title,
                'status' => $this->survey->status,
                'updated_at' => $this->survey->updated_at->toISOString(),
            ],
            'action' => $this->action,
        ];
    }

    public function broadcastAs(): string
    {
        return 'survey.updated';
    }
}
