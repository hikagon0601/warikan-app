<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('users')->restrictOnDelete();   // 払った人
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); // 記録した人
            $table->string('title', 100);                       // 例: レンタカー
            $table->unsignedInteger('amount');                  // 金額（円）
            $table->date('paid_on');                            // 払った日
            $table->boolean('is_settlement')->default(false);   // 精算の記録かどうか（16章で使う）
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};