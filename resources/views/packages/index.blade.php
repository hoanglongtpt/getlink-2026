@extends('layouts.app')

@section('header_title', 'Bảng Giá Nạp Xu')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl uppercase tracking-tight">
            QUÉT MÃ THANH TOÁN XONG XU SẼ TỰ ĐỘNG NẠP SAU VÀI GIÂY
        </h1>
        <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto italic text-balance">
            (Nếu sau 5 phút bạn chưa nhận được thông báo nạp thành công, vui lòng liên hệ Facebook để được hỗ trợ.)
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($packages as $package)
            @php
                $isPopular = !empty($package['is_popular']);
                $totalXu = ($package['xu_main'] ?? 0) + ($package['xu_bonus'] ?? 0);
            @endphp
            <div class="{{ $isPopular ? 'bg-gradient-to-b from-purple-600 to-purple-800 text-white shadow-lg border-0 transform md:-translate-y-4 hover:shadow-purple-200' : 'bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col hover:shadow-xl transition-all duration-300 relative group overflow-hidden' }}">
                @if($isPopular)
                    <div class="absolute top-0 inset-x-0 -translate-y-1/2 flex justify-center">
                        <span class="bg-yellow-400 text-yellow-900 text-[10px] font-black uppercase tracking-widest py-1.5 px-4 rounded-full shadow-lg border-2 border-white animate-pulse">Phổ biến nhất</span>
                    </div>
                @endif
                <div class="p-6 text-center border-b {{ $isPopular ? 'border-purple-500/20' : 'border-gray-50' }} {{ $isPopular ? 'text-white mt-4' : '' }}">
                    <h3 class="text-lg font-bold uppercase mb-2 tracking-wide">{{ $package['name'] }}</h3>
                    @if(!empty($package['description']))
                        <p class="text-sm {{ $isPopular ? 'text-purple-200' : 'text-gray-500' }} mb-3">{{ $package['description'] }}</p>
                    @endif
                    <p class="text-4xl font-black {{ $isPopular ? 'text-white' : 'text-purple-600' }}">{{ number_format($package['amount_vnd']) }}đ</p>
                    <p class="text-xs {{ $isPopular ? 'text-purple-200' : 'text-gray-400' }} mt-2 italic font-bold">Tổng nhận: {{ number_format($totalXu) }} xu</p>
                </div>
                <div class="flex-1 p-6 {{ $isPopular ? 'text-white' : '' }} flex flex-col">
                    <ul class="space-y-3 text-sm mb-8">
                        <li class="flex items-center gap-2"><i class="fas fa-check-circle {{ $isPopular ? 'text-yellow-400' : 'text-green-500' }}"></i> {{ number_format($package['xu_main']) }} xu chính</li>
                        <li class="flex items-center gap-2"><i class="fas fa-plus-circle {{ $isPopular ? 'text-yellow-400' : 'text-orange-500' }} font-bold"></i> <strong>Tặng thêm {{ number_format($package['xu_bonus']) }} xu</strong></li>
                        <li class="flex items-center gap-2"><i class="fas fa-headset {{ $isPopular ? 'text-purple-300' : 'text-gray-300' }}"></i> Hỗ trợ 24/7</li>
                        <li class="flex items-center gap-2"><i class="fas fa-infinity {{ $isPopular ? 'text-purple-300' : 'text-gray-300' }}"></i> Không thời hạn</li>
                    </ul>
                    <button onclick='openPaymentModal({{ $package['amount_vnd'] }}, {{ $totalXu }}, @json($package['name']))' class="w-full mt-auto {{ $isPopular ? 'bg-white text-purple-700 hover:bg-yellow-400 hover:text-yellow-900' : 'bg-purple-50 text-purple-700 group-hover:bg-purple-600 group-hover:text-white' }} font-bold py-3 px-4 rounded-xl transition duration-300 shadow-sm {{ $isPopular ? 'shadow-xl transform group-hover:scale-105' : 'transform group-hover:-translate-y-1' }}">CHỌN GÓI NÀY</button>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 lg:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                Chưa có gói nạp nào được cấu hình. Vui lòng liên hệ quản trị viên.
            </div>
        @endforelse
    </div>

    <!-- Recent History Section -->
    <div class="mt-20">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2"><i class="fas fa-history text-purple-600"></i> Lịch sử nạp xu của bạn</h2>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Mã giao dịch</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Số tiền (VND)</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Xu nhận được</th>
                            <th class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTableBody" class="divide-y divide-gray-50 text-sm">
                        @forelse($recentTransactions as $tx)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-8 py-5 font-mono text-xs text-gray-400 group-hover:text-gray-600">{{$tx->transaction_code}}</td>
                            <td class="px-8 py-5 font-bold text-gray-800">{{ number_format($tx->amount_vnd) }}đ</td>
                            <td class="px-8 py-5">
                                <span class="font-black text-green-600">+{{ number_format($tx->xu_amount) }} Xu</span>
                            </td>
                            <td class="px-8 py-5 text-gray-400">{{$tx->created_at->format('H:i d/m/Y')}}</td>
                        </tr>
                        @empty
                        <tr id="emptyTxRow"><td colspan="4" class="px-8 py-16 text-center text-gray-400 italic">Bạn chưa thực hiện giao dịch nạp nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Thanh toán -->
