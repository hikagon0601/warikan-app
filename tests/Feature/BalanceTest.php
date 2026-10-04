<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_払った額から負担額を引いた残高になる(): void
    {
        // 別府旅行：田中・佐藤・鈴木の3人
        $group = Group::factory()->create();
        $tanaka = $group->owner;
        $sato = User::factory()->create();
        $suzuki = User::factory()->create();
        $group->members()->attach([$sato->id, $suzuki->id]);

        $everyone = [$tanaka->id, $sato->id, $suzuki->id];
        $pay = fn (User $payer, string $title, int $amount) => $this->actingAs($payer)
            ->post(route('groups.payments.store', $group), [
                'title' => $title,
                'amount' => $amount,
                'paid_on' => '2026-10-05',
                'payer_id' => $payer->id,
                'beneficiaries' => $everyone,
            ]);

        $pay($tanaka, 'レンタカー', 12000);
        $pay($sato, '夕食', 9000);
        $pay($tanaka, 'ガソリン', 3000);

        // 合計24,000円、一人8,000円（assertEquals は並び順を気にしない）
        $this->assertEquals([
            $tanaka->id => 7000,  // 15,000円払った − 8,000円
            $sato->id => 1000,    // 9,000円払った − 8,000円
            $suzuki->id => -8000, // 0円払った − 8,000円
        ], $group->fresh()->balances());
    }
}