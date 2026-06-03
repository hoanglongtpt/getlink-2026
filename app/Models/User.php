<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    public const ROLE_MEMBER = 'member';
    public const ROLE_ADMIN = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'xu_balance',
        'bonus_xu',
        'role',
        'blocked_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'blocked_at' => 'datetime',
        'xu_balance' => 'integer',
        'bonus_xu' => 'integer',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function downloadHistories()
    {
        return $this->hasMany(DownloadHistory::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function hasSufficientXu(int $cost): bool
    {
        return $this->bonus_xu >= $cost || $this->xu_balance >= $cost;
    }

    public function deductXu(int $cost): string
    {
        if ($this->bonus_xu >= $cost) {
            $this->decrement('bonus_xu', $cost);
            return 'bonus';
        }

        if ($this->xu_balance >= $cost) {
            $this->decrement('xu_balance', $cost);
            return 'balance';
        }

        throw new \RuntimeException('Không đủ xu để trừ.');
    }

    public function refundXu(int $cost, string $source = 'balance'): void
    {
        if ($source === 'bonus') {
            $this->increment('bonus_xu', $cost);
            return;
        }

        $this->increment('xu_balance', $cost);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }
}
