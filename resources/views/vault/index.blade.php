@extends('layouts.app')

@section('title', 'Password Vault - PLFIS')
@section('page-title', 'Password Vault')

@section('content')
<style>
    .vault-warning {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid var(--warning-color);
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vault-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .vault-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-box {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
    }

    .stat-box-value {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .stat-box-label {
        font-size: 12px;
        color: var(--text-muted);
    }

    .vault-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 20px;
    }

    .vault-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
    }

    .vault-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .vault-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .vault-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .icon-email { background: rgba(239, 68, 68, 0.2); color: var(--danger-color); }
    .icon-game { background: rgba(139, 92, 246, 0.2); color: var(--secondary-color); }
    .icon-social { background: rgba(59, 130, 246, 0.2); color: var(--primary-color); }
    .icon-website { background: rgba(16, 185, 129, 0.2); color: var(--success-color); }
    .icon-api { background: rgba(245, 158, 11, 0.2); color: var(--warning-color); }
    .icon-other { background: rgba(107, 114, 128, 0.2); color: var(--text-muted); }

    .vault-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .vault-type {
        font-size: 12px;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .vault-field {
        margin-bottom: 12px;
    }

    .vault-field-label {
        font-size: 11px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .vault-field-value {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: var(--dark-bg);
        border-radius: 6px;
        font-family: monospace;
    }

    .password-hidden {
        letter-spacing: 3px;
    }

    .copy-btn {
        background: none;
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        padding: 4px;
        transition: color 0.3s;
        height: 24px;
        width: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .copy-btn:hover {
        color: var(--secondary-color);
    }

    .vault-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid var(--border-color);
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

    .vault-btn {
        flex: 1;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: var(--dark-bg);
        color: var(--text-light);
        cursor: pointer;
        font-size: 13px;
        transition: all 0.3s;
    }

    .vault-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    /* Modal Form Styles */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 10px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 6px; color: var(--text-light);
    }
    .form-textarea { resize: vertical; min-height: 80px; }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%; }
    .input-group { display: flex; gap: 10px; }
    .input-group .form-input { flex: 1; }
    .btn-icon {
        padding: 0 16px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        color: var(--text-light);
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .btn-icon:hover { background: var(--border-color); }
    
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

    .modal-overlay.active { display: flex; }

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
        line-height: 1;
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
</style>

<div class="vault-warning">
    <i class="fas fa-shield-alt" style="font-size: 24px; color: var(--warning-color);"></i>
    <div>
        <strong>Keamanan Terjamin</strong>
        <p style="font-size: 13px; margin-top: 4px; color: var(--text-muted);">
            Semua password dienkripsi dengan AES-256. Data Anda aman.
        </p>
    </div>
</div>

<div class="vault-header">
    <div>
        <h2 style="font-size: 20px; margin-bottom: 5px;">Password Manager</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Simpan dan kelola semua akun Anda dengan aman</p>
    </div>
    <button class="add-btn" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;" id="openCreateModalBtn">
        <i class="fas fa-plus"></i>
        Tambah Akun
    </button>
</div>

<!-- Statistics -->
@if(isset($statistics))
<div class="vault-stats">
    <div class="stat-box">
        <div class="stat-box-value">{{ $statistics['total_accounts'] ?? 0 }}</div>
        <div class="stat-box-label">Total Akun</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value" style="color: var(--danger-color);">{{ $statistics['old_passwords'] ?? 0 }}</div>
        <div class="stat-box-label">Password Lama</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value">{{ $statistics['by_type']['email'] ?? 0 }}</div>
        <div class="stat-box-label">Email</div>
    </div>
    <div class="stat-box">
        <div class="stat-box-value">{{ $statistics['by_type']['social_media'] ?? 0 }}</div>
        <div class="stat-box-label">Social Media</div>
    </div>
</div>
@endif

<!-- Vault Cards -->
<div class="vault-grid">
    @forelse($accounts as $account)
        <div class="vault-card">
            <div class="vault-card-header">
                <div>
                    <div class="vault-title">{{ $account->service_name }}</div>
                    <div class="vault-type">{{ str_replace('_', ' ', $account->account_type) }}</div>
                </div>
                <div class="vault-icon icon-{{ $account->account_type }}">
                    <i class="fas fa-{{ 
                        $account->account_type === 'email' ? 'envelope' : 
                        ($account->account_type === 'game' ? 'gamepad' : 
                        ($account->account_type === 'social_media' ? 'share-alt' : 
                        ($account->account_type === 'website' ? 'globe' : 
                        ($account->account_type === 'api' ? 'code' : 'key')))) 
                    }}"></i>
                </div>
            </div>

            @if($account->username)
            <div class="vault-field">
                <div class="vault-field-label">Username</div>
                <div class="vault-field-value">
                    <span style="flex: 1;">{{ $account->username }}</span>
                    <button class="copy-btn" data-copy="{{ $account->username }}">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            @endif

            @if($account->email)
            <div class="vault-field">
                <div class="vault-field-label">Email</div>
                <div class="vault-field-value">
                    <span style="flex: 1;">{{ $account->email }}</span>
                    <button class="copy-btn" data-copy="{{ $account->email }}">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            @endif

            <div class="vault-field">
                <div class="vault-field-label">Password</div>
                <div class="vault-field-value">
                    <span style="flex: 1;" id="pwd-{{ $account->id }}">{{ $account->password }}</span>
                    <button class="copy-btn toggle-password" data-id="{{ $account->id }}" data-password="{{ $account->password }}">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                    <button class="copy-btn copy-password" data-copy="{{ $account->password }}">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            <div class="vault-actions">
                <button class="vault-btn edit-vault-btn" data-account="{{ $account->toJson() }}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form action="{{ route('vault.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini dari vault?');" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="vault-btn" style="width: 100%;">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: var(--dark-card); border-radius: 12px;">
            <i class="fas fa-lock" style="font-size: 64px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">Vault Masih Kosong</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">
                Mulai simpan password Anda dengan aman
            </p>
            <button class="add-btn" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; margin: 0 auto;" id="openCreateModalEmptyBtn">
                <i class="fas fa-plus"></i>
                Tambah Akun Pertama
            </button>
        </div>
    @endforelse
</div>

<!-- Create Modal -->
<div id="createVaultModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Akun</h3>
            <button class="modal-close" id="closeCreateModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('vault.store') }}" method="POST" id="createVaultForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Tipe Akun</label>
                    <select name="account_type" class="form-select" required>
                        <option value="email">Email</option>
                        <option value="social_media">Social Media</option>
                        <option value="game">Game</option>
                        <option value="website">Website</option>
                        <option value="api">API Key</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="service_name" class="form-input" placeholder="Contoh: Gmail" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email (Opsional)</label>
                    <input type="email" name="email" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="create-password" class="form-input" required>
                        <button type="button" class="btn-icon toggle-visibility" data-target="create-password">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn-icon generate-password" data-target="create-password">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">URL (Opsional)</label>
                    <input type="url" name="url" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-textarea"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createVaultModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createVaultForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editVaultModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Akun</h3>
            <button class="modal-close" id="closeEditModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editVaultForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Tipe Akun</label>
                    <select name="account_type" id="edit-type" class="form-select" required>
                        <option value="email">Email</option>
                        <option value="social_media">Social Media</option>
                        <option value="game">Game</option>
                        <option value="website">Website</option>
                        <option value="api">API Key</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="service_name" id="edit-service" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="edit-username" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email (Opsional)</label>
                    <input type="email" name="email" id="edit-email" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Password (Biarkan kosong jika tidak ingin mengubah)</label>
                    <div class="input-group">
                        <input type="password" name="password" id="edit-password" class="form-input">
                        <button type="button" class="btn-icon toggle-visibility" data-target="edit-password">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn-icon generate-password" data-target="edit-password">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">URL (Opsional)</label>
                    <input type="url" name="url" id="edit-url" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" id="edit-notes" class="form-textarea"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editVaultModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editVaultForm').submit()">Update</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generic modal functions
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }

    // Event listeners for modal buttons
    document.getElementById('openCreateModalBtn').addEventListener('click', function() {
        openModal('createVaultModal');
    });

    document.getElementById('openCreateModalEmptyBtn')?.addEventListener('click', function() {
        openModal('createVaultModal');
    });

    document.getElementById('closeCreateModalBtn').addEventListener('click', function() {
        closeModal('createVaultModal');
    });

    document.getElementById('closeEditModalBtn').addEventListener('click', function() {
        closeModal('editVaultModal');
    });

    // Disabled click-outside-to-close to prevent accidental closure
    // document.getElementById('createVaultModal').addEventListener('click', function(e) {
    //     if (e.target === this) {
    //         closeModal('createVaultModal');
    //     }
    // });

    // document.getElementById('editVaultModal').addEventListener('click', function(e) {
    //     if (e.target === this) {
    //         closeModal('editVaultModal');
    //     }
    // });

    // Copy to clipboard
    document.querySelectorAll('.copy-btn[data-copy]').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(() => {
                // Optional: Add toast notification here
                const originalIcon = this.querySelector('i').className;
                this.querySelector('i').className = 'fas fa-check';
                setTimeout(() => {
                    this.querySelector('i').className = originalIcon;
                }, 1000);
            });
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const password = this.getAttribute('data-password');
            const element = document.getElementById('pwd-' + id);
            const icon = this.querySelector('i');
            
            if (element.classList.contains('password-hidden')) {
                // Show password
                element.textContent = password;
                element.classList.remove('password-hidden');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                // Hide password
                element.textContent = '••••••••';
                element.classList.add('password-hidden');
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Toggle input visibility
    document.querySelectorAll('.toggle-visibility').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Generate password
    document.querySelectorAll('.generate-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            
            fetch('{{ route("vault.generate-password") }}?length=16', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const input = document.getElementById(targetId);
                input.value = data.password;
                input.type = 'text';
                
                // Update toggle button icon
                const toggleBtn = document.querySelector(`.toggle-visibility[data-target="${targetId}"]`);
                const icon = toggleBtn.querySelector('i');
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            });
        });
    });

    // Edit vault
    document.querySelectorAll('.edit-vault-btn').forEach(button => {
        button.addEventListener('click', function() {
            const accountData = JSON.parse(this.getAttribute('data-account'));
            
            document.getElementById('edit-type').value = accountData.account_type;
            document.getElementById('edit-service').value = accountData.service_name;
            document.getElementById('edit-username').value = accountData.username;
            document.getElementById('edit-email').value = accountData.email || '';
            document.getElementById('edit-url').value = accountData.url || '';
            document.getElementById('edit-notes').value = accountData.notes || '';
            document.getElementById('edit-password').value = ''; // Don't populate password for security, only if changing

            document.getElementById('editVaultForm').action = '/vault/' + accountData.id;
            openModal('editVaultModal');
        });
    });

    // Make functions global for inline handlers that still exist
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text);
    };

    window.togglePassword = function(id, password) {
        const element = document.getElementById('pwd-' + id);
        if (element.classList.contains('password-hidden')) {
            element.textContent = password;
            element.classList.remove('password-hidden');
        } else {
            element.textContent = '••••••••';
            element.classList.add('password-hidden');
        }
    };

    window.toggleVisibility = function(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    };

    window.generatePassword = function(inputId) {
        fetch('{{ route("vault.generate-password") }}?length=16', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const input = document.getElementById(inputId);
            input.value = data.password;
            input.type = 'text';
            
            // Find toggle button and update icon
            const btn = input.nextElementSibling;
            const icon = btn.querySelector('i');
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        });
    };

    window.editVault = function(data) {
        document.getElementById('edit-type').value = data.account_type;
        document.getElementById('edit-service').value = data.service_name;
        document.getElementById('edit-username').value = data.username;
        document.getElementById('edit-email').value = data.email || '';
        document.getElementById('edit-url').value = data.url || '';
        document.getElementById('edit-notes').value = data.notes || '';
        document.getElementById('edit-password').value = ''; // Don't populate password for security, only if changing

        document.getElementById('editVaultForm').action = '/vault/' + data.id;
        openModal('editVaultModal');
    };
});
</script>
@endsection