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
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('asaas_id', 30)->nullable();
            $table->string('customer_id', 30)->nullable();
            $table->string('billing_type', 30)->nullable();
            $table->date('due_date')->nullable();
            $table->float('value')->nullable();
            $table->integer('installment')->nullable();
            $table->string('installment_token', 50)->nullable();
            $table->string('description')->nullable();
            $table->string('bank_slip_url')->nullable();
            $table->string('invoice_url')->nullable();
            $table->string('status', 30)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
