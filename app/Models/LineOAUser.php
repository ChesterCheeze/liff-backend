<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class LineOAUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'lineoausers';

    protected $fillable = ['line_id', 'name', 'picture_url', 'role'];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function survey_responses()
    {
        return $this->hasMany(SurveyResponse::class, 'line_id', 'line_id');
    }
}
