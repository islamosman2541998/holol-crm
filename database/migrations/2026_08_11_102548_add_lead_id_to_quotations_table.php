<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->change();

            $table->foreignId('lead_id')
                ->nullable()
                ->after('client_id')
                ->constrained('leads')
                ->nullOnDelete();

            $table->index(['lead_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
            $table->foreignId('client_id')->nullable(false)->change();
        });
    }
};
