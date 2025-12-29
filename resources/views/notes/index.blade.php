@extends('layouts.app')

@section('title', 'Catatan - PLFIS')
@section('page-title', 'Catatan')

@section('content')
<style>
    /* --- CSS Utama --- */
    .notes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-bar {
        display: flex;
        gap: 10px;
        flex: 1;
        max-width: 500px;
    }

    .search-input {
        flex: 1;
        padding: 10px 16px;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-light);
    }

    .search-input:focus { outline: none; border-color: var(--primary-color); }

    .tags-filter {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .tag-chip {
        padding: 6px 14px;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .tag-chip:hover, .tag-chip.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .notes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .note-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s;
        cursor: pointer;
        position: relative;
    }

    .note-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .note-card.pinned {
        border-color: var(--warning-color);
        background: rgba(245, 158, 11, 0.05);
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 12px;
    }

    .note-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }

    .note-content {
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 15px;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .note-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 12px;
        border-top: 1px solid var(--border-color);
    }

    .note-tags { display: flex; gap: 6px; flex-wrap: wrap; }

    .note-tag {
        padding: 3px 10px;
        background: rgba(59, 130, 246, 0.2);
        border-radius: 12px;
        font-size: 11px;
        color: var(--primary-color);
    }

    .note-date { font-size: 12px; color: var(--text-muted); }

    .pin-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        color: var(--warning-color);
        font-size: 18px;
    }

    .note-actions {
        display: flex;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .note-card:hover .note-actions { opacity: 1; }

    .note-action-btn {
        padding: 6px 10px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-light);
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s;
    }

    .note-action-btn:hover { background: var(--primary-color); border-color: var(--primary-color); }

    .color-indicator {
        width: 4px; height: 100%; position: absolute; left: 0; top: 0; border-radius: 12px 0 0 12px;
    }

    /* --- Form & Modal Styles --- */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-input, .form-textarea {
        width: 100%; padding: 10px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 6px; color: var(--text-light);
    }
    .form-textarea { resize: vertical; min-height: 150px; }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%; }
    
    .tags-input-container {
        display: flex; flex-wrap: wrap; gap: 8px; padding: 8px;
        background: var(--dark-bg); border: 1px solid var(--border-color); border-radius: 8px;
        min-height: 40px;
    }
    .tag-item {
        padding: 4px 10px; background: var(--primary-color); border-radius: 20px;
        font-size: 12px; display: flex; align-items: center; gap: 6px;
    }
    .tag-remove { cursor: pointer; font-weight: bold; }
    .tag-input { flex: 1; border: none; background: none; color: var(--text-light); outline: none; min-width: 80px; }
    
    .color-picker { display: flex; gap: 10px; flex-wrap: wrap; }
    .color-option {
        width: 30px; height: 30px; border-radius: 6px; cursor: pointer; border: 2px solid transparent; transition: all 0.3s;
    }
    .color-option.selected { border-color: white; box-shadow: 0 0 0 2px var(--primary-color); }
    .checkbox-group { display: flex; align-items: center; gap: 10px; }
    .checkbox-group input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }

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
        max-width: 500px;
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

<div class="notes-header">
    <div class="search-bar">
        <input type="text" class="search-input" placeholder="Cari catatan..." value="{{ $search ?? '' }}">
        <button class="btn-primary" style="width: auto;">
            <i class="fas fa-search"></i>
        </button>
    </div>
    <button class="btn-primary" style="width: auto; display: flex; align-items: center; gap: 8px;" onclick="openModal('createNoteModal')">
        <i class="fas fa-plus"></i>
        Buat Catatan
    </button>
</div>

@if($allTags->count() > 0)
<div class="tags-filter">
    <div class="tag-chip {{ !$tag ? 'active' : '' }}">
        <i class="fas fa-th"></i> Semua
    </div>
    @foreach($allTags as $tagItem)
        <div class="tag-chip {{ $tag === $tagItem ? 'active' : '' }}">
            <i class="fas fa-tag"></i> {{ $tagItem }}
        </div>
    @endforeach
</div>
@endif

<div class="notes-grid">
    @forelse($notes as $note)
        <div class="note-card {{ $note->is_pinned ? 'pinned' : '' }}">
            @if($note->color)
                <div class="color-indicator" style="background: {{ $note->color }};"></div>
            @endif

            @if($note->is_pinned)
                <i class="fas fa-thumbtack pin-icon"></i>
            @endif

            <div class="note-header">
                <div style="flex: 1;">
                    <div class="note-title">{{ $note->title }}</div>
                </div>
            </div>

            <div class="note-content">
                {{ Str::limit($note->content, 200) }}
            </div>

            <div class="note-footer">
                <div class="note-tags">
                    @if($note->tags)
                        @foreach($note->tags as $noteTag)
                            <span class="note-tag">{{ $noteTag }}</span>
                        @endforeach
                    @endif
                </div>
                <div class="note-date">
                    <i class="fas fa-clock"></i>
                    {{ $note->updated_at->diffForHumans() }}
                </div>
            </div>

            <div class="note-actions">
                <button type="button" class="note-action-btn" onclick='editNote(@json($note))'>
                    <i class="fas fa-edit"></i> Edit
                </button>
                <form action="{{ route('notes.toggle-pin', $note) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="note-action-btn">
                        <i class="fas fa-thumbtack"></i> {{ $note->is_pinned ? 'Unpin' : 'Pin' }}
                    </button>
                </form>
                <form action="{{ route('notes.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="note-action-btn">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; background: var(--dark-card); border-radius: 12px;">
            <i class="fas fa-sticky-note" style="font-size: 64px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">Belum Ada Catatan</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">
                Mulai catat ide, rencana, atau informasi penting Anda
            </p>
            <button class="btn-primary" style="width: auto; margin: 0 auto;" onclick="openModal('createNoteModal')">
                <i class="fas fa-plus"></i> Buat Catatan Pertama
            </button>
        </div>
    @endforelse
