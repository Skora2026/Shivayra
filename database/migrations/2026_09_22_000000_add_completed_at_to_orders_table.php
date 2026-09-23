<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The return window must anchor on the moment the order was actually
     * completed — not orders.updated_at, which every unrelated update
     * (payment note, admin touch) refreshes and thereby silently extends
     * the customer's return window.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('order_status');
        });

        // Preserve current windows exactly: existing completed orders keep the
        // anchor the old code was effectively using (their last update time).
        DB::table('orders')
            ->where('order_status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });
    }
};
