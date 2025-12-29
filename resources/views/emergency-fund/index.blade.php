@extends('layouts.app')

@section('title', 'Dana Darurat - PLFIS')
@section('page-title', 'Dana Darurat')

@section('content')
<style>
    /* --- Styles Halaman Utama --- */
    .fund-header {
        background: linear-gradient(135deg, var(--warning-color), #f97316);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
    }

    .fund-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: var(--text-light);
    }

    .progress-section {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
    }

    .progress-bar-container {
        width: 100%;
        height: 30px;
        background: var(--dark-bg);
        border-radius: 15px;
        overflow: hidden;
        margin: 20px 0;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--success-color), var(--warning-color));
        transition: width 0.5s ease;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 15px;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .action-btn {
        padding: 15px 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
    }

    .btn-add { background: var(--success-color); color: white; }
    .btn-withdraw { background: var(--danger-color); color: white; }
    .btn-update { background: var(--primary-color); color: white; }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .recommendations {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 25px;
    }

    .recommendation-item {
        padding: 15px;
        background: var(--dark-bg);
        border-left: 3px solid var(--primary-color);
        border-radius: 8px;
        margin-bottom: 15px;
    }

    /* --- Styles Modal (DIPERBAIKI) --- */
    .modal-overlay {
        display: none; /* Hidden by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px); /* Efek blur background */
    }

    .modal-content {
        background: var(--dark-card, #2d3748);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        animation: slideUp 0.3s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 { margin: 0; font-size: 1.25rem; }

    .modal-body { padding: 20px; }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid var(--border-color);
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .close-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 24px;
        cursor: pointer;
        line-height: 1;
    }

    .close-btn:hover { color: var(--danger-color); }

    .form-group { margin-bottom: 15px; }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-light);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .alert-warning {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid var(--warning-color);
        color: var(--warning-color);
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-secondary {
        background: transparent;
        border: 1px solid var(--border-color);
        color: var(--text-light);
    }
    
    .btn-secondary:hover { background: rgba(255,255,255,0.05); }

    .btn-primary { background: var(--primary-color); color: white; }
    .btn-danger { background: var(--danger-color); color: white; }

    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div class="fund-header">
    <h2 style="font-size: 24px; margin-bottom: 10px;">
        <i class="fas fa-shield-alt"></i> Dana Darurat Anda
    </h2>
    <p style="opacity: 0.9;">Lindungi masa depan finansial Anda dengan dana darurat yang cukup</p>
</div>

@if($fund)
    <div class="fund-stats">
        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-wallet"></i> Saldo Saat Ini
            </div>
            <div class="stat-value">Rp {{ number_format($fund->current_amount, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-bullseye"></i> Target Dana
            </div>
            <div class="stat-value">Rp {{ number_format($fund->target_amount, 0, ',', '.') }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-chart-line"></i> Progress
            </div>
            <div class="stat-value">{{ number_format($fund->progress_percentage, 1) }}%</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                <i class="fas fa-calendar-alt"></i> Bulan Tercapai
            </div>
            <div class="stat-value">{{ $fund->target_months }} Bulan</div>
        </div>
    </div>

    <div class="progress-section">
        <h3 style="margin-bottom: 15px;">
            <i class="fas fa-tasks"></i> Progress Dana Darurat
        </h3>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: {{ min($fund->progress_percentage, 100) }}%">
                {{ number_format($fund->progress_percentage, 1) }}%
            </div>
        </div>
        <p style="color: var(--text-muted); font-size: 14px;">
            Sisa yang dibutuhkan: <strong style="color: var(--warning-color);">Rp {{ number_format($fund->remaining_amount, 0, ',', '.') }}</strong>
        </p>
    </div>

    <div class="action-buttons">
        <button class="action-btn btn-add" onclick="showAddModal()">
            <i class="fas fa-plus-circle"></i>
            Tambah Dana
        </button>

        <button class="action-btn btn-withdraw" onclick="showWithdrawModal()">
            <i class="fas fa-minus-circle"></i>
            Tarik Dana
        </button>

        <button class="action-btn btn-update" onclick="showUpdateModal()">
            <i class="fas fa-edit"></i>
            Update Target
        </button>
    </div>

    @if($progressReport)
    <div class="recommendations">
        <h3 style="margin-bottom: 20px;">
            <i class="fas fa-lightbulb"></i> Rekomendasi
        </h3>

        <div class="recommendation-item">
            <strong><i class="fas fa-calendar-week"></i> Tabungan Bulanan yang Disarankan:</strong>
            <p style="margin-top: 8px; color: var(--text-muted);">
                Rp {{ number_format($progressReport['recommended_monthly'], 0, ',', '.') }} per bulan untuk mencapai target dalam {{ $fund->target_months }} bulan
            </p>
        </div>

        <div class="recommendation-item">
            <strong><i class="fas fa-clock"></i> Estimasi Waktu:</strong>
            <p style="margin-top: 8px; color: var(--text-muted);">
                Dengan kontribusi rutin, dana darurat akan tercapai dalam {{ $progressReport['months_to_target'] }} bulan
            </p>
        </div>

        @if($progressReport['status'] === 'on_track')
            <div class="recommendation-item" style="border-left-color: var(--success-color);">
                <strong><i class="fas fa-check-circle"></i> Status: On Track!</strong>
                <p style="margin-top: 8px; color: var(--success-color);">
                    Anda berada di jalur yang tepat. Pertahankan konsistensi menabung!
                </p>
            </div>
        @else
            <div class="recommendation-item" style="border-left-color: var(--warning-color);">
                <strong><i class="fas fa-exclamation-triangle"></i> Perlu Perhatian</strong>
                <p style="margin-top: 8px; color: var(--warning-color);">
                    Tingkatkan kontribusi bulanan untuk mencapai target lebih cepat
                </p>
            </div>
        @endif
    </div>
    @endif

@else
    <div style="text-align: center; padding: 60px 20px; background: var(--dark-card); border-radius: 12px;">
        <i class="fas fa-shield-alt" style="font-size: 64px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
        <h3 style="margin-bottom: 15px;">Dana Darurat Belum Diatur</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">
            Mulai lindungi masa depan finansial Anda dengan membuat dana darurat
        </p>
        <button class="action-btn btn-update" onclick="showUpdateModal()" style="display: inline-flex;">
            <i class="fas fa-plus-circle"></i>
            Buat Dana Darurat
        </button>
    </div>
@endif

<div id="addModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Tambah Dana Darurat</h3>
            <button class="close-btn" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form action="{{ route('emergency-fund.add-contribution') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Jumlah Dana</label>
                    <input type="number" name="amount" class="form-control" placeholder="Rp 0" required min="0">
                </div>
                <div class="form-group">
                    <label>Catatan (Opsional)</label>
                    <input type="text" name="note" class="form-control" placeholder="Contoh: Tabungan gaji bulan ini">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="withdrawModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-minus-circle"></i> Tarik Dana Darurat</h3>
            <button class="close-btn" onclick="closeModal('withdrawModal')">&times;</button>
        </div>
        <form action="{{ route('emergency-fund.withdraw') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Gunakan dana darurat hanya untuk keadaan mendesak.
                </div>
                <div class="form-group">
                    <label>Jumlah Penarikan</label>
                    <input type="number" name="amount" class="form-control" placeholder="Rp 0" required min="0" max="{{ $fund ? $fund->current_amount : 0 }}">
                    <small style="color: var(--text-muted)">Maksimal: Rp {{ $fund ? number_format($fund->current_amount, 0, ',', '.') : 0 }}</small>
                </div>
                <div class="form-group">
                    <label>Alasan Penarikan</label>
                    <input type="text" name="reason" class="form-control" placeholder="Contoh: Biaya rumah sakit, servis mobil" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('withdrawModal')">Batal</button>
                <button type="submit" class="btn btn-danger">Tarik Dana</button>
            </div>
        </form>
    </div>
</div>

<div id="updateModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-bullseye"></i> Update Target</h3>
            <button class="close-btn" onclick="closeModal('updateModal')">&times;</button>
        </div>
        <form action="{{ route('emergency-fund.update-target') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Pengeluaran Bulanan Rata-rata</label>
                    <input type="number" name="monthly_expense_base" class="form-control" value="{{ $fund ? $fund->monthly_expense_base : 0 }}" required min="0">
                    <small style="color: var(--text-muted)">Basis perhitungan dana darurat</small>
                </div>
                <div class="form-group">
                    <label>Target Bulan</label>
                    <input type="number" name="target_months" class="form-control" value="{{ $fund ? $fund->target_months : 6 }}" required min="1" max="24">
                    <small style="color: var(--text-muted)">Berapa bulan pengeluaran yang ingin dicover? (Standar: 6 bulan)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('updateModal')">Batal</button>
                <button type="submit" class="btn btn-primary">Update Target</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function showAddModal() {
        showModal('addModal');
    }

    function showWithdrawModal() {
        showModal('withdrawModal');
    }

    function showUpdateModal() {
        showModal('updateModal');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection