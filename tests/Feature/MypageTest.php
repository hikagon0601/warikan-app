<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    // $payer が $group の全員分として $amount 円を払った、と記録する
    private function pay(Group $group, User $payer, int $amount): void
    {
        $this->actingAs($payer)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => $amount,
            'paid_on' => '2026-10-05',
            'payer_id' => $payer->id,
            'beneficiaries' => $group->members()->pluck('users.id')->all(),
        ]);
    }

    public function test_amounts_to_pay_and_receive_are_shown(): void
    {
        $me = User::factory()->create();
        $tanaka = User::factory()->create(['name' => '田中']);
        $sato = User::factory()->create(['name' => '佐藤']);

        // 別府旅行：田中が2人分の6,000円を払った → 自分は田中に3,000円払う
        $trip = Group::factory()->create(['owner_id' => $tanaka->id, 'name' => '別府旅行']);
        $trip->members()->attach($me->id);
        $this->pay($trip, $tanaka, 6000);

        // ゼミ合宿：自分が2人分の4,000円を払った → 佐藤から2,000円受け取る
        $camp = Group::factory()->create(['owner_id' => $me->id, 'name' => 'ゼミ合宿']);
        $camp->members()->attach($sato->id);
        $this->pay($camp, $me, 4000);

        $this->actingAs($me)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('payTotal', 3000)
            ->assertViewHas('receiveTotal', 2000)
            ->assertSee('田中さんへ')
            ->assertSee('佐藤さんから');
    }

    public function test_users_without_groups_see_zero_yen(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('payTotal', 0)
            ->assertViewHas('receiveTotal', 0)
            ->assertSee('まだグループに入っていません');
    }
}