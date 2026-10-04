<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 幹事
            $table->string('title');                          // 例: OB会
            $table->date('date');                             // 開催日
            $table->time('meeting_time')->nullable();         // 集合時間
            $table->string('place');                          // 店名
            $table->unsignedInteger('total_amount')->default(0); // 合計金額（円）
            $table->text('memo')->nullable();                 // 幹事メモ
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};