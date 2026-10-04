<?php

namespace Tests\Unit;

use App\Models\Event;
use PHPUnit\Framework\TestCase;

class SplitEvenlyTest extends TestCase
{
    public function test_割り切れる場合はそのままの金額になる(): void
    {
        $this->assertSame(3000, Event::splitEvenly(12000, 4));
    }

    public function test_割り切れない場合は100円単位で切り上げる(): void
    {
        // 10000 ÷ 3 = 3333.33… → 3400
        $this->assertSame(3400, Event::splitEvenly(10000, 3));
    }

    public function test_参加者0人なら0円(): void
    {
        $this->assertSame(0, Event::splitEvenly(10000, 0));
    }
}