<div id="paymentModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 hidden opacity-0 transition-opacity duration-300 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto overflow-x-hidden transform scale-90 transition-all duration-300" id="paymentModalContent">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-700 to-indigo-800 px-8 py-8 text-white flex justify-between items-center relative">
            <div class="flex flex-col">
                <h3 class="font-black text-2xl" id="modalTitle">Thanh Toán Gói</h3>
                <p class="text-purple-200 text-xs opacity-80 mt-1 uppercase tracking-widest font-bold">Hệ thống tự động 24/7</p>
            </div>
            <button onclick="closePaymentModal()" class="w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div class="px-6 py-6 text-center">
            <!-- Amount -->
            <div class="bg-purple-50 px-6 py-4 rounded-3xl border border-purple-100 inline-block mb-6 shadow-inner transform -rotate-1">
                <p class="text-gray-400 text-[10px] uppercase font-bold tracking-widest mb-1">Số tiền thanh toán</p>
                <p class="text-4xl font-black text-purple-700" id="modalAmount">0đ</p>
            </div>

            <!-- QR Section -->
            <div class="flex justify-center mb-8 relative group">
                <div class="absolute -inset-2 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-[1.75rem] blur opacity-10 group-hover:opacity-20 transition"></div>
                <div class="relative bg-white p-4 rounded-[1.75rem] border border-gray-100 shadow-xl overflow-hidden">
                    <img id="qrImage" loading="eager" src="{{ $qrCodeUrl ?? 'https://placehold.co/320x320/f3e8ff/6b21a8?text=Loading+QR...' }}" class="w-56 h-56 md:w-64 md:h-64 object-cover rounded-2xl shadow-inner" alt="QR Code">
                    <div class="absolute bottom-0 inset-x-0 h-1.5 bg-gradient-to-r from-purple-600 via-indigo-500 to-purple-600 animate-pulse"></div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="space-y-3 mb-8 text-left">
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-purple-200 transition-colors">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2">Ngân hàng</p>
                    <p class="font-black text-gray-800 text-lg" id="modalBankName">MB-BANK</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-purple-200 transition-colors">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2">Chủ tài khoản</p>
                    <p class="font-black text-gray-800 text-lg" id="modalAccountHolder">PHAM XUAN QUY</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 hover:border-purple-200 transition-colors cursor-pointer" onclick="copyToClipboard(document.getElementById('modalAccountNumber').innerText)">
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-0.5">Số tài khoản</p>
                        <i class="far fa-copy text-purple-400 group-hover:text-purple-600"></i>
                    </div>
                    <p class="font-black text-gray-800 text-lg" id="modalAccountNumber">9999928071998</p>
                </div>
                <div class="p-4 bg-red-50 rounded-2xl border border-red-100 hover:border-red-300 transition-colors cursor-pointer" onclick="copyToClipboard(document.getElementById('modalTransferContent').innerText)">
                    <div class="flex justify-between items-center">
                        <p class="text-[10px] text-red-400 font-bold uppercase tracking-widest mb-0.5">Nội dung chuyển khoản</p>
                        <i class="far fa-copy text-red-400 group-hover:text-red-600"></i>
                    </div>
                    <p class="font-black text-red-600 text-2xl tracking-tighter" id="modalTransferContent">{{ $web2mDetails['transfer_content_prefix'] ?? 'id' }}{{ Auth::user()->id }}</p>
                </div>
            </div>

            <!-- Wait Status -->
            <div class="flex items-center justify-center gap-4 py-5 bg-purple-50/50 rounded-3xl border border-dashed border-purple-200">
                <div class="flex space-x-1.5">
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce [animation-delay:-.3s]"></div>
                    <div class="w-2 h-2 bg-purple-600 rounded-full animate-bounce [animation-delay:-.5s]"></div>
                </div>
                <p class="text-sm font-black text-purple-700 tracking-wide">ĐANG CHỜ THANH TOÁN...</p>
            </div>
        </div>
    </div>
</div>

