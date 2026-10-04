<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    // メールアドレスでメンバーを追加する（作った人のみ）
    public function store(Request $request, Group $group)
    {
        abort_if($group->owner_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'このメールアドレスのユーザーは登録されていません。',
        ]);

        $user = User::where('email', $validated['email'])->first();

        $group->members()->syncWithoutDetaching([$user->id]);

        return back();
    }

    // メンバーから外す（作った人のみ）
    public function destroy(Request $request, Group $group, User $user)
    {
        abort_if($group->owner_id !== $request->user()->id, 403);

        // 支払いの記録がある人（払った人、または誰かの支払いの対象になっている人）は外せない
        $hasRecords = $group->payments()
            ->where(function ($query) use ($user) {
                $query->where('payer_id', $user->id)
                    ->orWhereHas('beneficiaries', fn ($q) => $q->whereKey($user->id));
            })
            ->exists();

        if ($hasRecords) {
            return back()->withErrors(['member' => "{$user->name}さんは支払いの記録があるため、外せません。"]);
        }

        // 作った人自身は外せない
        abort_if($user->id === $group->owner_id, 403);

        $group->members()->detach($user->id);

        return back();
    }
}