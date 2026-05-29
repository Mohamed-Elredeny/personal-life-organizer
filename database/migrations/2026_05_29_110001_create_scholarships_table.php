<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('university')->nullable();
            $table->string('country')->nullable();
            $table->enum('level', ['phd', 'masters', 'postdoc', 'other'])->default('phd');
            $table->enum('status', [
                'interested', 'shortlisted', 'applied', 'interview',
                'accepted', 'rejected', 'withdrawn', 'enrolled',
            ])->default('interested');
            $table->date('deadline')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('funding_type')->nullable();
            $table->string('url', 1024)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
