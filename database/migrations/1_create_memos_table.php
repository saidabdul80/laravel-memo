<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            $table->string('title');
            $table->text('department_id')->nullable();
            $table->tinyInteger('type')->default(0);
            $table->tinyInteger('is_read')->default(0);
            $table->text('files')->nullable(); 
            $table->text('content');
            $table->timestamps();
            $table->tinyInteger('status')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memos');
    }
};
