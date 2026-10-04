<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('paid')->default(false); // 支払い済みかどうか
            $table->timestamps();

            $table->unique(['event_id', 'user_id']); // 同じ人が2回参加しないように
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_user');
    }
};