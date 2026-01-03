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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3)->default('USD');
            $table->foreignId('paid_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('group_id')
                ->constrained('groups')
                ->onDelete('cascade');
            $table->date('expense_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index for efficient querying
            $table->index(['group_id', 'expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
