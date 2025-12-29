@extends('layouts.app')

@section('title', 'Pendapatan Digital - PLFIS')
@section('page-title', 'Pendapatan Digital & Wallet')

@section('content')
<style>
    .accounts-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;
    }
    .account-card {
        background: var(--dark-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; transition: transform 0.2s; position: relative;
    }
    .account-card:hover { border-color: var(--primary-color); }
    .account-header { display: flex; gap: 15px; align-items: start; margin-bottom: 20px; }
    .account-icon { width: 48px; height: 48px; background: var(--dark-bg); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: var(--primary-color); }
    .account-info h3 { margin: 0 0 5px 0; font-size: 16px; }
    .account-link { font-size: 12px; color: var(--primary-color); text-decoration: none; }
    .account-balance { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
    .account-footer { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .btn-secondary { background: var(--dark-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 8px; border-radius: 6px; cursor: pointer; }
    .btn-secondary:hover { border-color: var(--text-muted); }
    .btn-icon { background: none; border: none; cursor: pointer; color: var(--text-muted); }
    .btn-icon.danger:hover { color: var(--danger-color); }
    .account-actions-top { margin-left: auto; }

    .section-title {
        font-size: 18px; font-weight: 600; margin-bottom: 15px; margin-top: 30px; display: flex; align-items: center; gap: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);
    }

    /* IMPROVED MODAL CSS */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: var(--dark-card);
        padding: 0;
        border-radius: 16px;
        width: 90%;
        max-width: 420px;
        max-height: 85vh;
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        animation: modalSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    @keyframes modalSlideIn {
        from { transform: scale(0.95) translateY(-20px); opacity: 0; }
        to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        flex-shrink: 0;
    }

    .modal-header h3 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 24px;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
        flex: 1;
        min-height: 0;
    }

    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: var(--dark-bg);
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .modal-footer .btn-primary {
        flex: 1;
        max-width: 200px;
    }

    .modal-footer .btn-cancel {
        background: var(--dark-bg);
        color: var(--text-light);
        border: 1px solid var(--border-color);
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
    }

    .modal-footer .btn-cancel:hover {
        background: var(--border-color);
    }

    /* Form Styles */
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 13px; color: var(--text-light); }
    .form-input, .form-select {
        width: 100%; padding: 9px 12px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 8px; color: var(--text-light); font-size: 13px; transition: all 0.3s;
    }
    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; }
    .btn-primary:hover { background: var(--secondary-color); }
</style>

<div class="digital-dashboard">
    <!-- Header Stats -->
    <div class="dashboard-grid">
        <div class="stat-card" style="border: 2px solid var(--primary-color);">
            <div class="stat-header">
                <span class="stat-title">Total Aset Digital Estimates</span>
                <div class="stat-icon primary"><i class="fas fa-globe"></i></div>
            </div>
            <div class="stat-value">Rp {{ number_format($totalBalance, 0, ',', '.') }}</div>
            <div class="stat-change">
                <span>(IDR + USD Converted)</span>
            </div>
        </div>
        
        <div class="stat-card" onclick="document.getElementById('addAccountModal').classList.add('active')" style="cursor: pointer; border: 2px dashed var(--border-color); display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <div class="stat-icon success" style="width: 50px; height: 50px; margin-bottom: 10px;">
                <i class="fas fa-plus"></i>
            </div>
            <span style="font-weight: 600;">Tambah Sumber/Wallet</span>
        </div>
    </div>

    <!-- Passive Income Section -->
    <h3 class="section-title"><i class="fas fa-hand-holding-usd" style="color: var(--success-color);"></i> Passive Income Sources</h3>
    <div class="accounts-grid">
        @forelse($incomeSources as $account)
            @include('digital_accounts.card', ['account' => $account])
        @empty
            <p class="text-muted">Belum ada sumber income.</p>
        @endforelse
    </div>

    <!-- Digital Wallets Section -->
    <h3 class="section-title"><i class="fas fa-wallet" style="color: var(--info-color);"></i> Digital Wallets</h3>
    <div class="accounts-grid">
        @forelse($wallets as $account)
            @include('digital_accounts.card', ['account' => $account])
        @empty
             <p class="text-muted">Belum ada wallet.</p>
        @endforelse
    </div>
</div>

<!-- Add Modal -->
<div id="addAccountModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah Akun Digital</h3>
            <button class="modal-close" onclick="closeModal('addAccountModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('digital-accounts.store') }}" method="POST" id="addAccountForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tipe Akun</label>
                    <select name="type" class="form-input">
                        <option value="income_source">Passive Income (ShrinkMe, Youtube, dll)</option>
                        <option value="wallet">Digital Wallet (Dana, OVO, dll)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Platform</label>
                    <input type="text" name="platform_name" class="form-input" placeholder="Contoh: PayPal, ShrinkMe" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Saldo Saat Ini</label>
                    <input type="number" name="current_balance" class="form-input" placeholder="0" required>
                </div>
                 <div class="form-group">
                    <label class="form-label">Website URL (Opsional)</label>
                    <input type="url" name="website_url" class="form-input" placeholder="https://...">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('addAccountModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('addAccountForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Saldo Real-time</h3>
            <button class="modal-close" onclick="closeModal('updateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="updateForm" method="POST">
                @csrf @method('PUT')
                <p class="text-muted">Update saldo manual sesuai website.</p>
                <div class="form-group">
                    <label class="form-label">Saldo Terbaru</label>
                    <input type="number" name="current_balance" id="updateBalanceInput" class="form-input" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('updateModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('updateForm').submit()">Update</button>
        </div>
    </div>
</div>

<!-- Withdraw Modal -->
<div id="withdrawModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cairkan Dana (Withdraw)</h3>
            <button class="modal-close" onclick="closeModal('withdrawModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="withdrawForm" method="POST">
                @csrf
                <div style="background: rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle" style="color: var(--success-color);"></i>
                    Dana akan dicatat sebagai <strong>Income</strong> di kas utama.
                </div>
                <div class="form-group">
                    <label class="form-label">Jumlah Penarikan</label>
                    <input type="number" name="amount" id="withdrawAmount" class="form-input" required>
                    <small class="text-muted">Maksimal: <span id="maxWithdraw"></span></small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('withdrawModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('withdrawForm').submit()">Konfirmasi</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function showModal(id) {
            const el = document.getElementById(id);
            if(el) {
                el.style.display = 'flex';
                setTimeout(() => el.classList.add('active'), 10);
            }
        }

        window.closeModal = function(id) {
            const el = document.getElementById(id);
            if(el) {
                el.classList.remove('active');
                setTimeout(() => {
                     if(!el.classList.contains('active')) el.style.display = 'none';
                }, 300);
            }
        }

        window.openUpdateModal = function(account) {
            document.getElementById('updateBalanceInput').value = parseInt(account.current_balance);
            document.getElementById('updateForm').action = "/digital-accounts/" + account.id;
            showModal('updateModal');
        }

        window.openWithdrawModal = function(account) {
            document.getElementById('withdrawAmount').max = account.current_balance;
            document.getElementById('maxWithdraw').innerText = account.current_balance;
            document.getElementById('withdrawForm').action = "/digital-accounts/" + account.id + "/withdraw";
            showModal('withdrawModal');
        }
    });
</script>
@endsection
