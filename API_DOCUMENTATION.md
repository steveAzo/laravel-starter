# 📚 Laravel Authentication System - Complete Guide

## 🎯 Overview

This is a complete, robust authentication system built with Laravel and Sanctum. It includes:

1. ✅ User Registration & Login
2. ✅ Token-based Authentication with Expiration
3. ✅ Profile Management
4. ✅ Forgot Password with Email OTP
5. ✅ Password Reset
6. ✅ Comprehensive Error Handling

---

## 🏗️ Architecture Explanation

### What is Laravel Sanctum?
**Sanctum** is Laravel's authentication system for APIs. Think of it like JWT in Node.js, but simpler:
- When users login, they get a **token** (a long random string)
- They send this token with every request to prove who they are
- Tokens can expire for security

### How Tokens Work
```
User logs in → Server creates token → User gets token
↓
User makes request with token → Server verifies token → Allow/Deny
```

---

## 📁 File Structure Explained

### Models (`app/Models/`)
**What are Models?** Think of them as JavaScript classes that represent database tables.

1. **User.php** - Represents the users table
   - Has methods to interact with user data
   - `HasApiTokens` trait gives users the ability to have authentication tokens

2. **PasswordResetOtp.php** - Represents the password_reset_otps table
   - Stores OTP codes for password resets
   - Has helper methods like `deleteExpired()` to clean up old OTPs

### Controllers (`app/Http/Controllers/`)
**What are Controllers?** They handle HTTP requests (like Express route handlers in Node.js)

**AuthController.php** - Handles all authentication logic:
- `signup()` - Register new users
- `login()` - Authenticate existing users
- `updateProfile()` - Update user information
- `getProfile()` - Get current user info
- `forgotPassword()` - Generate and send OTP
- `resetPassword()` - Reset password with OTP
- `logout()` - Revoke current token

### Migrations (`database/migrations/`)
**What are Migrations?** Version control for your database schema.

Think of them like Git commits, but for your database structure:
```bash
php artisan migrate          # Run all new migrations
php artisan migrate:rollback # Undo last migration
php artisan migrate:fresh    # Drop all tables and re-migrate
```

### Mail (`app/Mail/`)
**What is Mailable?** Laravel's way to send emails (like Nodemailer templates)

**PasswordResetOtpMail.php** - Defines the OTP email:
- Contains the email logic
- Uses a Blade template for the HTML

### Views (`resources/views/`)
**What are Blade Templates?** Laravel's templating engine (like EJS or Handlebars in Node.js)

**password-reset-otp.blade.php** - The email HTML template
- `{{ $variable }}` - Outputs variables (like <%= %> in EJS)
- Clean, styled email for password reset

---

## 🔧 Configuration Files

### .env File
Your environment variables (like .env in Node.js):

```env
# Email Configuration for Gmail
MAIL_MAILER=smtp                          # Use SMTP protocol
MAIL_HOST=smtp.gmail.com                  # Gmail's SMTP server
MAIL_PORT=587                             # Port for TLS
MAIL_USERNAME=your-email@gmail.com        # Your Gmail address
MAIL_PASSWORD=your-app-password           # App-specific password (NOT your Gmail password)
MAIL_FROM_ADDRESS="your-email@gmail.com"  # From address
MAIL_ENCRYPTION=tls                       # Use TLS encryption

# Token Expiration (in minutes)
SANCTUM_EXPIRATION=1440                   # 24 hours (1440 minutes)
```

### config/sanctum.php
Sanctum configuration:
- `expiration` - How long tokens last (default: 24 hours)
- Can be changed via `SANCTUM_EXPIRATION` in .env

---

## 🔐 Setting Up Gmail for Emails

### Step 1: Enable 2-Factor Authentication on Gmail
1. Go to your Google Account settings
2. Navigate to Security
3. Enable 2-Step Verification

### Step 2: Generate App Password
1. Go to Google Account → Security → 2-Step Verification
2. Scroll down to "App passwords"
3. Select "Mail" and "Windows Computer"
4. Google will generate a 16-character password
5. Copy this password to `.env` file as `MAIL_PASSWORD`

