<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'line_id' => $this->line_id,
            'survey_id' => $this->survey_id,
            'answers' => $this->answers,
            'completed_at' => $this->completed_at?->toISOString(),
            'user' => new LineOAUserResource($this->whenLoaded('lineOaUser')),
            'survey' => new SurveyResource($this->whenLoaded('survey')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
