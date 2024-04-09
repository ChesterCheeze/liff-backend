<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class LineOAUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'lineoausers';

    protected $fillable = ['line_id', 'name', 'picture_url'];

    public function survey_responses() {
        return $this->hasMany(SurveyResponse::class);
    }
}
