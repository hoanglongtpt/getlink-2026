document.addEventListener('DOMContentLoaded', function () {
    const downloadForm = document.getElementById('downloadForm');
    const historyTableBody = document.getElementById('historyTableBody');
    const btnSubmit = document.getElementById('btnSubmit');

    if (!downloadForm || !historyTableBody) return;

    let pollingInterval = null;

    // Handle Form Submit via AJAX
    downloadForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
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
                // Thêm bản ghi mới vào đầu bảng
                addNewHistoryRow(result.history);
                // Cập nhật số dư xu
                updateXuBalance(result.new_balance);
                
                // Show success toast
                showToast(result.message, 'success');
                downloadForm.reset();

                // Bắt đầu polling nếu có job pending/processing
                startPolling();
            } else {
                showToast(result.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            console.error(error);
            showToast('Lỗi kết nối máy chủ. Vui lòng thử lại.', 'error');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-cloud-download-alt"></i> Submit Download';
        }
    });

    // Start Polling for pending/processing rows
    function startPolling() {
        if (pollingInterval) return;

        pollingInterval = setInterval(async () => {
            const processingRows = document.querySelectorAll('tr[data-status="pending"], tr[data-status="processing"], tr[data-status="ready"]');
            
            if (processingRows.length === 0) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                return;
            }

            const idsToPoll = Array.from(processingRows).map(row => row.dataset.id);

            try {
                const response = await fetch('/download/poll-status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_csrf"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({ ids: idsToPoll })
                });

                const updatedHistories = await response.json();

                updatedHistories.forEach(history => {
                    updateRowDisplay(history);
                });
            } catch (error) {
                console.error("Polling error:", error);
            }

        }, 3000); // Poll mỗi 3 giây
    }

    function addNewHistoryRow(history) {
        // Xóa dòng "No download history" nếu có
        const emptyRow = document.getElementById('emptyHistoryRow');
        if (emptyRow) emptyRow.remove();

        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 transition';
        tr.dataset.id = history.id;
        tr.dataset.status = history.status;
        tr.id = `history-row-${history.id}`;
        
        const statusHtml = getStatusBadge(history.status);
        const actionHtml = getActionHtml(history);
        const dateStr = new Date(history.created_at).toLocaleString('vi-VN');

        tr.innerHTML = `
            <td class="px-6 py-4">
                <div class="flex items-center max-w-[200px] sm:max-w-xs md:max-w-sm">
                    <div class="truncate text-gray-800" title="${history.original_link}">
                        ${history.original_link}
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-1">${dateStr}</div>
            </td>
            <td class="px-6 py-4 status-cell">${statusHtml}</td>
            <td class="px-6 py-4 text-gray-600 font-medium">${history.xu_cost} Xu</td>
            <td class="px-6 py-4 whitespace-nowrap action-cell">${actionHtml}</td>
        `;

        historyTableBody.insertBefore(tr, historyTableBody.firstChild);
    }

    function updateRowDisplay(history) {
        const row = document.getElementById(`history-row-${history.id}`);
        if (!row) return;

        // Nếu status thay đổi thì cập nhật UI
        if (row.dataset.status !== history.status) {
            row.dataset.status = history.status;
            
            const statusCell = row.querySelector('.status-cell');
            const actionCell = row.querySelector('.action-cell');
            
            if (statusCell) statusCell.innerHTML = getStatusBadge(history.status);
            if (actionCell) actionCell.innerHTML = getActionHtml(history);

            if (history.status === 'completed' || history.status === 'cached') {
                showToast(`File đã sẵn sàng để tải xuống!`, 'success');
            } else if (history.status === 'failed') {
                showToast(`Tải thất bại. Số Xu của bạn đã được hoàn lại.`, 'error');
                // Gọi API lấy lại số dư xu thực tế nếu cần
            }
        }
    }

    function getStatusBadge(status) {
        if (status === 'completed' || status === 'cached' || status === 'ready') {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>`;
        } else if (status === 'failed') {
            return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Failed
                    </span>`;
        } else {
            return `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        <i class="fas fa-circle-notch fa-spin text-[10px]"></i> ${status.charAt(0).toUpperCase() + status.slice(1)}
                    </span>`;
        }
    }

    function getActionHtml(history) {
        if (history.direct_download_link) {
            return `<a href="${history.direct_download_link}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition text-xs font-medium" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>`;
        }
        return `<span class="text-gray-400 text-xs italic">Processing...</span>`;
    }

    function updateXuBalance(newBalance) {
        const balanceElements = document.querySelectorAll('.xu-balance-display');
        balanceElements.forEach(el => {
            el.textContent = `${newBalance} Xu`;
        });
    }

    function showToast(message, type = 'success') {
        const toastContainer = document.getElementById('toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `p-4 mb-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full flex items-start gap-3 min-w-[300px] border shadow-[0_4px_12px_rgba(0,0,0,0.05)] ${
            type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
        }`;
        
        const icon = type === 'success' ? '<i class="fas fa-check-circle mt-0.5 text-green-600"></i>' : '<i class="fas fa-exclamation-circle mt-0.5 text-red-600"></i>';
        
        toast.innerHTML = `
            ${icon}
            <div class="font-medium text-sm flex-1">${message}</div>
            <button class="text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.remove()">
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

    // Tự động start polling nếu lúc load trang đã có record pending/processing
    startPolling();
});