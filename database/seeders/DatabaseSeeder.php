<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use App\Support\Split;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ログイン用のユーザー（パスワードは全員 password）
        $me = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 部員たち（メールアドレスは「ローマ字@example.com」）
        $m = collect([
            'tanaka' => '田中',
            'sato' => '佐藤',
            'suzuki' => '鈴木',
            'takahashi' => '高橋',
            'ito' => '伊藤',
            'watanabe' => '渡辺',
            'yamamoto' => '山本',
            'nakamura' => '中村',
        ])->map(fn ($name, $key) => User::factory()->create([
            'name' => $name,
            'email' => "{$key}@example.com",
        ]));

        // 別府旅行：自分が作ったグループ。精算が1件済んでいる
        $trip = $this->createGroup($me, '別府旅行', '2泊3日。レンタカーで回る', [$m['tanaka'], $m['sato'], $m['suzuki']]);
        $everyone = [$me, $m['tanaka'], $m['sato'], $m['suzuki']];
        $this->pay($trip, $m['tanaka'], 'レンタカー', 16000, $everyone, daysAgo: 20);
        $this->pay($trip, $me, '旅館', 48000, $everyone, daysAgo: 20);
        $this->pay($trip, $m['sato'], '夕食', 14000, $everyone, daysAgo: 19);
        $this->pay($trip, $m['tanaka'], 'ガソリン', 3000, $everyone, daysAgo: 18);
        $this->pay($trip, $m['suzuki'], 'お酒', 4500, [$me, $m['tanaka'], $m['suzuki']], daysAgo: 19);
        $this->pay($trip, $m['suzuki'], '精算', 10000, [$me], daysAgo: 10, settlement: true);

        // ゼミ合宿：鈴木が作ったグループ。自分が払う側
        $camp = $this->createGroup($m['suzuki'], 'ゼミ合宿', null, [$me, $m['takahashi'], $m['ito']]);
        $everyone = [$m['suzuki'], $me, $m['takahashi'], $m['ito']];
        $this->pay($camp, $m['suzuki'], '宿代', 40000, $everyone, daysAgo: 5);
        $this->pay($camp, $m['takahashi'], '買い出し', 6200, $everyone, daysAgo: 5);

        // 月例飲み会：自分は入っていない（一覧にもマイページにも出ない）
        $monthly = $this->createGroup($m['tanaka'], '月例飲み会', null, [$m['watanabe'], $m['yamamoto'], $m['nakamura']]);
        $this->pay($monthly, $m['tanaka'], '鳥貴族 新宿店', 12000, [$m['tanaka'], $m['watanabe'], $m['yamamoto'], $m['nakamura']], daysAgo: 2);
    }

    // グループを作り、作った人と $members をメンバーに入れる
    private function createGroup(User $owner, string $name, ?string $memo, array $members): Group
    {
        $group = $owner->ownedGroups()->create(['name' => $name, 'memo' => $memo]);

        $group->members()->attach($owner->id);
        foreach ($members as $member) {
            $group->members()->attach($member->id);
        }

        return $group;
    }

    // $payer が $for の人たちの分として $amount 円払った、と記録する
    private function pay(Group $group, User $payer, string $title, int $amount, array $for, int $daysAgo = 0, bool $settlement = false): void
    {
        $payment = $group->payments()->create([
            'payer_id' => $payer->id,
            'created_by' => $payer->id,
            'title' => $title,
            'amount' => $amount,
            'paid_on' => today()->subDays($daysAgo),
            'is_settlement' => $settlement,
        ]);

        $ids = array_map(fn (User $user) => $user->id, $for);

        foreach (Split::evenly($amount, $ids) as $userId => $share) {
            $payment->beneficiaries()->attach($userId, ['share' => $share]);
        }
    }
}