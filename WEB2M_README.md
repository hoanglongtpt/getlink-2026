# 🎉 Web2m Payment Integration - Complete Setup Guide

Toàn bộ hệ thống thanh toán Web2m đã được tạo và sẵn sàng sử dụng. Hãy follow hướng dẫn dưới để bắt đầu.

## 📦 Những gì đã được tạo

### 🔧 Backend
| File | Mục đích |
|------|---------|
| `app/Services/Web2mService.php` | Service xử lý logic Web2m (tính Xu, verify signature, handle webhook) |
| `app/Http/Controllers/PaymentController.php` | API endpoints (initiate, status, getPackages) |
| `app/Http/Controllers/WebhookController.php` | Webhook handler (đã update để dùng service) |
| `config/web2m.php` | Configuration (pricing table, API settings) |
| `routes/web.php` | Routes (đã thêm payment endpoints) |

### 🎨 Frontend
| File | Mục đích |
|------|---------|
| `resources/js/web2m-payment.js` | JavaScript helper để call API từ frontend |

### 📋 Testing & Documentation
| File | Mục đích |
|------|---------|
| `tests/Feature/Web2mWebhookTest.php` | Unit tests (7 test cases) |
| `web2m-test.sh` | Bash script để test webhook |
| `web2m-test.ps1` | PowerShell script để test webhook (Windows) |
| `SETUP_WEB2M.md` | Setup guide chi tiết |
| `WEB2M_INTEGRATION_SUMMARY.md` | Quick reference & API docs |
| `WEB2M_CHECKLIST.md` | Pre-deployment checklist |
| `src/web2m-integration.md` | Technical documentation |

---

## 🚀 Quick Start (5 Steps)

### Step 1: Configure Environment
```bash
# Edit .env thêm Web2m credentials
WEB2M_WEBHOOK_SECRET=your_secret_from_web2m
WEB2M_PARTNER_ID=your_partner_id
WEB2M_API_KEY=your_api_key
WEB2M_API_URL=https://api.web2m.com
```

### Step 2: Verify Database
```bash
# Kiểm tra users table có 2 cột:
# - xu_balance INT DEFAULT 0
# - bonus_xu INT DEFAULT 0

# Kiểm tra transactions table có các cột:
# - transaction_code VARCHAR UNIQUE
# - amount_vnd, xu_amount, status, payment_method, type, metadata
```

### Step 3: Test Webhook
Chọn 1 trong 2:

**Option A: PowerShell (Windows)**
```powershell
.\web2m-test.ps1 -Secret "your_secret" -UserId 1
```

**Option B: Bash (macOS/Linux)**
```bash
bash web2m-test.sh "http://localhost:8000/webhook/web2m" "your_secret" 1
```

### Step 4: Run Unit Tests
```bash
php artisan test tests/Feature/Web2mWebhookTest.php
```

### Step 5: Test Frontend Integration
```javascript
// Include script trong layout
<script src="{{ asset('js/web2m-payment.js') }}"></script>

// Test API endpoints
Web2mPayment.getPackages((data) => {
  console.log('Packages:', data);
});

// Test payment initiation
Web2mPayment.initiate(100000, (data) => {
  console.log('Payment initiated:', data);
});

// Check payment status
Web2mPayment.checkStatus((data) => {
  console.log('Balance:', data.xu_balance);
});
```

---

## 📊 API Endpoints (3 endpoints)

### 1. GET /payment/packages
Lấy danh sách gói thanh toán
```bash
curl -X GET http://localhost:8000/payment/packages \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. POST /payment/initiate
Khởi tạo thanh toán
```bash
curl -X POST http://localhost:8000/payment/initiate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount_vnd": 100000}'
```

### 3. POST /payment/status
Check trạng thái thanh toán
```bash
curl -X POST http://localhost:8000/payment/status \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 💰 Bảng Giá (Pricing)

| VND | Xu Chính | Xu Thưởng | Total |
|-----|---------|----------|-------|
| 500,000 | 500 | 100 | **600** ⭐️ |
| 200,000 | 200 | 30 | **230** |
| 100,000 | 100 | 10 | **110** |
| 20,000 | 20 | 0 | **20** |
| <20,000 | /1000 | 0 | - |

---

## 🔒 Security Features

✅ HMAC-SHA256 signature verification  
✅ Transaction code uniqueness (prevent duplicates)  
✅ User validation  
✅ Amount validation  
✅ Comprehensive logging  
✅ Error handling & logging  

---

## 📚 Documentation

Bạn có thể tìm tài liệu chi tiết tại:

1. **[WEB2M_CHECKLIST.md](WEB2M_CHECKLIST.md)** - Pre-deployment checklist
2. **[SETUP_WEB2M.md](SETUP_WEB2M.md)** - Hướng dẫn cài đặt chi tiết
3. **[WEB2M_INTEGRATION_SUMMARY.md](WEB2M_INTEGRATION_SUMMARY.md)** - Tham khảo nhanh + API reference
4. **[src/web2m-integration.md](src/web2m-integration.md)** - Tài liệu kỹ thuật

