<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 飲み会アプリのテーブルを消す。外部キーで参照している側から先に消す
    public function up(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('events');
    }

    // 元に戻す処理は書かない（飲み会アプリには戻さないため）
    public function down(): void
    {
        //
    }
};