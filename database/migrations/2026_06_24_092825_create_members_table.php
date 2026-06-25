<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('team_id')
                ->nullable()
                ->constrained('teams')
                ->nullOnDelete();

            $table->foreignId('manager_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->string('name');
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('image')->nullable();

            $table->date('hire_date')->nullable();

            $table->boolean('is_manager')->default(false);

            $table->enum('status', [
                'active',
                'inactive',
                'on_leave',
                'left',
            ])->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['team_id', 'manager_id']);
            $table->index(['status', 'is_manager']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};