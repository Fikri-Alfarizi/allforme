@extends('layouts.app')

@section('title', 'Pengaturan - PLFIS')
@section('page-title', 'Pengaturan')

@section('content')
<style>
    .settings-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .settings-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid var(--border-color);
        overflow-x: auto;
    }

    .settings-tab {
        padding: 12px 24px;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-weight: 600;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
        white-space: nowrap;
    }

    .settings-tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .settings-tab:hover {
        color: var(--text-light);
    }

    .settings-section {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 12px 16px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-light);
        font-size: 14px;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .form-switch {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .form-switch:last-child {
        border-bottom: none;
    }

    .switch-label {
        flex: 1;
    }

    .switch-label-title {
        font-weight: 500;
        margin-bottom: 4px;
    }

    .switch-label-desc {
        font-size: 13px;
        color: var(--text-muted);
    }

    .toggle-switch {
        position: relative;
        width: 50px;
        height: 26px;
        background: var(--border-color);
        border-radius: 13px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .toggle-switch.active {
        background: var(--primary-color);
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        background: white;
        border-radius: 50%;
        top: 2px;
        left: 2px;
        transition: left 0.3s;
    }

    .toggle-switch.active::after {
        left: 26px;
    }

    .save-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }

    .save-btn:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
    }

    .danger-zone {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid var(--danger-color);
    }

    .danger-btn {
        background: var(--danger-color);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .profile-avatar:hover .avatar-overlay {
        opacity: 1;
    }

    .avatar-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
        color: white;
        font-size: 24px;
    }
</style>

