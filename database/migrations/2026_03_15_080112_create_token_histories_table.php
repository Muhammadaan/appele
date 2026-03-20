<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('token_histories', function (Blueprint $table) {
            $table->id();
              $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->integer('amount'); // jumlah token perubahan (+ atau -)

        $table->integer('balance_after'); // saldo setelah perubahan

        $table->string('type'); // admin_add, usage, correction

        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users');

        $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_histories');
    }
};
