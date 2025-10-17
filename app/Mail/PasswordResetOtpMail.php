<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * PasswordResetOtpMail
 * 
 * This is a "Mailable" class - Laravel's way of creating emails
 * It's similar to how you'd use templates in Node.js with Nodemailer
 * 
 * The Queueable trait allows emails to be sent in background jobs (optional)
 * The SerializesModels trait handles model serialization if you pass models to the email
 */
class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The OTP code to send to the user
     */
    public $otp;

    /**
     * The user's first name for personalization
     */
    public $firstName;

    /**
     * Create a new message instance.
     * 
     * @param string $otp The 6-digit OTP code
     * @param string $firstName The user's first name
     */
    public function __construct($otp, $firstName)
    {
        $this->otp = $otp;
        $this->firstName = $firstName;
    }

    /**
     * Get the message envelope.
     * This defines the email's subject and sender
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset OTP',
        );
    }

    /**
     * Get the message content definition.
     * This defines which view (HTML template) to use
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp', // We'll create this view next
        );
    }

    /**
     * Get the attachments for the message.
     * We don't need any attachments for this email
     * 
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
