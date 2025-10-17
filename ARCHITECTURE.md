# 🏗️ System Architecture Overview

## 📊 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT                              │
│  (Postman / React / Vue / Angular / Mobile App)            │
└────────────────────┬────────────────────────────────────────┘
                     │ HTTP Requests
                     │ (JSON)
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    LARAVEL API                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              ROUTES (api.php)                        │  │
│  │  Public Routes    │    Protected Routes              │  │
│  │  /signup          │    /profile (GET)                │  │
│  │  /login           │    /profile (PUT)                │  │
│  │  /forgot-password │    /logout                       │  │
│  │  /reset-password  │                                  │  │
│  └────┬─────────────┴──────────┬────────────────────────┘  │
│       │                        │                            │
│       ▼                        ▼                            │
│  ┌──────────────────┐   ┌────────────────────────────┐    │
│  │   Controllers    │   │   Middleware               │    │
│  │  AuthController  │   │   auth:sanctum             │    │
│  │  - signup()      │   │   (Token validation)       │    │
│  │  - login()       │   └────────────────────────────┘    │
│  │  - logout()      │                                      │
│  │  - getProfile()  │                                      │
│  │  - updateProfile │                                      │
│  │  - forgotPass()  │                                      │
│  │  - resetPass()   │                                      │
│  └────┬─────────────┘                                      │
│       │                                                     │
│       ▼                                                     │
│  ┌──────────────────────────────────────────────────────┐  │
│  │                 MODELS (ORM)                         │  │
│  │  ┌──────────┐  ┌──────────────────┐                 │  │
│  │  │   User   │  │ PasswordResetOtp │                 │  │
│  │  └──────────┘  └──────────────────┘                 │  │
│  └────┬────────────────────────┬──────────────────────────┘│
│       │                        │                            │
└───────┼────────────────────────┼────────────────────────────┘
        │                        │
        ▼                        ▼
┌─────────────────────────────────────────────────────────────┐
│                       DATABASE                              │
│  ┌──────────────┐  ┌──────────────────────┐               │
│  │    users     │  │ password_reset_otps  │               │
│  │ personal_access_tokens                 │               │
│  └──────────────┘  └──────────────────────┘               │
└─────────────────────────────────────────────────────────────┘

                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   EMAIL SERVICE                             │
│  Gmail SMTP → Send password reset OTPs                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Request Flow Diagrams

### 1. Sign Up Flow

```
Client                  API                    Database              Email
  │                     │                          │                   │
  │ POST /api/signup    │                          │                   │
  ├────────────────────>│                          │                   │
  │                     │ Validate input           │                   │
  │                     ├──────────────┐           │                   │
  │                     │              │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Hash password            │                   │
  │                     ├──────────────┐           │                   │
  │                     │              │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Create user              │                   │
  │                     ├─────────────────────────>│                   │
  │                     │                          │                   │
  │                     │ User created             │                   │
  │                     │<─────────────────────────┤                   │
  │                     │                          │                   │
  │                     │ Generate token           │                   │
  │                     ├─────────────────────────>│                   │
  │                     │                          │                   │
  │                     │ Token stored             │                   │
  │                     │<─────────────────────────┤                   │
  │                     │                          │                   │
  │ Response with token │                          │                   │
  │<────────────────────┤                          │                   │
  │                     │                          │                   │
```

### 2. Login Flow

```
Client                  API                    Database
  │                     │                          │
  │ POST /api/login     │                          │
  ├────────────────────>│                          │
  │ email + password    │                          │
  │                     │ Validate input           │
  │                     ├──────────────┐           │
  │                     │              │           │
  │                     │<─────────────┘           │
  │                     │                          │
  │                     │ Find user by email       │
  │                     ├─────────────────────────>│
  │                     │                          │
  │                     │ User found               │
  │                     │<─────────────────────────┤
  │                     │                          │
  │                     │ Verify password          │
  │                     ├──────────────┐           │
  │                     │ Hash.check() │           │
  │                     │<─────────────┘           │
  │                     │                          │
  │                     │ Generate token           │
  │                     ├─────────────────────────>│
  │                     │                          │
  │                     │ Token stored             │
  │                     │<─────────────────────────┤
  │                     │                          │
  │ Response with token │                          │
  │<────────────────────┤                          │
  │                     │                          │
```

