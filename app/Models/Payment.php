<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'created_by',
        'title',
        'amount',
        'paid_on',
        'is_settlement',
    ];

    protected function casts(): array
    {
        return [
            'paid_on' => 'date',
            'is_settlement' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    // 払った人
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    // 記録した人
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 誰の分か（多対多）。中間テーブルの share（負担額）も一緒に取り出す
    public function beneficiaries(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('share');
    }
}