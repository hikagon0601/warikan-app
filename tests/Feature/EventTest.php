<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインしていないと一覧を見られない(): void
    {
        $this->get(route('events.index'))->assertRedirect(route('login'));
    }

    public function test_イベントを作成すると幹事が参加者になる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('events.store'), [
            'title' => 'OB会',
            'date' => '2026-08-11',
            'meeting_time' => '18:30',
            'place' => '鳥貴族 渋谷店',
            'total_amount' => 30000,
        ])->assertRedirect();

        $event = Event::first();
        $this->assertSame('OB会', $event->title);
        $this->assertTrue($event->participants->contains($user));
    }

    public function test_参加するとやめるができる(): void
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('events.join', $event));
        $this->assertTrue($event->participants()->where('user_id', $user->id)->exists());

        $this->actingAs($user)->delete(route('events.leave', $event));
        $this->assertFalse($event->participants()->where('user_id', $user->id)->exists());
    }

    public function test_幹事は参加をやめられない(): void
    {
        $event = Event::factory()->create();
        $event->participants()->attach($event->user_id);

        $this->actingAs($event->user)->delete(route('events.leave', $event))->assertForbidden();
        $this->assertTrue($event->participants()->where('user_id', $event->user_id)->exists());

        $this->actingAs($event->user)->get(route('events.show', $event))
            ->assertSee('幹事は参加をやめられません')
            ->assertDontSee('参加をやめる');
    }

    public function test_幹事以外は編集できない(): void
    {
        $event = Event::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('events.edit', $event))->assertForbidden();
        $this->actingAs($other)->delete(route('events.destroy', $event))->assertForbidden();
    }

    public function test_幹事は支払い済みを切り替えられる(): void
    {
        $event = Event::factory()->create();
        $member = User::factory()->create();
        $event->participants()->attach($member->id);

        $this->actingAs($event->user)->patch(route('events.paid', [$event, $member]));

        $this->assertTrue((bool) $event->participants()->find($member->id)->pivot->paid);
    }

    public function test_詳細画面に一人あたりの金額が表示される(): void
    {
        $event = Event::factory()->create(['total_amount' => 10000]);
        $event->participants()->attach(User::factory()->count(3)->create());

        $this->actingAs($event->user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('一人 3,400円');
    }

    public function test_コメントを投稿できる(): void
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $event), ['body' => '10分遅れます']);

        $this->assertDatabaseHas('comments', ['event_id' => $event->id, 'body' => '10分遅れます']);
    }

    public function test_店名で検索できる(): void
    {
        $user = User::factory()->create();
        Event::factory()->create(['title' => 'OB会', 'place' => '鳥貴族 渋谷店']);
        Event::factory()->create(['title' => '新歓', 'place' => '魚民 新宿店']);

        $this->actingAs($user)
            ->get(route('events.index', ['keyword' => '鳥貴族']))
            ->assertSee('OB会')
            ->assertDontSee('新歓');
    }
}