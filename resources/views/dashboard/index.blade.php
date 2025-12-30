@extends('layouts.app')

@section('title', 'Dashboard - PLFIS')
@section('page-title', 'Dashboard')

@section('content')
<style>
    /* Stat Cards - Fit within viewport */
    .stat-cards-container {
        margin-bottom: 30px;
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
    }

    .stat-card {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 16px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .stat-title {
        font-size: 11px;
        color: var(--text-muted);
        font-weight: 500;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .stat-icon.primary {
        background: rgba(59, 130, 246, 0.2);
        color: var(--primary-color);
    }

    .stat-icon.success {
        background: rgba(16, 185, 129, 0.2);
        color: var(--success-color);
    }

    .stat-icon.danger {
        background: rgba(239, 68, 68, 0.2);
        color: var(--danger-color);
    }

    .stat-icon.warning {
        background: rgba(245, 158, 11, 0.2);
        color: var(--warning-color);
    }

    .stat-value {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .stat-change {
        font-size: 10px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .stat-change.positive {
        color: var(--success-color);
    }

    .stat-change.negative {
        color: var(--danger-color);
    }

    .chart-container {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        max-width: 100%;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 17px;
        font-weight: 600;
        letter-spacing: -0.3px;
    }

    .chart-filters {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 7px 14px;
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: 10px;
        color: var(--primary-color);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 13px;
        font-weight: 500;
    }

    .filter-btn:hover {
        background: rgba(59, 130, 246, 0.15);
        transform: translateY(-1px);
    }
    
    .filter-btn.active {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* AI Insight Theme Variables */
    :root {
        --ai-glass-bg: rgba(255, 255, 255, 0.03);
        --ai-glass-border: rgba(255, 255, 255, 0.08);
        --ai-text-primary: #ffffff;
        --ai-text-secondary: rgba(255, 255, 255, 0.5);
        --ai-text-content: rgba(255, 255, 255, 0.85);
        --ai-btn-bg: rgba(255, 255, 255, 0.08);
        --ai-btn-border: rgba(255, 255, 255, 0.1);
        --ai-btn-hover: rgba(255, 255, 255, 0.12);
        --ai-item-bg: rgba(0, 0, 0, 0.2);
        --ai-item-border: rgba(255, 255, 255, 0.05);
    }

    [data-theme="light"] {
        --ai-glass-bg: rgba(255, 255, 255, 0.8);
        --ai-glass-border: rgba(0, 0, 0, 0.05);
        --ai-text-primary: #1e293b;
        --ai-text-secondary: #64748b;
        --ai-text-content: #334155;
        --ai-btn-bg: #f1f5f9;
        --ai-btn-border: #e2e8f0;
        --ai-btn-hover: #e2e8f0;
        --ai-item-bg: #f8fafc;
        --ai-item-border: #e2e8f0;
    }

    .ai-insight {
        background: var(--ai-glass-bg);
        border: 1px solid var(--ai-glass-border);
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        position: relative;
        overflow: hidden;
    }

    /* Subtle gradient overlay */
    .ai-insight::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: linear-gradient(180deg, var(--ai-glass-border) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
        opacity: 0.5;
    }

    .ai-insight-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }

    .ai-icon {
        width: 52px;
        height: 52px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .ai-insight-title {
        font-size: 19px;
        font-weight: 700;
        letter-spacing: -0.02em;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: var(--ai-text-primary);
        margin-bottom: 2px;
    }
    
    .ai-insight-subtitle {
        font-size: 13px;
        color: var(--ai-text-secondary);
        font-weight: 500;
    }

    .ai-insight-content {
        color: var(--ai-text-content);
        font-size: 15px;
        line-height: 1.6;
        margin-bottom: 24px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        position: relative;
        z-index: 1;
        padding-left: 4px;
    }

    .ai-insight-btn {
        background: var(--ai-btn-bg);
        color: var(--ai-text-primary);
        border: 1px solid var(--ai-btn-border);
        padding: 12px 24px;
        border-radius: 14px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        position: relative;
        z-index: 1;
    }

    .ai-insight-btn:hover {
        background: var(--ai-btn-hover);
        transform: scale(1.01);
    }
    
    .ai-list-item {
        background: var(--ai-item-bg);
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 8px;
        border: 1px solid var(--ai-item-border);
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }

    .quick-action-btn {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        color: var(--text-light);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .quick-action-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
    }

    .quick-action-icon {
        width: 44px;
        height: 44px;
        background: rgba(59, 130, 246, 0.15);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: var(--primary-color);
        transition: all 0.3s;
    }

    .quick-action-text {
        font-weight: 500;
        font-size: 14px;
    }
    
    /* Two Column Tables Layout */
    .tables-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 30px;
    }
    
    .table-container {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .table-title {
        font-size: 17px;
        font-weight: 600;
        letter-spacing: -0.3px;
    }

    .chart-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 300px;
        color: var(--text-muted);
    }

    .chart-no-data {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 300px;
        color: var(--text-muted);
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        visibility: hidden;
        opacity: 0;
        transition: visibility 0s, opacity 0.3s;
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    .modal-overlay.active {
        visibility: visible;
        opacity: 1;
    }

    .modal-content {
        background: var(--dark-card);
        padding: 0;
        border-radius: 16px;
        width: 90%;
        max-width: 420px;
        max-height: 85vh;
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        transform: scale(0.95) translateY(-20px);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .modal-overlay.active .modal-content {
        transform: scale(1) translateY(0);
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

    /* Form Styles */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
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

    .form-input:focus, .form-select:focus, .form-textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.05);
    }

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

    .modal-footer {
        margin-top: 20px;
        text-align: right;
    }

    /* Form Styles */
    .form-group { 
        margin-bottom: 15px; 
    }
    
    .form-label { 
        display: block; 
        margin-bottom: 5px; 
        font-weight: 500; 
        font-size: 14px; 
    }
    
    .form-input, .form-select, .form-textarea {
        width: 100%; 
        padding: 10px; 
        background: var(--dark-bg); 
        border: 1px solid var(--border-color);
        border-radius: 6px; 
        color: var(--text-light);
    }
    
    .form-textarea { 
        resize: vertical; 
        min-height: 100px; 
    }
    
    .btn-primary { 
        background: var(--primary-color); 
        color: white; 
        border: none; 
        padding: 10px 20px; 
        border-radius: 6px; 
        cursor: pointer; 
        width: 100%; 
    }
    
    .type-selector {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .type-option {
        flex: 1;
        padding: 15px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s;
    }

    .type-option.active {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.1);
    }

    .type-option input[type="radio"] { display: none; }
    
    .tags-input-container {
        display: flex; 
        flex-wrap: wrap; 
        gap: 8px; 
        padding: 8px;
        background: var(--dark-bg); 
        border: 1px solid var(--border-color); 
        border-radius: 8px;
        min-height: 40px;
    }
    
    .tag-item {
        padding: 4px 10px; 
        background: var(--primary-color); 
        border-radius: 20px;
        font-size: 12px; 
        display: flex; 
        align-items: center; 
        gap: 6px;
    }
    
    .tag-remove { 
        cursor: pointer; 
        font-weight: bold; 
    }
    
    .tag-input { 
        flex: 1; 
        border: none; 
        background: none; 
        color: var(--text-light); 
        outline: none; 
        min-width: 80px; 
    }

    .color-picker { 
        display: flex; 
        gap: 10px; 
        flex-wrap: wrap; 
    }
    
    .color-option {
        width: 30px; 
        height: 30px; 
        border-radius: 6px; 
        cursor: pointer; 
        border: 2px solid transparent; 
        transition: all 0.3s;
    }
    
    .color-option.selected { 
        border-color: white; 
        box-shadow: 0 0 0 2px var(--primary-color); 
    }
    
    .checkbox-group { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    
    .checkbox-group input[type="checkbox"] { 
        width: 16px; 
        height: 16px; 
        cursor: pointer; 
    }

    /* Responsive Grid */
    @media (max-width: 1400px) {
        .dashboard-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 968px) {
        .dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .stat-card {
            padding: 14px;
        }

        .stat-value {
            font-size: 20px;
        }

        /* Stack charts vertically on mobile */
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        .tables-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Stats Cards -->
<div class="stat-cards-container">
    <div class="dashboard-grid">
    <!-- Saldo Bersih -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Saldo Bersih</span>
            <div class="stat-icon primary">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
        <div class="stat-value">Rp {{ number_format($summary['net_income'], 0, ',', '.') }}</div>
        @if($monthlyChange > 0)
            <div class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>+{{ number_format($monthlyChange, 1) }}% dari bulan lalu</span>
            </div>
        @elseif($monthlyChange < 0)
            <div class="stat-change negative">
                <i class="fas fa-arrow-down"></i>
                <span>{{ number_format($monthlyChange, 1) }}% dari bulan lalu</span>
            </div>
        @else
            <div class="stat-change">
                <i class="fas fa-minus"></i>
                <span>0% dari bulan lalu</span>
            </div>
        @endif
    </div>

    <!-- Pengeluaran Bulan Ini -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Pengeluaran</span>
            <div class="stat-icon danger">
                <i class="fas fa-arrow-down"></i>
            </div>
        </div>
        <div class="stat-value">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</div>
        <div class="stat-change {{ ($summary['income'] > 0 && $summary['expenses'] > $summary['income']) ? 'negative' : 'positive' }}">
            @if($summary['income'] > 0)
                <i class="fas fa-percentage"></i>
                <span>{{ number_format(($summary['expenses'] / $summary['income']) * 100, 1) }}% dari Income</span>
            @else
                <span>Belum ada income</span>
            @endif
        </div>
    </div>

    <!-- Dana Darurat -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Dana Darurat</span>
            <div class="stat-icon warning">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
        <div class="stat-value">{{ $emergencyFundData['progress_percentage'] ?? 0 }}%</div>
        <div class="stat-change">
            <span>Target: Rp {{ number_format($emergencyFundData['target_amount'] ?? 0, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Task Hari Ini -->
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Tugas Aktif</span>
            <div class="stat-icon success">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="stat-value">{{ $todayTasks->count() }}</div>
        <div class="stat-change">
            <i class="fas fa-clock"></i>
            <span>{{ $todayTasks->count() }} tugas aktif</span>
        </div>
    </div>

    <!-- Total Digital Assets -->
    <a href="{{ route('digital-accounts.index') }}" class="stat-card" style="text-decoration: none; border: 1px solid var(--info-color);">
        <div class="stat-header">
            <span class="stat-title">Aset Digital</span>
            <div class="stat-icon primary" style="background: rgba(6, 182, 212, 0.2); color: var(--info-color);">
                <i class="fas fa-globe"></i>
            </div>
        </div>
        <div class="stat-value" style="color: var(--text-light);">Rp {{ number_format($totalDigitalAssets ?? 0, 0, ',', '.') }}</div>
        <div class="stat-change">
            <i class="fas fa-wallet"></i>
            <span style="color: var(--text-muted);">Estimasi (IDR)</span>
        </div>
    </a>

    <!-- Pinned Notes -->
    <a href="{{ route('notes.index') }}" class="stat-card" style="text-decoration: none; border: 1px solid var(--warning-color);">
        <div class="stat-header">
            <span class="stat-title">Catatan Pin</span>
            <div class="stat-icon warning">
                <i class="fas fa-thumbtack"></i>
            </div>
        </div>
        <div class="stat-value" style="color: var(--text-light);">{{ $pinnedNotes->count() }}</div>
        <div class="stat-change">
            <i class="fas fa-sticky-note"></i>
            <span style="color: var(--text-muted);">Catatan di-pin</span>
        </div>
    </a>
    </div>
</div>

<!-- Charts Grid -->
<div style="display: grid; grid-template-columns: 4fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Cashflow Chart (Left) -->
    <div class="chart-container" style="margin-bottom: 0; padding-left: 30px;">
        <div class="chart-header">
            <h2 class="chart-title">
                <i class="fas fa-chart-line" style="color: var(--primary-color); margin-right: 10px;"></i>
                Cashflow
            </h2>
            <div class="chart-filters">
                <button class="filter-btn active" data-period="weekly">Mingguan</button>
                <button class="filter-btn" data-period="monthly">Bulanan</button>
                <button class="filter-btn" data-period="yearly">Tahunan</button>
            </div>
        </div>
        <div style="height: 300px; position: relative;">
            <div id="cashflowLoading" class="chart-loading">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Memuat data...
            </div>
            <canvas id="cashflowChart" style="display: none;"></canvas>
        </div>
    </div>

    <!-- Expense by Category (Right) -->
    <div class="chart-container" style="margin-bottom: 0;">
        <div class="chart-header">
            <h2 class="chart-title">
                <i class="fas fa-chart-pie" style="color: var(--secondary-color); margin-right: 10px;"></i>
                Pengeluaran per Kategori
            </h2>
        </div>
        
        <!-- Total Display Above Chart -->
        <div id="categoryTotalDisplay" style="text-align: center; margin-bottom: 15px; display: none;">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                Total Pengeluaran
            </div>
            <div id="categoryTotalAmount" style="font-size: 24px; font-weight: 700; color: var(--text-light); letter-spacing: -0.5px;">
                Rp 0
            </div>
        </div>
        
        <div style="height: 250px; position: relative;">
            <div id="categoryLoading" class="chart-loading" style="height: 250px;">
                <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-right: 10px;"></i>
                Memuat data...
            </div>
            <canvas id="categoryChart" style="display: none;"></canvas>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <div class="quick-action-btn" id="addIncomeBtn">
        <div class="quick-action-icon" style="background: rgba(16, 185, 129, 0.2); color: var(--success-color);">
            <i class="fas fa-plus"></i>
        </div>
        <span class="quick-action-text">Tambah Income</span>
    </div>

    <div class="quick-action-btn" id="addExpenseBtn">
        <div class="quick-action-icon" style="background: rgba(239, 68, 68, 0.2); color: var(--danger-color);">
            <i class="fas fa-minus"></i>
        </div>
        <span class="quick-action-text">Tambah Expense</span>
    </div>

    <div class="quick-action-btn" id="addNoteBtn">
        <div class="quick-action-icon" style="background: rgba(245, 158, 11, 0.2); color: var(--warning-color);">
            <i class="fas fa-sticky-note"></i>
        </div>
        <span class="quick-action-text">Catatan Baru</span>
    </div>

    <div class="quick-action-btn" id="addTaskBtn">
        <div class="quick-action-icon" style="background: rgba(6, 182, 212, 0.2); color: var(--info-color);">
            <i class="fas fa-calendar-plus"></i>
        </div>
        <span class="quick-action-text">Task Baru</span>
    </div>

    <!-- AI Voice Input -->
    <div class="quick-action-btn" id="aiVoiceBtn">
        <div class="quick-action-icon" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(139, 92, 246, 0.2)); color: #d946ef;">
            <i class="fas fa-microphone"></i>
        </div>
        <span class="quick-action-text">AI Voice</span>
    </div>
</div>

<!-- AI Insight -->
<div class="ai-insight">
    <div class="ai-insight-header">
        <div class="ai-icon">
            <i class="fas fa-sparkles"></i>
        </div>
        <div>
            <div class="ai-insight-title">AI Insight</div>
            <div class="ai-insight-subtitle">Analisis cerdas aktivitas terakhir Anda</div>
        </div>
    </div>
    
    <div class="ai-insight-content">
        @if($latestAiLog)
            @php
                $jsonDecoded = json_decode($latestAiLog->response, true);
            @endphp

            @if(json_last_error() === JSON_ERROR_NONE && is_array($jsonDecoded))
                <!-- Handle Multi-Intent JSON (New Structure) -->
                @if(isset($jsonDecoded['intent']) && isset($jsonDecoded['data']))
                    @php 
                        $intent = $jsonDecoded['intent'];
                        $data = $jsonDecoded['data'];
                        $items = is_array($data) && isset($data[0]) ? $data : [$data]; // Ensure array for list
                        if ($intent === 'emergency_fund') $items = [$data]; // Special case for object data
                    @endphp

                    <p style="margin-bottom: 12px; font-weight: 600; opacity: 0.9;">
                        @if($intent === 'transaction') Transaksi Baru Dideteksi:
                        @elseif($intent === 'task') Tugas Baru Dideteksi:
                        @elseif($intent === 'emergency_fund') Update Dana Darurat:
                        @else Info Penting:
                        @endif
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($items as $item)
                            <div class="ai-list-item">
                                <div style="color: #60a5fa; margin-top: 2px;"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    @if($intent === 'transaction')
                                        <div style="font-weight: 600; font-size: 14px;">{{ $item['description'] ?? '-' }}</div>
                                        <div style="font-size: 13px; opacity: 0.7;">
                                            <span style="text-transform: capitalize;">{{ $item['type'] ?? 'unknown' }}</span> &bull; 
                                            Rp {{ number_format($item['amount'] ?? 0, 0, ',', '.') }}
                                        </div>
                                    @elseif($intent === 'task')
                                        <div style="font-weight: 600; font-size: 14px;">{{ $item['title'] ?? 'Tugas' }}</div>
                                        <div style="font-size: 13px; opacity: 0.7;">Tenggat: {{ $item['due_date'] ?? 'No Date' }}</div>
                                    @elseif($intent === 'emergency_fund')
                                        @if(($item['action'] ?? '') === 'calculate_recommendation')
                                            <div style="font-weight: 600;">Menghitung Rekomendasi</div>
                                        @else
                                            <div style="font-weight: 600; text-transform: capitalize;">{{ str_replace('_', ' ', $item['action'] ?? 'update') }}</div>
                                            <div style="font-size: 13px; opacity: 0.7;">Rp {{ number_format($item['amount'] ?? 0, 0, ',', '.') }}</div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                <!-- Handle Legacy Layout (Direct Array of Transactions) -->
                @elseif(isset($jsonDecoded[0]) && isset($jsonDecoded[0]['type']))
                     <p style="margin-bottom: 12px; font-weight: 600;">Menambahkan:</p>
                     <div style="display: flex; flex-direction: column; gap: 8px;">
                        @foreach($jsonDecoded as $item)
                            <div class="ai-list-item">
                                <div style="color: #60a5fa; margin-top: 2px;"><i class="fas fa-check-circle"></i></div>
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;">{{ $item['description'] ?? '-' }}</div>
                                    <div style="font-size: 13px; opacity: 0.7;">
                                        <span style="text-transform: capitalize;">{{ $item['type'] }}</span> &bull; 
                                        Rp {{ number_format($item['amount'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback if JSON but unknown structure -->
                   {!! Str::markdown(Str::limit($latestAiLog->response, 300)) !!}
                @endif
            @else
                <!-- Regular Text Response -->
                {!! Str::markdown(Str::limit($latestAiLog->response, 300)) !!}
            @endif
        @else
            <p style="opacity: 0.7; text-align: center; padding: 10px;">Belum ada analisis. Mulai percakapan untuk mendapatkan insight cerdas.</p>
        @endif
    </div>
    
    <button class="ai-insight-btn" id="aiInsightBtn">
        <i class="fas fa-comments"></i> Lanjutkan Percakapan
    </button>
</div>

<!-- Two Column Tables -->
<div class="tables-grid">
    <!-- Financial Transactions Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">
                <i class="fas fa-exchange-alt" style="color: var(--primary-color); margin-right: 10px;"></i>
                Transaksi Terakhir
            </h3>
            <a href="{{ route('finance.index') }}" style="color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: 500;">
                Lihat Semua <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
            </a>
        </div>
        <div style="max-height: 400px; overflow-y: auto;">
            @php
                $recentTransactions = \App\Models\FinancialTransaction::with('category')->where('user_id', auth()->id())->orderBy('transaction_date', 'desc')->limit(5)->get();
            @endphp
            @if($recentTransactions->count() > 0)
                @foreach($recentTransactions as $transaction)
                <div style="padding: 14px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;" onmouseover="this.style.background='var(--dark-bg)'" onmouseout="this.style.background='transparent'">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: {{ $transaction->type === 'income' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }};">
                            <i class="fas fa-{{ $transaction->type === 'income' ? 'arrow-down' : 'arrow-up' }}" style="color: {{ $transaction->type === 'income' ? 'var(--success-color)' : 'var(--danger-color)' }};"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 500; font-size: 14px; margin-bottom: 2px;">{{ $transaction->category->name ?? 'Tanpa Kategori' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $transaction->transaction_date->format('d M Y') }}</div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600; font-size: 15px; color: {{ $transaction->type === 'income' ? 'var(--success-color)' : 'var(--danger-color)' }};">
                            {{ $transaction->type === 'income' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </div>
                        @if($transaction->source)
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ $transaction->source }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>Belum ada transaksi</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Notes Table -->
    <div class="table-container">
        <div class="table-header">
            <h3 class="table-title">
                <i class="fas fa-sticky-note" style="color: var(--warning-color); margin-right: 10px;"></i>
                Catatan Terakhir
            </h3>
            <a href="{{ route('notes.index') }}" style="color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: 500;">
                Lihat Semua <i class="fas fa-arrow-right" style="font-size: 11px;"></i>
            </a>
        </div>
        <div style="max-height: 400px; overflow-y: auto;">
            @php
                $recentNotes = \App\Models\Note::where('user_id', auth()->id())->orderBy('created_at', 'desc')->limit(5)->get();
            @endphp
            @if($recentNotes->count() > 0)
                @foreach($recentNotes as $note)
                <div style="padding: 14px; border-bottom: 1px solid var(--border-color); transition: background 0.2s; cursor: pointer;" onclick="window.location='{{ route('notes.index') }}'" onmouseover="this.style.background='var(--dark-bg)'" onmouseout="this.style.background='transparent'">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                        <div style="font-weight: 500; font-size: 14px; flex: 1;">{{ Str::limit($note->title, 40) }}</div>
                        @if($note->is_pinned)
                        <i class="fas fa-thumbtack" style="color: var(--warning-color); font-size: 12px; margin-left: 8px;"></i>
                        @endif
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 8px;">
                        {{ Str::limit(strip_tags($note->content), 80) }}
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 11px; color: var(--text-muted);">
                            {{ $note->created_at->diffForHumans() }}
                        </div>
                        @if($note->tags && count($note->tags) > 0)
                        <div style="display: flex; gap: 4px;">
                            @foreach(array_slice($note->tags, 0, 2) as $tag)
                            <span style="font-size: 10px; padding: 2px 8px; background: rgba(59, 130, 246, 0.15); color: var(--primary-color); border-radius: 10px;">{{ $tag }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i class="fas fa-sticky-note" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>Belum ada catatan</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- AI Voice Overlay -->
<div id="aiVoiceOverlay" class="modal-overlay">
    <div class="modal-content" style="text-align: center; max-width: 400px;">
        <div style="font-size: 60px; color: var(--primary-color); margin-bottom: 20px;" id="aiVoiceIcon">
            <i class="fas fa-microphone-alt"></i>
        </div>
        <h3 id="aiVoiceStatus">Mendengarkan...</h3>
        <p id="aiVoiceText" style="color: var(--text-muted); min-height: 40px; margin-top: 10px;">Katakan sesuatu...</p>
        <div id="aiVoiceWave" style="height: 40px; display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 20px;">
            <div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div><div class="wave-bar"></div>
        </div>
        <button class="btn-primary" id="stopVoiceBtn" style="margin-top: 20px; background: var(--danger-color);">Stop</button>
    </div>
</div>

<style>
    .wave-bar {
        width: 6px; height: 10px; background: var(--primary-color); border-radius: 3px;
        animation: wave 1s infinite ease-in-out;
    }
    .wave-bar:nth-child(2) { animation-delay: 0.1s; }
    .wave-bar:nth-child(3) { animation-delay: 0.2s; }
    .wave-bar:nth-child(4) { animation-delay: 0.3s; }
    .wave-bar:nth-child(5) { animation-delay: 0.4s; }
    @keyframes wave {
        0%, 100% { height: 10px; }
        50% { height: 30px; }
    }
</style>

<!-- Income/Expense Modal -->
<div id="financeModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="financeModalTitle">Tambah Transaksi</h3>
            <button class="modal-close" id="closeFinanceModal">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('finance.store') }}" method="POST" id="financeForm">
                @csrf
                <div class="type-selector">
                    <label class="type-option active" id="incomeOption">
                        <input type="radio" name="type" value="income" checked>
                        <i class="fas fa-arrow-down"></i> Pemasukan
                    </label>
                    <label class="type-option" id="expenseOption">
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
            <button type="button" class="btn-cancel" id="cancelFinanceModal">Batal</button>
            <button type="button" class="btn-primary" onclick="document.getElementById('financeForm').submit()">Simpan</button>
        </div>
    </div>
</div>

<!-- Note Modal -->
<div id="noteModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title">Buat Catatan Baru</h3>
            <button class="modal-close" id="closeNoteModal">&times;</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('notes.store') }}" method="POST" id="noteForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-input" placeholder="Judul catatan..." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Isi Catatan</label>
                    <textarea name="content" class="form-textarea" placeholder="Tulis catatan Anda di sini..." rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tags</label>
                    <div class="tags-input-container" id="note-tags-container">
                        <input type="text" class="tag-input" id="note-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="note-tags-hidden-inputs"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Warna</label>
                    <div class="color-picker" id="note-color-picker">
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981"></div>
                        <div class="color-option" style="background: #3b82f6;" data-color="#3b82f6"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899"></div>
                        <div class="color-option selected" style="background: transparent; border: 2px dashed var(--border-color);" data-color=""></div>
                    </div>
                    <input type="hidden" name="color" id="note-color-input" value="">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_pinned" id="note-pinned" value="1">
                        <label for="note-pinned">Pin catatan ini</label>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Simpan Catatan</button>
            </div>
        </form>
    </div>
</div>

<!-- Task Modal -->
<div id="taskModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Task Baru</h3>
            <button class="modal-close" id="closeTaskModal">&times;</button>
        </div>
        <form action="{{ route('tasks.store') }}" method="POST">
            @csrf
            <div class="modal-body">
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
                    <div class="tags-input-container" id="task-tags-container">
                        <input type="text" class="tag-input" id="task-tag-input" placeholder="Ketik tag dan tekan Enter...">
                    </div>
                    <div id="task-tags-hidden-inputs"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-primary">Simpan Task</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data from Controller
    const cashflowData = @json($cashflowData);
    const expensesByCategory = @json($expensesByCategory);
    
    // Initialize charts after a short delay to show loading
    setTimeout(() => {
        initializeCashflowChart();
        initializeCategoryChart();
    }, 500);

    // Initialize Cashflow Chart
    function initializeCashflowChart() {
        try {
            // Hide loading indicator
            document.getElementById('cashflowLoading').style.display = 'none';
            document.getElementById('cashflowChart').style.display = 'block';
            
            // Prepare Cashflow Chart Data
            const cfLabels = cashflowData.map(item => item.period);
            const cfIncome = cashflowData.map(item => item.income);
            const cfExpenses = cashflowData.map(item => item.expenses);

            // Cashflow Chart
            const ctxCashflow = document.getElementById('cashflowChart').getContext('2d');
            window.cashflowChartInstance = new Chart(ctxCashflow, {
                type: 'line',
                data: {
                    labels: cfLabels,
                    datasets: [
                        {
                            label: 'Income',
                            data: cfIncome,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Expense',
                            data: cfExpenses,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.15)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { 
                                color: '#94a3b8',
                                font: {
                                    family: 'Poppins',
                                    size: 12,
                                    weight: 500
                                },
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            padding: 12,
                            borderColor: 'rgba(148, 163, 184, 0.2)',
                            borderWidth: 1,
                            titleFont: {
                                family: 'Poppins',
                                size: 13,
                                weight: 600
                            },
                            bodyFont: {
                                family: 'Poppins',
                                size: 12
                            },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            border: {
                                display: false
                            },
                            grid: { 
                                display: false
                            },
                            ticks: { 
                                color: '#94a3b8',
                                font: {
                                    family: 'Poppins',
                                    size: 11
                                },
                                padding: 10
                            }
                        },
                        x: {
                            border: {
                                display: false
                            },
                            grid: { 
                                display: false
                            },
                            ticks: { 
                                color: '#94a3b8',
                                font: {
                                    family: 'Poppins',
                                    size: 11
                                },
                                padding: 10
                            }
                        }
                    },
                    animation: {
                        duration: 750,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing cashflow chart:', error);
            document.getElementById('cashflowLoading').innerHTML = 
                '<div class="chart-no-data">' +
                '<i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px; color: var(--warning-color);"></i>' +
                '<p>Terjadi kesalahan saat memuat data</p></div>';
        }
    }

    // Initialize Category Chart
    function initializeCategoryChart() {
        try {
            // Hide loading indicator
            document.getElementById('categoryLoading').style.display = 'none';
            document.getElementById('categoryChart').style.display = 'block';
            
            // Prepare Category Chart Data
            const categoryData = Object.values(expensesByCategory);
            const catLabels = categoryData.map(item => item.category);
            const catTotals = categoryData.map(item => item.total);
            
            // --- REVISED: Enhanced Color Palette ---
            const backgroundColors = [
                '#4F46E5', // Indigo
                '#7C3AED', // Purple
                '#EC4899', // Pink
                '#F59E0B', // Amber
                '#10B981', // Emerald
                '#06B6D4'  // Cyan
            ];
            const borderColors = [
                '#4338CA', // Darker Indigo
                '#6D28D9', // Darker Purple
                '#DB2777', // Darker Pink
                '#D97706', // Darker Amber
                '#059669', // Darker Emerald
                '#0891B2'  // Darker Cyan
            ];

            // Category Chart
            const ctxCategory = document.getElementById('categoryChart').getContext('2d');
            
            // Detect current theme for adaptive colors
            const isDarkMode = document.documentElement.getAttribute('data-theme') !== 'light';
            const chartBorderColor = isDarkMode ? '#1e293b' : '#e2e8f0';
            const legendTextColor = isDarkMode ? '#cbd5e1' : '#475569';
            const tooltipBgColor = isDarkMode ? 'rgba(15, 23, 42, 0.95)' : 'rgba(255, 255, 255, 0.95)';
            const tooltipBorderColor = isDarkMode ? 'rgba(148, 163, 184, 0.2)' : 'rgba(71, 85, 105, 0.2)';
            const tooltipTextColor = isDarkMode ? '#f1f5f9' : '#1e293b';
            
            if (categoryData.length > 0) {
                window.categoryChartInstance = new Chart(ctxCategory, {
                    type: 'doughnut',
                    data: {
                        labels: catLabels,
                        datasets: [{
                            data: catTotals,
                            backgroundColor: backgroundColors,
                            borderColor: chartBorderColor,
                            borderWidth: 2,
                            borderRadius: 8,
                            spacing: 2,
                            hoverOffset: 15
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        layout: {
                            padding: 20
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { 
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 20,
                                    font: {
                                        family: 'Poppins',
                                        size: 12,
                                        weight: '600'
                                    },
                                    color: legendTextColor
                                }
                            },
                            tooltip: {
                                backgroundColor: tooltipBgColor,
                                padding: 12,
                                borderColor: tooltipBorderColor,
                                borderWidth: 1,
                                titleColor: tooltipTextColor,
                                bodyColor: tooltipTextColor,
                                titleFont: {
                                    family: 'Poppins',
                                    size: 13,
                                    weight: 600
                                },
                                bodyFont: {
                                    family: 'Poppins',
                                    size: 12
                                },
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'Rp ' + context.parsed.toLocaleString('id-ID');
                                        return label;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: false,
                            duration: 1000,
                            easing: 'easeInOutQuart'
                        }
                    }
                });
                
                // Calculate and display total above chart
                const total = catTotals.reduce((a, b) => a + b, 0);
                const totalDisplay = document.getElementById('categoryTotalDisplay');
                const totalAmount = document.getElementById('categoryTotalAmount');
                
                if (totalDisplay && totalAmount) {
                    totalAmount.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                    totalDisplay.style.display = 'block';
                }
            } else {
                // Show "No Data" message if empty
                document.getElementById('categoryChart').style.display = 'none';
                document.getElementById('categoryTotalDisplay').style.display = 'none';
                document.getElementById('categoryLoading').innerHTML = 
                    '<div class="chart-no-data">' +
                    '<i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>' +
                    '<p>Belum ada data pengeluaran</p></div>';
                document.getElementById('categoryLoading').style.display = 'flex';
            }
        } catch (error) {
            console.error('Error initializing category chart:', error);
            document.getElementById('categoryLoading').innerHTML = 
                '<div class="chart-no-data">' +
                '<i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px; color: var(--warning-color);"></i>' +
                '<p>Terjadi kesalahan saat memuat data</p></div>';
        }
    }

    // Chart filter functionality
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update active state
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Get selected period
            const period = this.getAttribute('data-period');
            
            // Show loading
            document.getElementById('cashflowLoading').style.display = 'flex';
            document.getElementById('cashflowChart').style.display = 'none';
            
            // Fetch new data based on period
            fetch(`/dashboard/cashflow?period=${period}`, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Update chart with new data
                if (window.cashflowChartInstance) {
                    const cfLabels = data.map(item => item.period);
                    const cfIncome = data.map(item => item.income);
                    const cfExpenses = data.map(item => item.expenses);
                    
                    window.cashflowChartInstance.data.labels = cfLabels;
                    window.cashflowChartInstance.data.datasets[0].data = cfIncome;
                    window.cashflowChartInstance.data.datasets[1].data = cfExpenses;
                    window.cashflowChartInstance.update();
                }
                
                // Hide loading
                document.getElementById('cashflowLoading').style.display = 'none';
                document.getElementById('cashflowChart').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching cashflow data:', error);
                document.getElementById('cashflowLoading').innerHTML = 
                    '<div class="chart-no-data">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 15px; color: var(--warning-color);"></i>' +
                    '<p>Terjadi kesalahan saat memuat data</p></div>';
            });
        });
    });

    // Theme Change Observer - Re-render category chart on theme switch
    const themeObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                // Re-initialize category chart with new theme colors
                if (window.categoryChartInstance) {
                    window.categoryChartInstance.destroy();
                }
                initializeCategoryChart();
            }
        });
    });

    // Start observing theme changes
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });

    // Sidebar Toggle Observer - Resize charts when sidebar expands/collapses
    // Observe body or main content for class changes (more reliable)
    const resizeCharts = () => {
        setTimeout(() => {
            if (window.cashflowChartInstance) {
                window.cashflowChartInstance.resize();
            }
            if (window.categoryChartInstance) {
                window.categoryChartInstance.resize();
            }
        }, 50); // Quick initial resize
        
        setTimeout(() => {
            if (window.cashflowChartInstance) {
                window.cashflowChartInstance.resize();
            }
            if (window.categoryChartInstance) {
                window.categoryChartInstance.resize();
            }
        }, 350); // After animation completes
    };

    // Observer for body class changes (catches sidebar toggle)
    const bodyObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                resizeCharts();
            }
        });
    });

    // Observe body element
    bodyObserver.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    // Also observe sidebar if it exists
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        const sidebarObserver = new MutationObserver(resizeCharts);
        sidebarObserver.observe(sidebar, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    // Observe main content area
    const mainContent = document.querySelector('.main-content') || document.querySelector('main');
    if (mainContent) {
        const contentObserver = new MutationObserver(resizeCharts);
        contentObserver.observe(mainContent, {
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    // Also handle window resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (window.cashflowChartInstance) {
                window.cashflowChartInstance.resize();
            }
            if (window.categoryChartInstance) {
                window.categoryChartInstance.resize();
            }
        }, 250);
    });

    // AI Insight button
    document.getElementById('aiInsightBtn').addEventListener('click', function() {
        window.location.href = '{{ route('ai.index') }}';
    });

    // Modal functionality
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.style.display = 'flex'; // Force display clear
            // Slight delay to allow display to apply before active class for transitions
            setTimeout(() => modal.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if(modal) {
            modal.classList.remove('active');
            setTimeout(() => {
                if(!modal.classList.contains('active')) {
                    modal.style.display = 'none';
                }
            }, 300); // Wait for transition
            document.body.style.overflow = '';
        }
    }

    // Quick action buttons
    // --- Finance Modal Logic with Categories ---
    const categories = @json($categories);
    const categorySelect = document.querySelector('select[name="category_id"]');
    const typeOptions = document.querySelectorAll('.type-option');
    const incomeRadio = document.querySelector('input[name="type"][value="income"]');
    const expenseRadio = document.querySelector('input[name="type"][value="expense"]');

    function filterCategories(type) {
        if(!categorySelect) return;
        categorySelect.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        categories.forEach(cat => {
            if (cat.type === type) {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                categorySelect.appendChild(option);
            }
        });
    }

    // Type Options Click Listener
    typeOptions.forEach(option => {
        option.addEventListener('click', function() {
            typeOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            if(radio) {
                radio.checked = true;
                filterCategories(radio.value);
            }
        });
    });

    // Initial Filter
    if(incomeRadio && incomeRadio.checked) filterCategories('income');
    if(expenseRadio && expenseRadio.checked) filterCategories('expense');

    document.getElementById('addIncomeBtn').addEventListener('click', function() {
        document.getElementById('financeModalTitle').textContent = 'Tambah Pemasukan';
        const opt = document.getElementById('incomeOption');
        if(opt) opt.click();
        openModal('financeModal');
    });

    document.getElementById('addExpenseBtn').addEventListener('click', function() {
        document.getElementById('financeModalTitle').textContent = 'Tambah Pengeluaran';
        const opt = document.getElementById('expenseOption');
        if(opt) opt.click();
        openModal('financeModal');
    });

    document.getElementById('addNoteBtn').addEventListener('click', function() {
        openModal('noteModal');
    });

    document.getElementById('addTaskBtn').addEventListener('click', function() {
        openModal('taskModal');
    });

    // Close modal buttons
    document.getElementById('closeFinanceModal').addEventListener('click', function() {
        closeModal('financeModal');
    });

    document.getElementById('cancelFinanceModal').addEventListener('click', function() {
        closeModal('financeModal');
    });

    document.getElementById('closeNoteModal').addEventListener('click', function() {
        closeModal('noteModal');
    });

    document.getElementById('closeTaskModal').addEventListener('click', function() {
        closeModal('taskModal');
    });

    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal(this.id);
            }
        });
    });

    // Type selector for finance modal
    document.querySelectorAll('.type-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
        });
    });

    // Tag Manager for notes
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
    const noteTagManager = new TagManager('note-tags-container', 'note-tag-input', 'note-tags-hidden-inputs');
    const taskTagManager = new TagManager('task-tags-container', 'task-tag-input', 'task-tags-hidden-inputs');

    // Color Picker for notes
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

    const noteColorPicker = setupColorPicker('note-color-picker', 'note-color-input');

    // --- AI VOICE FEATURE ---
    const aiVoiceBtn = document.getElementById('aiVoiceBtn');
    const aiVoiceOverlay = document.getElementById('aiVoiceOverlay');
    const aiVoiceStatus = document.getElementById('aiVoiceStatus');
    const aiVoiceText = document.getElementById('aiVoiceText');
    const stopVoiceBtn = document.getElementById('stopVoiceBtn');
    const aiVoiceIcon = document.getElementById('aiVoiceIcon');
    let recognition;
    let silenceTimer;
    let accumulatedText = "";

    if ('webkitSpeechRecognition' in window) {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true; // Use continuous to not stop immediately
        recognition.lang = 'id-ID';
        recognition.interimResults = true;

        recognition.onstart = function() {
            aiVoiceStatus.textContent = "Mendengarkan...";
            aiVoiceText.textContent = "Katakan sesuatu...";
            aiVoiceIcon.style.color = "var(--primary-color)";
            document.getElementById('aiVoiceWave').style.display = 'flex';
            accumulatedText = "";
        };

        recognition.onerror = function(event) {
            aiVoiceStatus.textContent = "Error: " + event.error;
            aiVoiceIcon.style.color = "var(--danger-color)";
            document.getElementById('aiVoiceWave').style.display = 'none';
        };

        recognition.onresult = function(event) {
            let interimTranscript = '';
            
            // Reconstruct final transcript from continuous session
            let tempFinal = '';
            
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    tempFinal += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            
            if (tempFinal) {
                accumulatedText += tempFinal + " ";
            }

            // Update UI with what we have so far
            let displayText = accumulatedText + interimTranscript;
            aiVoiceText.textContent = displayText || "Mendengarkan...";

            // Reset Silence Timer
            clearTimeout(silenceTimer);
            silenceTimer = setTimeout(() => {
                if (displayText.trim().length > 0) {
                    processVoiceCommand(displayText);
                }
            }, 2000); // Wait 2 seconds of silence
        };
    } else {
        if(aiVoiceBtn) aiVoiceBtn.style.display = 'none'; // Hide if not supported
        console.warn("Web Speech API not supported");
    }

    if(aiVoiceBtn) {
        aiVoiceBtn.addEventListener('click', function() {
            openModal('aiVoiceOverlay');
            recognition.start();
        });
    }

    if(stopVoiceBtn) {
        stopVoiceBtn.addEventListener('click', function() {
            clearTimeout(silenceTimer);
            if (accumulatedText.trim().length > 0) {
                 processVoiceCommand(accumulatedText);
            } else {
                recognition.stop();
                closeModal('aiVoiceOverlay');
            }
        });
    }

    function processVoiceCommand(text) {
        recognition.stop(); // Stop listening
        clearTimeout(silenceTimer); // Ensure no double send
        aiVoiceStatus.textContent = "Memproses dengan AI...";
        document.getElementById('aiVoiceWave').style.display = 'none';
        aiVoiceIcon.innerHTML = '<i class="fas fa-cog fa-spin"></i>';

        fetch("{{ route('ai.voice-command') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ text: text })
        })
        .then(response => {
            if (!response.ok) {
                console.error('Response status:', response.status);
                throw new Error('Server error: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Voice command response:', data);
            if (data.success) {
                aiVoiceStatus.textContent = "Berhasil!";
                aiVoiceIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                aiVoiceIcon.style.color = "var(--success-color)";
                setTimeout(() => {
                    location.reload(); // Reload to show new transactions
                }, 1500);
            } else {
                throw new Error(data.message || 'Perintah gagal diproses');
            }
        })
        .catch(error => {
            console.error('Voice command error:', error);
            aiVoiceStatus.textContent = "Gagal";
            aiVoiceText.textContent = error.message || 'Terjadi kesalahan';
            aiVoiceIcon.innerHTML = '<i class="fas fa-times-circle"></i>';
            aiVoiceIcon.style.color = "var(--danger-color)";
        });
    }
});
</script>
@endsection