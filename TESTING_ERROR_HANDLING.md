# 🧪 Testing Token Expiration & Error Handling

## Test Scenarios

### 1. Test with No Token
**Request:**
```http
GET http://localhost:8000/api/profile
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Authorization header is missing",
  "error": "Please provide a valid Bearer token in the Authorization header"
}
```

---

### 2. Test with Invalid Token Format
**Request:**
```http
GET http://localhost:8000/api/profile
Authorization: InvalidFormat abc123
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid authorization format",
  "error": "Authorization header must be in the format: Bearer {token}"
}
```

---

### 3. Test with Invalid/Revoked Token
**Request:**
```http
GET http://localhost:8000/api/profile
Authorization: Bearer invalid_token_12345
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Invalid token",
  "error": "The provided authentication token is invalid or has been revoked"
}
```

---

### 4. Test with Expired Token

#### Step 1: Create a User and Get Token
```bash
# Using PowerShell
Invoke-RestMethod -Uri "http://localhost:8000/api/signup" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"first_name":"Test","last_name":"User","email":"test@example.com","password":"password123"}'
```

Save the token from the response.

#### Step 2: Make the Token Expire Immediately

**Option A: Update Token Expiration in Database (Quick Test)**

Using the terminal:
```bash
php artisan tinker
```

Then run:
```php
// Get the latest token
$token = \Laravel\Sanctum\PersonalAccessToken::latest()->first();

// Set it to expire 1 minute ago
$token->expires_at = now()->subMinute();
$token->save();

// Exit tinker
exit
```

**Option B: Set Short Expiration in .env (Better for Testing)**

Update `.env`:
```env
SANCTUM_EXPIRATION=1  # 1 minute
```

Then:
```bash
php artisan config:clear
```

Login again to get a token with 1-minute expiration, then wait 1 minute.

#### Step 3: Try to Access Protected Route
```http
GET http://localhost:8000/api/profile
Authorization: Bearer {your_token_here}
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Token has expired",
  "error": "Your authentication token has expired. Please login again to get a new token.",
  "expired_at": "2025-10-17 12:30:00"
}
```

---

### 5. Test with Valid Token
**Request:**
```http
GET http://localhost:8000/api/profile
Authorization: Bearer {valid_token_here}
```

**Expected Response (200):**
```json
{
  "success": true,
  "user": {
    "id": 1,
    "first_name": "Test",
    "last_name": "User",
    "email": "test@example.com",
    "created_at": "2025-10-17T10:30:00.000000Z"
  }
}
```

---

## Testing Other Errors

### 6. Test 404 Not Found
**Request:**
```http
GET http://localhost:8000/api/nonexistent-route
```

**Expected Response (404):**
```json
{
  "success": false,
  "message": "Endpoint not found",
  "error": "The requested resource does not exist"
}
```

---

### 7. Test 405 Method Not Allowed
**Request:**
```http
GET http://localhost:8000/api/signup
```
(Should be POST, not GET)

**Expected Response (405):**
```json
{
  "success": false,
  "message": "Method not allowed",
  "error": "The HTTP method used is not supported for this endpoint"
}
```

---

### 8. Test Validation Errors
**Request:**
```http
POST http://localhost:8000/api/signup
Content-Type: application/json

{
  "email": "invalid-email",
  "password": "123"
}
```

**Expected Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "first_name": ["The first name field is required."],
    "last_name": ["The last name field is required."],
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Quick Test Using Postman

### Test Expired Token (Quick Method)

1. **Login and get a token**
   ```json
   POST /api/login
   {
     "email": "your@email.com",
     "password": "password123"
   }
   ```

2. **Manually expire the token using Tinker**
   ```bash
   php artisan tinker
   ```
   ```php
   $token = \Laravel\Sanctum\PersonalAccessToken::latest()->first();
   $token->expires_at = now()->subMinute();
   $token->save();
   exit
   ```

3. **Try to access protected route**
   ```
   GET /api/profile
   Authorization: Bearer {token_from_step_1}
   ```

4. **You should get:**
   ```json
   {
     "success": false,
     "message": "Token has expired",
     "error": "Your authentication token has expired. Please login again to get a new token.",
     "expired_at": "2025-10-17 12:30:00"
   }
   ```

---

## What Changed?

### 1. Custom Exception Handling
**File:** `bootstrap/app.php`

- Added handlers for `AuthenticationException` (expired/invalid tokens)
- Added handlers for `ValidationException` (validation errors)
- Added handlers for `NotFoundHttpException` (404 errors)
- Added handlers for `MethodNotAllowedHttpException` (405 errors)

All API errors now return consistent JSON format instead of HTML.

### 2. Custom Middleware
**File:** `app/Http/Middleware/HandleApiAuthentication.php`

This middleware runs BEFORE Sanctum's authentication and checks:
- ✅ If Authorization header exists
- ✅ If token format is correct (Bearer {token})
- ✅ If token exists in database
- ✅ If token has expired

This provides much better error messages than the default 500 errors.

---

## Error Response Format

All API errors now follow this consistent format:

```json
{
  "success": false,
  "message": "Brief error message",
  "error": "Detailed explanation of the error",
  "errors": {} // Only for validation errors
}
```

---

## Benefits

✅ **Graceful Error Handling** - No more HTML error pages in Postman  
✅ **Consistent JSON Format** - All errors follow the same structure  
✅ **Clear Error Messages** - Users know exactly what went wrong  
✅ **Better Debugging** - Errors are logged with context  
✅ **Security** - Doesn't expose sensitive stack traces in production  

---

## Production Setup

For production, make sure:

```env
APP_ENV=production
APP_DEBUG=false  # This prevents stack traces from being exposed
```

With `APP_DEBUG=false`, users will get clean error messages without seeing your code or file paths.

---

**Your API is now production-ready with proper error handling!** 🎉
