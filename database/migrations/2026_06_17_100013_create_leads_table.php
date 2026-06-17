<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('converted_client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->nullable();

            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'unqualified',
                'converted',
                'lost',
            ])->default('new');

            $table->text('notes')->nullable();

            $table->timestamp('converted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index(['name', 'company', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};