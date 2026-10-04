<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'memo',
    ];

    // グループを作った人
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // メンバー（多対多）
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    // 支払いの記録
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // 一人ひとりの残高 [user_id => 残高]。プラスは受け取る額、マイナスは払う額
    public function balances(): array
    {
        $this->loadMissing(['members', 'payments.beneficiaries']);

        // 全員 0 円から始める
        $balances = [];
        foreach ($this->members as $member) {
            $balances[$member->id] = 0;
        }

        foreach ($this->payments as $payment) {
            // 払った人は、払った分だけプラス
            $balances[$payment->payer_id] += $payment->amount;

            // 誰の分かに入っている人は、負担額だけマイナス
            foreach ($payment->beneficiaries as $user) {
                $balances[$user->id] -= $user->pivot->share;
            }
        }

        return $balances;
    }

    // この人がメンバーかどうか
    public function hasMember(User $user): bool
    {
        return $this->members()->whereKey($user->id)->exists();
    }
}