@extends('layouts.app')

@section('title', 'Kategori - PLFIS')
@section('page-title', 'Pengaturan Kategori')

@section('content')
<style>
    /* CSS Existing */
    .header-section {
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
        text-decoration: none;
        transition: all 0.3s;
    }

    .add-btn:hover {
        background: var(--secondary-color);
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .category-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s;
    }

    .category-card:hover {
        transform: translateY(-2px);
    }

    .category-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .category-icon {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: rgba(59, 130, 246, 0.1);
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .category-details h4 {
        margin-bottom: 4px;
        font-size: 16px;
    }

    .category-details p {
        color: var(--text-muted);
        font-size: 13px;
        margin: 0;
    }

    .category-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: var(--dark-bg);
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .action-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 8px;
    }

    .badge-income {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success-color);
    }

    .badge-expense {
        background: rgba(239, 68, 68, 0.2);
        color: var(--danger-color);
    }

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
        padding: 0;
        line-height: 1;
    }

    .modal-close:hover {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger-color, red);
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
    /* ------------------------------------- */

    /* Form Styles */
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 13px; color: var(--text-light); }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 9px 12px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 8px; color: var(--text-light); font-size: 13px; transition: all 0.3s;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }
    .form-textarea { resize: vertical; min-height: 80px; }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.3s; }
    .btn-primary:hover { background: var(--secondary-color); }
    
    .color-picker {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .color-option {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid transparent;
        transition: transform 0.2s;
    }

    .color-option:hover {
        transform: scale(1.1);
    }

    .color-option.selected {
        border-color: white;
        box-shadow: 0 0 0 2px var(--primary-color);
    }
</style>

<div class="header-section">
    <div>
        <h2 style="font-size: 20px; margin-bottom: 5px;">Daftar Kategori</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Kelola kategori pemasukan dan pengeluaran Anda</p>
    </div>
    <button class="add-btn" onclick="openModal('createCategoryModal')">
        <i class="fas fa-plus"></i> Tambah Kategori
    </button>
</div>

@if(session('success'))
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success-color); border-radius: 8px; padding: 15px; margin-bottom: 25px; color: var(--success-color);">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="category-grid">
    @foreach($categories as $category)
    <div class="category-card">
        <div class="category-info">
            <div class="category-icon" style="color: {{ $category->color }}; background: {{ $category->color }}20;">
                <i class="{{ $category->icon ?? 'fas fa-tag' }}"></i>
            </div>
            <div class="category-details">
                <h4>
                    {{ $category->name }}
                    <span class="badge {{ $category->type == 'income' ? 'badge-income' : 'badge-expense' }}">
                        {{ ucfirst($category->type == 'income' ? 'Pemasukan' : 'Pengeluaran') }}
                    </span>
                </h4>
                <p>{{ $category->description ?? 'Tidak ada deskripsi' }}</p>
            </div>
        </div>
        <div class="category-actions">
            @if($category->user_id === auth()->id())
                <button type="button" class="action-btn" title="Edit" onclick='editCategory(@json($category))'>
                    <i class="fas fa-edit"></i>
                </button>
                <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini? Transaksi terkait mungkin terpengaruh.');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @else
                <span title="Kategori Default" style="color: var(--text-muted); font-size: 12px; display: flex; align-items: center; padding: 0 10px;">
                    <i class="fas fa-lock"></i>
                </span>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div id="createCategoryModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3 class="modal-title">Buat Kategori Baru</h3>
            <button class="modal-close" onclick="closeModal('createCategoryModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('categories.store') }}" method="POST" id="createCategoryForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" class="form-input" placeholder="Contoh: Belanja, Gaji" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select" required>
                        <option value="expense">Pengeluaran (Expense)</option>
                        <option value="income">Pemasukan (Income)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-textarea" placeholder="Deskripsi singkat..." rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Ikon (Font Awesome)</label>
                    <input type="text" name="icon" class="form-input" placeholder="fas fa-tag" value="fas fa-tag">
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <div class="color-picker" id="create-color-picker">
                        <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899"></div>
                        <div class="color-option" style="background: #6b7280;" data-color="#6b7280"></div>
                        <div class="color-option" style="background: #06b6d4;" data-color="#06b6d4"></div>
                    </div>
                    <input type="hidden" name="color" id="create-color-input" value="#3b82f6">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createCategoryModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createCategoryForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<div id="editCategoryModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Kategori</h3>
            <button class="modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Nama Kategori</label>
                    <input type="text" name="name" id="edit-name" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipe</label>
                    <select name="type" id="edit-type" class="form-select" required>
                        <option value="expense">Pengeluaran (Expense)</option>
                        <option value="income">Pemasukan (Income)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-description" class="form-textarea" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Ikon (Font Awesome)</label>
                    <input type="text" name="icon" id="edit-icon" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <div class="color-picker" id="edit-color-picker">
                        <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899"></div>
                        <div class="color-option" style="background: #6b7280;" data-color="#6b7280"></div>
                        <div class="color-option" style="background: #06b6d4;" data-color="#06b6d4"></div>
                    </div>
                    <input type="hidden" name="color" id="edit-color-input" value="">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editCategoryModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editCategoryForm').submit()">Update</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // --- PERBAIKAN: FUNGSI MODAL ---
    // --- PERBAIKAN: FUNGSI MODAL ---
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10); // If using active class for animation
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('active'); // Remove active if present
        modal.style.display = 'none'; // Immediate hide for this simple view usually
    }

    // Disabled click-outside-to-close to prevent accidental closure
    // window.onclick = function(event) {
    //     if (event.target.classList.contains('modal-overlay')) {
    //         event.target.classList.remove('active');
    //         event.target.style.display = 'none';
    //     }
    // }
    // -----------------------------

    // Color Picker Setup
    function setupColorPicker(pickerId, inputId) {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        const options = picker.querySelectorAll('.color-option');

        // Set initial state
        if (input.value) {
            const initial = Array.from(options).find(o => o.dataset.color === input.value);
            if (initial) initial.classList.add('selected');
        }

        options.forEach(opt => {
            opt.addEventListener('click', function() {
                options.forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                input.value = this.dataset.color;
            });
        });

        return {
            setColor: (color) => {
                options.forEach(o => o.classList.remove('selected'));
                const match = Array.from(options).find(o => o.dataset.color === color);
                if (match) {
                    match.classList.add('selected');
                    input.value = color;
                } else if (input.value) {
                     // try to reuse current value if new color not in palette
                     input.value = color;
                }
            }
        };
    }

    const createColorPicker = setupColorPicker('create-color-picker', 'create-color-input');
    const editColorPicker = setupColorPicker('edit-color-picker', 'edit-color-input');

    function editCategory(data) {
        document.getElementById('edit-name').value = data.name;
        document.getElementById('edit-type').value = data.type;
        document.getElementById('edit-description').value = data.description || '';
        document.getElementById('edit-icon').value = data.icon || 'fas fa-tag';
        
        editColorPicker.setColor(data.color);

        // Pastikan route update sesuai dengan definisi route Laravel Anda
        document.getElementById('editCategoryForm').action = '/categories/' + data.id;
        openModal('editCategoryModal');
    }
</script>
@endsection