<x-layouts.app>
    <flux:heading size="xl" class="mb-1">支払いを記録</flux:heading>
    <flux:text class="mb-6">{{ $group->name }}</flux:text>

    <form method="POST" action="{{ route('groups.payments.store', $group) }}" class="max-w-lg space-y-6">
        @csrf

        <flux:input name="title" label="何に" :value="old('title')" placeholder="レンタカー" />
        <flux:input name="amount" type="number" label="金額（円）" :value="old('amount')" min="1" />
        <flux:input name="paid_on" type="date" label="払った日" :value="old('paid_on', today()->format('Y-m-d'))" />

        <flux:select name="payer_id" label="払った人">
            @foreach ($group->members as $member)
                <flux:select.option :value="$member->id" :selected="(int) old('payer_id', auth()->id()) === $member->id">
                    {{ $member->name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        {{-- 誰の分か。最初は全員にチェックを入れておく --}}
        <fieldset>
            <legend class="mb-2 text-sm font-medium">誰の分か</legend>
            <div class="space-y-2">
                @foreach ($group->members as $member)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="beneficiaries[]" value="{{ $member->id }}"
                               class="size-4 rounded border-zinc-300"
                               @checked(in_array($member->id, old('beneficiaries', $group->members->pluck('id')->all())))>
                        {{ $member->name }}
                    </label>
                @endforeach
            </div>
            <flux:error name="beneficiaries" />
        </fieldset>

        <div class="flex gap-2">
            <flux:button type="submit" variant="primary">記録する</flux:button>
            <flux:button :href="route('groups.show', $group)" variant="ghost">戻る</flux:button>
        </div>
    </form>
</x-layouts.app>