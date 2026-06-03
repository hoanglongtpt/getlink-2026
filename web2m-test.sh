#!/bin/bash

# Web2m Webhook Testing Script
# Usage: bash web2m-test.sh

echo "🔧 Web2m Webhook Testing Script"
echo "================================"
echo ""

# Configuration
WEBHOOK_URL="${1:-http://localhost:8000/webhook/web2m}"
SECRET="${2:-your_web2m_webhook_secret}"
USER_ID="${3:-1}"

echo "📝 Configuration:"
echo "  Webhook URL: $WEBHOOK_URL"
echo "  Secret: $SECRET"
echo "  User ID: $USER_ID"
echo ""

# Test Case 1: Valid Payment
echo "🧪 Test Case 1: Valid Payment (100,000 VND)"
echo "=============================================="

PAYLOAD='{"transaction_code":"TXN_TEST_001","amount_vnd":100000,"description":"NAPXU '$USER_ID'","email":"test@example.com","status":"completed"}'
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" -hex | cut -d' ' -f2)

echo "Payload: $PAYLOAD"
echo "Signature: $SIGNATURE"
echo ""

curl -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -H "X-Web2m-Signature: $SIGNATURE" \
  -d "$PAYLOAD" \
  | jq '.' 2>/dev/null || echo "Response (raw):"

echo ""
echo ""

# Test Case 2: Invalid Signature
echo "🧪 Test Case 2: Invalid Signature"
echo "==================================="

curl -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -H "X-Web2m-Signature: invalid_signature" \
  -d "$PAYLOAD" \
  | jq '.' 2>/dev/null || echo "Response (raw):"

echo ""
echo ""

# Test Case 3: Missing Signature
echo "🧪 Test Case 3: Missing Signature"
echo "==================================="

curl -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -d "$PAYLOAD" \
  | jq '.' 2>/dev/null || echo "Response (raw):"

echo ""
echo ""

# Test Case 4: Different Pricing Tiers
echo "🧪 Test Case 4: Different Pricing Tiers"
echo "=========================================="

for AMOUNT in 20000 100000 200000 500000; do
  echo "Testing $AMOUNT VND..."
  
  PAYLOAD_TMP='{"transaction_code":"TXN_'$AMOUNT'_001","amount_vnd":'$AMOUNT',"description":"NAPXU '$USER_ID'","status":"completed"}'
  SIGNATURE_TMP=$(echo -n "$PAYLOAD_TMP" | openssl dgst -sha256 -hmac "$SECRET" -hex | cut -d' ' -f2)
  
  RESPONSE=$(curl -s -X POST "$WEBHOOK_URL" \
    -H "Content-Type: application/json" \
    -H "X-Web2m-Signature: $SIGNATURE_TMP" \
    -d "$PAYLOAD_TMP")
  
  echo "$RESPONSE" | jq '.xu_main, .xu_bonus, .total_xu' 2>/dev/null || echo "  Response: $RESPONSE"
  echo ""
done

echo ""
echo "✅ Testing Complete!"
echo ""
echo "📋 Summary:"
echo "  - Valid payment should return 200 with success message"
echo "  - Invalid signature should return 403"
echo "  - Missing signature should return 403"
echo "  - Different amounts should calculate correct Xu"
