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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
             $table->string('title')->nullable();
        $table->text('description')->nullable();

        $table->string('badge')->nullable();

        $table->string('button_text')->nullable();
        $table->string('button_link')->nullable();

        $table->string('secondary_button_text')->nullable();
        $table->string('secondary_button_link')->nullable();

        $table->string('image');

        $table->integer('sort_order')->nullable()->default(0);

        $table->boolean('status')->nullable()->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
