<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['quotation_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};