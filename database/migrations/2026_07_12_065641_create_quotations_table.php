<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('quotation_number')->unique();

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'open',
                'closed',
                'cancelled',
            ])->default('pending');

            $table->date('quotation_date')->nullable();
            $table->date('valid_until')->nullable();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
            $table->index(['status', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};