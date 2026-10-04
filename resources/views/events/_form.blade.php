{{-- create と edit で共通の入力欄 --}}
<div class="space-y-4">
    <flux:input name="title" label="イベント名" :value="old('title', $event->title ?? '')" placeholder="OB会" />
    <flux:input name="date" type="date" label="開催日" :value="old('date', isset($event) ? $event->date->format('Y-m-d') : '')" />
    <flux:input name="meeting_time" type="time" label="集合時間" :value="old('meeting_time', isset($event->meeting_time) ? substr($event->meeting_time, 0, 5) : '')" />
    <flux:input name="place" label="店名" :value="old('place', $event->place ?? '')" placeholder="鳥貴族 渋谷店" />
    <flux:input name="total_amount" type="number" label="合計金額（円）" :value="old('total_amount', $event->total_amount ?? 0)" />
    <flux:textarea name="memo" label="幹事メモ">{{ old('memo', $event->memo ?? '') }}</flux:textarea>
</div>