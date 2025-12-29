@extends('layouts.app')

@section('title', 'Hasil Pencarian - PLFIS')
@section('page-title', 'Pencarian')

@section('content')
<div class="search-results-container">
    <div class="search-header">
        <h2>Hasil Pencarian untuk "{{ $query }}"</h2>
        <p class="text-muted">
            Ditemukan: 
            <span class="badge bg-primary">{{ $transactions->count() }} Transaksi</span>
            <span class="badge bg-success">{{ $tasks->count() }} Tasks</span>
            <span class="badge bg-warning">{{ $notes->count() }} Catatan</span>
        </p>
    </div>

    @if($transactions->isEmpty() && $tasks->isEmpty() && $notes->isEmpty())
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Tidak ditemukan hasil</h3>
            <p>Coba kata kunci lain atau periksa ejaan Anda.</p>
        </div>
    @else
        <div class="results-grid">
            <!-- Transactions -->
            @if($transactions->isNotEmpty())
            <div class="result-section">
                <h3 class="section-title"><i class="fas fa-wallet"></i> Transaksi</h3>
                <div class="result-list">
                    @foreach($transactions as $transaction)
                    <div class="result-item">
                        <div class="item-icon {{ $transaction->type == 'income' ? 'success' : 'danger' }}">
                            <i class="fas fa-{{ $transaction->type == 'income' ? 'arrow-down' : 'arrow-up' }}"></i>
                        </div>
                        <div class="item-details">
                            <h4 class="item-title">{{ $transaction->description ?: $transaction->category->name }}</h4>
                            <span class="item-meta">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }} 
                                • {{ $transaction->transaction_date->format('d M Y') }}
                            </span>
                        </div>
                        <a href="{{ route('finance.edit', $transaction->id) }}" class="btn-action">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Tasks -->
            @if($tasks->isNotEmpty())
            <div class="result-section">
                <h3 class="section-title"><i class="fas fa-tasks"></i> Tasks</h3>
                <div class="result-list">
                    @foreach($tasks as $task)
                    <div class="result-item">
                        <div class="item-icon task-{{ $task->priority }}">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="item-details">
                            <h4 class="item-title">{{ $task->title }}</h4>
                            <span class="item-meta">
                                Due: {{ $task->due_date ? $task->due_date->format('d M Y') : 'No Date' }}
                                • <span class="badge-sm {{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            </span>
                        </div>
                        <button class="btn-action edit-task-btn" data-task="{{ $task->toJson() }}">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Notes -->
            @if($notes->isNotEmpty())
            <div class="result-section">
                <h3 class="section-title"><i class="fas fa-sticky-note"></i> Catatan</h3>
                <div class="result-list">
                    @foreach($notes as $note)
                    <div class="result-item" style="border-left: 4px solid {{ $note->color ?: 'var(--border-color)' }}">
                        <div class="item-details">
                            <h4 class="item-title">{{ $note->title }}</h4>
                            <span class="item-meta">
                                Updated: {{ $note->updated_at->diffForHumans() }}
                            </span>
                            <p class="item-preview">{{ Str::limit($note->content, 60) }}</p>
                        </div>
                        <a href="{{ route('notes.edit', $note->id) }}" class="btn-action">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    @endif
</div>

