<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettlementController extends Controller
{
    // 「精算した」を記録する（払った人 from → 受け取った人 to）
    public function store(Request $request, Group $group)
    {
        abort_unless($group->hasMember($request->user()), 403);

        $member = Rule::exists('group_user', 'user_id')->where('group_id', $group->id);

        $validated = $request->validate([
            'from_id' => ['required', 'integer', $member],
            'to_id' => ['required', 'integer', 'different:from_id', $member],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        // 精算も「支払い」の1つとして記録する。
        // from が to の分を払った、と記録すれば、2人の残高がその分だけ0に近づく
        DB::transaction(function () use ($request, $group, $validated) {
            $payment = $group->payments()->create([
                'payer_id' => $validated['from_id'],
                'created_by' => $request->user()->id,
                'title' => '精算',
                'amount' => $validated['amount'],
                'paid_on' => today(),
                'is_settlement' => true,
            ]);

            $payment->beneficiaries()->attach($validated['to_id'], ['share' => $validated['amount']]);
        });

        return back();
    }
}