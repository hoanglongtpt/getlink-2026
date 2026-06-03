# Web2m Payment Setup Guide

## Cài đặt Web2m Payment Integration

### 1. Environment Variables

Thêm vào `.env`:

```env
WEB2M_WEBHOOK_SECRET=your_web2m_webhook_secret
WEB2M_PARTNER_ID=your_partner_id
WEB2M_API_KEY=your_api_key
WEB2M_API_URL=https://api.web2m.com
```

### 2. Database Requirements

Đảm bảo bảng `users` có các cột:
```sql
xu_balance INT DEFAULT 0
bonus_xu INT DEFAULT 0
```

Đảm bảo bảng `transactions` có các cột:
```sql
transaction_code VARCHAR UNIQUE
amount_vnd INT
xu_amount INT
status VARCHAR
payment_method VARCHAR
type VARCHAR
metadata JSON
```

### 3. File Structure

```
app/
├── Services/
│   └── Web2mService.php          # Service xử lý Web2m logic
├── Http/Controllers/
│   ├── WebhookController.php    # Webhook handler (đã update)
│   └── PaymentController.php    # Payment endpoints

config/
└── web2m.php                      # Configuration

routes/
└── web.php                        # Routes (đã thêm payment routes)

src/
└── web2m-integration.md           # Documentation

tests/
└── Feature/
    └── Web2mWebhookTest.php      # Unit tests
```

### 4. API Endpoints

#### Lấy danh sách gói thanh toán
```bash
GET /payment/packages
Authorization: Bearer <token>

Response:
{
  "packages": [
    {
      "name": "Gói Tiết Kiệm",
      "amount_vnd": 100000,
      "xu_main": 100,
      "xu_bonus": 10,
      "description": "Lý tưởng cho người mới bắt đầu"
    },
    ...
  ],
  "currency": "VND",
  "pricing_table": { ... }
}
```

#### Khởi tạo thanh toán
```bash
POST /payment/initiate
Authorization: Bearer <token>
Content-Type: application/json

{
  "amount_vnd": 100000
}

Response:
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

#### Kiểm tra trạng thái thanh toán
```bash
POST /payment/status
Authorization: Bearer <token>

Response:
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

#### Web2m Webhook
```bash
POST /webhook/web2m
Headers:
  X-Web2m-Signature: <signature>

Payload:
{
  "transaction_code": "TXN123456",
  "amount_vnd": 100000,
  "description": "NAPXU 5",
  "email": "user@example.com",
  "status": "completed"
}
```

### 5. Workflow Thanh Toán

```
1. User chọn gói thanh toán
   ↓
2. Gọi POST /payment/initiate
   ↓
3. Backend tạo pending transaction
   ↓
4. User được redirect tới Web2m payment gateway
   ↓
5. User thanh toán thành công
   ↓
6. Web2m gửi webhook POST /webhook/web2m
   ↓
7. Backend verify signature
   ↓
8. Tính Xu dựa vào amount_vnd
   ↓
9. Update user xu_balance + bonus_xu
   ↓
10. Update transaction status = completed
   ↓
11. Frontend polling /payment/status để cập nhật UI
```

### 6. Testing

#### Unit Tests
```bash
php artisan test tests/Feature/Web2mWebhookTest.php
```

#### Manual Test dengan cURL
```bash
# 1. Tạo signature
SECRET="your_web2m_webhook_secret"
PAYLOAD='{"transaction_code":"TXN_TEST_123","amount_vnd":100000,"description":"NAPXU 1","status":"completed"}'
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" -hex | cut -d' ' -f2)

# 2. Gửi request
curl -X POST http://localhost:8000/webhook/web2m \
  -H "Content-Type: application/json" \
  -H "X-Web2m-Signature: $SIGNATURE" \
  -d "$PAYLOAD"
```

### 7. Troubleshooting

**Problem**: Webhook signature invalid
- **Solution**: Kiểm tra `WEB2M_WEBHOOK_SECRET` có khớp với Web2m dashboard không
- Log: `storage/logs/laravel.log`

**Problem**: User not found
- **Solution**: Đảm bảo description có format `NAPXU {user_id}` hoặc email có trong hệ thống
- Ví dụ: `NAPXU 5` sẽ tìm user có ID = 5

**Problem**: Xu không được cập nhật
- **Solution**: Kiểm tra bảng `users` có cột `xu_balance` và `bonus_xu`
- Chạy migration nếu cần

**Problem**: Duplicate transactions
- **Solution**: Hệ thống dùng `transaction_code` làm unique identifier, không có vấn đề

### 8. Security Notes

- Luôn verify webhook signature trước khi xử lý
- Không expose `WEB2M_WEBHOOK_SECRET` vào client-side
- Log tất cả transaction cho audit trail
- Dùng HTTPS để communicate với Web2m
- Rate limit webhook endpoint để tránh spam

### 9. Next Steps

1. **Integrateintegration actual Web2m API**
   - Lấy Web2m PARTNER_ID và API_KEY
   - Cấu hình webhook URL trên Web2m dashboard
   - Test webhook signature

2. **Frontend Integration**
   - Update payment modal để gọi `/payment/initiate`
   - Redirect user tới Web2m payment gateway
   - Polling `/payment/status` để check payment status

3. **Production Deployment**
   - Thay đổi `APP_ENV=production`
   - Đảm bảo HTTPS được enable
   - Sử dụng strong `WEB2M_WEBHOOK_SECRET`
   - Enable logging cho audit trail
