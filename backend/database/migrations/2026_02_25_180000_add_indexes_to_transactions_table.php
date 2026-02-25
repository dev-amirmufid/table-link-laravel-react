<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Indexes for aggregation-heavy queries as per refactor.md:
     * - buyer_id, seller_id, item_id (JOIN optimization)
     * - created_at (date filtering)
     * - compound index (item_id, created_at)
     */
    public function up(): void
    {
        // Index for buyer_id - used in top buyers query
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('buyer_id', 'transactions_buyer_id_index');
        });

        // Index for seller_id - used in top sellers query
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('seller_id', 'transactions_seller_id_index');
        });

        // Index for item_id - used in trending items query
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('item_id', 'transactions_item_id_index');
        });

        // Index for created_at - used in date filtering
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at', 'transactions_created_at_index');
        });

        // Compound index for item_id + created_at - used for filtering items by date
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['item_id', 'created_at'], 'transactions_item_created_index');
        });

        // Compound index for buyer_id + created_at - used for filtering buyers by date
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['buyer_id', 'created_at'], 'transactions_buyer_created_index');
        });

        // Compound index for seller_id + created_at - used for filtering sellers by date
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['seller_id', 'created_at'], 'transactions_seller_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_buyer_id_index');
            $table->dropIndex('transactions_seller_id_index');
            $table->dropIndex('transactions_item_id_index');
            $table->dropIndex('transactions_created_at_index');
            $table->dropIndex('transactions_item_created_index');
            $table->dropIndex('transactions_buyer_created_index');
            $table->dropIndex('transactions_seller_created_index');
        });
    }
};
