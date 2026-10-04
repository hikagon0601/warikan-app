<x-layouts.app>
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">飲み会一覧</flux:heading>
        <flux:button variant="primary" :href="route('events.create')">＋ 飲み会を作る</flux:button>
    </div>

    {{-- 検索フォーム --}}
    <form method="GET" action="{{ route('events.index') }}" class="flex gap-2 mb-6">
        <flux:input name="keyword" :value="$keyword" placeholder="イベント名・店名で検索" />
        <flux:button type="submit">検索</flux:button>
    </form>

    <div class="space-y-3">
        @forelse ($events as $event)
            <a href="{{ route('events.show', $event) }}"
               class="block rounded-lg border border-zinc-200 p-4 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700">
                <div class="font-semibold">{{ $event->date->format('Y-m-d') }} {{ $event->title }}</div>
                <div class="text-sm text-zinc-500">
                    {{ $event->place }} ・ 参加 {{ $event->participants_count }}人
                </div>
            </a>
        @empty
            <p class="text-zinc-500">飲み会はまだありません。</p>
        @endforelse
    </div>
</x-layouts.app>