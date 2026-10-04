<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ログイン用のユーザー（パスワードは password）
        $me = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 部員たち
        $members = collect(['田中', '佐藤', '鈴木', '高橋', '伊藤'])
            ->map(fn ($name) => User::factory()->create(['name' => $name]));

        // 自分が幹事の飲み会
        $obkai = $me->organizedEvents()->create([
            'title' => 'OB会',
            'date' => '2026-08-11',
            'meeting_time' => '18:30',
            'place' => '鳥貴族 渋谷店',
            'total_amount' => 30000,
            'memo' => '先輩方が来るので遅刻厳禁！',
        ]);
        $obkai->participants()->attach($me->id, ['paid' => true]);
        $obkai->participants()->attach($members->pluck('id'));

        // 他の人が幹事の飲み会
        $party = $members[0]->organizedEvents()->create([
            'title' => '新歓コンパ',
            'date' => '2026-10-10',
            'meeting_time' => '19:00',
            'place' => '魚民 新宿店',
            'total_amount' => 24000,
        ]);
        $party->participants()->attach($members->take(3)->pluck('id'));
        $party->comments()->create([
            'body' => '新入生は無料にします！',
            'user_id' => $members[0]->id,
        ]);
    }
}