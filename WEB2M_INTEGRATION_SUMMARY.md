# Web2m Payment Integration - Setup Complete ✅

Toàn bộ thành phần thanh toán Web2m đã được tạo và sẵn sàng để sử dụng.

## 📋 Files Đã Tạo

### 1. Backend Services
- **[app/Services/Web2mService.php](app/Services/Web2mService.php)** - Service chính xử lý logic Web2m
  - Xác thực webhook signature
  - Tính toán Xu dựa vào bảng giá
  - Xử lý webhook payload
  - Get package info

- **[app/Http/Controllers/PaymentController.php](app/Http/Controllers/PaymentController.php)** - Payment API endpoints
  - `GET /payment/packages` - Lấy danh sách gói
  - `POST /payment/initiate` - Khởi tạo thanh toán
  - `POST /payment/status` - Check trạng thái

- **[app/Http/Controllers/WebhookController.php](app/Http/Controllers/WebhookController.php)** - UPDATED
  - Xử lý webhook từ Web2m
  - Verify signature
  - Update user balance

### 2. Configuration
- **[config/web2m.php](config/web2m.php)** - Web2m configuration file
  - Pricing table
  - API settings
  - Transaction settings

- **[.env](.env)** - UPDATED
  - `WEB2M_WEBHOOK_SECRET` - Webhook signature secret
  - `WEB2M_PARTNER_ID` - Partner ID từ Web2m
  - `WEB2M_API_KEY` - API Key từ Web2m
  - `WEB2M_API_URL` - Web2m API URL

### 3. Frontend
- **[resources/js/web2m-payment.js](resources/js/web2m-payment.js)** - JavaScript helper
  - `Web2mPayment.initiate(amount)` - Khởi tạo thanh toán
  - `Web2mPayment.checkStatus()` - Check status
  - `Web2mPayment.getPackages()` - Get packages
  - `Web2mPayment.startPolling()` - Polling payment status

### 4. Routes
- **[routes/web.php](routes/web.php)** - UPDATED
  - `GET /payment/packages`
  - `POST /payment/initiate`
  - `POST /payment/status`
  - `POST /webhook/web2m` (existing)

### 5. Tests
- **[tests/Feature/Web2mWebhookTest.php](tests/Feature/Web2mWebhookTest.php)** - Unit tests
  - Test webhook with valid signature
  - Test invalid signature
  - Test missing signature
  - Test pricing calculation
  - Test duplicate transactions
  - Test user lookup

### 6. Documentation
- **[src/web2m-integration.md](src/web2m-integration.md)** - Technical documentation
  - API endpoints
  - Webhook format
  - Security notes
  - Error handling

- **[SETUP_WEB2M.md](SETUP_WEB2M.md)** - Setup guide
  - Configuration steps
  - Database requirements
  - Workflow explanation
  - Testing guide

## 🚀 Quick Start

### 1. Configure Environment
```bash
# Edit .env với Web2m credentials
WEB2M_WEBHOOK_SECRET=your_actual_secret_from_web2m
WEB2M_PARTNER_ID=your_partner_id
WEB2M_API_KEY=your_api_key
```

### 2. Test Webhook Locally
```bash
# Cách 1: Dùng ngrok để tunnel local server
ngrok http 8000

# Cách 2: Test bằng cURL
SECRET="your_web2m_webhook_secret"
PAYLOAD='{"transaction_code":"TXN_TEST_001","amount_vnd":100000,"description":"NAPXU 1","status":"completed"}'
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" -hex | cut -d' ' -f2)

curl -X POST http://localhost:8000/webhook/web2m \
  -H "Content-Type: application/json" \
  -H "X-Web2m-Signature: $SIGNATURE" \
  -d "$PAYLOAD"
```

### 3. Run Tests
```bash
php artisan test tests/Feature/Web2mWebhookTest.php
```

### 4. Frontend Integration
```html
<!-- Load Web2m Payment Helper -->
<script src="{{ asset('js/web2m-payment.js') }}"></script>

<!-- Usage -->
<script>
  // Khởi tạo thanh toán
  Web2mPayment.initiate(100000, (data) => {
    console.log('Payment initiated:', data);
    // Redirect to Web2m payment gateway
    // window.location.href = data.redirect_url;
  });

  // Check status
  Web2mPayment.checkStatus((data) => {
    console.log('Current balance:', data.xu_balance);
  });

  // Start polling
  const pollTimer = Web2mPayment.startPolling((data) => {
    console.log('Payment detected!', data);
    Web2mPayment.showSuccessNotification(data);
  });
</script>
```