<div class="settings-container">
    <!-- Tabs -->
    <div class="settings-tabs">
        <button class="settings-tab active" onclick="showTab('profile')">
            <i class="fas fa-user"></i> Profil
        </button>
        <button class="settings-tab" onclick="showTab('general')">
            <i class="fas fa-cog"></i> Umum
        </button>
        <button class="settings-tab" onclick="showTab('notifications')">
            <i class="fas fa-bell"></i> Notifikasi
        </button>
        <button class="settings-tab" onclick="showTab('security')">
            <i class="fas fa-shield-alt"></i> Keamanan
        </button>
    </div>

    <!-- Profile Settings -->
    <div id="tab-profile" class="tab-content">
        <div class="settings-section">
            <div class="section-title">
                <i class="fas fa-user-circle"></i>
                Informasi Profil
            </div>

            <form action="{{ route('settings.update-profile') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div style="text-align: center; margin-bottom: 30px;">
                    <input type="file" name="avatar" id="avatarInput" style="display: none;" accept="image/*" onchange="previewAvatar(this)">
                    <div class="profile-avatar" onclick="document.getElementById('avatarInput').click()">
                        @if(auth()->user()->avatar)
                            <img id="avatarPreview" src="{{ auth()->user()->avatar }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                            <div id="avatarInitials" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <img id="avatarPreview" src="" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: none;">
                        @endif
                        <div class="avatar-overlay">
                            <i class="fas fa-camera"></i>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted);">Klik gambar untuk mengubah avatar</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-input" 
                           value="{{ auth()->user()->name }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" 
                           value="{{ auth()->user()->email }}" required>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>

        <div class="settings-section">
            <div class="section-title">
                <i class="fas fa-key"></i>
                Ubah Password
            </div>

            <form action="{{ route('settings.change-password') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="form-input" required>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <!-- General Settings -->
    <div id="tab-general" class="tab-content" style="display: none;">
        <div class="settings-section">
            <div class="section-title">
                <i class="fas fa-globe"></i>
                Pengaturan Umum
            </div>

            <form id="generalSettingsForm">
                @csrf
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label">Mata Uang</label>
                        <span class="setting-status" id="status-currency"></span>
                    </div>
                    <select name="currency" class="form-select" onchange="updateSetting('currency', this.value)">
                        <option value="IDR" {{ ($settings->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                        <option value="USD" {{ ($settings->currency ?? '') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="EUR" {{ ($settings->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    </select>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label">Bahasa</label>
                        <span class="setting-status" id="status-language"></span>
                    </div>
                    <select name="language" class="form-select" onchange="updateSetting('language', this.value)">
                        <option value="id" {{ ($settings->language ?? 'id') === 'id' ? 'selected' : '' }}>Indonesia</option>
                        <option value="en" {{ ($settings->language ?? '') === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label">Zona Waktu</label>
                        <span class="setting-status" id="status-timezone"></span>
                    </div>
                    <select name="timezone" class="form-select" onchange="updateSetting('timezone', this.value)">
                        <option value="Asia/Jakarta" {{ ($settings->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                        <option value="Asia/Makassar" {{ ($settings->timezone ?? '') === 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                        <option value="Asia/Jayapura" {{ ($settings->timezone ?? '') === 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Settings -->
    <div id="tab-notifications" class="tab-content" style="display: none;">
        <div class="settings-section">
            <div class="section-title">
                <i class="fas fa-bell"></i>
                Pengaturan Notifikasi
            </div>

            <form action="{{ route('settings.update-notifications') }}" method="POST">
                @csrf
                <div class="form-switch">
                    <div class="switch-label">
                        <div class="switch-label-title">Notifikasi Umum</div>
                        <div class="switch-label-desc">Terima notifikasi untuk aktivitas penting</div>
                    </div>
                    <div class="toggle-switch {{ ($settings->notification_enabled ?? true) ? 'active' : '' }}" 
                         onclick="toggleSwitch(this, 'notification_enabled')">
                    </div>
                    <input type="hidden" name="notification_enabled" value="{{ ($settings->notification_enabled ?? true) ? '1' : '0' }}">
                </div>

                <div class="form-switch">
                    <div class="switch-label">
                        <div class="switch-label-title">AI Assistant</div>
                        <div class="switch-label-desc">Aktifkan fitur AI untuk saran keuangan</div>
                    </div>
                    <div class="toggle-switch {{ ($settings->ai_enabled ?? true) ? 'active' : '' }}" 
                         onclick="toggleSwitch(this, 'ai_enabled')">
                    </div>
                    <input type="hidden" name="ai_enabled" value="{{ ($settings->ai_enabled ?? true) ? '1' : '0' }}">
                </div>

                <button type="submit" class="save-btn" style="margin-top: 20px;">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div id="tab-security" class="tab-content" style="display: none;">
        <div class="settings-section">
            <div class="section-title">
                <i class="fas fa-shield-alt"></i>
                Keamanan Vault
            </div>

            <form action="{{ route('settings.update-security') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Timeout Vault (Menit)</label>
                    <input type="number" name="vault_timeout_minutes" class="form-input" 
                           value="{{ $settings->vault_timeout_minutes ?? 5 }}" min="1" max="120">
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">
                        Password akan otomatis tersembunyi setelah waktu ini
                    </p>
                </div>

                <button type="submit" class="save-btn">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>

        <div class="settings-section danger-zone">
            <div class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Danger Zone
            </div>

            <p style="color: var(--text-muted); margin-bottom: 20px;">
                Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.
            </p>

            <button class="danger-btn" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Hapus Akun
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.style.display = 'none';
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.settings-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById('tab-' + tabName).style.display = 'block';

        // Add active class to clicked button
        event.target.closest('.settings-tab').classList.add('active');
    }

    function toggleSwitch(element, inputName) {
        element.classList.toggle('active');
        const input = element.nextElementSibling;
        input.value = element.classList.contains('active') ? '1' : '0';
    }

    function confirmDelete() {
        if (confirm('Apakah Anda yakin ingin menghapus akun? Semua data akan hilang permanen!')) {
            alert('Fitur hapus akun akan segera tersedia');
        }
    }

    async function updateSetting(key, value) {
        const statusEl = document.getElementById('status-' + key);
        if (statusEl) {
            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="color: var(--primary-color);"></i> Menyimpan...';
            statusEl.style.fontSize = '12px';
        }

        try {
            const formData = new FormData();
            formData.append(key, value);
            formData.append('_token', '{{ csrf_token() }}');

            const response = await fetch('{{ route("settings.update-general-ajax") }}', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-check" style="color: var(--success-color);"></i> Tersimpan';
                    setTimeout(() => {
                        statusEl.innerHTML = '';
                    }, 2000);
                }
            } else {
                if (statusEl) statusEl.innerHTML = '<span style="color: var(--danger-color);">Gagal</span>';
                alert(data.error || 'Gagal menyimpan pengaturan.');
            }
        } catch (error) {
            console.error('Error updating setting:', error);
            if (statusEl) statusEl.innerHTML = '<span style="color: var(--danger-color);">Error</span>';
        }
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const initials = document.getElementById('avatarInitials');
                
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (initials) initials.style.display = 'none';
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
