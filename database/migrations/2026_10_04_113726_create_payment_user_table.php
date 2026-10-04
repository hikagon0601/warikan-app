<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_user', function (Blueprint $table) {
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('share'); // この人の負担額（合計は payments.amount と同じ）

            $table->primary(['payment_id', 'user_id']); // 同じ人を2回入れない
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_user');
    }
};