<div class="account-card">
    <div class="account-header">
        <div class="account-icon">
            @if(stripos($account->platform_name, 'paypal') !== false) <i class="fab fa-paypal"></i>
            @elseif(stripos($account->platform_name, 'youtube') !== false) <i class="fab fa-youtube"></i>
            @elseif(stripos($account->platform_name, 'google') !== false) <i class="fab fa-google"></i>
            @elseif(stripos($account->platform_name, 'shopee') !== false) <i class="fas fa-shopping-bag"></i>
            @else <i class="fas fa-globe"></i>
            @endif
        </div>
        <div class="account-info">
            <h3>{{ $account->platform_name }}</h3>
            @if($account->website_url)
                <a href="{{ $account->website_url }}" target="_blank" class="account-link">
                    Buka Website <i class="fas fa-external-link-alt"></i>
                </a>
            @endif
        </div>
        <div class="account-actions-top">
            <form action="{{ route('digital-accounts.destroy', $account->id) }}" method="POST" onsubmit="return confirm('Hapus sumber ini?');">
                @csrf @method('DELETE')
                <button type="submit" class="btn-icon danger"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>

    <div class="account-balance">
        @if($account->currency == 'USD') $ @else Rp @endif{{ number_format($account->current_balance, 2, ',', '.') }}
        <span style="font-size: 12px; color: var(--text-muted);">{{ $account->currency }}</span>
    </div>
    
    <div class="account-footer">
        <button class="btn-secondary" onclick='openUpdateModal(@json($account))'>
            <i class="fas fa-sync"></i> Update
        </button>
        <button class="btn-primary" onclick='openWithdrawModal(@json($account))'>
            <i class="fas fa-download"></i> Withdraw
        </button>
    </div>
</div>
