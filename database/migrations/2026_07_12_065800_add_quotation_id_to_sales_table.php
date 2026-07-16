<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('quotation_id')
                ->nullable()
                ->after('client_id')
                ->constrained('quotations')
                ->nullOnDelete();

            $table->index(['quotation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropIndex(['quotation_id', 'status']);
            $table->dropColumn('quotation_id');
        });
    }
};