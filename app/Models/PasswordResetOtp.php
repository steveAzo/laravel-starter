<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PasswordResetOtp Model
 * 
 * This model handles password reset OTPs (One-Time Passwords).
 * OTPs are stored with an expiration time for security.
 * 
 * @property string $email - The user's email address
 * @property string $otp - The hashed OTP code
 * @property \Carbon\Carbon $expires_at - When this OTP expires
 * @property \Carbon\Carbon $created_at - When this OTP was created
 */
class PasswordResetOtp extends Model
{
    /**
     * The table associated with the model.
     * This tells Laravel which database table to use
     */
    protected $table = 'password_reset_otps';

    /**
     * Disable updated_at timestamp since we only need created_at
     * We don't need to track updates because OTPs are either created or deleted
     */
    public $timestamps = true;
    const UPDATED_AT = null; // Disable updated_at

    /**
     * The attributes that can be mass assigned.
     * This is a security feature - only these fields can be filled using create()
     */
    protected $fillable = [
        'email',
        'otp',
        'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     * This automatically converts database values to PHP types
     */
    protected $casts = [
        'expires_at' => 'datetime', // Converts to Carbon datetime object
    ];

    /**
     * Delete expired OTPs for a specific email
     * This is a helper method to clean up old OTPs
     * 
     * @param string $email
     * @return int Number of deleted records
     */
    public static function deleteExpired($email)
    {
        return self::where('email', $email)
            ->where('expires_at', '<=', now())
            ->delete();
    }

    /**
     * Check if an OTP exists and is still valid
     * 
     * @param string $email
     * @return bool
     */
    public static function hasValidOtp($email)
    {
        return self::where('email', $email)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
