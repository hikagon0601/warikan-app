<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    // 作った人＋2人の、3人のグループを用意する
    private function makeGroup(): array
    {
        $group = Group::factory()->create();
        $tanaka = User::factory()->create(['name' => '田中']);
        $sato = User::factory()->create(['name' => '佐藤']);
        $group->members()->attach([$tanaka->id, $sato->id]);

        return [$group, $group->owner, $tanaka, $sato];
    }

    public function test_recording_a_payment_stores_shares_in_one_yen_units(): void
    {
        [$group, $owner, $tanaka, $sato] = $this->makeGroup();

        $this->actingAs($tanaka)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 10000,
            'paid_on' => '2026-10-05',
            'payer_id' => $tanaka->id,
            'beneficiaries' => [$owner->id, $tanaka->id, $sato->id],
        ])->assertRedirect(route('groups.show', $group));

        $payment = $group->payments()->first();
        $this->assertSame($tanaka->id, $payment->payer_id);
        $this->assertSame($tanaka->id, $payment->created_by);
        $this->assertSame(10000, $payment->beneficiaries->sum('pivot.share'));
        // id がいちばん小さい作った人が、余りの1円を負担する
        $this->assertSame(3334, $payment->beneficiaries->find($owner->id)->pivot->share);
    }

    public function test_a_payment_can_be_for_only_some_members(): void
    {
        [$group, $owner, $tanaka, $sato] = $this->makeGroup();

        $this->actingAs($owner)->post(route('groups.payments.store', $group), [
            'title' => 'お酒',
            'amount' => 3000,
            'paid_on' => '2026-10-05',
            'payer_id' => $owner->id,
            'beneficiaries' => [$tanaka->id, $sato->id],
        ]);

        $payment = $group->payments()->first();
        $this->assertEqualsCanonicalizing([$tanaka->id, $sato->id], $payment->beneficiaries->pluck('id')->all());
        $this->assertSame(1500, $payment->beneficiaries->find($sato->id)->pivot->share);
    }

    public function test_non_members_cannot_be_payer_or_beneficiary(): void
    {
        [$group, $owner] = $this->makeGroup();
        $outsider = User::factory()->create();

        $this->actingAs($owner)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 1000,
            'paid_on' => '2026-10-05',
            'payer_id' => $outsider->id,
            'beneficiaries' => [$outsider->id],
        ])->assertSessionHasErrors(['payer_id', 'beneficiaries.0']);

        $this->assertSame(0, $group->payments()->count());
    }

    public function test_non_members_cannot_record_a_payment(): void
    {
        [$group, $owner] = $this->makeGroup();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 1000,
            'paid_on' => '2026-10-05',
            'payer_id' => $owner->id,
            'beneficiaries' => [$owner->id],
        ])->assertForbidden();
    }

    public function test_only_recorder_or_owner_can_delete_a_payment(): void
    {
        [$group, $owner, $tanaka, $sato] = $this->makeGroup();

        $this->actingAs($tanaka)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 3000,
            'paid_on' => '2026-10-05',
            'payer_id' => $tanaka->id,
            'beneficiaries' => [$tanaka->id, $sato->id],
        ]);
        $payment = $group->payments()->first();

        $this->actingAs($sato)->delete(route('groups.payments.destroy', [$group, $payment]))->assertForbidden();
        $this->actingAs($owner)->delete(route('groups.payments.destroy', [$group, $payment]))->assertRedirect();

        $this->assertSame(0, $group->payments()->count());
    }

    public function test_members_with_payment_records_cannot_be_removed(): void
    {
        [$group, $owner, $tanaka, $sato] = $this->makeGroup();

        // 佐藤は払っていないが、負担がある
        $this->actingAs($tanaka)->post(route('groups.payments.store', $group), [
            'title' => '夕食',
            'amount' => 3000,
            'paid_on' => '2026-10-05',
            'payer_id' => $tanaka->id,
            'beneficiaries' => [$tanaka->id, $sato->id],
        ]);

        $this->actingAs($owner)
            ->delete(route('groups.members.destroy', [$group, $sato]))
            ->assertSessionHasErrors('member');

        $this->assertTrue($group->hasMember($sato));
    }
}