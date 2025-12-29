@extends('layouts.app')

@section('title', 'Keuangan - PLFIS')
@section('page-title', 'Keuangan')

@section('content')
<style>
    /* --- Summary Cards --- */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }
    
    .summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    
    .summary-label {
        font-size: 12px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .summary-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .summary-icon.income {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success-color);
    }
    
    .summary-icon.expense {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger-color);
    }
    
    .summary-icon.balance {
        background: rgba(59, 130, 246, 0.15);
        color: var(--primary-color);
    }
    
    .summary-icon.count {
        background: rgba(139, 92, 246, 0.15);
        color: var(--secondary-color);
    }
    
    .summary-value {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }
    
    .summary-value.positive {
        color: var(--success-color);
    }
    
    .summary-value.negative {
        color: var(--danger-color);
    }
    
    .summary-change {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* --- Skeleton Loading --- */
    .skeleton-loader {
        padding: 20px;
    }
    
    .skeleton-row {
        display: grid;
        grid-template-columns: 120px 1fr 130px 150px 100px 180px 100px;
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        gap: 15px;
    }
    
    .skeleton-item {
        height: 16px;
        background: linear-gradient(90deg, var(--dark-bg) 25%, var(--dark-sidebar) 50%, var(--dark-bg) 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 4px;
    }
    
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* --- CSS Utama --- */
    .finance-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .add-transaction-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }

    .add-transaction-btn:hover {
        background: var(--secondary-color);
    }

    .finance-tabs {
        display: flex;
        gap: 10px;
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    
    .finance-tabs-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0;
    }

    .tab-btn {
        padding: 12px 24px;
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .tab-btn:hover,
    .tab-btn.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .transaction-table {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
    }

    .table-header {
        display: grid;
        grid-template-columns: 120px 1fr 130px 150px 100px 180px 100px;
        padding: 15px 20px;
        background: var(--dark-sidebar);
        font-weight: 600;
        font-size: 14px;
        color: var(--text-muted);
    }

    .table-row {
        display: grid;
        grid-template-columns: 120px 1fr 130px 150px 100px 180px 100px;
        padding: 15px 20px;
        border-top: 1px solid var(--border-color);
        transition: background 0.3s;
        align-items: center;
    }

    .table-row:hover {
        background: rgba(59, 130, 246, 0.05);
    }

    .transaction-type {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .type-income { background: rgba(16, 185, 129, 0.2); color: var(--success-color); }
    .type-expense { background: rgba(239, 68, 68, 0.2); color: var(--danger-color); }

    .amount-income { color: var(--success-color); font-weight: 600; }
    .amount-expense { color: var(--danger-color); font-weight: 600; }

    .action-btns { display: flex; gap: 8px; }

    .action-btn {
        padding: 6px 10px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        color: var(--text-light);
        cursor: pointer;
        transition: all 0.3s;
    }

    .action-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 72px;
        margin-bottom: 24px;
        opacity: 0.25;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .empty-state h3 {
        font-size: 20px;
        margin-bottom: 8px;
        color: var(--text-light);
    }
    
    .empty-state p {
        font-size: 14px;
        margin-bottom: 24px;
        color: var(--text-muted);
    }
    
    .empty-state-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 12px 28px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .empty-state-btn:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
    }

    /* --- Form & Modal Styles --- */
    .search-filter-bar {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        align-items: center;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 13px;
        color: var(--text-light);
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%;
        padding: 9px 12px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        color: var(--text-light);
        font-size: 13px;
        transition: all 0.3s;
    }
    
    .search-box {
        flex: 1;
        position: relative;
    }
    
    .search-input {
        width: 100%;
        padding: 10px 40px 10px 40px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-light);
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .search-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    
    .search-clear {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        display: none;
    }
    
    .search-clear:hover {
        color: var(--text-light);
    }
    
    .filter-toggle-btn {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 10px 16px;
        color: var(--text-light);
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 500;
    }
    
    .filter-toggle-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .filter-badge {
        background: var(--primary-color);
        color: white;
        border-radius: 10px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .export-btn {
        background: var(--success-color);
        border: none;
        border-radius: 10px;
        padding: 10px 16px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        font-size: 14px;
        font-weight: 500;
    }
    
    .export-btn:hover {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    /* Filter Panel */
    .filter-panel {
        position: fixed;
        right: -400px;
        top: 0;
        width: 400px;
        height: 100vh;
        background: var(--dark-card);
        border-left: 1px solid var(--border-color);
        z-index: 1001;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.2);
    }
    
    .filter-panel.active {
        right: 0;
    }
    
    .filter-panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        background: var(--dark-card);
        z-index: 10;
    }
    
    .filter-panel-title {
        font-size: 18px;
        font-weight: 600;
    }
    
    .filter-panel-body {
        padding: 20px;
    }
    
    .form-group {
        margin-bottom: 18px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    .filter-group-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-light);
        margin-bottom: 10px;
        display: block;
    }
    
    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .filter-chip {
        background: rgba(59, 130, 246, 0.15);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 12px;
        color: var(--primary-color);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .filter-chip-remove {
        background: none;
        border: none;
        color: var(--primary-color);
        cursor: pointer;
        padding: 0;
        font-size: 14px;
        line-height: 1;
    }
    
    .filter-chip-remove:hover {
        color: var(--danger-color);
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 0;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-group label {
        cursor: pointer;
        font-size: 14px;
    }
    
    /* Detail Drawer */
    .detail-drawer {
        position: fixed;
        right: -450px;
        top: 0;
        width: 450px;
        height: 100vh;
        background: var(--dark-card);
        border-left: 1px solid var(--border-color);
        z-index: 1002;
        transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.2);
    }
    
    .detail-drawer.active {
        right: 0;
    }
    
    .detail-drawer-header {
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        background: var(--dark-card);
        z-index: 10;
    }
    
    .detail-amount {
        font-size: 32px;
        font-weight: 700;
        margin: 12px 0;
        letter-spacing: -1px;
    }
    
    .detail-section {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .detail-section-title {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        font-size: 14px;
    }
    
    .detail-label {
        color: var(--text-muted);
    }
    
    .detail-value {
        color: var(--text-light);
        font-weight: 500;
    }
    
    /* Bulk Action Bar */
    .bulk-action-bar {
        position: fixed;
        top: 70px;
        left: 220px;
        right: 20px;
        background: var(--primary-color);
        border-radius: 12px;
        padding: 16px 24px;
        display: none;
        align-items: center;
        justify-content: space-between;
        z-index: 100;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        animation: slideDown 0.3s ease;
    }
    
    .bulk-action-bar.active {
        display: flex;
    }
    
    @keyframes slideDown {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .bulk-action-info {
        color: white;
        font-weight: 500;
    }
    
    .bulk-action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .bulk-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .bulk-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    /* Toast Notification */
    .toast-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .toast {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px 20px;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: toastSlideIn 0.3s ease;
    }
    
    @keyframes toastSlideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .toast.success { border-left: 4px solid var(--success-color); }
    .toast.error { border-left: 4px solid var(--danger-color); }
    .toast.info { border-left: 4px solid var(--primary-color); }
    
    .toast-icon {
        font-size: 20px;
    }
    
    .toast.success .toast-icon { color: var(--success-color); }
    .toast.error .toast-icon { color: var(--danger-color); }
    .toast.info .toast-icon { color: var(--primary-color); }
    
    .toast-content {
        flex: 1;
    }
    
    .toast-message {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 4px;
    }
    
    .toast-action {
        font-size: 12px;
        color: var(--primary-color);
        cursor: pointer;
        text-decoration: underline;
    }
    
    .toast-close {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 18px;
        padding: 0;
    }
    
    .toast-close:hover {
        color: var(--text-light);
    }
    
    /* Sortable Headers */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .sortable-header:hover {
        color: var(--primary-color);
    }
    
    .sort-icon {
        font-size: 10px;
        opacity: 0.5;
    }
    
    .sort-icon.active {
        opacity: 1;
        color: var(--primary-color);
    }
    
    /* Overlay */
    .panel-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        display: none;
        backdrop-filter: blur(2px);
    }
    
    .panel-overlay.active {
        display: block;
    }

    /* --- Form & Modal Styles --- */
    .type-selector {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        justify-content: center;
    }

    .type-option {
        flex: 1;
        padding: 8px 12px;
        border: 1.5px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: var(--dark-bg);
        font-size: 13px;
        font-weight: 500;
    }

    .type-option input[type="radio"] {
        display: none;
    }

    .type-option:hover {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }

    .type-option.active {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.1);
        color: var(--primary-color);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 10px; background: var(--dark-bg); border: 1px solid var(--border-color);
        border-radius: 6px; color: var(--text-light);
    }
    .form-input:focus, .form-select:focus { outline: none; border-color: var(--primary-color); }
    .btn-primary { background: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; width: 100%; }

    /* --- STANDARD MODAL CSS --- */
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
    /* ------------------------- */

    @media (max-width: 1200px) {
        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
        
        .table-header, .table-row { grid-template-columns: 1fr; gap: 10px; }
        .table-header { display: none; }
        .table-row { padding: 20px; }
    }
</style>

<!-- Summary Cards -->
<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Total Income</span>
            <div class="summary-icon income">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        <div class="summary-value positive">
            Rp {{ number_format($summary['income'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="summary-change">Total pemasukan bulan ini</div>
    </div>
    
    <div class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Total Expense</span>
            <div class="summary-icon expense">
                <i class="fas fa-arrow-up"></i>
            </div>
        </div>
        <div class="summary-value negative">
            Rp {{ number_format($summary['expenses'] ?? 0, 0, ',', '.') }}
        </div>
        <div class="summary-change">Total pengeluaran bulan ini</div>
    </div>
    
    <div class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Net Balance</span>
            <div class="summary-icon balance">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
        @php
            $netBalance = ($summary['income'] ?? 0) - ($summary['expenses'] ?? 0);
        @endphp
        <div class="summary-value {{ $netBalance >= 0 ? 'positive' : 'negative' }}">
            {{ $netBalance >= 0 ? '+' : '' }} Rp {{ number_format($netBalance, 0, ',', '.') }}
        </div>
        <div class="summary-change">Selisih income - expense</div>
    </div>
    
    <div class="summary-card">
        <div class="summary-header">
            <span class="summary-label">Transactions</span>
            <div class="summary-icon count">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="summary-value">
            {{ $transactions->total() }}
        </div>
        <div class="summary-change">Total transaksi tercatat</div>
    </div>
</div>

<div class="finance-header">
    <div>
        <h2 style="font-size: 20px; margin-bottom: 5px;">Transaksi Keuangan</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Kelola semua transaksi income dan expense</p>
    </div>
    <button class="add-transaction-btn" onclick="openModal('createModal')">
        <i class="fas fa-plus"></i>
        Tambah Transaksi
    </button>
</div>

<div class="finance-tabs-container">
    <div class="finance-tabs">
        <a href="{{ route('finance.index') }}" class="tab-btn {{ !request('type') || request('type') == 'all' ? 'active' : '' }}">
            <i class="fas fa-list"></i> Semua
        </a>
        <a href="{{ route('finance.index', ['type' => 'income']) }}" class="tab-btn {{ request('type') == 'income' ? 'active' : '' }}">
            <i class="fas fa-arrow-up"></i> Income
        </a>
        <a href="{{ route('finance.index', ['type' => 'expense']) }}" class="tab-btn {{ request('type') == 'expense' ? 'active' : '' }}">
            <i class="fas fa-arrow-down"></i> Expense
        </a>
    </div>
    
    <div class="search-box" style="max-width: 300px;">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="financeSearchInput" placeholder="Cari transaksi..." style="padding: 8px 35px 8px 35px; font-size: 13px;">
        <button class="search-clear" id="financeSearchClear" style="display: none;"><i class="fas fa-times"></i></button>
    </div>
</div>

<div class="transaction-table">
    <div class="table-header">
        <div>Tanggal</div>
        <div>Deskripsi</div>
        <div>Kategori</div>
        <div>Sumber/Tujuan</div>
        <div>Tipe</div>
        <div>Nominal</div>
        <div>Aksi</div>
    </div>

    @forelse($transactions as $transaction)
    <div class="table-row">
        <div>{{ \Carbon\Carbon::parse($transaction->transaction_date)->translatedFormat('d M Y') }}</div>
        <div>{{ $transaction->description ?? '-' }}</div>
        <div>{{ $transaction->category->name ?? '-' }}</div>
        <div style="font-size: 13px; color: var(--text-muted);">{{ $transaction->source ?? '-' }}</div>
        <div>
            <span class="transaction-type {{ $transaction->type == 'income' ? 'type-income' : 'type-expense' }}">
                {{ ucfirst($transaction->type) }}
            </span>
        </div>
        <div class="{{ $transaction->type == 'income' ? 'amount-income' : 'amount-expense' }}">
            {{ $transaction->type == 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
        </div>
        <div class="action-btns">
            <button class="action-btn" title="Edit" 
                data-transaction="{{ json_encode($transaction) }}"
                onclick="openEditModal(this)">
                <i class="fas fa-edit"></i>
            </button>
            <form action="{{ route('finance.destroy', $transaction->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="fas fa-wallet"></i>
        <h3>Belum Ada Transaksi</h3>
        <p>Mulai kelola keuangan Anda dengan menambahkan transaksi pertama</p>
        <button class="empty-state-btn" onclick="openModal('createModal')">
            <i class="fas fa-plus"></i>
            Tambah Transaksi Pertama
        </button>
    </div>
    @endforelse
</div>

<div style="margin-top: 20px;">
    {{ $transactions->withQueryString()->links() }}
</div>

<div id="createModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Transaksi</h3>
            <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('finance.store') }}" method="POST" id="createForm">
                @csrf
                <div class="type-selector">
                    <label class="type-option active" onclick="selectType(this)">
                        <input type="radio" name="type" value="income" checked>
                        <i class="fas fa-arrow-down"></i> Pemasukan
                    </label>
                    <label class="type-option" onclick="selectType(this)">
                        <input type="radio" name="type" value="expense">
                        <i class="fas fa-arrow-up"></i> Pengeluaran
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="amount" class="form-input" required step="0.01" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="transaction_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                    </div>
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
                    <label class="form-label">Sumber/Tujuan</label>
                    <input type="text" name="source" class="form-input" placeholder="Contoh: Gaji, Tokopedia">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-textarea" placeholder="Opsional" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('createModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('createForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Transaksi</h3>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="type-selector">
                    <label class="type-option" id="edit-type-income" onclick="selectType(this)">
                        <input type="radio" name="type" value="income">
                        <i class="fas fa-arrow-down"></i> Pemasukan
                    </label>
                    <label class="type-option" id="edit-type-expense" onclick="selectType(this)">
                        <input type="radio" name="type" value="expense">
                        <i class="fas fa-arrow-up"></i> Pengeluaran
                    </label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Jumlah (Rp)</label>
                        <input type="number" name="amount" id="edit-amount" class="form-input" required step="0.01">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="transaction_date" id="edit-date" class="form-input" required>
                    </div>
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
                    <label class="form-label">Sumber/Tujuan</label>
                    <input type="text" name="source" id="edit-source" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-description" class="form-textarea" rows="3"></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('editForm').submit()">Update</button>
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

    // Removed click-outside-to-close to prevent accidental closure
    // Modals can only be closed via Cancel button or X button
    // window.onclick = function(event) {
    //     if (event.target.classList.contains('modal-overlay')) {
    //         event.target.style.display = 'none';
    //     }
    // }
    // -----------------------------

    function selectType(element) {
        // Find inputs in the specific container
        const parent = element.closest('.type-selector');
        parent.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
        element.classList.add('active');
        element.querySelector('input').checked = true;
    }

    function openEditModal(btn) {
        const data = JSON.parse(btn.getAttribute('data-transaction'));
        populateEditModal(data);
    }

    function populateEditModal(data) {
        // Populate inputs
        document.getElementById('edit-amount').value = data.amount;
        document.getElementById('edit-category').value = data.category_id;
        document.getElementById('edit-date').value = data.transaction_date.substring(0, 10);
        document.getElementById('edit-source').value = data.source;
        document.getElementById('edit-description').value = data.description;
        
        // Handle Type
        if (data.type === 'income') {
            const incomeOpt = document.getElementById('edit-type-income');
            incomeOpt.classList.add('active');
            document.getElementById('edit-type-expense').classList.remove('active');
            incomeOpt.querySelector('input').checked = true;
        } else {
            const expenseOpt = document.getElementById('edit-type-expense');
            expenseOpt.classList.add('active');
            document.getElementById('edit-type-income').classList.remove('active');
            expenseOpt.querySelector('input').checked = true;
        }

        // Action URL
        document.getElementById('editForm').action = '/finance/' + data.id;

        openModal('editModal');
    }

    // Auto-open edit modal if redirected from edit route
    @if(session('edit_transaction_id'))
        // Fetch transaction data and open edit modal
        const transactionId = {{ session('edit_transaction_id') }};
        
        fetch(`/finance/${transactionId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(transaction => {
            populateEditModal(transaction);
        })
        .catch(error => {
            console.error('Error loading transaction:', error);
            alert('Gagal memuat data transaksi');
        });
    @endif
    
    // ===== REAL-TIME FINANCE SEARCH =====
    const financeSearchInput = document.getElementById('financeSearchInput');
    const financeSearchClear = document.getElementById('financeSearchClear');
    const tableRows = document.querySelectorAll('.table-row');
    
    if (financeSearchInput) {
        financeSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            financeSearchClear.style.display = searchTerm ? 'block' : 'none';
            
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const description = row.querySelector('div:nth-child(2)')?.textContent.toLowerCase() || '';
                const category = row.querySelector('div:nth-child(3)')?.textContent.toLowerCase() || '';
                const source = row.querySelector('div:nth-child(4)')?.textContent.toLowerCase() || '';
                const amount = row.querySelector('div:nth-child(6)')?.textContent.toLowerCase() || '';
                
                const matches = description.includes(searchTerm) || 
                              category.includes(searchTerm) || 
                              source.includes(searchTerm) ||
                              amount.includes(searchTerm);
                
                if (matches || searchTerm === '') {
                    row.style.display = 'grid';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                if (visibleCount === 0 && searchTerm !== '') {
                    emptyState.style.display = 'block';
                    const h3 = emptyState.querySelector('h3');
                    const p = emptyState.querySelector('p');
                    if (h3) h3.textContent = 'Tidak ada hasil';
                    if (p) p.textContent = `Tidak ditemukan transaksi dengan kata kunci "${searchTerm}"`;
                } else if (visibleCount === 0) {
                    emptyState.style.display = 'block';
                } else {
                    emptyState.style.display = 'none';
                }
            }
        });
        
        financeSearchClear.addEventListener('click', function() {
            financeSearchInput.value = '';
            financeSearchClear.style.display = 'none';
            tableRows.forEach(row => row.style.display = 'grid');
            const emptyState = document.querySelector('.empty-state');
            if (emptyState && tableRows.length > 0) {
                emptyState.style.display = 'none';
            }
        });
    }
</script>
@endsection