<style>
    .search-results-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    .search-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .text-muted {
        color: var(--text-muted);
        margin-top: 5px;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-right: 5px;
    }

    .bg-primary { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .bg-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .bg-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .result-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .result-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 12px;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        transition: transform 0.2s;
    }

    .result-item:hover {
        transform: translateY(-2px);
        border-color: var(--primary-color);
    }

    .item-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .item-icon.success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .item-icon.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    
    .item-icon.task-urgent { color: #ef4444; background: rgba(239, 68, 68, 0.1); }
    .item-icon.task-high { color: #f59e0b; background: rgba(245, 158, 11, 0.1); }
    .item-icon.task-medium { color: #3b82f6; background: rgba(59, 130, 246, 0.1); }
    .item-icon.task-low { color: #10b981; background: rgba(16, 185, 129, 0.1); }

    .item-details {
        flex: 1;
        min-width: 0;
    }

    .item-title {
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-meta {
        font-size: 12px;
        color: var(--text-muted);
        display: block;
    }

    .item-preview {
        font-size: 12px;
        color: var(--text-muted);
        margin: 4px 0 0 0;
    }

    .badge-sm {
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
    }
    
    .badge-sm.completed { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .badge-sm.pending { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
    .badge-sm.in_progress { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }

    .btn-action {
        color: var(--text-muted);
        padding: 8px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: rgba(255, 255, 255, 0.1);
        color: var(--primary-color);
    }
</style>
@endsection

<!-- Edit Modal -->
<div id="editTaskModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Task</h3>
            <button class="modal-close" id="closeEditModalBtn">&times;</button>
        </div>
        <form id="editTaskForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Judul Task</label>
                    <input type="text" name="title" id="edit-title" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-description" class="form-textarea"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Prioritas</label>
                        <select name="priority" id="edit-priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit-status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tenggat Waktu</label>
                    <input type="date" name="due_date" id="edit-due_date" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container" id="edit-tags-container">
                        <input type="text" class="tag-input" id="edit-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="edit-tags-hidden-inputs"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary">Update Task</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function openModal(id) {
        document.getElementById(id).classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
        document.body.style.overflow = '';
    }

    document.getElementById('closeEditModalBtn').addEventListener('click', function() {
        closeModal('editTaskModal');
    });

    document.getElementById('editTaskModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal('editTaskModal');
        }
    });

    class TagManager {
        constructor(containerId, inputId, hiddenContainerId, initialTags = []) {
            this.container = document.getElementById(containerId);
            this.input = document.getElementById(inputId);
            this.hiddenContainer = document.getElementById(hiddenContainerId);
            this.tags = initialTags || [];
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
            const existingTags = this.container.querySelectorAll('.tag-item');
            existingTags.forEach(t => t.remove());
            this.hiddenContainer.innerHTML = '';
            this.tags.forEach((tag, index) => {
                const tagEl = document.createElement('div');
                tagEl.className = 'tag-item';
                tagEl.innerHTML = `${tag} <span class="tag-remove">&times;</span>`;
                tagEl.querySelector('.tag-remove').onclick = () => {
                    this.tags.splice(index, 1);
                    this.render();
                };
                this.container.insertBefore(tagEl, this.input);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tags[]';
                hiddenInput.value = tag;
                this.hiddenContainer.appendChild(hiddenInput);
            });
        }
    }

    const editTagManager = new TagManager('edit-tags-container', 'edit-tag-input', 'edit-tags-hidden-inputs');

    function editTask(data) {
        document.getElementById('edit-title').value = data.title;
        document.getElementById('edit-description').value = data.description || '';
        document.getElementById('edit-priority').value = data.priority;
        document.getElementById('edit-status').value = data.status;
        document.getElementById('edit-due_date').value = data.due_date ? data.due_date.substring(0, 10) : '';
        editTagManager.setTags(data.tags);
        document.getElementById('editTaskForm').action = '/tasks/' + data.id;
        openModal('editTaskModal');
    }

    document.querySelectorAll('.edit-task-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskData = JSON.parse(this.getAttribute('data-task'));
            editTask(taskData);
        });
    });
});
</script>
<style>
/* Ensure Modal Styles if not globally available */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.7); display: flex; justify-content: center; align-items: center;
    z-index: 1000; visibility: hidden; opacity: 0; transition: visibility 0s, opacity 0.3s;
}
.modal-overlay.active { visibility: visible; opacity: 1; }
.modal-content {
    background: var(--dark-card); border-radius: 12px; padding: 25px; width: 90%; max-width: 500px;
    transform: translateY(-20px); transition: transform 0.3s ease-out; position: relative;
}
.modal-overlay.active .modal-content { transform: translateY(0); }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.modal-title { font-size: 1.5em; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.8em; color: var(--text-muted); cursor: pointer; line-height: 1; }
.form-input, .form-select, .form-textarea {
    width: 100%; padding: 10px; background: var(--dark-bg); border: 1px solid var(--border-color);
    border-radius: 6px; color: var(--text-light);
}
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
</style>
@endsection
