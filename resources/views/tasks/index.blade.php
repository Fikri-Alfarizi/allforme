@extends('layouts.app')

@section('title', 'Agenda - PLFIS')
@section('page-title', 'Agenda & Tasks')

@section('content')
<style>
    .tasks-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 10px 20px;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-tab.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .filter-tab:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .task-count {
        background: rgba(255,255,255,0.2);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }

    .tasks-list {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
    }

    .task-item {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        gap: 15px;
        align-items: start;
        transition: background 0.3s;
    }

    .task-item:hover {
        background: rgba(59, 130, 246, 0.05);
    }

    .task-item:last-child {
        border-bottom: none;
    }

    .task-checkbox {
        width: 24px;
        height: 24px;
        border: 2px solid var(--border-color);
        border-radius: 6px;
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 2px;
        transition: all 0.3s;
    }

    .task-checkbox:hover {
        border-color: var(--success-color);
    }

    .task-checkbox.checked {
        background: var(--success-color);
        border-color: var(--success-color);
        position: relative;
    }

    .task-checkbox.checked::after {
        content: '✓';
        position: absolute;
        color: white;
        font-weight: bold;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .task-content {
        flex: 1;
    }

    .task-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .task-title.completed {
        text-decoration: line-through;
        opacity: 0.6;
    }

    .task-meta {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: var(--text-muted);
        flex-wrap: wrap;
    }

    .priority-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .priority-urgent {
        background: rgba(239, 68, 68, 0.2);
        color: var(--danger-color);
    }

    .priority-high {
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning-color);
    }

    .priority-medium {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-color);
    }

    .priority-low {
        background: rgba(107, 114, 128, 0.2);
        color: var(--text-muted);
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-pending {
        background: rgba(107, 114, 128, 0.2);
        color: var(--text-muted);
    }

    .status-in_progress {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-color);
    }

    .status-completed {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success-color);
    }

    .task-actions {
        display: flex;
        gap: 8px;
    }

    .task-action-btn {
        padding: 8px 12px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-light);
        cursor: pointer;
        font-size: 12px;
        transition: all 0.3s;
    }

    .task-action-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .overdue-indicator {
        color: var(--danger-color);
        font-weight: bold;
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

    .modal-overlay.active { display: flex; }

    .modal-content {
        background: var(--dark-card);
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

    /* Modal Form Styles */
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 10px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 6px; color: var(--text-light);
    }
    .form-textarea { resize: vertical; min-height: 100px; }
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
</style>

<div class="tasks-header">
    <div>
        <h2 style="font-size: 20px; margin-bottom: 5px;">Agenda & Tasks</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Kelola tugas dan jadwal Anda</p>
    </div>
    <button class="add-btn" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;" id="openCreateModalBtn">
        <i class="fas fa-plus"></i>
        Tambah Task
    </button>
</div>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <div class="filter-tab {{ !$status ? 'active' : '' }}" onclick="window.location.href='{{ route('tasks.index') }}'">
        <i class="fas fa-th"></i>
        <span>Semua</span>
        <span class="task-count">{{ $counts['all'] }}</span>
    </div>
    <div class="filter-tab {{ $status === 'pending' ? 'active' : '' }}" onclick="window.location.href='{{ route('tasks.index', ['status' => 'pending']) }}'">
        <i class="fas fa-clock"></i>
        <span>Pending</span>
        <span class="task-count">{{ $counts['pending'] }}</span>
    </div>
    <div class="filter-tab {{ $status === 'in_progress' ? 'active' : '' }}" onclick="window.location.href='{{ route('tasks.index', ['status' => 'in_progress']) }}'">
        <i class="fas fa-spinner"></i>
        <span>In Progress</span>
        <span class="task-count">{{ $counts['in_progress'] }}</span>
    </div>
    <div class="filter-tab {{ $status === 'completed' ? 'active' : '' }}" onclick="window.location.href='{{ route('tasks.index', ['status' => 'completed']) }}'">
        <i class="fas fa-check-circle"></i>
        <span>Completed</span>
        <span class="task-count">{{ $counts['completed'] }}</span>
    </div>
    <div class="filter-tab {{ $status === 'overdue' ? 'active' : '' }}" onclick="window.location.href='{{ route('tasks.index', ['status' => 'overdue']) }}'">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Overdue</span>
        <span class="task-count">{{ $counts['overdue'] }}</span>
    </div>
</div>

<!-- Tasks List -->
<div class="tasks-list">
    @forelse($tasks as $task)
        <div class="task-item">
            <div class="task-checkbox {{ $task->status === 'completed' ? 'checked' : '' }}"
                 onclick="toggleTask({{ $task->id }})">
            </div>

            <div class="task-content">
                <div class="task-title {{ $task->status === 'completed' ? 'completed' : '' }}">
                    {{ $task->title }}
                </div>

                @if($task->description)
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 10px;">
                        {{ Str::limit($task->description, 150) }}
                    </p>
                @endif

                <div class="task-meta">
                    <span class="priority-badge priority-{{ $task->priority }}">
                        <i class="fas fa-flag"></i> {{ ucfirst($task->priority) }}
                    </span>

                    <span class="status-badge status-{{ $task->status }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>

                    @if($task->due_date)
                        <span class="{{ $task->isOverdue() ? 'overdue-indicator' : '' }}">
                            <i class="fas fa-calendar"></i>
                            {{ $task->due_date->format('d M Y') }}
                            @if($task->isOverdue())
                                (Terlambat)
                            @endif
                        </span>
                    @endif

                    @if($task->tags)
                        @foreach($task->tags as $tag)
                            <span style="color: var(--primary-color);">
                                <i class="fas fa-tag"></i> {{ $tag }}
                            </span>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="task-actions">
                @if($task->status !== 'completed')
                    <form action="{{ route('tasks.complete', $task) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="task-action-btn">
                            <i class="fas fa-check"></i> Selesai
                        </button>
                    </form>
                @endif

                <button type="button" class="task-action-btn" onclick='editTask(@json($task))'>
                    <i class="fas fa-edit"></i> Edit
                </button>
                
                <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Hapus task ini?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="task-action-btn">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-tasks" style="font-size: 64px; color: var(--text-muted); opacity: 0.3; margin-bottom: 20px;"></i>
            <h3 style="margin-bottom: 15px;">Belum Ada Task</h3>
            <p style="color: var(--text-muted); margin-bottom: 25px;">
                Mulai kelola tugas dan jadwal Anda
            </p>
            <button class="add-btn" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px; margin: 0 auto;" id="openCreateModalEmptyBtn">
                <i class="fas fa-plus"></i>
                Buat Task Pertama
            </button>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($tasks->hasPages())
<div style="margin-top: 30px; display: flex; justify-content: center;">
    {{ $tasks->links() }}
</div>
@endif

<!-- Create Modal -->
<div id="createTaskModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Task Baru</h3>
            <button class="modal-close" id="closeCreateModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('tasks.store') }}" method="POST" id="createTaskForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Judul Task</label>
                    <input type="text" name="title" class="form-input" placeholder="Apa yang perlu diselesaikan?" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-textarea" placeholder="Detail tugas..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Prioritas</label>
                        <select name="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tenggat Waktu</label>
                    <input type="date" name="due_date" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container" id="create-tags-container">
                        <input type="text" class="tag-input" id="create-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="create-tags-hidden-inputs"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createTaskModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createTaskForm').submit()">Simpan Task</button>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editTaskModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Task</h3>
            <button class="modal-close" id="closeEditModalBtn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
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
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editTaskModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editTaskForm').submit()">Update Task</button>
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
        openModal('createTaskModal');
    });

    document.getElementById('openCreateModalEmptyBtn').addEventListener('click', function() {
        openModal('createTaskModal');
    });

    document.getElementById('closeCreateModalBtn').addEventListener('click', function() {
        closeModal('createTaskModal');
    });

    document.getElementById('closeEditModalBtn').addEventListener('click', function() {
        closeModal('editTaskModal');
    });

    // Disabled click-outside-to-close to prevent accidental closure
    // document.getElementById('createTaskModal').addEventListener('click', function(e) {
    //     if (e.target === this) {
    //         closeModal('createTaskModal');
    //     }
    // });

    // document.getElementById('editTaskModal').addEventListener('click', function(e) {
    //     if (e.target === this) {
    //         closeModal('editTaskModal');
    //     }
    // });

    // Tag Manager
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

    // Toggle task completion
    function toggleTask(taskId) {
        fetch(`/tasks/${taskId}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            location.reload();
        });
    }

    // Edit task
    function editTask(data) {
        document.getElementById('edit-title').value = data.title;
        document.getElementById('edit-description').value = data.description || '';
        document.getElementById('edit-priority').value = data.priority;
        document.getElementById('edit-status').value = data.status;
        document.getElementById('edit-due_date').value = data.due_date ? data.due_date.substring(0, 10) : '';

        // Setup Tags
        editTagManager.setTags(data.tags);

        document.getElementById('editTaskForm').action = '/tasks/' + data.id;
        openModal('editTaskModal');
    }

    // Event listeners for edit buttons
    // Event listeners for edit buttons (Removed - switched to inline onclick)
    // document.querySelectorAll('.edit-task-btn').forEach(button => {
    //     button.addEventListener('click', function() {
    //         const taskData = JSON.parse(this.getAttribute('data-task'));
    //         editTask(taskData);
    //     });
    // });

    // Make functions global
    window.toggleTask = toggleTask;
    window.editTask = editTask;
});
</script>
@endsection