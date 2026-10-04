<x-layouts.app>
    <div class="max-w-4xl space-y-8">
        <flux:heading size="xl">こんにちは、{{ auth()->user()->name }}さん</flux:heading>

        {{-- 上段：数字のカード3枚 --}}
        <div class="grid gap-4 md:grid-cols-3">
            <flux:card>
                <flux:text>払う</flux:text>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ number_format($payTotal) }}円</div>
                <flux:text class="mt-1">{{ count($toPay) }}件の精算</flux:text>
            </flux:card>

            <flux:card>
                <flux:text>受け取る</flux:text>
                <div class="mt-2 text-2xl font-bold text-green-600">{{ number_format($receiveTotal) }}円</div>
                <flux:text class="mt-1">{{ count($toReceive) }}件の精算</flux:text>
            </flux:card>

            <flux:card>
                <flux:text>グループ</flux:text>
                <div class="mt-2 text-2xl font-bold">{{ $groups->count() }}件</div>
                <flux:text class="mt-1">入っている割り勘グループ</flux:text>
            </flux:card>
        </div>

        {{-- 中段：自分が関わる精算 --}}
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <flux:heading size="lg">あなたが払う</flux:heading>
                <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    @forelse ($toPay as $row)
                        <li>
                            <a href="{{ route('groups.show', $row['group']) }}" class="flex items-center justify-between p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <div>
                                    <div class="font-semibold">{{ $row['name'] }}さんへ</div>
                                    <div class="text-sm text-zinc-500">{{ $row['group']->name }}</div>
                                </div>
                                <span class="font-semibold text-red-600">{{ number_format($row['amount']) }}円</span>
                            </a>
                        </li>
                    @empty
                        <li class="p-4 text-zinc-500">払う精算はありません。</li>
                    @endforelse
                </ul>
            </div>

            <div>
                <flux:heading size="lg">あなたが受け取る</flux:heading>
                <ul class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    @forelse ($toReceive as $row)
                        <li>
                            <a href="{{ route('groups.show', $row['group']) }}" class="flex items-center justify-between p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                                <div>
                                    <div class="font-semibold">{{ $row['name'] }}さんから</div>
                                    <div class="text-sm text-zinc-500">{{ $row['group']->name }}</div>
                                </div>
                                <span class="font-semibold text-green-600">{{ number_format($row['amount']) }}円</span>
                            </a>
                        </li>
                    @empty
                        <li class="p-4 text-zinc-500">受け取る精算はありません。</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- 下段：グループごとの自分の残高 --}}
        <div>
            <flux:heading size="lg">グループ</flux:heading>
            <div class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                @forelse ($groups as $group)
                    @php $balance = $myBalances[$group->id]; @endphp
                    <a href="{{ route('groups.show', $group) }}"
                       class="flex items-center justify-between gap-4 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <div>
                            <div class="font-semibold">{{ $group->name }}</div>
                            <div class="text-sm text-zinc-500">メンバー {{ $group->members->count() }}人</div>
                        </div>
                        @if ($balance > 0)
                            <flux:badge color="green" size="sm">{{ number_format($balance) }}円 受け取る</flux:badge>
                        @elseif ($balance < 0)
                            <flux:badge color="red" size="sm">{{ number_format(-$balance) }}円 払う</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">精算済み</flux:badge>
                        @endif
                    </a>
                @empty
                    <p class="p-4 text-zinc-500">まだグループに入っていません。</p>
                @endforelse
            </div>

            <div class="mt-3 text-right">
                <flux:link :href="route('groups.create')" wire:navigate>グループを作る →</flux:link>
            </div>
        </div>
    </div>
</x-layouts.app>