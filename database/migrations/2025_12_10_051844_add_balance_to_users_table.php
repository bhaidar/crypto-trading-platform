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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 18, 8)->default(0)->after('remember_token');
            $table->decimal('locked_balance', 18, 8)->default(0)->after('balance');
        });
    }
};
