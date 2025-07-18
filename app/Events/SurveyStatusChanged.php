<?php

namespace App\Events;

use App\Models\Survey;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SurveyStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Survey $survey,
        public string $oldStatus,
        public string $newStatus
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('surveys'),
            new PrivateChannel('surveys.admin'),
            new PrivateChannel('survey.'.$this->survey->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'survey' => [
                'id' => $this->survey->id,
                'title' => $this->survey->title,
                'status' => $this->newStatus,
                'updated_at' => $this->survey->updated_at->toISOString(),
            ],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }

    public function broadcastAs(): string
    {
        return 'survey.status.changed';
    }
}