<script>
    @php
        $web2mDetails = $web2mDetails ?? [
            'bank_name' => 'MB-BANK',
            'bank_code' => 'MBB',
            'account_number' => '9999928071998',
            'account_holder' => 'PHAM XUAN QUY',
            'transfer_content_prefix' => 'id',
        ];
    @endphp
    const web2mDetails = @json($web2mDetails);
    const web2mUserId = {{ Auth::user()->id }};
    let lastTotalXu = {{ Auth::user()->xu_balance + Auth::user()->bonus_xu }};
    let pollingInterval = null;

    async function openPaymentModal(amount, xu, packageName) {
        const modal = document.getElementById('paymentModal');
        const modalContent = document.getElementById('paymentModalContent');
        const qrImage = document.getElementById('qrImage');
        
        document.getElementById('modalTitle').innerText = packageName;
        document.getElementById('modalAmount').innerText = new Intl.NumberFormat('vi-VN').format(amount) + 'đ';

        document.getElementById('modalBankName').innerText = web2mDetails.bank_name;
        document.getElementById('modalAccountHolder').innerText = web2mDetails.account_holder;
        document.getElementById('modalAccountNumber').innerText = web2mDetails.account_number;
        document.getElementById('modalTransferContent').innerText = `${web2mDetails.transfer_content_prefix}${web2mUserId}`;
        qrImage.src = 'https://placehold.co/320x320/f3e8ff/6b21a8?text=Loading+QR...';
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-90');
        }, 10);

        const response = await fetch("{{ route('payment.initiate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ amount_vnd: amount }),
        });

        if (!response.ok) {
            const msg = await response.text();
            alert('Không thể tạo đơn thanh toán. Vui lòng thử lại.');
            console.error('Payment initiate failed:', response.status, msg);
            closePaymentModal();
            return;
        }

        const data = await response.json();
        if (!data.success) {
            alert('Không thể tạo đơn thanh toán. Vui lòng thử lại.');
            closePaymentModal();
            return;
        }

        qrImage.src = data.qr_url;
        qrImage.onerror = () => {
            qrImage.src = 'https://placehold.co/320x320/f3e8ff/6b21a8?text=QR+Load+Failed';
        };
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-90');
        }, 10);
        
        startTxPolling();
    }

    function closePaymentModal() {
        const modal = document.getElementById('paymentModal');
        const modalContent = document.getElementById('paymentModalContent');
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-90');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function startTxPolling() {
        if (pollingInterval) return;
        pollingInterval = setInterval(async () => {
            try {
                const response = await fetch("{{ route('payment.status') }}", { 
                    method: 'POST', 
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
                });
                const data = await response.json();
                const currentTotal = data.xu_balance + data.bonus_xu;
                
                if (currentTotal > lastTotalXu) {
                    lastTotalXu = currentTotal;
                    // Update global UI
                    document.querySelectorAll(".xu-balance-display").forEach(el => {
                        el.innerHTML = `${data.xu_balance} Xu <span class="text-yellow-400 font-bold ml-1">+${data.bonus_xu}</span>`;
                    });
                    
                    if (data.latest_transaction) {
                        addNewTxRow(data.latest_transaction);
                        showSuccessPopup(data.latest_transaction);
                    }
                    closePaymentModal();
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } catch (e) { console.error(e); }
        }, 3000);
    }

    function addNewTxRow(tx) {
        const table = document.getElementById("transactionTableBody");
        const empty = document.getElementById("emptyTxRow"); 
        if (empty) empty.remove();
        
        const tr = document.createElement("tr");
        tr.className = "bg-green-50 animate-pulse transition duration-1000";
        tr.innerHTML = `
            <td class="px-8 py-5 font-mono text-xs text-gray-600">${tx.transaction_code}</td>
            <td class="px-8 py-5 font-bold text-gray-800">${new Intl.NumberFormat('vi-VN').format(tx.amount_vnd)}đ</td>
            <td class="px-8 py-5 font-black text-green-600">+${tx.xu_amount} Xu</td>
            <td class="px-8 py-5 text-gray-500 font-bold">Vừa xong</td>
        `;
        table.insertBefore(tr, table.firstChild);
        setTimeout(() => tr.classList.remove('bg-green-50', 'animate-pulse'), 5000);
    }

    function showSuccessPopup(tx) {
        const div = document.createElement("div");
        div.className = "fixed bottom-8 right-8 z-[200] bg-white rounded-[2rem] shadow-[0_30px_70px_rgba(0,0,0,0.3)] border-2 border-green-500 p-8 max-w-sm transform translate-y-20 opacity-0 transition-all duration-700 flex flex-col items-center text-center";
        div.innerHTML = `
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mb-6 shadow-inner animate-bounce">
                <i class="fas fa-check-circle"></i>
            </div>
            <h4 class="font-black text-gray-800 text-2xl mb-2">NẠP THÀNH CÔNG!</h4>
            <p class="text-gray-500 font-medium leading-relaxed">Bạn vừa nạp thêm <strong class="text-purple-600 text-lg">${tx.xu_amount} Xu</strong>. Chúc bạn có trải nghiệm tuyệt vời!</p>
            <button onclick="this.parentElement.remove()" class="mt-8 w-full bg-gray-900 text-white py-4 rounded-2xl font-black text-sm hover:bg-black transition shadow-lg uppercase tracking-widest">Tuyệt vời</button>
        `;
        document.body.appendChild(div);
        setTimeout(() => { div.classList.remove('translate-y-20', 'opacity-0'); }, 100);
        setTimeout(() => { if(div) div.remove(); }, 15000);
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.createElement('div');
            toast.className = "fixed top-10 left-1/2 -translate-x-1/2 bg-gray-800 text-white px-6 py-2.5 rounded-full text-xs font-bold z-[300] shadow-2xl animate-fade-in";
            toast.innerText = "ĐÃ COPY: " + text;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2500);
        });
    }
</script>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translate(-50%, -20px); } to { opacity: 1; transform: translate(-50%, 0); } }
    .animate-fade-in { animation: fade-in 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    .text-balance { text-wrap: balance; }
</style>
@endsection
