// ===== Finance CRUD Advanced Features JavaScript =====
// Add this to resources/js/finance-advanced.js or directly in @section('scripts')

// ===== SEARCH FUNCTIONALITY =====
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const searchClear = document.getElementById('searchClear');

if (searchInput) {
    // Show/hide clear button based on input value
    if (searchInput.value) {
        searchClear.style.display = 'block';
    }

    searchInput.addEventListener('input', function () {
        const value = this.value;
        searchClear.style.display = value ? 'block' : 'none';

        // Debounce search - wait 300ms after user stops typing
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const url = new URL(window.location.href);
            if (value) {
                url.searchParams.set('search', value);
            } else {
                url.searchParams.delete('search');
            }
            window.location.href = url.toString();
        }, 300);
    });

    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        searchClear.style.display = 'none';
        const url = new URL(window.location.href);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    });
}

// ===== FILTER PANEL =====
const filterPanel = document.getElementById('filterPanel');
const filterToggleBtn = document.getElementById('filterToggleBtn');
const panelOverlay = document.getElementById('panelOverlay');

function openFilterPanel() {
    if (filterPanel) {
        filterPanel.classList.add('active');
        panelOverlay.classList.add('active');
    }
}

function closeFilterPanel() {
    if (filterPanel) {
        filterPanel.classList.remove('active');
        panelOverlay.classList.remove('active');
    }
}

if (filterToggleBtn) {
    filterToggleBtn.addEventListener('click', openFilterPanel);
}

if (panelOverlay) {
    panelOverlay.addEventListener('click', function () {
        closeFilterPanel();
        closeDetailDrawer();
    });
}

function applyFilters() {
    const url = new URL(window.location.href);

    const dateFrom = document.getElementById('filterDateFrom')?.value;
    const dateTo = document.getElementById('filterDateTo')?.value;
    const amountMin = document.getElementById('filterAmountMin')?.value;
    const amountMax = document.getElementById('filterAmountMax')?.value;

    if (dateFrom) url.searchParams.set('date_from', dateFrom);
    if (dateTo) url.searchParams.set('date_to', dateTo);
    if (amountMin) url.searchParams.set('amount_min', amountMin);
    if (amountMax) url.searchParams.set('amount_max', amountMax);

    // Get selected categories
    const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked'))
        .map(cb => cb.value);
    if (selectedCategories.length > 0) {
        url.searchParams.set('categories', selectedCategories.join(','));
    }

    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    url.searchParams.delete('amount_min');
    url.searchParams.delete('amount_max');
    url.searchParams.delete('categories');
    window.location.href = url.toString();
}

// ===== DETAIL DRAWER =====
const detailDrawer = document.getElementById('detailDrawer');
let currentTransactionId = null;

function openDetailDrawer(transaction) {
    if (!detailDrawer) return;

    currentTransactionId = transaction.id;

    // Set type badge
    const typeEl = document.getElementById('detailType');
    if (typeEl) {
        typeEl.textContent = transaction.type === 'income' ? 'Income' : 'Expense';
        typeEl.className = 'transaction-type ' + (transaction.type === 'income' ? 'type-income' : 'type-expense');
    }

    // Set amount
    const amountEl = document.getElementById('detailAmount');
    if (amountEl) {
        const amount = (transaction.type === 'income' ? '+' : '-') + ' Rp ' + Number(transaction.amount).toLocaleString('id-ID');
        amountEl.textContent = amount;
        amountEl.className = 'detail-amount ' + (transaction.type === 'income' ? 'positive' : 'negative');
    }

    // Set details
    if (document.getElementById('detailCategory')) {
        document.getElementById('detailCategory').textContent = transaction.category?.name || '-';
    }
    if (document.getElementById('detailDate')) {
        document.getElementById('detailDate').textContent = new Date(transaction.transaction_date).toLocaleDateString('id-ID');
    }
    if (document.getElementById('detailSource')) {
        document.getElementById('detailSource').textContent = transaction.source || '-';
    }
    if (document.getElementById('detailDescription')) {
        document.getElementById('detailDescription').textContent = transaction.description || 'Tidak ada deskripsi';
    }

    detailDrawer.classList.add('active');
    panelOverlay.classList.add('active');
}

