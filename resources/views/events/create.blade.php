<x-layouts.app>
    <flux:heading size="xl" class="mb-6">飲み会を作る</flux:heading>

    <form method="POST" action="{{ route('events.store') }}" class="max-w-lg space-y-6">
        @csrf
        @include('events._form')
        <flux:button type="submit" variant="primary">作成する</flux:button>
    </form>
</x-layouts.app>