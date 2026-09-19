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
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('tax_percent', 5, 2)->default(5.00)->after('youtube');
            $table->decimal('delivery_fee', 10, 2)->default(60.00)->after('tax_percent');
            $table->decimal('min_order_for_free_delivery', 10, 2)->default(1000.00)->after('delivery_fee');
            $table->boolean('is_cod_enabled')->default(true)->after('min_order_for_free_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'tax_percent',
                'delivery_fee',
                'min_order_for_free_delivery',
                'is_cod_enabled'
            ]);
        });
    }
};
