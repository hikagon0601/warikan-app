<?php

namespace App\Http\Controllers;

use App\Support\Settlement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // マイページ
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // 自分が入っているグループ（残高の計算に使うデータもまとめて読み込む）
        $groups = $user->groups()
            ->with(['members', 'payments.beneficiaries'])
            ->latest()
            ->get();

        $myBalances = []; // [group_id => 自分の残高]
        $toPay = [];      // 自分が払う精算
        $toReceive = [];  // 自分が受け取る精算

        foreach ($groups as $group) {
            $balances = $group->balances();
            $names = $group->members->pluck('name', 'id');

            $myBalances[$group->id] = $balances[$user->id];

            foreach (Settlement::transfers($balances) as $transfer) {
                if ($transfer['from'] === $user->id) {
                    $toPay[] = ['group' => $group, 'name' => $names[$transfer['to']], 'amount' => $transfer['amount']];
                }
                if ($transfer['to'] === $user->id) {
                    $toReceive[] = ['group' => $group, 'name' => $names[$transfer['from']], 'amount' => $transfer['amount']];
                }
            }
        }

        $payTotal = array_sum(array_column($toPay, 'amount'));
        $receiveTotal = array_sum(array_column($toReceive, 'amount'));

        return view('dashboard', compact(
            'groups',
            'myBalances',
            'toPay',
            'toReceive',
            'payTotal',
            'receiveTotal',
        ));
    }
}