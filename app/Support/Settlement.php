<?php

namespace App\Support;

class Settlement
{
    /**
     * 一人ひとりの残高から、「誰が誰にいくら払えば0になるか」を組み立てる。
     * 残高がプラスの人は受け取る側、マイナスの人は払う側。
     *
     * 例: [1 => 7000, 2 => 1000, 3 => -8000]
     *   → [['from' => 3, 'to' => 1, 'amount' => 7000], ['from' => 3, 'to' => 2, 'amount' => 1000]]
     *
     * @param  array<int, int>  $balances  [user_id => 残高]
     * @return array<int, array{from: int, to: int, amount: int}>
     */
    public static function transfers(array $balances): array
    {
        $creditors = []; // 受け取る人
        $debtors = [];   // 払う人

        foreach ($balances as $userId => $balance) {
            if ($balance > 0) {
                $creditors[] = ['id' => $userId, 'amount' => $balance];
            } elseif ($balance < 0) {
                $debtors[] = ['id' => $userId, 'amount' => -$balance];
            }
        }

        // 金額の大きい順に並べる（同じ金額なら id の小さい順）
        $byAmount = function (array $a, array $b): int {
            if ($a['amount'] !== $b['amount']) {
                return $b['amount'] <=> $a['amount'];
            }

            return $a['id'] <=> $b['id'];
        };
        usort($creditors, $byAmount);
        usort($debtors, $byAmount);

        // 払う人と受け取る人を、先頭から順に組み合わせていく
        $transfers = [];
        $i = 0; // 払う人の位置
        $j = 0; // 受け取る人の位置

        while ($i < count($debtors) && $j < count($creditors)) {
            $amount = min($debtors[$i]['amount'], $creditors[$j]['amount']);

            $transfers[] = [
                'from' => $debtors[$i]['id'],
                'to' => $creditors[$j]['id'],
                'amount' => $amount,
            ];

            $debtors[$i]['amount'] -= $amount;
            $creditors[$j]['amount'] -= $amount;

            if ($debtors[$i]['amount'] === 0) {
                $i++;
            }
            if ($creditors[$j]['amount'] === 0) {
                $j++;
            }
        }

        return $transfers;
    }
}