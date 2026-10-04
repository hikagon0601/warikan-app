<x-layouts.app>
    <div class="max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">割り勘グループ</flux:heading>
            <flux:button variant="primary" icon="plus" :href="route('groups.create')">グループを作る</flux:button>
        </div>

        <div class="space-y-3">
            @forelse ($groups as $group)
                <a href="{{ route('groups.show', $group) }}"
                   class="block rounded-lg border border-zinc-200 p-4 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700">
                    <div class="font-semibold">{{ $group->name }}</div>
                    <div class="text-sm text-zinc-500">メンバー {{ $group->members_count }}人</div>
                </a>
            @empty
                <p class="text-zinc-500">まだグループがありません。「グループを作る」から始めましょう。</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>