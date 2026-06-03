# Web2m Payment Integration Guide

## Overview
Web2m payment integration cho phép người dùng nạp tiền VND và nhận Xu trong hệ thống.

## Configuration

### 1. Environment Variables (.env)
```env
WEB2M_WEBHOOK_SECRET=your_web2m_webhook_secret
WEB2M_PARTNER_ID=your_partner_id
WEB2M_API_KEY=your_api_key
WEB2M_API_URL=https://api.web2m.com
```

### 2. Pricing Table (Bảng giá)
Định nghĩa tại `Web2mService::PRICING_TABLE`:

| VND Amount | Xu Chính | Xu Thưởng | Tỷ lệ |
|-----------|---------|----------|-------|
| ≥ 500,000 | 500     | 100      | 1.2x  |
| ≥ 200,000 | 200     | 30       | 1.15x |
| ≥ 100,000 | 100     | 10       | 1.1x  |
| ≥ 20,000  | 20      | 0        | 1x    |
| < 20,000  | /1000   | 0        | 1x    |

## Webhook Flow

### 1. Request từ Web2m
```
POST /webhook/web2m
Headers:
  X-Web2m-Signature: HMAC-SHA256(payload, secret)

Body:
{
  "transaction_code": "TXN123456",
  "amount_vnd": 100000,
  "amount": 100000,
  "description": "NAPXU 5",
  "email": "user@example.com",
  "status": "completed"
}
```

### 2. Signature Verification
```php
$signature = hash_hmac('sha256', $payloadJson, $secret);
// So sánh với X-Web2m-Signature header
```

### 3. User Identification
Hệ thống tìm user theo thứ tự:
1. Từ `description` (pattern: `NAPXU {user_id}`)
2. Từ `email`

### 4. Xu Calculation
```php
$xuInfo = $web2mService->calculateXu($amountVnd);
// Returns: ['xu_main' => int, 'xu_bonus' => int]
```

### 5. Update User Balance
```php
$user->increment('xu_balance', $xuMain);
$user->increment('bonus_xu', $xuBonus);
```

## Transaction Status

| Status    | Description |
|-----------|------------|
| completed | Thanh toán thành công |
| pending   | Đang chờ xác nhận |
| failed    | Thanh toán thất bại |
| refunded  | Đã hoàn tiền |

## API Usage

### Handle Webhook
```php
// app/Http/Controllers/WebhookController.php
$web2mService = new Web2mService();
$result = $web2mService->handleWebhookPayload($request->all());
```

### Calculate Xu
```php
$xuInfo = $web2mService->calculateXu(100000); 
// ['xu_main' => 100, 'xu_bonus' => 10]
```

### Get Package Info
```php
$packages = $web2mService->getPackageInfo();
// Array of package details for display
```

## Security Considerations

1. **Signature Verification**: Bắt buộc phải kiểm tra signature từ X-Web2m-Signature header
2. **Transaction Code**: Dùng `transaction_code` làm unique identifier để tránh duplicate
3. **Idempotent**: Webhook có thể được gửi lại, hệ thống phải handle gracefully
4. **Logging**: Log tất cả transaction cho audit trail

## Testing

### Local Testing
1. Sử dụng ngrok để tunnel local server
   ```bash
   ngrok http 8000
   ```

2. Update webhook URL trên Web2m dashboard

3. Test webhook bằng cURL:
   ```bash
   curl -X POST http://localhost:8000/webhook/web2m \
     -H "Content-Type: application/json" \
     -H "X-Web2m-Signature: your_signature" \
     -d '{"transaction_code":"TEST123","amount_vnd":100000,"description":"NAPXU 1","email":"user@example.com"}'
   ```

## Database Schema

### transactions table
```sql
id              BIGINT
user_id         BIGINT
transaction_code VARCHAR
amount_vnd      INT
xu_amount       INT
status          VARCHAR (completed, pending, failed, refunded)
payment_method  VARCHAR (web2m, ...)
type            VARCHAR (top_up, download, ...)
metadata        JSON
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### users table (additional columns)
```sql
xu_balance      INT DEFAULT 0
bonus_xu        INT DEFAULT 0
```

## Error Handling

| Error | Solution |
|-------|----------|
| Missing signature | Check X-Web2m-Signature header |
| Invalid signature | Verify WEB2M_WEBHOOK_SECRET env variable |
| User not found | Check description format (NAPXU {id}) or email |
| Invalid amount | amount_vnd must be > 0 |
| Duplicate transaction | Use transaction_code as idempotent key |

## Related Files

- Service: `app/Services/Web2mService.php`
- Webhook Handler: `app/Http/Controllers/WebhookController.php`
- Routes: `routes/web.php` (POST /webhook/web2m)
- Database: `database/migrations/*_create_transactions_table.php`
- Tests: `tests/Feature/Web2mWebhookTest.php`
