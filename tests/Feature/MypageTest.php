<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_参加予定と未払いの合計が表示される(): void
    {
        $me = User::factory()->create();
        $friend = User::factory()->create();

        // 来週の会：2人で 7,000円 → 一人 3,500円（未払い）
        $next = Event::factory()->create(['title' => '来週の会', 'date' => now()->addWeek(), 'total_amount' => 7000]);
        $next->participants()->attach([$me->id, $friend->id]);

        // 先月の会：1人で 3,000円（未払い。過去でも未払いに数える）
        $past = Event::factory()->create(['title' => '先月の会', 'date' => now()->subMonth(), 'total_amount' => 3000]);
        $past->participants()->attach($me->id);

        $this->actingAs($me)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('来週の会')
            ->assertDontSee('先月の会')
            ->assertViewHas('unpaidTotal', 6500); // 3,500円 + 3,000円
    }

    public function test_支払い済みの会は未払いに数えない(): void
    {
        $me = User::factory()->create();

        $event = Event::factory()->create(['date' => now()->addWeek(), 'total_amount' => 4000]);
        $event->participants()->attach($me->id, ['paid' => true]);

        $this->actingAs($me)->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('unpaidTotal', 0)
            ->assertSee('支払い済み');
    }
}