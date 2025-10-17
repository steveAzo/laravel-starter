<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration for password_reset_otps table
 * 
 * This table stores OTP codes for password resets.
 * Each OTP is temporary and expires after a set time (10 minutes).
 * 
 * Think of migrations as "version control for your database"
 * They let you modify your database structure and share those changes with your team
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     * This method creates the table when you run: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('password_reset_otps', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            
            // Email of the user requesting password reset
            $table->string('email')->index(); // index() makes lookups faster
            
            // The OTP code (will be hashed for security, like passwords)
            $table->string('otp');
            
            // When this OTP expires (we'll set this to 10 minutes from creation)
            $table->timestamp('expires_at');
            
            // When this OTP was created
            $table->timestamp('created_at')->useCurrent();
            
            // Index on expires_at for efficient cleanup of expired OTPs
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     * This method drops the table when you run: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_otps');
    }
};
