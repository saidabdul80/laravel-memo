<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memo_id')->constrained()->onDelete('cascade');
            $table->text('comment'); 
            $table->text('files'); 
            $table->bigInteger('approver_id');
            $table->bigInteger('approver_type');
            $table->tinyInteger('status')->default(4);
            $table->timestamps();
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_comments');
    }
};
