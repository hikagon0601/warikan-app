<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_a_settlement_brings_balances_back_to_zero(): void
    {
        $group = Group::factory()->create();
        $tanaka = $group->owner;
        $suzuki = User::factory()->create(['name' => '鈴木']);
        $group->members()->attach($suzuki->id);

        // 田中が2人分の夕食6,000円を払った → 鈴木が田中に3,000円払えば精算完了
        $this->actingAs($tanaka)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 6000,
            'paid_on' => '2026-10-05',
            'payer_id' => $tanaka->id,
            'beneficiaries' => [$tanaka->id, $suzuki->id],
        ]);

        $this->actingAs($tanaka)->get(route('groups.show', $group))
            ->assertSee('3,000円 払う')
            ->assertSee('精算した');

        $this->actingAs($suzuki)->post(route('groups.settlements.store', $group), [
            'from_id' => $suzuki->id,
            'to_id' => $tanaka->id,
            'amount' => 3000,
        ])->assertRedirect();

        $this->assertEquals([$tanaka->id => 0, $suzuki->id => 0], $group->fresh()->balances());
        $this->actingAs($tanaka)->get(route('groups.show', $group))
            ->assertSee('全員の貸し借りが0円です');
    }

    public function test_cannot_record_a_settlement_with_yourself(): void
    {
        $group = Group::factory()->create();

        $this->actingAs($group->owner)->post(route('groups.settlements.store', $group), [
            'from_id' => $group->owner_id,
            'to_id' => $group->owner_id,
            'amount' => 1000,
        ])->assertSessionHasErrors('to_id');
    }

    public function test_non_members_cannot_record_a_settlement(): void
    {
        $group = Group::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->post(route('groups.settlements.store', $group), [
            'from_id' => $outsider->id,
            'to_id' => $group->owner_id,
            'amount' => 1000,
        ])->assertForbidden();
    }
}