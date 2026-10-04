<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Payment;
use App\Support\Split;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    // 支払いを記録するフォーム
    public function create(Request $request, Group $group)
    {
        abort_unless($group->hasMember($request->user()), 403);

        $group->load('members');

        return view('payments.create', compact('group'));
    }

    // 保存
    public function store(Request $request, Group $group)
    {
        abort_unless($group->hasMember($request->user()), 403);

        // 「このグループのメンバーであること」を確かめるルール
        $member = Rule::exists('group_user', 'user_id')->where('group_id', $group->id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'integer', 'min:1', 'max:10000000'],
            'paid_on' => ['required', 'date'],
            'payer_id' => ['required', 'integer', $member],
            'beneficiaries' => ['required', 'array', 'min:1'],
            'beneficiaries.*' => ['integer', 'distinct', $member],
        ], [
            'beneficiaries.required' => '誰の分かを1人以上選んでください。',
        ]);

        // 支払いと負担額は、両方そろって保存されないと金額が合わなくなるので、まとめて保存する
        DB::transaction(function () use ($request, $group, $validated) {
            $payment = $group->payments()->create([
                'payer_id' => $validated['payer_id'],
                'created_by' => $request->user()->id,
                'title' => $validated['title'],
                'amount' => $validated['amount'],
                'paid_on' => $validated['paid_on'],
            ]);

            $shares = Split::evenly($validated['amount'], $validated['beneficiaries']);

            foreach ($shares as $userId => $share) {
                $payment->beneficiaries()->attach($userId, ['share' => $share]);
            }
        });

        return redirect()->route('groups.show', $group);
    }

    // 削除（記録した人と、グループを作った人だけ）
    public function destroy(Request $request, Group $group, Payment $payment)
    {
        abort_unless($payment->group_id === $group->id, 404);

        $userId = $request->user()->id;
        abort_unless($payment->created_by === $userId || $group->owner_id === $userId, 403);

        $payment->delete();

        return back();
    }
}