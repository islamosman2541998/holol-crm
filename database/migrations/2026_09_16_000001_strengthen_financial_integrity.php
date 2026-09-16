<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateQuotation = DB::table('sales')
            ->select('quotation_id')
            ->whereNotNull('quotation_id')
            ->groupBy('quotation_id')
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicateQuotation) {
            throw new \RuntimeException(
                'Cannot add the sales quotation unique index: duplicate quotation_id '
                . $duplicateQuotation->quotation_id
                . ' must be resolved first.'
            );
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->unique('quotation_id', 'sales_quotation_id_unique');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique('sales_quotation_id_unique');
        });
    }
};
