<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = ['survey_id', 'form_data'];

    public function lineoauser() {
        return $this->belongsTo(LineOAUser::class);
    }
}
