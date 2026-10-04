<x-layouts.app>
    <flux:heading size="xl" class="mb-6">飲み会を編集</flux:heading>

    <form method="POST" action="{{ route('events.update', $event) }}" class="max-w-lg space-y-6">
        @csrf
        @method('PUT')
        @include('events._form')
        <flux:button type="submit" variant="primary">更新する</flux:button>
    </form>
</x-layouts.app>