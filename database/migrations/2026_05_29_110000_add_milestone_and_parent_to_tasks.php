<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('milestone_id')->nullable()->after('goal_id')
                ->constrained()->nullOnDelete();
            $table->foreignId('parent_task_id')->nullable()->after('milestone_id')
                ->constrained('tasks')->nullOnDelete();
            $table->index('parent_task_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['milestone_id']);
            $table->dropForeign(['parent_task_id']);
            $table->dropColumn(['milestone_id', 'parent_task_id']);
        });
    }
};
