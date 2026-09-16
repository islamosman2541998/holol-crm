<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reversals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->unique()
                ->constrained('payments')
                ->cascadeOnDelete();
            $table->foreignId('reversed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->timestamp('reversed_at');
            $table->timestamps();

            $table->index('reversed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reversals');
    }
};
