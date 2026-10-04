<?php

namespace App\Support;

class Split
{
    /**
     * 金額を人数で1円単位に分ける。
     * 割り切れない1円は、user_id の小さい人から1円ずつ多く負担する。
     *
     * 例: evenly(10000, [3, 1, 2]) → [1 => 3334, 2 => 3333, 3 => 3333]
     *
     * @param  int[]  $userIds
     * @return array<int, int>  [user_id => 負担額]
     */
    public static function evenly(int $amount, array $userIds): array
    {
        sort($userIds);

        $count = count($userIds);
        if ($count === 0) {
            return [];
        }

        $base = intdiv($amount, $count);   // 一人あたり（1円未満は切り捨て）
        $remainder = $amount % $count;     // 割り切れずに余った円

        $shares = [];
        foreach ($userIds as $i => $userId) {
            $shares[$userId] = $base + ($i < $remainder ? 1 : 0);
        }

        return $shares;
    }
}