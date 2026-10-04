<?php

namespace Tests\Unit;

use App\Support\Settlement;
use PHPUnit\Framework\TestCase;

class SettlementTest extends TestCase
{
    public function test_払う人が1人なら受け取る人それぞれに払う(): void
    {
        // 田中(1)は7,000円、佐藤(2)は1,000円受け取る。鈴木(3)は8,000円払う
        $transfers = Settlement::transfers([1 => 7000, 2 => 1000, 3 => -8000]);

        $this->assertSame([
            ['from' => 3, 'to' => 1, 'amount' => 7000],
            ['from' => 3, 'to' => 2, 'amount' => 1000],
        ], $transfers);
    }

    public function test_受け取る人が1人なら払う人それぞれから受け取る(): void
    {
        $transfers = Settlement::transfers([1 => -2000, 2 => 5000, 3 => -3000]);

        $this->assertSame([
            ['from' => 3, 'to' => 2, 'amount' => 3000],
            ['from' => 1, 'to' => 2, 'amount' => 2000],
        ], $transfers);
    }

    public function test_全員0円なら精算はない(): void
    {
        $this->assertSame([], Settlement::transfers([1 => 0, 2 => 0]));
    }

    public function test_精算どおりに払うと全員の残高が0になる(): void
    {
        $balances = [1 => 4500, 2 => -3000, 3 => 2500, 4 => -1500, 5 => -2500];

        foreach (Settlement::transfers($balances) as $t) {
            $balances[$t['from']] += $t['amount'];
            $balances[$t['to']] -= $t['amount'];
        }

        $this->assertSame([1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0], $balances);
    }
}