</div>

@if($notes->hasPages())
<div style="margin-top: 30px; display: flex; justify-content: center;">
    {{ $notes->links() }}
</div>
@endif

<div id="createNoteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Buat Catatan Baru</h3>
            <button class="modal-close" onclick="closeModal('createNoteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('notes.store') }}" method="POST" id="createNoteForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-input" placeholder="Judul catatan..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Catatan</label>
                    <textarea name="content" class="form-textarea" placeholder="Tulis catatan Anda di sini..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container" id="create-tags-container">
                        <input type="text" class="tag-input" id="create-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="create-tags-hidden-inputs"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <div class="color-picker" id="create-color-picker">
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981"></div>
                        <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899"></div>
                        <div class="color-option selected" style="background: transparent; border: 2px dashed var(--border-color);" data-color=""></div>
                    </div>
                    <input type="hidden" name="color" id="create-color-input" value="">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_pinned" id="create-pinned" value="1">
                        <label for="create-pinned">Pin catatan ini</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createNoteModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createNoteForm').submit()">Simpan Catatan</button>
        </div>
    </div>
</div>

<div id="editNoteModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Catatan</h3>
            <button class="modal-close" onclick="closeModal('editNoteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editNoteForm" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" id="edit-title" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Catatan</label>
                    <textarea name="content" id="edit-content" class="form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container" id="edit-tags-container">
                        <input type="text" class="tag-input" id="edit-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="edit-tags-hidden-inputs"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <div class="color-picker" id="edit-color-picker">
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981"></div>
                        <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899"></div>
                        <div class="color-option" style="background: transparent; border: 2px dashed var(--border-color);" data-color=""></div>
                    </div>
                    <input type="hidden" name="color" id="edit-color-input" value="">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_pinned" id="edit-pinned" value="1">
                        <label for="edit-pinned">Pin catatan ini</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editNoteModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editNoteForm').submit()">Update Catatan</button>
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

    // Tag Manager Class
    class TagManager {
        constructor(containerId, inputId, hiddenContainerId, initialTags = []) {
            this.container = document.getElementById(containerId);
            this.input = document.getElementById(inputId);
            this.hiddenContainer = document.getElementById(hiddenContainerId);
            this.tags = initialTags;
            
            this.setupListeners();
            this.render();
        }

        setTags(newTags) {
            this.tags = newTags || [];
            this.render();
        }

        setupListeners() {
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const tag = this.input.value.trim();
                    if (tag && !this.tags.includes(tag)) {
                        this.tags.push(tag);
                        this.render();
                        this.input.value = '';
                    }
                }
            });
        }

        render() {
            // Remove existing tags in UI
            const existingTags = this.container.querySelectorAll('.tag-item');
            existingTags.forEach(t => t.remove());

            // Clear hidden inputs
            this.hiddenContainer.innerHTML = '';

            // Render tags
            this.tags.forEach((tag, index) => {
                // UI
                const tagEl = document.createElement('div');
                tagEl.className = 'tag-item';
                tagEl.innerHTML = `${tag} <span class="tag-remove">&times;</span>`;
                tagEl.querySelector('.tag-remove').onclick = () => {
                    this.tags.splice(index, 1);
                    this.render();
                };
                this.container.insertBefore(tagEl, this.input);

                // Hidden Input
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tags[]';
                hiddenInput.value = tag;
                this.hiddenContainer.appendChild(hiddenInput);
            });
        }
    }

    // Initialize Tag Managers
    const createTagManager = new TagManager('create-tags-container', 'create-tag-input', 'create-tags-hidden-inputs');
    const editTagManager = new TagManager('edit-tags-container', 'edit-tag-input', 'edit-tags-hidden-inputs');

    // Color Picker Logic
    function setupColorPicker(pickerId, inputId) {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        const options = picker.querySelectorAll('.color-option');

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
                // Find matching color or default to transparent
                let match = Array.from(options).find(o => o.dataset.color === color);
                if (!match) match = Array.from(options).find(o => o.dataset.color === '');
                
                if (match) {
                    match.classList.add('selected');
                    input.value = match.dataset.color;
                }
            }
        };
    }

    const createColorPicker = setupColorPicker('create-color-picker', 'create-color-input');
    const editColorPicker = setupColorPicker('edit-color-picker', 'edit-color-input');

    // Edit Function
    function editNote(data) {
        document.getElementById('edit-title').value = data.title;
        document.getElementById('edit-content').value = data.content;
        document.getElementById('edit-pinned').checked = data.is_pinned;
        
        // Setup Tags
        editTagManager.setTags(data.tags);
        
        // Setup Color
        editColorPicker.setColor(data.color);

        document.getElementById('editNoteForm').action = '/notes/' + data.id;
        openModal('editNoteModal');
    }
</script>
@endsection