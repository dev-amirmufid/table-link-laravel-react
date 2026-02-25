<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->integer('minimum_price');
            $table->integer('maximum_price');
            $table->timestamps();

            $table->index('item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
