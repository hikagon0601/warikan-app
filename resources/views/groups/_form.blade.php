{{-- create と edit で共通の入力欄 --}}
<div class="space-y-4">
    <flux:input name="name" label="グループ名" :value="old('name', $group->name ?? '')" placeholder="別府旅行" />
    <flux:textarea name="memo" label="メモ" rows="3">{{ old('memo', $group->memo ?? '') }}</flux:textarea>
</div>