### Step 3: Update .env
```env
MAIL_USERNAME=your-actual-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password    # The one Google generated
MAIL_FROM_ADDRESS="your-actual-email@gmail.com"
```

---

## 📡 API Endpoints

### Base URL
All endpoints are prefixed with `/api/`

### Public Endpoints (No token required)

#### 1. Sign Up
```http
POST /api/signup
Content-Type: application/json

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "User created successfully",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  },
  "token": "1|abc123xyz..."
}
```

#### 2. Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  },
  "token": "2|xyz789abc..."
}
```

#### 3. Forgot Password
```http
POST /api/forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OTP has been sent to your email"
}
```

**What happens:**
- System generates a 6-digit OTP (e.g., 123456)
- OTP is hashed and stored in database with 10-minute expiration
- Email is sent to user with the plain OTP

#### 4. Reset Password
```http
POST /api/reset-password
Content-Type: application/json

{
  "email": "john@example.com",
  "otp": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

**What happens:**
- System verifies OTP is valid and not expired
- Password is updated
- All existing tokens are revoked (user must login again)
- OTP is deleted from database

---

### Protected Endpoints (Token required)

**How to authenticate:**
Add this header to all protected requests:
```
Authorization: Bearer {your_token_here}
```

#### 5. Get Profile
```http
GET /api/profile
Authorization: Bearer 1|abc123xyz...
```

**Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "created_at": "2025-10-17T10:30:00.000000Z"
  }
}
```

#### 6. Update Profile
```http
PUT /api/profile
Authorization: Bearer 1|abc123xyz...
Content-Type: application/json

{
  "first_name": "Jane",
  "last_name": "Smith"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "john@example.com"
  }
}
```

**Note:** You can update one or both fields (first_name, last_name)

#### 7. Logout
```http
POST /api/logout
Authorization: Bearer 1|abc123xyz...
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**What happens:**
- Current token is revoked and becomes invalid
- User must login again to get a new token

---

## 🛡️ Error Handling

### Validation Errors (422)
When required fields are missing or invalid:
```json
{
  "success": false,
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### Authentication Errors (401)
When credentials are wrong or token is invalid:
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

### Server Errors (500)
When something goes wrong on the server:
```json
{
  "success": false,
  "message": "An error occurred while processing your request"
}
```

---

## 🔒 Security Features

### 1. Token Expiration
- Tokens expire after 24 hours (configurable)
- Prevents stolen tokens from working forever
- Set via `SANCTUM_EXPIRATION` in .env

### 2. OTP Expiration
- OTPs expire after 10 minutes
- Old/expired OTPs are automatically cleaned up
- Each new OTP request deletes previous ones

### 3. Password Hashing
- Passwords are hashed with bcrypt (irreversible)
- OTPs are also hashed in database
- Never store plain text passwords/OTPs

### 4. Token Revocation
- On password reset, all tokens are revoked
- On logout, current token is revoked
- Prevents unauthorized access after password change

### 5. Input Validation
- All inputs are validated before processing
- Email format validation
- Password minimum length enforcement
- Prevents SQL injection and XSS attacks

---

## 🧪 Testing the API

### Using Postman

1. **Test Signup:**
   - Method: POST
   - URL: `http://localhost:8000/api/signup`
   - Body (JSON):
     ```json
     {
       "first_name": "Test",
       "last_name": "User",
       "email": "test@example.com",
       "password": "password123"
     }
     ```
   - Copy the token from response

2. **Test Login:**
   - Method: POST
   - URL: `http://localhost:8000/api/login`
   - Body (JSON):
     ```json
     {
       "email": "test@example.com",
       "password": "password123"
     }
     ```

3. **Test Protected Route (Get Profile):**
   - Method: GET
   - URL: `http://localhost:8000/api/profile`
   - Headers:
     - Key: `Authorization`
     - Value: `Bearer {paste-your-token-here}`

4. **Test Forgot Password:**
   - Method: POST
   - URL: `http://localhost:8000/api/forgot-password`
   - Body (JSON):
     ```json
     {
       "email": "test@example.com"
     }
     ```
   - Check your email for the OTP

