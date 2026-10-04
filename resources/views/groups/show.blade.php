<x-layouts.app>
    @php
        $isOwner = $group->owner_id === auth()->id();
    @endphp

    <div class="max-w-3xl space-y-10">
        {{-- グループの情報 --}}
        <div>
            <flux:heading size="xl">{{ $group->name }}</flux:heading>
            @if ($group->memo)
                <flux:text class="mt-2 whitespace-pre-line">{{ $group->memo }}</flux:text>
            @endif

            @if ($isOwner)
                <div class="mt-4 flex gap-2">
                    <flux:button size="sm" :href="route('groups.edit', $group)">編集</flux:button>
                    <form method="POST" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm('グループと支払いの記録をすべて削除しますか？')">
                        @csrf
                        @method('DELETE')
                        <flux:button size="sm" variant="danger" type="submit">削除</flux:button>
                    </form>
                </div>
            @endif
        </div>

        {{-- 支払いの記録 --}}
        <div>
            <div class="flex items-center justify-between">
                <flux:heading size="lg">支払いの記録</flux:heading>
                <flux:button size="sm" variant="primary" icon="plus" :href="route('groups.payments.create', $group)">支払いを記録</flux:button>
            </div>
            <flux:text class="mt-1">
                合計 {{ number_format($group->payments->where('is_settlement', false)->sum('amount')) }}円
            </flux:text>

            <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                @forelse ($group->payments as $payment)
                    <li class="flex items-start justify-between gap-4 px-4 py-3">
                        <div>
                            <div class="font-semibold">
                                @if ($payment->is_settlement)
                                    <flux:badge size="sm" color="green" class="mr-1">精算</flux:badge>
                                    {{ $payment->payer->name }} → {{ $payment->beneficiaries->first()->name }}
                                @else
                                    {{ $payment->title }}
                                @endif
                                <span class="ml-1 text-sm font-normal text-zinc-500">{{ $payment->paid_on->format('n/j') }}</span>
                            </div>
                            @unless ($payment->is_settlement)
                                <div class="text-sm text-zinc-500">
                                    {{ $payment->payer->name }}が払った ・
                                    @if ($payment->beneficiaries->count() === $group->members->count())
                                        全員の分
                                    @else
                                        {{ $payment->beneficiaries->pluck('name')->join('、') }}の分
                                    @endif
                                </div>
                            @endunless
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="whitespace-nowrap font-semibold">{{ number_format($payment->amount) }}円</span>
                            @if ($payment->created_by === auth()->id() || $isOwner)
                                <form method="POST" action="{{ route('groups.payments.destroy', [$group, $payment]) }}" onsubmit="return confirm('この記録を削除しますか？')">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button size="sm" variant="ghost" icon="trash" type="submit" />
                                </form>
                            @endif
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-3 text-zinc-500">まだ支払いの記録がありません。</li>
                @endforelse
            </ul>
        </div>

        {{-- 精算 --}}
        <div>
            <flux:heading size="lg">精算</flux:heading>

            @if (count($transfers) === 0)
                <flux:text class="mt-2">精算はありません。全員の貸し借りが0円です。</flux:text>
            @else
                <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    @foreach ($transfers as $transfer)
                        <li class="flex items-center justify-between gap-4 px-4 py-3">
                            <div>
                                <span class="font-semibold">{{ $names[$transfer['from']] }}</span>
                                → <span class="font-semibold">{{ $names[$transfer['to']] }}</span>
                                <span class="ml-2">{{ number_format($transfer['amount']) }}円</span>
                            </div>
                            <form method="POST" action="{{ route('groups.settlements.store', $group) }}"
                                  onsubmit="return confirm('この精算が終わったことを記録しますか？')">
                                @csrf
                                <input type="hidden" name="from_id" value="{{ $transfer['from'] }}">
                                <input type="hidden" name="to_id" value="{{ $transfer['to'] }}">
                                <input type="hidden" name="amount" value="{{ $transfer['amount'] }}">
                                <flux:button size="sm" type="submit" icon="check">精算した</flux:button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- 一人ひとりの残高 --}}
            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($group->members as $member)
                    @php $balance = $balances[$member->id]; @endphp
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-2 text-sm dark:bg-zinc-700">
                        <span>{{ $member->name }}</span>
                        @if ($balance > 0)
                            <span class="font-semibold text-green-600">{{ number_format($balance) }}円 受け取る</span>
                        @elseif ($balance < 0)
                            <span class="font-semibold text-red-600">{{ number_format(-$balance) }}円 払う</span>
                        @else
                            <span class="text-zinc-500">0円</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- メンバー --}}
        <div>
            <flux:heading size="lg">メンバー（{{ $group->members->count() }}人）</flux:heading>

            <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                @foreach ($group->members as $member)
                    <li class="flex items-center justify-between px-4 py-2">
                        <span>
                            {{ $member->name }}
                            @if ($member->id === $group->owner_id)
                                <flux:badge size="sm" class="ml-1">作った人</flux:badge>
                            @endif
                        </span>
                        @if ($isOwner && $member->id !== $group->owner_id)
                            <form method="POST" action="{{ route('groups.members.destroy', [$group, $member]) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button size="sm" variant="ghost" type="submit">外す</flux:button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            <flux:error name="member" class="mt-2" />

            @if ($isOwner)
                <form method="POST" action="{{ route('groups.members.store', $group) }}" class="mt-4 flex items-end gap-2">
                    @csrf
                    <div class="flex-1">
                        <flux:input name="email" type="email" label="メンバーを追加（メールアドレス）" placeholder="tanaka@example.com" />
                    </div>
                    <flux:button type="submit" icon="user-plus">追加</flux:button>
                </form>
            @endif
        </div>

        <a href="{{ route('groups.index') }}" class="text-sm text-zinc-500 hover:underline">← グループ一覧に戻る</a>
    </div>
</x-layouts.app>