<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memo_id')->constrained('memos')->onDelete('cascade');
            $table->bigInteger('approver_id');
            $table->tinyInteger('status')->default(4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_approvers');
    }
};
