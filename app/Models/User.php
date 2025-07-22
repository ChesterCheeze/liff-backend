<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verification_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_token' => null,
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $token = Str::random(64);

        $this->forceFill([
            'email_verification_token' => hash('sha256', $token),
        ])->save();

        $this->notify(new VerifyEmailNotification($token));
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    public function generateEmailVerificationToken(): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'email_verification_token' => hash('sha256', $token),
        ])->save();

        return $token;
    }

    public function verifyEmailToken(string $token): bool
    {
        return hash_equals($this->email_verification_token, hash('sha256', $token));
    }

    public function responses()
    {
        return $this->morphMany(SurveyResponse::class, 'user');
    }
}