function closeDetailDrawer() {
    if (detailDrawer) {
        detailDrawer.classList.remove('active');
        panelOverlay.classList.remove('active');
        currentTransactionId = null;
    }
}

function editFromDetail() {
    if (currentTransactionId) {
        closeDetailDrawer();
        // Trigger edit modal - assumes editTransaction function exists
        const row = document.querySelector(`[data-transaction-id="${currentTransactionId}"]`);
        if (row) {
            const transactionData = row.dataset.transaction;
            if (transactionData && typeof editTransaction === 'function') {
                editTransaction(JSON.parse(transactionData));
            }
        }
    }
}

function deleteFromDetail() {
    if (currentTransactionId && confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/finance/' + currentTransactionId;
        form.innerHTML = '@csrf @method("DELETE")';
        document.body.appendChild(form);
        form.submit();
    }
}

// Add click handlers to table rows
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table-row').forEach(row => {
        row.addEventListener('click', function (e) {
            // Don't open if clicking checkbox or action buttons
            if (e.target.closest('.row-checkbox') || e.target.closest('.action-btns')) {
                return;
            }

            const transactionData = this.dataset.transaction;
            if (transactionData) {
                try {
                    openDetailDrawer(JSON.parse(transactionData));
                } catch (error) {
                    console.error('Error parsing transaction data:', error);
                }
            }
        });
    });
});

// ===== BULK ACTIONS =====
const selectAllCheckbox = document.getElementById('selectAll');
const rowCheckboxes = document.querySelectorAll('.row-checkbox');
const bulkActionBar = document.getElementById('bulkActionBar');
const selectedCountSpan = document.getElementById('selectedCount');
let selectedIds = [];

function updateBulkActionBar() {
    selectedIds = Array.from(rowCheckboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    if (bulkActionBar && selectedCountSpan) {
        if (selectedIds.length > 0) {
            bulkActionBar.classList.add('active');
            selectedCountSpan.textContent = selectedIds.length;
        } else {
            bulkActionBar.classList.remove('active');
        }
    }
}

if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function () {
        rowCheckboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActionBar();
    });
}

rowCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateBulkActionBar);
});

function cancelSelection() {
    rowCheckboxes.forEach(cb => cb.checked = false);
    if (selectAllCheckbox) selectAllCheckbox.checked = false;
    updateBulkActionBar();
}

function bulkDelete() {
    if (selectedIds.length === 0) return;

    if (confirm(`Hapus ${selectedIds.length} transaksi yang dipilih?`)) {
        showToast('Fitur bulk delete memerlukan endpoint backend', 'info');
        // TODO: Implement bulk delete endpoint
        // You can add a form submission or AJAX call here
    }
}

// ===== TOAST NOTIFICATIONS =====
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    const iconClass = type === 'success' ? 'fa-check-circle' :
        type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

    toast.innerHTML = `
        <i class="fas ${iconClass} toast-icon"></i>
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;

    container.appendChild(toast);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Show toast for Laravel session messages
document.addEventListener('DOMContentLoaded', function () {
    // Check for success message
    const successMessage = document.querySelector('meta[name="success-message"]');
    if (successMessage) {
        showToast(successMessage.content, 'success');
    }

    // Check for error message
    const errorMessage = document.querySelector('meta[name="error-message"]');
    if (errorMessage) {
        showToast(errorMessage.content, 'error');
    }
});

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function (e) {
    // Ctrl/Cmd + K: Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (searchInput) {
            searchInput.focus();
        }
    }

    // Ctrl/Cmd + F: Open filters
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        openFilterPanel();
    }

    // ESC: Close panels
    if (e.key === 'Escape') {
        closeFilterPanel();
        closeDetailDrawer();
    }
});

console.log('Finance Advanced Features Loaded');
