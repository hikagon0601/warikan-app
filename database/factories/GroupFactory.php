<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->randomElement(['別府旅行', 'ゼミ合宿', '卒業旅行', '忘年会']),
            'memo' => null,
        ];
    }

    // 作ったあとに、作った人をメンバーに入れる（画面から作ったときと同じ状態にする）
    public function configure(): static
    {
        return $this->afterCreating(function (Group $group) {
            $group->members()->attach($group->owner_id);
        });
    }
}