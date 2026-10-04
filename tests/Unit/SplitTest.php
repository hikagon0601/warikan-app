<?php

namespace Tests\Unit;

use App\Support\Split;
use PHPUnit\Framework\TestCase;

class SplitTest extends TestCase
{
    public function test_divisible_amount_gives_everyone_the_same_share(): void
    {
        $this->assertSame([1 => 4000, 2 => 4000, 3 => 4000], Split::evenly(12000, [1, 2, 3]));
    }

    public function test_leftover_yen_goes_to_smallest_user_ids(): void
    {
        // 10000 ÷ 3 = 3333 あまり 1
        $this->assertSame([1 => 3334, 2 => 3333, 3 => 3333], Split::evenly(10000, [3, 1, 2]));
    }

    public function test_shares_add_up_to_the_original_amount(): void
    {
        $shares = Split::evenly(10001, [5, 8, 13, 21]);

        $this->assertSame(10001, array_sum($shares));
    }

    public function test_single_person_pays_the_full_amount(): void
    {
        $this->assertSame([7 => 3000], Split::evenly(3000, [7]));
    }
}