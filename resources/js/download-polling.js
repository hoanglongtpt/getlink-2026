document.addEventListener('DOMContentLoaded', function () {
    const downloadForm = document.getElementById('downloadForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const pollingSection = document.getElementById('pollingSection');
    const pollingLinkDisplay = document.getElementById('pollingLinkDisplay');
    const pollingStatusText = document.getElementById('pollingStatusText');
    const pollingResultLink = document.getElementById('pollingResultLink');

    if (!downloadForm) return;

    let pollingInterval = null;
    let currentPollingId = null;

    // Check if we need to resume polling for a pending item
    const checkPendingDownloads = async () => {
        try {
            // Find all pending/processing histories
            const pendingHistories = document.querySelectorAll('tr[data-status="pending"], tr[data-status="processing"], tr[data-status="ready"]');
            if(pendingHistories && pendingHistories.length > 0) {
                 // In a real app we might want to fetch pending downloads from API on load
                 // For now this will be handled by the history page
            }
        } catch (e) {
            console.error(e);
        }
    };
    
    checkPendingDownloads();

    // Handle Form Submit via AJAX
    downloadForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        const originalContent = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i><span>Đang xử lý...</span>';
        
        try {
            const formData = new FormData(downloadForm);
            const response = await fetch(downloadForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Update Xu balance if possible
                updateXuBalance(result.new_balance);
                
                // Show success toast
                showToast(result.message, 'success');
                downloadForm.reset();

                // Start polling visually
                startVisualPolling(result.history);
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Lỗi kết nối máy chủ. Vui lòng thử lại.', 'error');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalContent;
        }
    });

    function startVisualPolling(history) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        currentPollingId = history.id;
        
        // Show the polling UI
        if (pollingSection) {
            pollingSection.classList.remove('hidden');
            if (pollingLinkDisplay) pollingLinkDisplay.textContent = history.original_link;
            if (pollingStatusText) {
                pollingStatusText.innerHTML = 'Hệ thống đang tải tài nguyên...';
                pollingStatusText.parentElement.querySelector('.animate-ping')?.classList.remove('hidden');
            }
            if (pollingResultLink) pollingResultLink.classList.add('hidden');
            
            // Scroll to polling section smoothly
            pollingSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // If it's already completed from the initial response (e.g., cached)
        if (history.status === 'completed' || history.status === 'cached' || history.status === 'ready') {
             handlePollingSuccess(history);
             return;
        }

        // Start polling loop
        pollingInterval = setInterval(async () => {
            if (!currentPollingId) {
                clearInterval(pollingInterval);
                return;
            }

            try {
                const response = await fetch('/download/poll-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_csrf"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ ids: [currentPollingId] })
                });

                const updatedHistories = await response.json();
                
                if (updatedHistories && updatedHistories.length > 0) {
                    const updatedHistory = updatedHistories[0];
                    
                    if (updatedHistory.status === 'completed' || updatedHistory.status === 'cached' || updatedHistory.status === 'ready') {
                        handlePollingSuccess(updatedHistory);
                    } else if (updatedHistory.status === 'failed') {
                        handlePollingError(updatedHistory);
                    }
                }
            } catch (error) {
                console.error("Polling error:", error);
            }

        }, 3000); // Poll every 3 seconds
    }

    function handlePollingSuccess(history) {
        clearInterval(pollingInterval);
        pollingInterval = null;
        
        if (pollingStatusText) {
            pollingStatusText.innerHTML = 'Tải thành công! File đã sẵn sàng.';
            pollingStatusText.className = "text-sm font-bold text-green-600";
            const pingIndicator = pollingStatusText.parentElement.querySelector('.animate-ping');
            if (pingIndicator) pingIndicator.classList.add('hidden');
            
            const dotIndicator = pollingStatusText.parentElement.querySelector('.bg-purple-500');
            if (dotIndicator) {
                dotIndicator.classList.remove('bg-purple-500');
                dotIndicator.classList.add('bg-green-500');
            }
        }
        
        if (pollingResultLink && history.direct_download_link) {
            pollingResultLink.href = history.direct_download_link;
            pollingResultLink.classList.remove('hidden');
            pollingResultLink.classList.add('flex');
            
            // Auto click to download or navigate
            setTimeout(() => {
                 window.open(history.direct_download_link, '_blank');
            }, 1000);
        }
        
        showToast('Tài nguyên đã sẵn sàng để tải xuống!', 'success');
        
        // Hide polling section after some time
        setTimeout(() => {
            if (pollingSection) {
                pollingSection.classList.add('hidden');
                // reset UI states for next time
                if (pollingStatusText) {
                    pollingStatusText.className = "text-sm font-medium text-purple-700";
                    const dotIndicator = pollingStatusText.parentElement.querySelector('.bg-green-500');
                    if (dotIndicator) {
                        dotIndicator.classList.remove('bg-green-500');
                        dotIndicator.classList.add('bg-purple-500');
                    }
                }
            }
            window.location.href = '/profile#history'; // redirect to history
        }, 5000);
    }
    
    function handlePollingError(history) {
        clearInterval(pollingInterval);
        pollingInterval = null;
        
        if (pollingStatusText) {
            pollingStatusText.innerHTML = 'Tải thất bại. Vui lòng thử lại sau.';
            pollingStatusText.className = "text-sm font-bold text-red-600";
            const pingIndicator = pollingStatusText.parentElement.querySelector('.animate-ping');
            if (pingIndicator) pingIndicator.classList.add('hidden');
            
            const dotIndicator = pollingStatusText.parentElement.querySelector('.bg-purple-500');
            if (dotIndicator) {
                dotIndicator.classList.remove('bg-purple-500');
                dotIndicator.classList.add('bg-red-500');
            }
        }
        
        showToast('Tải tài nguyên thất bại. Xu đã được hoàn lại.', 'error');
        
        // Hide polling section after some time
        setTimeout(() => {
            if (pollingSection) pollingSection.classList.add('hidden');
        }, 5000);
    }

    function updateXuBalance(newBalance) {
        if(newBalance === undefined || newBalance === null) return;
        
        const balanceElements = document.querySelectorAll('.xu-balance-display');
        balanceElements.forEach(el => {
            // Need to handle the bonus part if exist, this is a simplified version
            // In a real app we might return structured balance { xu: 10, bonus: 5 }
            // For now just update the text content with a basic format
            el.innerHTML = `${newBalance} Xu`;
        });
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `p-4 mb-3 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-start gap-3 min-w-[300px] border shadow-[0_8px_30px_rgb(0,0,0,0.12)] ${
            type === 'success' ? 'bg-white border-green-100 text-green-800' : 'bg-white border-red-100 text-red-800'
        }`;
        
        const iconContainer = document.createElement('div');
        iconContainer.className = `w-8 h-8 rounded-full flex items-center justify-center shrink-0 ${type === 'success' ? 'bg-green-100' : 'bg-red-100'}`;
        
        const icon = type === 'success' ? '<i class="fas fa-check text-green-600"></i>' : '<i class="fas fa-exclamation text-red-600"></i>';
        iconContainer.innerHTML = icon;
        
        toast.innerHTML = `
            ${iconContainer.outerHTML}
            <div class="font-medium text-sm flex-1 pt-1.5 text-gray-700">${message}</div>
            <button class="text-gray-400 hover:text-gray-600 focus:outline-none pt-1" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        toastContainer.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
        });

        // Auto remove after 5s
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.classList.add('translate-x-full');
                toast.classList.add('opacity-0');
                setTimeout(() => {
                    if (document.body.contains(toast)) toast.remove();
                }, 300);
            }
        }, 5000);
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-20 right-6 z-[9999] flex flex-col items-end pointer-events-none';
        
        // Make children clickable
        const style = document.createElement('style');
        style.textContent = '#toast-container > div { pointer-events: auto; }';
        document.head.appendChild(style);
        
        document.body.appendChild(container);
        return container;
    }
});