<?php
// database/migrations/2024_01_01_000001_add_role_and_admin_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'super_admin','admin','accountant','hr','manager','viewer'
            ])->default('viewer')->after('email');

            $table->boolean('is_active')->default(true)->after('role');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('avatar')->nullable()->after('last_login_at');

            // OTP fields (if not already present)
            if (!Schema::hasColumn('users', 'email_otp')) {
                $table->string('email_otp', 6)->nullable();
                $table->timestamp('email_otp_expires')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','is_active','last_login_at','avatar']);
        });
    }
};
