<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_followups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note');

            $table->dateTime('next_followup_at')->nullable();

            $table->enum('status', [
                'pending',
                'done',
                'cancelled',
            ])->default('pending');

            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('next_followup_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_followups');
    }
};