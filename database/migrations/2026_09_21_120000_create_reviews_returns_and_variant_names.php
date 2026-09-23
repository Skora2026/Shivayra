<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Variants: a human label per variant row (e.g. "18K / Rose Gold")
        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'name')) {
                $table->string('name')->nullable()->after('product_id');
            }
        });

        // 2. Store settings: return policy + home section card counts
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'return_window_days')) {
                $table->unsignedInteger('return_window_days')->default(7)->after('is_cod_enabled');
            }
            if (! Schema::hasColumn('settings', 'featured_limit')) {
                $table->unsignedInteger('featured_limit')->default(8)->after('return_window_days');
            }
            if (! Schema::hasColumn('settings', 'trending_limit')) {
                $table->unsignedInteger('trending_limit')->default(8)->after('featured_limit');
            }
        });

        // 3. Products: is the item returnable at all (admin controls per product)
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_returnable')) {
                $table->boolean('is_returnable')->default(true)->after('is_featured');
            }
        });

        // 4. Customer reviews — only for verified purchases (delivered orders)
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5
            $table->string('title')->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->unique('order_item_id'); // one review per purchased line item
            $table->index(['product_id', 'is_approved']);
        });

        // 5. Return / refund requests
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // RET-XXXXXX
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'refunded'])->default('pending');
            $table->text('reason');                       // customer's reason
            $table->text('admin_note')->nullable();       // admin's note on decision
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->unique('order_item_id'); // one active request per line item
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_requests');
        Schema::dropIfExists('product_reviews');

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_returnable')) {
                $table->dropColumn('is_returnable');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            foreach (['return_window_days', 'featured_limit', 'trending_limit'] as $col) {
                if (Schema::hasColumn('settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
