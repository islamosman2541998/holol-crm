<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('type', [
                'call',
                'whatsapp',
                'meeting',
                'note',
                'email',
            ])->default('note');

            $table->text('note');

            $table->dateTime('next_followup_at')->nullable();

            $table->enum('status', [
                'pending',
                'done',
                'cancelled',
            ])->default('pending');

            $table->timestamps();

            $table->index(['lead_id', 'status']);
            $table->index('next_followup_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_followups');
    }
};