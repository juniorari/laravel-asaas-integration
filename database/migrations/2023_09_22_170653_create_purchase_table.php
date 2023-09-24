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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('product_name', 100)->comment('Purpose test only');
            $table->integer('quantity')->comment('Purpose test only');
            $table->decimal('original_value', 8, 2)->comment('Purpose test only');
            $table->decimal('discount', 8, 2)->default(0)->comment('Purpose test only');
            $table->decimal('freight', 8, 2)->default(0)->comment('Purpose test only');
            $table->decimal('total_value', 8, 2)->comment('Purpose test only');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
