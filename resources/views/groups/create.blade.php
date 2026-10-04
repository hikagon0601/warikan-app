<x-layouts.app>
    <flux:heading size="xl" class="mb-6">グループを作る</flux:heading>

    <form method="POST" action="{{ route('groups.store') }}" class="max-w-lg space-y-6">
        @csrf
        @include('groups._form')
        <flux:button type="submit" variant="primary">作成する</flux:button>
    </form>
</x-layouts.app>