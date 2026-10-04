<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_see_the_group_list(): void
    {
        $this->get(route('groups.index'))->assertRedirect(route('login'));
    }

    public function test_creating_a_group_makes_the_owner_a_member(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('groups.store'), ['name' => '別府旅行'])->assertRedirect();

        $group = Group::first();
        $this->assertSame('別府旅行', $group->name);
        $this->assertSame($user->id, $group->owner_id);
        $this->assertTrue($group->hasMember($user));
    }

    public function test_group_list_shows_only_my_groups(): void
    {
        $user = User::factory()->create();
        Group::factory()->create(['owner_id' => $user->id, 'name' => '自分のグループ']);
        Group::factory()->create(['name' => 'よそのグループ']);

        $this->actingAs($user)->get(route('groups.index'))
            ->assertSee('自分のグループ')
            ->assertDontSee('よそのグループ');
    }

    public function test_non_members_cannot_open_a_group(): void
    {
        $group = Group::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('groups.show', $group))->assertForbidden();
        $this->actingAs($group->owner)->get(route('groups.show', $group))->assertOk();
    }

    public function test_members_can_be_added_by_email(): void
    {
        $group = Group::factory()->create();
        $tanaka = User::factory()->create(['email' => 'tanaka@example.com']);

        $this->actingAs($group->owner)->post(route('groups.members.store', $group), ['email' => 'tanaka@example.com']);

        $this->assertTrue($group->hasMember($tanaka));
    }

    public function test_unregistered_email_cannot_be_added(): void
    {
        $group = Group::factory()->create();

        $this->actingAs($group->owner)
            ->post(route('groups.members.store', $group), ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_only_the_owner_can_add_members(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id);
        User::factory()->create(['email' => 'tanaka@example.com']);

        $this->actingAs($member)
            ->post(route('groups.members.store', $group), ['email' => 'tanaka@example.com'])
            ->assertForbidden();
    }

    public function test_owner_cannot_be_removed_from_the_group(): void
    {
        $group = Group::factory()->create();

        $this->actingAs($group->owner)
            ->delete(route('groups.members.destroy', [$group, $group->owner]))
            ->assertForbidden();

        $this->assertTrue($group->hasMember($group->owner));
    }

    public function test_group_members_cannot_delete_their_account(): void
    {
        $group = Group::factory()->create();

        $this->actingAs($group->owner);

        Volt::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser')
            ->assertHasErrors(['password']);

        $this->assertNotNull($group->owner->fresh());
    }
}