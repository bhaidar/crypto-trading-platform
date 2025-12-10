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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('side');
            $table->decimal('price', 18, 8);
            $table->decimal('quantity', 18, 8);
            $table->string('status')->default('open');
            $table->timestamps();

            $table->index(['asset_id', 'side', 'status', 'price']);
            $table->index(['user_id', 'status']);
        });
    }
};