### 3. Forgot Password Flow

```
Client                  API                    Database              Email
  │                     │                          │                   │
  │ POST /forgot-pass   │                          │                   │
  ├────────────────────>│                          │                   │
  │ email               │                          │                   │
  │                     │ Validate email exists    │                   │
  │                     ├─────────────────────────>│                   │
  │                     │                          │                   │
  │                     │ User found               │                   │
  │                     │<─────────────────────────┤                   │
  │                     │                          │                   │
  │                     │ Generate 6-digit OTP     │                   │
  │                     ├──────────────┐           │                   │
  │                     │ e.g. 123456  │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Hash OTP                 │                   │
  │                     ├──────────────┐           │                   │
  │                     │              │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Store OTP + expiry       │                   │
  │                     ├─────────────────────────>│                   │
  │                     │ (expires in 10 min)      │                   │
  │                     │                          │                   │
  │                     │ Send OTP email           │                   │
  │                     ├──────────────────────────────────────────────>│
  │                     │                          │                   │
  │ Success response    │                          │         Email sent│
  │<────────────────────┤                          │                   │
  │                     │                          │                   │
```

### 4. Reset Password Flow

```
Client                  API                    Database
  │                     │                          │
  │ POST /reset-pass    │                          │
  ├────────────────────>│                          │
  │ email + OTP + pass  │                          │
  │                     │ Validate input           │
  │                     ├──────────────┐           │
  │                     │              │           │
  │                     │<─────────────┘           │
  │                     │                          │
  │                     │ Get OTP records          │
  │                     ├─────────────────────────>│
  │                     │                          │
  │                     │ OTP records found        │
  │                     │<─────────────────────────┤
  │                     │                          │
  │                     │ Check expiry             │
  │                     ├──────────────┐           │
  │                     │              │           │
  │                     │<─────────────┘           │
  │                     │                          │
  │                     │ Verify OTP               │
  │                     ├──────────────┐           │
  │                     │ Hash.check() │           │
  │                     │<─────────────┘           │
  │                     │                          │
  │                     │ Update password          │
  │                     ├─────────────────────────>│
  │                     │                          │
  │                     │ Revoke all tokens        │
  │                     ├─────────────────────────>│
  │                     │                          │
  │                     │ Delete OTP               │
  │                     ├─────────────────────────>│
  │                     │                          │
  │ Success response    │                          │
  │<────────────────────┤                          │
  │                     │                          │
```

### 5. Protected Route Flow (Get Profile)

```
Client                  Middleware              Controller           Database
  │                     │                          │                   │
  │ GET /api/profile    │                          │                   │
  ├────────────────────>│                          │                   │
  │ Bearer Token        │                          │                   │
  │                     │ Extract token            │                   │
  │                     ├──────────────┐           │                   │
  │                     │              │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Verify token             │                   │
  │                     ├─────────────────────────────────────────────>│
  │                     │                          │                   │
  │                     │ Token valid + User       │                   │
  │                     │<─────────────────────────────────────────────┤
  │                     │                          │                   │
  │                     │ Attach user to request   │                   │
  │                     ├──────────────┐           │                   │
  │                     │              │           │                   │
  │                     │<─────────────┘           │                   │
  │                     │                          │                   │
  │                     │ Pass to controller       │                   │
  │                     ├─────────────────────────>│                   │
  │                     │                          │                   │
  │                     │                          │ Get user data     │
  │                     │                          ├──────────────┐    │
  │                     │                          │              │    │
  │                     │                          │<─────────────┘    │
  │                     │                          │                   │
  │                     │ Response with user       │                   │
  │                     │<─────────────────────────┤                   │
  │                     │                          │                   │
  │ User profile        │                          │                   │
  │<────────────────────┤                          │                   │
  │                     │                          │                   │
```

---

## 🗂️ Database Schema

