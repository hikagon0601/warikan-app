<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use App\Support\Settlement;

class GroupController extends Controller
{
    // 一覧（自分が入っているグループだけ）
    public function index(Request $request)
    {
        $groups = $request->user()->groups()
            ->withCount('members')
            ->latest()
            ->get();

        return view('groups.index', compact('groups'));
    }

    // 作成フォーム
    public function create()
    {
        return view('groups.create');
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $this->validateGroup($request);

        $group = $request->user()->ownedGroups()->create($validated);

        // 作った人は自動でメンバーにする
        $group->members()->attach($request->user()->id);

        return redirect()->route('groups.show', $group);
    }

    // 詳細
    public function show(Request $request, Group $group)
    {
        $this->authorizeMember($request, $group);

        $group->load([
            'owner',
            'members',
            'payments' => fn ($query) => $query->latest('paid_on')->latest('id'),
            'payments.payer',
            'payments.beneficiaries',
        ]);

        $balances = $group->balances();
        $transfers = Settlement::transfers($balances);

        // id から名前を引けるようにしておく（精算の表示で使う）
        $names = $group->members->pluck('name', 'id');

        return view('groups.show', compact('group', 'balances', 'transfers', 'names'));
    }

    // 編集フォーム
    public function edit(Request $request, Group $group)
    {
        $this->authorizeOwner($request, $group);

        return view('groups.edit', compact('group'));
    }

    // 更新
    public function update(Request $request, Group $group)
    {
        $this->authorizeOwner($request, $group);

        $group->update($this->validateGroup($request));

        return redirect()->route('groups.show', $group);
    }

    // 削除（支払いの記録も一緒に消える）
    public function destroy(Request $request, Group $group)
    {
        $this->authorizeOwner($request, $group);

        $group->delete();

        return redirect()->route('groups.index');
    }

    private function validateGroup(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'memo' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    // メンバー以外は 403 エラーにする
    private function authorizeMember(Request $request, Group $group): void
    {
        abort_unless($group->hasMember($request->user()), 403);
    }

    // 作った人以外は 403 エラーにする
    private function authorizeOwner(Request $request, Group $group): void
    {
        abort_if($group->owner_id !== $request->user()->id, 403);
    }
}