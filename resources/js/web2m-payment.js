/**
 * Web2m Payment Integration JavaScript Helper
 * 
 * Usage:
 *   Web2mPayment.initiate(100000);  // Khởi tạo thanh toán 100k
 *   Web2mPayment.checkStatus();      // Kiểm tra trạng thái
 */

const Web2mPayment = {
    /**
     * Khởi tạo thanh toán
     * @param {number} amountVnd - Số tiền VND
     * @param {Function} onSuccess - Callback khi thành công
     * @param {Function} onError - Callback khi lỗi
     */
    async initiate(amountVnd, onSuccess = null, onError = null) {
        try {
            const response = await fetch('/payment/initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ amount_vnd: amountVnd }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            console.log('✅ Payment initiated:', data);

            if (onSuccess) onSuccess(data);

            // Nếu có redirect_url, chuyển hướng
            if (data.redirect_url) {
                // window.location.href = data.redirect_url;
            }

            return data;

        } catch (error) {
            console.error('❌ Payment initiation error:', error);
            if (onError) onError(error);
            throw error;
        }
    },

    /**
     * Kiểm tra trạng thái thanh toán
     * @param {Function} onSuccess - Callback khi thành công
     * @param {Function} onError - Callback khi lỗi
     */
    async checkStatus(onSuccess = null, onError = null) {
        try {
            const response = await fetch('/payment/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (onSuccess) onSuccess(data);
            return data;

        } catch (error) {
            console.error('❌ Status check error:', error);
            if (onError) onError(error);
        }
    },

    /**
     * Lấy danh sách gói thanh toán
     * @param {Function} onSuccess - Callback khi thành công
     * @param {Function} onError - Callback khi lỗi
     */
    async getPackages(onSuccess = null, onError = null) {
        try {
            const response = await fetch('/payment/packages', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (onSuccess) onSuccess(data);
            return data;

        } catch (error) {
            console.error('❌ Packages fetch error:', error);
            if (onError) onError(error);
        }
    },

    /**
     * Bắt đầu polling để kiểm tra thanh toán
     * @param {Function} onPaymentSuccess - Callback khi phát hiện thanh toán thành công
     * @param {number} pollInterval - Khoảng thời gian polling (ms)
     * @param {number} maxAttempts - Số lần polling tối đa
     */
    startPolling(onPaymentSuccess = null, pollInterval = 3000, maxAttempts = 100) {
        let attempts = 0;
        let lastTotalXu = this.getCurrentTotalXu();

        const pollTimer = setInterval(async () => {
            attempts++;

            if (attempts > maxAttempts) {
                console.log('⏱️ Polling timeout');
                clearInterval(pollTimer);
                return;
            }

            try {
                const data = await this.checkStatus();
                const currentTotal = data.xu_balance + data.bonus_xu;

                if (currentTotal > lastTotalXu) {
                    console.log('💰 Payment detected!', data);
                    lastTotalXu = currentTotal;
                    
                    if (onPaymentSuccess) onPaymentSuccess(data);
                    
                    clearInterval(pollTimer);
                }
            } catch (error) {
                console.error('❌ Polling error:', error);
            }
        }, pollInterval);

        return pollTimer;
    },

    /**
     * Lấy tổng Xu hiện tại từ UI
     */
    getCurrentTotalXu() {
        const xuElement = document.querySelector('.xu-balance-display');
        if (xuElement) {
            const match = xuElement.textContent.match(/(\d+)/);
            return match ? parseInt(match[1]) : 0;
        }
        return 0;
    },

    /**
     * Format tiền VND
     * @param {number} amount
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    },

    /**
     * Show success notification
     * @param {Object} data - Transaction data
     */
    showSuccessNotification(data) {
        const div = document.createElement('div');
        div.className = "fixed bottom-8 right-8 z-[200] bg-white rounded-[2rem] shadow-[0_30px_70px_rgba(0,0,0,0.3)] border-2 border-green-500 p-8 max-w-sm transform translate-y-20 opacity-0 transition-all duration-700 flex flex-col items-center text-center";
        div.innerHTML = `
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mb-6 shadow-inner animate-bounce">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4 class="font-black text-gray-800 text-2xl mb-2">NẠP THÀNH CÔNG!</h4>
            <p class="text-gray-500 font-medium leading-relaxed">Bạn vừa nạp thêm <strong class="text-purple-600 text-lg">${data.total_xu} Xu</strong>. Chúc bạn có trải nghiệm tuyệt vời!</p>
            <button onclick="this.parentElement.remove()" class="mt-8 w-full bg-gray-900 text-white py-4 rounded-2xl font-black text-sm hover:bg-black transition shadow-lg uppercase tracking-widest">Tuyệt vời</button>
        `;
        document.body.appendChild(div);
        setTimeout(() => { div.classList.remove('translate-y-20', 'opacity-0'); }, 100);
        setTimeout(() => { if(div) div.remove(); }, 15000);
    },
};

// Export cho module
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Web2mPayment;
}
