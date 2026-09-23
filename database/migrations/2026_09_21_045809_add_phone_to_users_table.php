<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add columns that may not exist yet
            if (! Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('google_id');
            }
            if (! Schema::hasColumn('users', 'phone_verified')) {
                $table->boolean('phone_verified')->default(false)->after('email_verified');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'email_verified', 'phone_verified']);
        });
    }
};