5. **Test Reset Password:**
   - Method: POST
   - URL: `http://localhost:8000/api/reset-password`
   - Body (JSON):
     ```json
     {
       "email": "test@example.com",
       "otp": "123456",
       "password": "newpassword123",
       "password_confirmation": "newpassword123"
     }
     ```

---

## 🚀 Running the Application

### Start the Server
```bash
php artisan serve
```

The API will be available at: `http://localhost:8000`

### Clear Cache (if something isn't working)
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 📊 Database Tables

### users
```
id              INT (Primary Key)
first_name      VARCHAR(255)
last_name       VARCHAR(255)
name            VARCHAR(255) (Auto-generated from first + last)
email           VARCHAR(255) (Unique)
password        VARCHAR(255) (Hashed)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### personal_access_tokens
```
id              INT (Primary Key)
tokenable_type  VARCHAR(255)
tokenable_id    INT
name            VARCHAR(255)
token           VARCHAR(64) (Unique, Hashed)
abilities       TEXT
expires_at      TIMESTAMP (Nullable)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### password_reset_otps
```
id              INT (Primary Key)
email           VARCHAR(255) (Indexed)
otp             VARCHAR(255) (Hashed)
expires_at      TIMESTAMP (Indexed)
created_at      TIMESTAMP
```

---

## 🐛 Common Issues & Solutions

### 1. Emails Not Sending
**Problem:** OTP emails aren't being received

**Solutions:**
- Check if Gmail credentials are correct in .env
- Verify you're using App Password, not your Gmail password
- Check spam folder
- Test with: `php artisan tinker` then `Mail::raw('Test', function($msg) { $msg->to('your@email.com')->subject('Test'); });`

### 2. Token Not Working
**Problem:** Getting "Unauthenticated" error

**Solutions:**
- Ensure you're sending `Authorization: Bearer {token}` header
- Check if token has expired (default: 24 hours)
- Verify token is copied correctly (no extra spaces)

### 3. Migration Errors
**Problem:** Migration fails

**Solutions:**
- Run: `php artisan migrate:fresh` (WARNING: Deletes all data!)
- Check database connection in .env
- Ensure database file exists (for SQLite)

### 4. Validation Errors
**Problem:** Always getting validation errors

**Solutions:**
- Check your JSON syntax is correct
- Ensure Content-Type header is `application/json`
- Verify all required fields are included

---

## 📖 Laravel Concepts for Node.js Developers

### Artisan Commands (like npm scripts)
```bash
php artisan serve              # Start development server
php artisan migrate            # Run database migrations
php artisan make:model User    # Create a model
php artisan make:controller    # Create a controller
php artisan tinker             # Interactive PHP shell
```

### Routing (like Express routing)
```php
// Node.js Express
app.post('/api/login', loginController);

// Laravel
Route::post('/login', [AuthController::class, 'login']);
```

### Middleware (like Express middleware)
```php
// Protect routes with authentication
Route::middleware('auth:sanctum')->group(function() {
    // Protected routes here
});
```

### Dependency Injection (automatic)
```php
// Laravel automatically injects Request object
public function login(Request $request) {
    $email = $request->email; // Access request data
}
```

### Environment Variables
```php
// Node.js
process.env.API_KEY

// Laravel
env('API_KEY')
config('app.name') // For cached config values
```

---

## 🎓 Key Takeaways

1. **Models = Database Tables** - Each model represents a table
2. **Controllers = Route Handlers** - Handle HTTP requests
3. **Migrations = Database Version Control** - Track schema changes
4. **Middleware = Guards** - Protect routes from unauthorized access
5. **Sanctum Tokens = JWT** - API authentication
6. **Blade = Template Engine** - Like EJS/Handlebars
7. **Artisan = CLI Tool** - Like npm/yarn commands

---

## 📞 Need Help?

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: `APP_DEBUG=true` in .env
3. Clear caches: `php artisan config:clear && php artisan cache:clear`

---

## 🔗 Useful Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel API Tutorial](https://laravel.com/docs/eloquent)

---

**Built with ❤️ using Laravel 11 & Sanctum**