```sql
-- users table
CREATE TABLE users (
    id              INTEGER PRIMARY KEY,
    first_name      VARCHAR(255) NOT NULL,
    last_name       VARCHAR(255) NOT NULL,
    name            VARCHAR(255),
    email           VARCHAR(255) UNIQUE NOT NULL,
    password        VARCHAR(255) NOT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);

-- personal_access_tokens table (Sanctum)
CREATE TABLE personal_access_tokens (
    id              INTEGER PRIMARY KEY,
    tokenable_type  VARCHAR(255) NOT NULL,
    tokenable_id    INTEGER NOT NULL,
    name            VARCHAR(255) NOT NULL,
    token           VARCHAR(64) UNIQUE NOT NULL,
    abilities       TEXT,
    last_used_at    TIMESTAMP,
    expires_at      TIMESTAMP,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    INDEX(tokenable_type, tokenable_id),
    INDEX(token),
    INDEX(expires_at)
);

-- password_reset_otps table
CREATE TABLE password_reset_otps (
    id              INTEGER PRIMARY KEY,
    email           VARCHAR(255) NOT NULL,
    otp             VARCHAR(255) NOT NULL,
    expires_at      TIMESTAMP NOT NULL,
    created_at      TIMESTAMP,
    INDEX(email),
    INDEX(expires_at)
);
```

---

## 🔐 Security Layers

```
┌────────────────────────────────────────────────┐
│          SECURITY LAYERS                       │
├────────────────────────────────────────────────┤
│  1. Input Validation                           │
│     └─ Validator rules on all inputs           │
├────────────────────────────────────────────────┤
│  2. Authentication Middleware                  │
│     └─ auth:sanctum checks token validity      │
├────────────────────────────────────────────────┤
│  3. Password Hashing                           │
│     └─ bcrypt (one-way encryption)             │
├────────────────────────────────────────────────┤
│  4. Token Expiration                           │
│     └─ 24-hour default expiry                  │
├────────────────────────────────────────────────┤
│  5. OTP Expiration                             │
│     └─ 10-minute expiry                        │
├────────────────────────────────────────────────┤
│  6. Mass Assignment Protection                 │
│     └─ $fillable whitelist on models           │
├────────────────────────────────────────────────┤
│  7. SQL Injection Protection                   │
│     └─ Eloquent ORM with parameter binding     │
├────────────────────────────────────────────────┤
│  8. XSS Protection                             │
│     └─ Laravel's automatic escaping            │
├────────────────────────────────────────────────┤
│  9. CSRF Protection                            │
│     └─ Built-in for web routes                 │
├────────────────────────────────────────────────┤
│  10. Error Logging                             │
│      └─ All errors logged to storage/logs      │
└────────────────────────────────────────────────┘
```

---

## 📦 Component Relationships

```
AuthController
    ├── Uses: User Model
    │   └── Interacts with: users table
    │   └── Has: HasApiTokens trait
    │       └── Creates: personal_access_tokens
    │
    ├── Uses: PasswordResetOtp Model
    │   └── Interacts with: password_reset_otps table
    │   └── Methods: create(), deleteExpired()
    │
    ├── Uses: PasswordResetOtpMail
    │   └── Uses: Blade template
    │       └── password-reset-otp.blade.php
    │   └── Sends via: Gmail SMTP
    │
    └── Uses: Laravel Facades
        ├── Hash (bcrypt)
        ├── Validator
        ├── Mail
        └── Log
```

---

## 🎯 Key Features Summary

| Feature | Implementation | Security |
|---------|---------------|----------|
| **Registration** | User::create() | Password hashed with bcrypt |
| **Login** | Hash::check() | Token generated via Sanctum |
| **Token Auth** | Sanctum middleware | Token expires in 24h |
| **Forgot Password** | Generate 6-digit OTP | OTP hashed in database |
| **Reset Password** | Verify OTP + Update | All tokens revoked |
| **Profile Update** | Model update | Auth required |
| **Logout** | Token deletion | Current token revoked |

---

## 🚀 Performance Considerations

1. **Database Indexing**
   - email (users table) - Faster lookups
   - token (personal_access_tokens) - Faster auth
   - expires_at (password_reset_otps) - Faster cleanup

2. **Caching** (Production)
   - Config cache: `php artisan config:cache`
   - Route cache: `php artisan route:cache`
   - View cache: `php artisan view:cache`

3. **Token Cleanup**
   - Expired tokens should be cleaned periodically
   - Use Laravel's scheduler or manual cleanup

4. **OTP Cleanup**
   - Expired OTPs auto-deleted on new requests
   - Can add scheduled cleanup job

---

This architecture provides a **secure, scalable, and maintainable** authentication system! 🎉
