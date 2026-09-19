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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_name');
            $table->string('coupon_code')->unique();

            $table->decimal('discount', 10, 2);

            $table->enum('discount_type', ['percentage', 'fixed']);

            $table->date('start_date');
            $table->date('end_date');

            $table->decimal('min_order_amount', 10, 2)->nullable();

            $table->decimal('max_discount', 10, 2)->nullable();

            $table->integer('usage_limit')->nullable();

            $table->integer('used_count')->default(0);

            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
