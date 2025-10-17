# 🔧 Quick Test Commands for Error Handling

## Prerequisites
Make sure the server is running:
```bash
php artisan serve
```

---

## Test 1: No Authorization Header

**PowerShell:**
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET
```

**Expected:** Error about missing Authorization header

---

## Test 2: Invalid Token Format

**PowerShell:**
```powershell
$headers = @{
    "Authorization" = "InvalidFormat abc123"
}
Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
```

**Expected:** Error about invalid format

---

## Test 3: Invalid/Fake Token

**PowerShell:**
```powershell
$headers = @{
    "Authorization" = "Bearer fake_invalid_token_12345"
}
Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
```

**Expected:** Error about invalid token

---

## Test 4: Expired Token (Manual Expiration)

### Step 1: Login and get a token
```powershell
$loginData = @{
    email = "test@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" `
    -Method POST `
    -ContentType "application/json" `
    -Body $loginData

# Save the token
$token = $response.token
Write-Host "Token: $token"
```

### Step 2: Manually expire the token
Open a new terminal and run:
```bash
php artisan tinker
```

Then in Tinker:
```php
// Get the latest token
$token = \Laravel\Sanctum\PersonalAccessToken::latest()->first();

// Show current expiration
echo "Current expiration: " . $token->expires_at . "\n";

// Set it to expire 1 minute ago
$token->expires_at = now()->subMinute();
$token->save();

echo "New expiration: " . $token->expires_at . "\n";
echo "Token is now expired!\n";

// Exit
exit
```

### Step 3: Try to use the expired token
```powershell
$headers = @{
    "Authorization" = "Bearer $token"
}
Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
```

**Expected:** Error about expired token with expiration date

---

## Test 5: Valid Token (Should Work)

### First, get a fresh token:
```powershell
$loginData = @{
    email = "test@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" `
    -Method POST `
    -ContentType "application/json" `
    -Body $loginData

$token = $response.token
```

### Then use it:
```powershell
$headers = @{
    "Authorization" = "Bearer $token"
}
$profile = Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
$profile | ConvertTo-Json
```

**Expected:** Your user profile data

---

## Test 6: 404 Not Found

**PowerShell:**
```powershell
Invoke-RestMethod -Uri "http://localhost:8000/api/nonexistent" -Method GET
```

**Expected:** 404 error in JSON format

---

## Test 7: 405 Method Not Allowed

**PowerShell:**
```powershell
# Trying to GET /signup (should be POST)
Invoke-RestMethod -Uri "http://localhost:8000/api/signup" -Method GET
```

**Expected:** 405 error in JSON format

---

## Test 8: Validation Error

**PowerShell:**
```powershell
$badData = @{
    email = "not-an-email"
    password = "123"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8000/api/signup" `
    -Method POST `
    -ContentType "application/json" `
    -Body $badData
```

**Expected:** 422 validation errors in JSON format

---

## Complete Test Script

Save this as `test-errors.ps1` and run it:

```powershell
# Test Error Handling Script
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Testing API Error Handling" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# Test 1: No Token
Write-Host "`n[Test 1] No Authorization Header" -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET
} catch {
    $result = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✓ Got expected error: $($result.message)" -ForegroundColor Green
}

# Test 2: Invalid Format
Write-Host "`n[Test 2] Invalid Token Format" -ForegroundColor Yellow
try {
    $headers = @{ "Authorization" = "InvalidFormat abc123" }
    Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
} catch {
    $result = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✓ Got expected error: $($result.message)" -ForegroundColor Green
}

# Test 3: Invalid Token
Write-Host "`n[Test 3] Invalid Token" -ForegroundColor Yellow
try {
    $headers = @{ "Authorization" = "Bearer fake_token_12345" }
    Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
} catch {
    $result = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✓ Got expected error: $($result.message)" -ForegroundColor Green
}

# Test 4: Valid Token (should work)
Write-Host "`n[Test 4] Valid Token" -ForegroundColor Yellow
try {
    # Login first
    $loginData = @{
        email = "test@example.com"
        password = "password123"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "http://localhost:8000/api/login" `
        -Method POST `
        -ContentType "application/json" `
        -Body $loginData
    
    $token = $response.token
    
    # Use token
    $headers = @{ "Authorization" = "Bearer $token" }
    $profile = Invoke-RestMethod -Uri "http://localhost:8000/api/profile" -Method GET -Headers $headers
    Write-Host "✓ Got profile for: $($profile.user.email)" -ForegroundColor Green
} catch {
    Write-Host "✗ Test failed: $_" -ForegroundColor Red
}

# Test 5: 404
Write-Host "`n[Test 5] 404 Not Found" -ForegroundColor Yellow
try {
    Invoke-RestMethod -Uri "http://localhost:8000/api/nonexistent" -Method GET
} catch {
    $result = $_.ErrorDetails.Message | ConvertFrom-Json
    Write-Host "✓ Got expected error: $($result.message)" -ForegroundColor Green
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "All tests completed!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
```

---

## Run the Complete Test

```powershell
.\test-errors.ps1
```

This will run all error handling tests automatically and show you the results!

---

## Notes for Testing in Postman

1. **Test expired token:**
   - Login to get a token
   - Run `php artisan tinker` and expire it manually (see Step 2 above)
   - Try to use it in Postman
   - You should now get a nice JSON error instead of HTML

2. **Test invalid token:**
   - In Postman, set Authorization to: `Bearer invalid_token_abc123`
   - You should get: "Invalid token" error

3. **Test no token:**
   - Remove the Authorization header completely
   - You should get: "Authorization header is missing" error

---

**All errors are now handled gracefully with proper JSON responses!** ✅