---

## 🧪 Testing Examples

### Test 1: Valid Payment (PowerShell)
```powershell
$payload = '{"transaction_code":"TXN_001","amount_vnd":100000,"description":"NAPXU 1","status":"completed"}'
$signature = # HMAC-SHA256 của payload
curl -X POST http://localhost:8000/webhook/web2m `
  -H "X-Web2m-Signature: $signature" `
  -d "$payload"
```

### Test 2: Invalid Signature
```powershell
curl -X POST http://localhost:8000/webhook/web2m `
  -H "X-Web2m-Signature: invalid" `
  -d "$payload"
# Response: 403 Forbidden
```

### Test 3: Check Balance
```javascript
Web2mPayment.checkStatus((data) => {
  console.log('XU Balance:', data.xu_balance);
  console.log('Bonus XU:', data.bonus_xu);
  console.log('Total:', data.total_xu);
});
```

---

## ⚙️ Configuration Files

### .env
```env
WEB2M_WEBHOOK_SECRET=<webhook_signature_secret>
WEB2M_PARTNER_ID=<partner_id>
WEB2M_API_KEY=<api_key>
WEB2M_API_URL=https://api.web2m.com
```

### config/web2m.php
```php
return [
    'partner_id' => env('WEB2M_PARTNER_ID', ''),
    'api_key' => env('WEB2M_API_KEY', ''),
    'webhook_secret' => env('WEB2M_WEBHOOK_SECRET', ''),
    'api_url' => env('WEB2M_API_URL', 'https://api.web2m.com'),
    'pricing' => [ /* pricing table */ ],
];
```

---

## 🐛 Common Issues & Solutions

| Issue | Giải pháp |
|-------|----------|
| Webhook 403 | Check `WEB2M_WEBHOOK_SECRET` khớp Web2m dashboard |
| User not found | Đảm bảo description = `NAPXU {user_id}` |
| Balance not updating | Verify `xu_balance` & `bonus_xu` columns trong DB |
| Signature invalid | Kiểm tra encoding (UTF-8) & secret string |

---

## 📝 File Structure

```
getlink-2026/
├── app/
│   ├── Services/
│   │   └── Web2mService.php ✨ NEW
│   └── Http/Controllers/
│       ├── PaymentController.php ✨ NEW
│       └── WebhookController.php 📝 UPDATED
├── config/
│   └── web2m.php ✨ NEW
├── routes/
│   └── web.php 📝 UPDATED
├── resources/
│   └── js/
│       └── web2m-payment.js ✨ NEW
├── tests/
│   └── Feature/
│       └── Web2mWebhookTest.php ✨ NEW
├── src/
│   └── web2m-integration.md ✨ NEW
├── .env 📝 UPDATED
├── SETUP_WEB2M.md ✨ NEW
├── WEB2M_INTEGRATION_SUMMARY.md ✨ NEW
├── WEB2M_CHECKLIST.md ✨ NEW
├── web2m-test.sh ✨ NEW
└── web2m-test.ps1 ✨ NEW
```

---

## ✨ Features

### Backend
- ✅ Web2mService - tính Xu, verify signature, handle webhook
- ✅ PaymentController - 3 API endpoints
- ✅ Configuration file - centralized settings
- ✅ Route - web2m payment routes

### Frontend
- ✅ JavaScript helper - easy API calls
- ✅ Polling - check payment status
- ✅ UI notifications - success/error messages

### Testing
- ✅ 7 unit tests
- ✅ Bash testing script
- ✅ PowerShell testing script

---

## 🎯 Next Steps

1. **Get Web2m Credentials**
   - Liên hệ Web2m
   - Lấy PARTNER_ID, API_KEY, WEBHOOK_SECRET

2. **Setup & Test**
   - Update .env
   - Run tests
   - Verify database

3. **Configure Web2m Dashboard**
   - Set webhook URL
   - Set signature secret
   - Test webhook

4. **Deploy to Production**
   - Update .env with production values
   - Run migrations
   - Enable logging

---

## 📞 Support

### Tài liệu
- Xem [WEB2M_CHECKLIST.md](WEB2M_CHECKLIST.md) để pre-deployment checklist
- Xem [SETUP_WEB2M.md](SETUP_WEB2M.md) để hướng dẫn chi tiết
- Xem [WEB2M_INTEGRATION_SUMMARY.md](WEB2M_INTEGRATION_SUMMARY.md) để API reference

### Testing
- Chạy `php artisan test tests/Feature/Web2mWebhookTest.php`
- Chạy PowerShell script: `.\web2m-test.ps1`
- Chạy Bash script: `bash web2m-test.sh`

---

## ✅ Status

- **Implementation**: ✅ Complete
- **Testing**: ✅ Ready
- **Documentation**: ✅ Complete
- **Ready for**: Development → Testing → Production

---

**Created**: June 3, 2026  
**Version**: 1.0  
**Status**: 🟢 Ready to Deploy
