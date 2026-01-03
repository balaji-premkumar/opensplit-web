<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRITICAL TABLE: expense_splits
 * 
 * This table tracks how each expense is divided among users.
 * 
 * Balance Logic:
 * - paid_share: Amount this user contributed to the expense payment
 * - owed_share: Amount this user owes from the expense
 * - Net Balance = paid_share - owed_share
 *   - Positive: User is owed money
 *   - Negative: User owes money
 * 
 * Example: $100 dinner, User A pays, split equally with User B
 * - User A: paid_share=100, owed_share=50, net=+50 (is owed $50)
 * - User B: paid_share=0, owed_share=50, net=-50 (owes $50)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')
                ->constrained('expenses')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Amount this user paid towards the expense
            $table->decimal('paid_share', 12, 2)->default(0);
            
            // Amount this user owes from the expense
            $table->decimal('owed_share', 12, 2)->default(0);
            
            $table->timestamps();

            // Each user can only have one split per expense
            $table->unique(['expense_id', 'user_id']);
            
            // Index for user balance calculations
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_splits');
    }
};
