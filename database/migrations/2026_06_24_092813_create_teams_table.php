<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();

            // هنسيبه بدون foreign key دلوقتي عشان members لسه هيتعمل بعده
            $table->unsignedBigInteger('manager_member_id')->nullable();

            $table->string('name');
            $table->string('code')->unique();
            $table->string('role_name')->unique();

            $table->text('description')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('manager_member_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};