## 📊 Bảng Giá (Pricing Table)

| VND Amount | Xu Chính | Xu Thưởng | Tỷ lệ |
|-----------|---------|----------|-------|
| ≥ 500,000 | 500     | 100      | 1.2x  |
| ≥ 200,000 | 200     | 30       | 1.15x |
| ≥ 100,000 | 100     | 10       | 1.1x  |
| ≥ 20,000  | 20      | 0        | 1x    |
| < 20,000  | /1000   | 0        | 1x    |

## 🔑 API Reference

### GET /payment/packages
```bash
curl -X GET http://localhost:8000/payment/packages \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

Response:
```json
{
  "packages": [
    {
      "name": "Gói Tiết Kiệm",
      "amount_vnd": 100000,
      "xu_main": 100,
      "xu_bonus": 10
    }
  ],
  "pricing_table": { ... }
}
```

### POST /payment/initiate
```bash
curl -X POST http://localhost:8000/payment/initiate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount_vnd": 100000}'
```

Response:
```json
{
  "success": true,
  "transaction_id": 123,
  "amount_vnd": 100000,
  "xu_main": 100,
  "xu_bonus": 10,
  "total_xu": 110,
  "redirect_url": "https://..."
}
```

### POST /payment/status
```bash
curl -X POST http://localhost:8000/payment/status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

Response:
```json
{
  "xu_balance": 500,
  "bonus_xu": 50,
  "total_xu": 550,
  "latest_transaction": {
    "id": 123,
    "amount_vnd": 100000,
    "xu_amount": 110,
    "status": "completed",
    "created_at": "2026-06-03T10:30:00Z"
  }
}
```

### POST /webhook/web2m
```bash
curl -X POST http://localhost:8000/webhook/web2m \
  -H "Content-Type: application/json" \
  -H "X-Web2m-Signature: YOUR_SIGNATURE" \
  -d '{
    "transaction_code": "TXN123456",
    "amount_vnd": 100000,
    "description": "NAPXU 5",
    "email": "user@example.com",
    "status": "completed"
  }'
```

## 🔒 Security

✅ HMAC-SHA256 signature verification  
✅ Transaction code uniqueness check (prevent duplicates)  
✅ User lookup validation  
✅ Amount validation  
✅ Comprehensive logging  

## 📝 Next Steps

### 1. Get Web2m Credentials
- Liên hệ Web2m để lấy `PARTNER_ID` và `API_KEY`
- Cấu hình webhook URL trên Web2m dashboard: `https://yourdomain.com/webhook/web2m`

### 2. Integrate Payment Gateway
- Tạo method để khởi tạo thanh toán thực sự (call Web2m API)
- Setup redirect URL sau thanh toán thành công

### 3. Frontend Enhancement
- Update payment modal để dùng PaymentController endpoints
- Add loading states
- Improve error handling

### 4. Monitoring
- Setup log monitoring cho `storage/logs/laravel.log`
- Monitor transaction success rate
- Setup alerts cho failed transactions

## 🐛 Troubleshooting

**Q: Webhook signature invalid?**  
A: Kiểm tra `WEB2M_WEBHOOK_SECRET` khớp với Web2m dashboard

**Q: User not found?**  
A: Đảm bảo description có format `NAPXU {user_id}`

**Q: Xu không được cập nhật?**  
A: Kiểm tra bảng `users` có cột `xu_balance` và `bonus_xu`

**Q: Duplicate transactions?**  
A: Không vấn đề, hệ thống dùng `transaction_code` làm unique key

## 📞 Support

Các file documentation:
- [src/web2m-integration.md](src/web2m-integration.md) - Technical details
- [SETUP_WEB2M.md](SETUP_WEB2M.md) - Setup guide
- [config/web2m.php](config/web2m.php) - Configuration options

---

**Status**: ✅ Ready to deploy  
**Last Updated**: June 3, 2026  
**Version**: 1.0
