<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'meeting_time',
        'place',
        'total_amount',
        'memo',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    // 幹事（このイベントを作った人）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 参加者（多対多）。中間テーブルの paid も一緒に取り出す
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('paid')
            ->withTimestamps();
    }

    // 連絡コメント
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // 均等割りの計算（100円単位で切り上げ）
    public static function splitEvenly(int $total, int $people): int
    {
        if ($people <= 0) {
            return 0;
        }

        return (int) (ceil($total / $people / 100) * 100);
    }

    // このイベントの一人あたりの金額
    public function perPerson(): int
    {
        return self::splitEvenly($this->total_amount, $this->participants->count());
    }
}