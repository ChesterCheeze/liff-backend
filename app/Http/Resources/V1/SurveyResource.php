<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section' => $this->section,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'questions_count' => $this->when($this->relationLoaded('questions'), fn () => $this->questions->count()),
            'responses_count' => $this->when($this->relationLoaded('responses'), fn () => $this->responses->count()),
            'questions' => SurveyQuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
