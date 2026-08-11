<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'member_id']);
        });

        DB::table('tasks')
            ->whereNotNull('assigned_member_id')
            ->orderBy('id')
            ->select('id', 'assigned_member_id')
            ->chunkById(500, function ($tasks) {
                $now = now();

                DB::table('task_member')->insert(
                    $tasks->map(fn ($task) => [
                        'task_id' => $task->id,
                        'member_id' => $task->assigned_member_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_member_id']);
            $table->dropIndex(['assigned_member_id', 'status']);
            $table->dropColumn('assigned_member_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_member_id')
                ->nullable()
                ->after('id')
                ->constrained('members')
                ->nullOnDelete();

            $table->index(['assigned_member_id', 'status']);
        });

        DB::table('task_member')
            ->orderBy('task_id')
            ->orderBy('id')
            ->get(['task_id', 'member_id'])
            ->groupBy('task_id')
            ->each(function ($rows, $taskId) {
                DB::table('tasks')
                    ->where('id', $taskId)
                    ->update(['assigned_member_id' => $rows->first()->member_id]);
            });

        Schema::dropIfExists('task_member');
    }
};
