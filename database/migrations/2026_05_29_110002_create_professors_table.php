<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('professors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scholarship_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('university')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('lab')->nullable();
            $table->string('research_area')->nullable();
            $table->string('website', 1024)->nullable();
            $table->enum('status', [
                'planned', 'contacted', 'replied', 'meeting_scheduled',
                'positive', 'negative', 'no_response', 'closed',
            ])->default('planned');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('last_contact_at')->nullable();
            $table->date('next_follow_up_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('next_follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professors');
    }
};
