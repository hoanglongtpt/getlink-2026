<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Web2mService
{
    private const DEFAULT_PACKAGES = [
        [
            'name' => 'Gói Trải Nghiệm',
            'amount_vnd' => 20000,
            'xu_main' => 20,
            'xu_bonus' => 0,
            'description' => 'Lý tưởng cho người mới bắt đầu',
            'is_popular' => false,
        ],
        [
            'name' => 'Gói Tiết Kiệm',
            'amount_vnd' => 100000,
            'xu_main' => 100,
            'xu_bonus' => 10,
            'description' => 'Phổ biến nhất',
            'is_popular' => true,
        ],
        [
            'name' => 'Gói Bán Chuyên',
            'amount_vnd' => 200000,
            'xu_main' => 200,
            'xu_bonus' => 30,
            'description' => 'Phù hợp với bán chuyên',
            'is_popular' => false,
        ],
        [
            'name' => 'Gói Chuyên Nghiệp',
            'amount_vnd' => 500000,
            'xu_main' => 500,
            'xu_bonus' => 100,
            'description' => 'Thích hợp cho chuyên nghiệp',
            'is_popular' => false,
        ],
    ];

    /**
     * Bảng giá chuyển đổi VND sang Xu
     * Format: [amount_vnd_min => [xu_main, xu_bonus], ...]
     */
    private const PRICING_TABLE = [
        500000 => ['xu_main' => 500, 'xu_bonus' => 100],
        200000 => ['xu_main' => 200, 'xu_bonus' => 30],
        100000 => ['xu_main' => 100, 'xu_bonus' => 10],
        20000  => ['xu_main' => 20, 'xu_bonus' => 0],
        0      => ['xu_main' => 1, 'xu_bonus' => 0],  // 1000đ = 1 xu (for small amounts)
    ];

    /**
     * Xác thực signature webhook từ Web2m
     */
    public function verifyWebhookSignature(string $payloadJson, string $signature, string $secret): bool
    {
        if (!$secret) {
            Log::warning('WEB2M_WEBHOOK_SECRET is not configured. Webhook signature validation skipped.');
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $payloadJson, $secret);
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Web2m webhook invalid signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Trích xuất signature từ request headers
     */
    public function extractSignature($request): ?string
    {
        return $request->header('X-Web2m-Signature')
            ?? $request->header('Web2m-Signature')
            ?? $request->header('X-Signature');
    }

    /**
     * Tìm user từ payload webhook
     */
    public function findUserFromPayload(array $payload): ?User
    {
        // Tìm từ description: NAPXU {user_id}
        $description = $payload['description'] ?? '';
        if (preg_match('/NAPXU\s+(\d+)/i', $description, $matches)) {
            $userId = $matches[1];
            $user = User::find($userId);
            if ($user) {
                return $user;
            }
        }

        // Fallback: tìm từ email
        $email = $payload['email'] ?? '';
        if ($email) {
            return User::where('email', $email)->first();
        }

        return null;
    }

    /**
     * Tính toán Xu dựa vào số tiền VND
     */
    public function calculateXu(int $amountVnd): array
    {
        // Sắp xếp bảng giá từ cao xuống thấp
        $sortedPricing = collect(self::PRICING_TABLE)
            ->sortKeys(SORT_NUMERIC, true)
            ->toArray();

        foreach ($sortedPricing as $minAmount => $xuInfo) {
            if ($amountVnd >= $minAmount) {
                // Nếu là gói lẻ (mức 0), tính toán: 1000đ = 1 xu
                if ($minAmount === 0 && $amountVnd < 20000) {
                    return [
                        'xu_main' => max(1, (int)($amountVnd / 1000)),
                        'xu_bonus' => 0,
                    ];
                }
                return $xuInfo;
            }
        }

        // Fallback (không bao giờ đến đây)
        return ['xu_main' => 0, 'xu_bonus' => 0];
    }

    /**
     * Tạo QR URL Web2m
     */
    public function generateQRWeb2mQr(string $bankCode, string $account, ?int $amount = null, ?string $memo = null): string
    {
        $accountHolder = config('web2m.account_holder', 'PHAM XUAN QUY');
        $accountHolderEncoded = rawurlencode($accountHolder);
        $baseUrl = rtrim(config('web2m.api_url'), '/');
        $url = "{$baseUrl}/quicklink/{$bankCode}/{$account}/{$accountHolderEncoded}";

        $queryParams = [];
        if (!empty($amount)) {
            $queryParams['amount'] = $amount;
        }
        if (!empty($memo)) {
            $queryParams['memo'] = $memo;
        }

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Xử lý webhook Web2m và cập nhật balance người dùng
     */
    public function handleWebhookPayload(array $payload): array
    {
        $transactionCode = $payload['transaction_code'] ?? $payload['id'] ?? null;
        $amountVnd = (int)($payload['amount_vnd'] ?? $payload['amount'] ?? 0);

        // Validate payload
        if (!$transactionCode || $amountVnd <= 0) {
            Log::error('Web2m webhook: Invalid payload', ['amount' => $amountVnd, 'code' => $transactionCode]);
            throw new \InvalidArgumentException('Invalid webhook payload: missing transaction_code or invalid amount');
        }

        // Tìm user
        $user = $this->findUserFromPayload($payload);
        if (!$user) {
            Log::error('Web2m webhook: User not found', $payload);
            throw new \RuntimeException('User not found for payment');
        }

        // Tính toán Xu
        $xuInfo = $this->calculateXu($amountVnd);
        $xuMain = $xuInfo['xu_main'];
        $xuBonus = $xuInfo['xu_bonus'];

        // Tạo hoặc cập nhật transaction
        $transaction = Transaction::updateOrCreate(
            ['transaction_code' => $transactionCode],
            [
                'user_id' => $user->id,
                'amount_vnd' => $amountVnd,
                'xu_amount' => $xuMain + $xuBonus,
                'status' => 'completed',
                'payment_method' => 'web2m',
                'type' => 'top_up',
                'metadata' => array_merge($payload, [
                    'xu_main' => $xuMain,
                    'xu_bonus' => $xuBonus,
                ]),
            ]
        );

        // Cập nhật balance user
        $user->increment('xu_balance', $xuMain);
        $user->increment('bonus_xu', $xuBonus);

        Log::info('Web2m payment processed successfully', [
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'amount_vnd' => $amountVnd,
            'xu_main' => $xuMain,
            'xu_bonus' => $xuBonus,
        ]);

        return [
            'success' => true,
            'transaction_id' => $transaction->id,
            'user_id' => $user->id,
            'xu_main' => $xuMain,
            'xu_bonus' => $xuBonus,
            'total_xu' => $xuMain + $xuBonus,
        ];
    }

    /**
     * Lấy bảng giá hiện tại
     */
    public function getPricingTable(): array
    {
        return self::PRICING_TABLE;
    }

    /**
     * Tìm gói theo số tiền VND đã chọn
     */
    public function findPackageByAmount(int $amountVnd): ?array
    {
        return collect($this->getPackageInfo())
            ->firstWhere('amount_vnd', $amountVnd);
    }

    /**
     * Lấy thông tin gói thanh toán để hiển thị
     */
    public function getPackageInfo(): array
    {
        $savedPackages = Setting::getValue('payment_packages');
        if ($savedPackages) {
            $packages = json_decode($savedPackages, true);
            if (is_array($packages) && count($packages) > 0) {
                return array_map(function ($package) {
                    return [
                        'name' => isset($package['name']) ? trim((string) $package['name']) : '',
                        'amount_vnd' => isset($package['amount_vnd']) ? (int) $package['amount_vnd'] : 0,
                        'xu_main' => isset($package['xu_main']) ? (int) $package['xu_main'] : 0,
                        'xu_bonus' => isset($package['xu_bonus']) ? (int) $package['xu_bonus'] : 0,
                        'description' => isset($package['description']) ? trim((string) $package['description']) : '',
                        'is_popular' => !empty($package['is_popular']),
                    ];
                }, $packages);
            }
        }

        return self::DEFAULT_PACKAGES;
    }
}
