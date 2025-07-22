<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = ['line_id', 'survey_id', 'form_data', 'completed_at', 'user_id', 'user_type'];

    protected $casts = [
        'completed_at' => 'datetime',
        'form_data' => 'array',
        'answers' => 'array',
    ];

    protected $appends = ['answers'];

    public function getAnswersAttribute()
    {
        return $this->form_data ?: $this->attributes['answers'] ?? [];
    }

    public function setAnswersAttribute($value)
    {
        $this->attributes['form_data'] = is_array($value) ? json_encode($value) : $value;
        $this->attributes['answers'] = $value;
    }

    public function lineOaUser()
    {
        return $this->belongsTo(LineOAUser::class, 'line_id', 'line_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'survey_id', 'id');
    }

    public function user()
    {
        return $this->morphTo();
    }
}
