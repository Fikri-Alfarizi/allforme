@extends('layouts.app')

@section('title', 'Private Links - PLFIS')
@section('page-title', 'Private Links')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card" style="background: var(--dark-card); border: 1px solid var(--border-color); border-radius: 16px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-color);">
                <h4 class="card-title" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-user-secret" style="color: var(--primary-color);"></i>
                    Daftar Link Privat
                </h4>
                <button class="btn btn-primary" onclick="openAddLinkModal()" style="background: var(--primary-color); border: none; padding: 10px 20px; border-radius: 8px; color: white;">
                    <i class="fas fa-plus"></i> Tambah Link
                </button>
            </div>
            <div class="card-body" style="padding: 24px;">
                @if(session('success'))
                    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--success-color); padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if($links->count() > 0)
                    <div class="table-responsive">
                        <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0 8px;">
                            <thead>
                                <tr style="color: var(--text-muted); font-size: 13px; text-transform: uppercase;">
                                    <th style="padding: 12px; text-align: left;">Judul</th>
                                    <th style="padding: 12px; text-align: left;">URL</th>
                                    <th style="padding: 12px; text-align: left;">Deskripsi</th>
                                    <th style="padding: 12px; text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($links as $link)
                                    <tr style="background: var(--dark-bg); transition: transform 0.2s;">
                                        <td style="padding: 16px; border-radius: 10px 0 0 10px;">
                                            <div style="font-weight: 600; color: var(--text-light);">{{ $link->title }}</div>
                                        </td>
                                        <td style="padding: 16px;">
                                            <a href="{{ $link->url }}" target="_blank" style="color: var(--primary-color); text-decoration: none; display: flex; align-items: center; gap: 6px;">
                                                <i class="fas fa-external-link-alt" style="font-size: 12px;"></i>
                                                {{ Str::limit($link->url, 40) }}
                                            </a>
                                        </td>
                                        <td style="padding: 16px; color: var(--text-muted);">
                                            {{ $link->description ?? '-' }}
                                        </td>
                                        <td style="padding: 16px; text-align: right; border-radius: 0 10px 10px 0;">
                                            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                                                <a href="{{ $link->url }}" target="_blank" class="action-btn" style="color: var(--success-color); background: rgba(16, 185, 129, 0.1); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                    <i class="fas fa-play"></i>
                                                </a>
                                                <form action="{{ route('private-links.destroy', $link) }}" method="POST" onsubmit="return confirm('Hapus link ini?');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="action-btn" style="color: var(--danger-color); background: rgba(239, 68, 68, 0.1); width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; border: none; cursor: pointer;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                        <i class="fas fa-user-secret" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                        <p>Belum ada link privat tersimpan.</p>
                        <button onclick="openAddLinkModal()" style="margin-top: 10px; background: none; border: 1px solid var(--primary-color); color: var(--primary-color); padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                            Simpan Link Pertama
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Link Modal -->
<div id="addLinkModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Link Privat</h3>
            <button class="modal-close" onclick="closeAddLinkModal()">&times;</button>
        </div>
        <form action="{{ route('private-links.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-input" placeholder="Nama link..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">URL</label>
                    <input type="url" name="url" class="form-input" placeholder="https://..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi (Opsional)</label>
                    <textarea name="description" class="form-textarea" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddLinkModal()">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddLinkModal() {
        const modal = document.getElementById('addLinkModal');
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeAddLinkModal() {
        const modal = document.getElementById('addLinkModal');
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    }

    // Close on click outside
    document.getElementById('addLinkModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddLinkModal();
        }
    });
</script>
@endsection
