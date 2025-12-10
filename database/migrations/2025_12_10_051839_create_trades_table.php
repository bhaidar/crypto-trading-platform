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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('sell_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 18, 8);
            $table->decimal('quantity', 18, 8);
            $table->decimal('commission', 18, 8);
            $table->timestamp('created_at')->useCurrent();
        });
    }
};
