<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('category', ['work', 'finance', 'marriage', 'personal', 'health', 'learning'])->default('personal');
            $table->enum('horizon', ['quarterly', 'yearly', 'long_term'])->default('quarterly');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'abandoned'])->default('not_started');
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
