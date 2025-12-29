@extends('layouts.app')

@section('title', 'Kebutuhan Pokok - PLFIS')
@section('page-title', 'Kebutuhan Pokok')

@section('content')
<style>
    /* --- CSS Utama --- */
    .recurring-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .add-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .add-btn:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
    }

    .summary-label {
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 8px;
    }

    .summary-value {
        font-size: 24px;
        font-weight: bold;
    }

    .expense-list {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
    }

    .expense-item {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 20px;
        align-items: center;
        transition: background 0.3s;
    }

    .expense-item:hover {
        background: rgba(59, 130, 246, 0.05);
    }

    .expense-item:last-child {
        border-bottom: none;
    }

    .expense-info h4 {
        margin-bottom: 5px;
        font-size: 16px;
    }

    .expense-meta {
        font-size: 13px;
        color: var(--text-muted);
        display: flex;
        gap: 15px;
    }

    .expense-amount {
        font-size: 20px;
        font-weight: bold;
        color: var(--text-light);
    }

    .expense-actions {
        display: flex;
        gap: 8px;
    }

    .action-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--dark-bg);
        color: var(--text-light);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .action-icon-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-active { background: rgba(16, 185, 129, 0.2); color: var(--success-color); }
    .badge-inactive { background: rgba(107, 114, 128, 0.2); color: var(--text-muted); }
    .badge-overdue { background: rgba(239, 68, 68, 0.2); color: var(--danger-color); }
    .badge-upcoming { background: rgba(245, 158, 11, 0.2); color: var(--warning-color); }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 64px;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    
    /* --- Form & Modal Styles --- */
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 13px; color: var(--text-light); }
    .form-input, .form-select {
        width: 100%; padding: 9px 12px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 8px; color: var(--text-light); font-size: 13px; transition: all 0.3s;
    }
    .form-input:focus, .form-select:focus { outline: none; border-color: var(--primary-color); background: rgba(59, 130, 246, 0.05); }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; }
    .btn-primary:hover { background: var(--secondary-color); }
    .checkbox-group { display: flex; align-items: center; gap: 10px; }
    .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }

    /* --- IMPROVED MODAL CSS --- */
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

    .modal-content {
        background: var(--dark-card, #2d3748);
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

    .modal-title {
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

    .modal-body::-webkit-scrollbar { width: 6px; }
    .modal-body::-webkit-scrollbar-track { background: var(--dark-bg); border-radius: 3px; }
    .modal-body::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
    .modal-body::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-shrink: 0;
    }

    .modal-footer .btn-primary { flex: 1; max-width: 200px; }
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
    .modal-footer .btn-cancel:hover { background: var(--border-color); }
    /* ------------------------- */
</style>

<div class="recurring-header">
    <div>
        <h2 style="font-size: 20px; margin-bottom: 5px;">Pengeluaran Rutin</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Kelola tagihan dan pengeluaran bulanan Anda</p>
    </div>
    <button class="add-btn" onclick="openModal('createRecurringModal')">
        <i class="fas fa-plus"></i>
        Tambah Pengeluaran
    </button>
</div>

<div class="summary-cards">
    <div class="summary-card">
        <div class="summary-label">
            <i class="fas fa-calendar-alt"></i> Total Bulanan
        </div>
        <div class="summary-value">Rp {{ number_format($monthlyTotal, 0, ',', '.') }}</div>
    </div>

    <div class="summary-card">
        <div class="summary-label">
            <i class="fas fa-clock"></i> Upcoming (7 Hari)
        </div>
        <div class="summary-value" style="color: var(--warning-color);">{{ $upcoming->count() }}</div>
    </div>

    <div class="summary-card">
        <div class="summary-label">
            <i class="fas fa-exclamation-triangle"></i> Terlambat
        </div>
        <div class="summary-value" style="color: var(--danger-color);">{{ $overdue->count() }}</div>
    </div>
</div>

<div class="expense-list">
    @forelse($expenses as $expense)
        <div class="expense-item">
            <div class="expense-info">
                <h4>
                    {{ $expense->name }}
                    @if($expense->is_active)
                        <span class="badge badge-active">Aktif</span>
                    @else
                        <span class="badge badge-inactive">Nonaktif</span>
                    @endif
                    
                    @if($expense->isOverdue())
                        <span class="badge badge-overdue">Terlambat</span>
                    @elseif($expense->next_due_date && $expense->next_due_date->diffInDays(now()) <= 7)
                        <span class="badge badge-upcoming">Segera Jatuh Tempo</span>
                    @endif
                </h4>
                <div class="expense-meta">
                    <span>
                        <i class="fas fa-sync-alt"></i> 
                        {{ ucfirst($expense->period) }}
                    </span>
                    <span>
                        <i class="fas fa-calendar"></i> 
                        Jatuh tempo: {{ $expense->next_due_date ? $expense->next_due_date->format('d M Y') : '-' }}
                    </span>
                    @if($expense->category)
                        <span>
                            <i class="fas fa-tag"></i> 
                            {{ $expense->category->name }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="expense-amount">
                Rp {{ number_format($expense->amount, 0, ',', '.') }}
            </div>

            <div class="expense-actions">
                <form action="{{ route('recurring.mark-paid', $expense->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="action-icon-btn" title="Tandai Sudah Bayar">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
                
                <button type="button" class="action-icon-btn" title="Edit" onclick='editRecurring(@json($expense))'>
                    <i class="fas fa-edit"></i>
                </button>

                <form action="{{ route('recurring.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengeluaran rutin ini?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-icon-btn" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>Belum Ada Pengeluaran Rutin</h3>
            <p>Tambahkan tagihan bulanan seperti listrik, internet, atau langganan</p>
        </div>
    @endforelse
</div>

<div id="createRecurringModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Pengeluaran Rutin</h3>
            <button class="modal-close" onclick="closeModal('createRecurringModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('recurring.store') }}" method="POST" id="createRecurringForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Pengeluaran</label>
                    <input type="text" name="name" class="form-input" placeholder="Contoh: Listrik, Wifi" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-input" placeholder="0" required min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-select" required>
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                        <option value="daily">Harian</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Jatuh Tempo Berikutnya</label>
                    <input type="date" name="next_due_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ingatkan Saya (Hari Sebelum)</label>
                    <input type="number" name="reminder_days_before" class="form-input" value="3" min="0" max="30">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <label>Aktif</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createRecurringModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createRecurringForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<div id="editRecurringModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Pengeluaran Rutin</h3>
            <button class="modal-close" onclick="closeModal('editRecurringModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editRecurringForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Pengeluaran</label>
                    <input type="text" name="name" id="edit-name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="number" name="amount" id="edit-amount" class="form-input" required min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" id="edit-category" class="form-select">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Periode</label>
                    <select name="period" id="edit-period" class="form-select" required>
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                        <option value="daily">Harian</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Jatuh Tempo Berikutnya</label>
                    <input type="date" name="next_due_date" id="edit-date" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ingatkan Saya (Hari Sebelum)</label>
                    <input type="number" name="reminder_days_before" id="edit-reminder" class="form-input" min="0" max="30">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="edit-active" value="1">
                        <label>Aktif</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editRecurringModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editRecurringForm').submit()">Update</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- Standard Modal Functions ---
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Disabled click-outside-to-close to prevent accidental closure
    // window.onclick = function(event) {
    //     if (event.target.classList.contains('modal-overlay')) {
    //         event.target.style.display = 'none';
    //     }
    // }
    // -----------------------------

    function editRecurring(data) {
        document.getElementById('edit-name').value = data.name;
        document.getElementById('edit-amount').value = data.amount;
        document.getElementById('edit-category').value = data.category_id;
        document.getElementById('edit-period').value = data.period;
        document.getElementById('edit-date').value = data.next_due_date ? data.next_due_date.substring(0, 10) : '';
        document.getElementById('edit-reminder').value = data.reminder_days_before;
        document.getElementById('edit-active').checked = data.is_active;

        document.getElementById('editRecurringForm').action = '/recurring/' + data.id;
        
        openModal('editRecurringModal');
    }
</script>
@endsection