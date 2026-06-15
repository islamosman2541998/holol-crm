<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->nullable();

            $table->text('notes')->nullable();

            $table->enum('status', [
                'new',
                'active',
                'inactive',
                'lost',
            ])->default('new');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index(['name', 'company']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};