# Web2m Integration Setup Checklist

## ✅ Implementation Complete

### Backend Components
- [x] `app/Services/Web2mService.php` - Service xử lý Web2m logic
- [x] `app/Http/Controllers/PaymentController.php` - Payment API endpoints
- [x] `app/Http/Controllers/WebhookController.php` - Updated webhook handler
- [x] `config/web2m.php` - Configuration file
- [x] `routes/web.php` - Updated with payment routes

### Database
- [x] `users` table - Ensure `xu_balance` and `bonus_xu` columns exist
- [x] `transactions` table - Ensure proper schema

### Frontend
- [x] `resources/js/web2m-payment.js` - JavaScript helper library

### Testing & Documentation
- [x] `tests/Feature/Web2mWebhookTest.php` - Unit tests
- [x] `src/web2m-integration.md` - Technical documentation
- [x] `SETUP_WEB2M.md` - Setup guide
- [x] `WEB2M_INTEGRATION_SUMMARY.md` - Summary & quick start

### Testing Scripts
- [x] `web2m-test.sh` - Bash testing script
- [x] `web2m-test.ps1` - PowerShell testing script

---

## 📋 Pre-Deployment Checklist

### 1. Configuration
- [ ] Copy `.env.example` or update `.env` with:
  ```env
  WEB2M_WEBHOOK_SECRET=<secret_from_web2m>
  WEB2M_PARTNER_ID=<partner_id>
  WEB2M_API_KEY=<api_key>
  WEB2M_API_URL=https://api.web2m.com
  ```

### 2. Database
- [ ] Verify `users` table has columns:
  ```sql
  xu_balance INT DEFAULT 0
  bonus_xu INT DEFAULT 0
  ```
- [ ] Verify `transactions` table has columns:
  ```sql
  transaction_code VARCHAR UNIQUE
  amount_vnd INT
  xu_amount INT
  status VARCHAR
  payment_method VARCHAR
  type VARCHAR
  metadata JSON
  ```
- [ ] Run migrations if needed:
  ```bash
  php artisan migrate
  ```

### 3. Testing
- [ ] Run unit tests:
  ```bash
  php artisan test tests/Feature/Web2mWebhookTest.php
  ```
- [ ] Test webhook locally:
  ```bash
  # PowerShell
  .\web2m-test.ps1
  
  # Bash
  bash web2m-test.sh
  ```

### 4. Web2m Configuration
- [ ] Create Web2m account / Get partner credentials
- [ ] Configure webhook URL: `https://yourdomain.com/webhook/web2m`
- [ ] Set webhook signature secret in Web2m dashboard
- [ ] Test webhook from Web2m dashboard

### 5. Frontend Integration
- [ ] Include JavaScript helper in layout:
  ```html
  <script src="{{ asset('js/web2m-payment.js') }}"></script>
  ```
- [ ] Update payment modal (optional - already done in packages.index)
- [ ] Test payment flow:
  1. User selects package
  2. Click "Chọn gói này"
  3. Payment modal opens
  4. Simulate payment
  5. Check balance updates

### 6. Monitoring
- [ ] Setup log monitoring:
  ```bash
  tail -f storage/logs/laravel.log | grep -i web2m
  ```
- [ ] Monitor transaction table for success rate
- [ ] Setup alerts for failed transactions

### 7. Documentation
- [ ] Review [SETUP_WEB2M.md](SETUP_WEB2M.md)
- [ ] Review [src/web2m-integration.md](src/web2m-integration.md)
- [ ] Update team documentation

---

## 🚀 Deployment Steps

### Development
```bash
# 1. Setup environment
cp .env.example .env
# Edit .env with Web2m credentials

# 2. Run migrations
php artisan migrate

# 3. Run tests
php artisan test tests/Feature/Web2mWebhookTest.php

# 4. Test webhook
.\web2m-test.ps1

# 5. Start development server
php artisan serve
```

### Production
```bash
# 1. Update .env
APP_ENV=production
APP_DEBUG=false

# 2. Update Web2m credentials
WEB2M_WEBHOOK_SECRET=<strong_secret>
WEB2M_PARTNER_ID=<prod_partner_id>
WEB2M_API_KEY=<prod_api_key>
WEB2M_API_URL=https://api.web2m.com

# 3. Run migrations
php artisan migrate --force

# 4. Cache config
php artisan config:cache
php artisan route:cache

# 5. Setup log rotation
# Configure logrotate or use Laravel's daily log channel

# 6. Test webhook on production
curl -X POST https://yourdomain.com/webhook/web2m \
  -H "X-Web2m-Signature: <signature>" \
  -d '<payload>'
```

---

## 🔍 Verification

### After Deployment
- [ ] Webhook endpoint returns 200 for valid requests
- [ ] User balance updates correctly
- [ ] Transaction records created in database
- [ ] Logs show successful webhook processing
- [ ] Frontend payment flow works end-to-end
- [ ] Signature verification working
- [ ] Duplicate transactions handled gracefully

### Monitoring
- [ ] Check logs for errors:
  ```bash
  cat storage/logs/laravel.log | grep -i "error\|warning"
  ```
- [ ] Monitor transaction success rate
- [ ] Track webhook response times
- [ ] Alert on failed signature verification

---

## 📞 Troubleshooting

| Issue | Solution |
|-------|----------|
| Webhook 403 | Check X-Web2m-Signature header & WEB2M_WEBHOOK_SECRET |
| User not found | Ensure description format is "NAPXU {user_id}" |
| Balance not updating | Check users table columns & transaction logs |
| Duplicate transactions | Normal - uses transaction_code as idempotent key |
| Signature mismatch | Verify payload encoding (UTF-8) |

---

## 📚 Related Files

- [SETUP_WEB2M.md](SETUP_WEB2M.md) - Detailed setup guide
- [WEB2M_INTEGRATION_SUMMARY.md](WEB2M_INTEGRATION_SUMMARY.md) - Quick reference
- [src/web2m-integration.md](src/web2m-integration.md) - API documentation
- [app/Services/Web2mService.php](app/Services/Web2mService.php) - Service code
- [config/web2m.php](config/web2m.php) - Configuration options
- [tests/Feature/Web2mWebhookTest.php](tests/Feature/Web2mWebhookTest.php) - Test examples

---

## ✨ Features Implemented

✅ HMAC-SHA256 webhook signature verification  
✅ Dynamic Xu calculation based on pricing tiers  
✅ Duplicate transaction prevention (idempotent)  
✅ User lookup from description or email  
✅ Transaction status tracking  
✅ Comprehensive error handling  
✅ Extensive logging for audit trail  
✅ Unit tests with multiple test cases  
✅ JavaScript helper for frontend integration  
✅ PaymentController with 3 API endpoints  

---

**Last Updated**: June 3, 2026  
**Status**: ✅ Ready for Testing
