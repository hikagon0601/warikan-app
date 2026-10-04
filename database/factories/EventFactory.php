<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['OB会', '新歓コンパ', '追いコン', '打ち上げ']),
            'date' => fake()->dateTimeBetween('-1 month', '+2 months')->format('Y-m-d'),
            'meeting_time' => '18:30',
            'place' => fake()->randomElement(['鳥貴族 渋谷店', '魚民 新宿店', '串カツ田中 池袋店']),
            'total_amount' => fake()->numberBetween(10, 60) * 1000,
            'memo' => null,
        ];
    }
}