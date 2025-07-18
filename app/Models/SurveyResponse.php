<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = ['line_id', 'survey_id', 'answers', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function lineOaUser()
    {
        return $this->belongsTo(LineOAUser::class, 'line_id', 'line_id');
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
