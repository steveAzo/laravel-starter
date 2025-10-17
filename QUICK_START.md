# 🚀 Quick Start Guide

## Setup Checklist

### 1. Configure Gmail SMTP
Update your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_ENCRYPTION=tls
```

**Important:** Use Gmail App Password, not your regular password!
- Go to Google Account → Security → 2-Step Verification → App passwords
- Generate a new app password for "Mail"
- Copy the 16-character password to `.env`

### 2. Set Token Expiration (Optional)
In `.env`, add or modify:
```env
SANCTUM_EXPIRATION=1440  # 24 hours (in minutes)
```

### 3. Clear Config Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Start the Server
```bash
php artisan serve
```

---

## 📡 API Endpoints Quick Reference

### Public Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api/signup` | Register new user |
| POST | `/api/login` | Login user |
| POST | `/api/forgot-password` | Request password reset OTP |
| POST | `/api/reset-password` | Reset password with OTP |

### Protected Endpoints (Require Token)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/profile` | Get user profile |
| PUT | `/api/profile` | Update user profile |
| POST | `/api/logout` | Logout user |

---

## 🧪 Test with cURL

### Sign Up
```bash
curl -X POST http://localhost:8000/api/signup \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Profile (Replace TOKEN)
```bash
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Forgot Password
```bash
curl -X POST http://localhost:8000/api/forgot-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com"
  }'
```

### Reset Password
```bash
curl -X POST http://localhost:8000/api/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "otp": "123456",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

---

## 📁 File Structure

```
app/
├── Http/Controllers/
│   └── AuthController.php          # All auth logic
├── Mail/
│   └── PasswordResetOtpMail.php   # OTP email template
└── Models/
    ├── User.php                    # User model
    └── PasswordResetOtp.php        # OTP model

config/
└── sanctum.php                     # Sanctum config (token expiration)

database/migrations/
└── 2025_10_17_000000_create_password_reset_otps_table.php

resources/views/emails/
└── password-reset-otp.blade.php   # Email HTML template

routes/
└── api.php                         # API routes

.env                                # Environment variables
```

---

## 🔑 Key Features

✅ **Token Expiration** - Tokens expire after 24 hours (configurable)  
✅ **OTP System** - 6-digit OTP valid for 10 minutes  
✅ **Email Notifications** - Beautiful HTML emails via Gmail  
✅ **Secure Password Reset** - All tokens revoked on password change  
✅ **Profile Management** - Update user information  
✅ **Error Handling** - Graceful error responses with logging  
✅ **Input Validation** - All inputs validated before processing  

---

## 🛠️ Common Commands

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Interactive PHP shell
php artisan tinker

# View routes
php artisan route:list
```

---

## 🐛 Troubleshooting

### Emails not sending?
1. Check Gmail credentials in `.env`
2. Use App Password (not regular password)
3. Run: `php artisan config:clear`
4. Check spam folder

### Token not working?
1. Check `Authorization: Bearer {token}` header
2. Verify token hasn't expired
3. Ensure no extra spaces in token

### Database errors?
1. Check `.env` DB settings
2. Run: `php artisan migrate:fresh`
3. Clear config: `php artisan config:clear`

---

## 📚 Learn More

Read the full documentation: **API_DOCUMENTATION.md**
