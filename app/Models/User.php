<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        return ($this->xu_balance + $this->bonus_xu) >= $cost;
    }

    public function deductXu(int $cost): void
    {
        if ($this->bonus_xu >= $cost) {
            $this->decrement('bonus_xu', $cost);
        } else {
            $remainingCost = $cost - $this->bonus_xu;
            $this->update(['bonus_xu' => 0]);
            $this->decrement('xu_balance', $remainingCost);
        }
    }

    public function refundXu(int $cost): void
    {
        // Khi hoàn tiền, hệ thống mặc định ưu tiên cộng lại vào xu chính
        $this->increment('xu_balance', $cost);
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }
}
