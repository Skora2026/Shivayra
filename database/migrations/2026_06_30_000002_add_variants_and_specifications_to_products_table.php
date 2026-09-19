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
        // Update products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('variant_name_1')->default('Size')->nullable();
            $table->string('variant_name_2')->default('Color')->nullable();
            $table->text('specifications')->nullable(); // JSON of key-values
            $table->text('gallery_images')->nullable(); // JSON of additional image paths
        });

        // Create product variants table
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('value_1')->nullable(); // e.g. S, M, L or 4g, 5g
            $table->string('value_2')->nullable(); // e.g. #c63939 (Hex Code) or Shade
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['variant_name_1', 'variant_name_2', 'specifications', 'gallery_images']);
        });
    }
};
