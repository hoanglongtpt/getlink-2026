# Web2m Webhook Testing Script for PowerShell (Windows)
# Usage: .\web2m-test.ps1 -Url "http://localhost:8000/webhook/web2m" -Secret "your_secret" -UserId 1

param(
    [string]$Url = "http://localhost:8000/webhook/web2m",
    [string]$Secret = "your_web2m_webhook_secret",
    [int]$UserId = 1
)

Write-Host "🔧 Web2m Webhook Testing Script (PowerShell)" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "📝 Configuration:" -ForegroundColor Yellow
Write-Host "  Webhook URL: $Url"
Write-Host "  Secret: $Secret"
Write-Host "  User ID: $UserId"
Write-Host ""

# Helper function to create signature
function Get-HmacSignature {
    param([string]$Payload, [string]$Secret)
    
    $hmacsha256 = New-Object System.Security.Cryptography.HMACSHA256
    $hmacsha256.key = [Text.Encoding]::UTF8.GetBytes($Secret)
    $signature = $hmacsha256.ComputeHash([Text.Encoding]::UTF8.GetBytes($Payload))
    return [Convert]::ToHexString($signature).ToLower()
}

# Test Case 1: Valid Payment
Write-Host "🧪 Test Case 1: Valid Payment (100,000 VND)" -ForegroundColor Green
Write-Host "=============================================" -ForegroundColor Green

$payload1 = @{
    transaction_code = "TXN_TEST_001"
    amount_vnd = 100000
    description = "NAPXU $UserId"
    email = "test@example.com"
    status = "completed"
} | ConvertTo-Json -Compress

$signature1 = Get-HmacSignature -Payload $payload1 -Secret $Secret

Write-Host "Payload: $payload1"
Write-Host "Signature: $signature1"
Write-Host ""

try {
    $response1 = Invoke-WebRequest -Uri $Url `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "X-Web2m-Signature" = $signature1
        } `
        -Body $payload1 `
        -UseBasicParsing
    
    Write-Host "✅ Response (200):" -ForegroundColor Green
    $response1.Content | ConvertFrom-Json | ConvertTo-Json | Write-Host
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host ""

# Test Case 2: Invalid Signature
Write-Host "🧪 Test Case 2: Invalid Signature" -ForegroundColor Magenta
Write-Host "===================================" -ForegroundColor Magenta

try {
    $response2 = Invoke-WebRequest -Uri $Url `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "X-Web2m-Signature" = "invalid_signature_here"
        } `
        -Body $payload1 `
        -UseBasicParsing
} catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    $errorContent = $_.Exception.Response.Content.ReadAsStream() | ForEach-Object { [System.IO.StreamReader]::new($_).ReadToEnd() }
    
    Write-Host "⚠️ Response ($statusCode):" -ForegroundColor Yellow
    $errorContent | ConvertFrom-Json | ConvertTo-Json | Write-Host
}

Write-Host ""
Write-Host ""

# Test Case 3: Missing Signature
Write-Host "🧪 Test Case 3: Missing Signature" -ForegroundColor Magenta
Write-Host "===================================" -ForegroundColor Magenta

try {
    $response3 = Invoke-WebRequest -Uri $Url `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
        } `
        -Body $payload1 `
        -UseBasicParsing
} catch {
    $statusCode = $_.Exception.Response.StatusCode.Value__
    $errorContent = $_.Exception.Response.Content.ReadAsStream() | ForEach-Object { [System.IO.StreamReader]::new($_).ReadToEnd() }
    
    Write-Host "⚠️ Response ($statusCode):" -ForegroundColor Yellow
    $errorContent | ConvertFrom-Json | ConvertTo-Json | Write-Host
}

Write-Host ""
Write-Host ""

# Test Case 4: Different Pricing Tiers
Write-Host "🧪 Test Case 4: Different Pricing Tiers" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan

$amounts = @(20000, 100000, 200000, 500000)

foreach ($amount in $amounts) {
    Write-Host "Testing $amount VND..." -ForegroundColor Yellow
    
    $payload = @{
        transaction_code = "TXN_TEST_$amount"
        amount_vnd = $amount
        description = "NAPXU $UserId"
        status = "completed"
    } | ConvertTo-Json -Compress
    
    $signature = Get-HmacSignature -Payload $payload -Secret $Secret
    
    try {
        $response = Invoke-WebRequest -Uri $Url `
            -Method POST `
            -Headers @{
                "Content-Type" = "application/json"
                "X-Web2m-Signature" = $signature
            } `
            -Body $payload `
            -UseBasicParsing
        
        $data = $response.Content | ConvertFrom-Json
        Write-Host "  ✅ Xu Main: $($data.xu_main), Xu Bonus: $($data.xu_bonus), Total: $($data.total_xu)" -ForegroundColor Green
    } catch {
        Write-Host "  ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "✅ Testing Complete!" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Summary:" -ForegroundColor Yellow
Write-Host "  - Valid payment should return 200 with success message"
Write-Host "  - Invalid signature should return 403"
Write-Host "  - Missing signature should return 403"
Write-Host "  - Different amounts should calculate correct Xu"
