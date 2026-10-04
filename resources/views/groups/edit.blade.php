<x-layouts.app>
    <flux:heading size="xl" class="mb-6">グループを編集</flux:heading>

    <form method="POST" action="{{ route('groups.update', $group) }}" class="max-w-lg space-y-6">
        @csrf
        @method('PUT')
        @include('groups._form')
        <flux:button type="submit" variant="primary">更新する</flux:button>
    </form>
</x-layouts.app>