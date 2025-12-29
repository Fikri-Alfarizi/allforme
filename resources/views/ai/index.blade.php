@extends('layouts.app')

@section('title', 'AI Assistant - PLFIS')
@section('page-title', 'AI Assistant')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<style>
    .ai-container {
        display: grid;
        grid-template-columns: 260px 1fr 300px;
        gap: 0;
        height: calc(100vh - 140px);
        max-width: 1800px;
        margin: 0 auto;
        position: relative;
    }

    /* Left Sidebar - Quick Actions */
    .left-sidebar {
        background: var(--dark-sidebar);
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 20px 15px;
    }

    .left-sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .left-sidebar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 2px;
    }

    /* Right Sidebar - History */
    .right-sidebar {
        background: var(--dark-sidebar);
        border-left: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 20px 15px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .right-sidebar.collapsed {
        margin-right: -300px;
        opacity: 0;
        pointer-events: none;
    }

    .right-sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .right-sidebar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 2px;
    }

    .right-sidebar-toggle {
        position: absolute;
        left: -40px;
        top: 20px;
        width: 36px;
        height: 36px;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--text-light);
        transition: all 0.3s;
        z-index: 10;
    }

    .right-sidebar-toggle:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
    }

    /* Chat Section */
    .chat-section {
        background: var(--dark-bg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 30px 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        max-width: 100%;
        margin: 0;
        width: 100%;
    }

    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 3px;
    }

    .message {
        display: flex;
        gap: 14px;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .message.user {
        flex-direction: row-reverse;
    }

    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .message.ai .message-avatar {
        background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        color: white;
    }

    .message.user .message-avatar {
        background: var(--primary-color);
        color: white;
    }

    .message-content {
        flex: 1;
        padding: 14px 18px;
        border-radius: 16px;
        line-height: 1.6;
        font-size: 14px;
    }

    .message.ai .message-content {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
    }

    .message.user .message-content {
        background: var(--primary-color);
        color: white;
        max-width: 70%;
        margin-left: auto;
    }

    .message-time {
        font-size: 10px;
        color: var(--text-muted);
        margin-top: 6px;
        padding-left: 50px;
    }

    .message.user .message-time {
        text-align: right;
        padding-right: 50px;
        padding-left: 0;
    }

    .chat-input-area {
        padding: 20px;
        border-top: 1px solid var(--border-color);
        background: var(--dark-sidebar);
        max-width: 100%;
        margin: 0;
        width: 100%;
    }

    .chat-input-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .chat-input {
        flex: 1;
        padding: 12px 16px;
        background: var(--dark-bg);
        border: 1.5px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-light);
        font-size: 14px;
        resize: none;
        max-height: 120px;
        font-family: inherit;
        transition: border 0.2s;
    }

    .chat-input:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .voice-btn, .send-btn, .stop-btn {
        width: 44px;
        height: 44px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .voice-btn {
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        color: var(--text-light);
    }

    .voice-btn:hover {
        background: var(--warning-color);
        border-color: var(--warning-color);
        color: white;
    }

    .voice-btn.recording {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .send-btn {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
    }

    .send-btn:hover {
        transform: scale(1.05);
    }

    .send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        display: none;
    }

    .stop-btn {
        background: var(--danger-color);
        color: white;
        display: none;
    }
    
    .stop-btn:hover {
        transform: scale(1.05);
        background: #dc2626;
    }

    .section-title {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .action-btn {
        width: 100%;
        padding: 11px 14px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        color: var(--text-light);
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 8px;
        text-align: left;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
    }

    .action-btn:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateX(2px);
    }

    .action-btn i {
        font-size: 14px;
        width: 18px;
        text-align: center;
    }

    .new-chat-btn {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
        justify-content: center;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .new-chat-btn:hover {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
        transform: translateX(0) scale(1.02);
    }

    .session-item {
        padding: 10px 12px;
        background: var(--dark-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
        margin-bottom: 6px;
        transition: all 0.2s;
    }

    .session-item:hover {
        background: var(--dark-card);
        border-color: var(--primary-color);
    }

    .session-item.active {
        border-color: var(--primary-color);
        background: rgba(59, 130, 246, 0.1);
    }

    .session-title {
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 3px;
    }

    .session-date {
        font-size: 10px;
        color: var(--text-muted);
    }

    .loading {
        display: flex;
        gap: 5px;
        padding: 10px;
        justify-content: center;
    }

    .loading-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--text-muted);
        animation: bounce 1.4s infinite ease-in-out both;
    }

    .loading-dot:nth-child(1) { animation-delay: -0.32s; }
    .loading-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.4;
    }

    .empty-state h3 {
        font-size: 18px;
        margin-bottom: 8px;
        color: var(--text-light);
    }

    .empty-state p {
        font-size: 13px;
    }

    /* Refined Markdown Styles */
    .markdown-content {
        font-size: 13px;
        line-height: 1.7;
        color: var(--text-light);
    }

    .markdown-content p {
        margin-bottom: 12px;
    }

    .markdown-content strong {
        color: var(--primary-color);
        font-weight: 700;
    }

    .markdown-content code {
        background: rgba(0,0,0,0.3);
        padding: 2px 6px;
        border-radius: 5px;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 12px;
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.2);
    }

    .markdown-content pre {
        background: rgba(0,0,0,0.4);
        padding: 14px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 12px 0;
        border: 1px solid var(--border-color);
    }

    .markdown-content pre code {
        background: transparent;
        padding: 0;
        color: #e5e7eb;
        border: none;
        font-size: 12px;
    }

    .markdown-content ul, .markdown-content ol {
        margin: 10px 0 10px 24px;
    }

    .markdown-content li {
        margin-bottom: 6px;
    }

    .markdown-content h1, .markdown-content h2, .markdown-content h3 {
        margin-top: 18px;
        margin-bottom: 10px;
        font-weight: 700;
        color: var(--text-light);
    }

    .markdown-content h1 { font-size: 20px; }
    .markdown-content h2 { font-size: 17px; }
    .markdown-content h3 { font-size: 15px; }

    .markdown-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 14px 0;
        background: rgba(0,0,0,0.2);
        border-radius: 8px;
        overflow: hidden;
        font-size: 12px;
    }

    .markdown-content th, .markdown-content td {
        border: 1px solid var(--border-color);
        padding: 8px 12px;
        text-align: left;
    }

    .markdown-content th {
        background: rgba(59, 130, 246, 0.15);
        font-weight: 600;
        color: var(--primary-color);
    }

    .markdown-content blockquote {
        border-left: 3px solid var(--primary-color);
        padding-left: 14px;
        margin: 14px 0;
        color: var(--text-muted);
        font-style: italic;
    }

    .context-menu {
        position: absolute;
        background: var(--dark-card);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 4px 0;
        min-width: 160px;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        display: none;
    }

    .context-menu-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 12px;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background 0.2s;
    }

    .context-menu-item:hover {
        background: rgba(255,255,255,0.05);
    }

    .context-menu-item.danger {
        color: var(--danger-color);
    }

    .context-menu-item.danger:hover {
        background: rgba(220, 38, 38, 0.15);
    }

    @media (max-width: 1200px) {
        .ai-container {
            grid-template-columns: 220px 1fr 280px;
        }
    }

    @media (max-width: 968px) {
        .ai-container {
            grid-template-columns: 1fr;
        }

        .left-sidebar, .right-sidebar {
            display: none;
        }
    }
</style>

<div class="ai-container">
    <!-- Left Sidebar - Quick Actions -->
    <div class="left-sidebar">
        <div class="section-title">
            <i class="fas fa-headset"></i>
            Live Assistant
        </div>
        
        <button class="action-btn" style="background: linear-gradient(135deg, #ec4899, #8b5cf6); border:none; color:white; justify-content:center; margin-bottom: 20px; font-weight: 600; box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);" onclick="startVoiceCall()">
            <i class="fas fa-phone-alt"></i>
            <span>Mulai Telepon AI</span>
        </button>

        <div class="section-title">
            <i class="fas fa-bolt"></i>
            Quick Prompts
        </div>

        <button class="action-btn" onclick="quickAction('financial')">
            <i class="fas fa-chart-line"></i>
            <span>Analisis Keuangan</span>
        </button>

        <button class="action-btn" onclick="quickAction('budget')">
            <i class="fas fa-calculator"></i>
            <span>Optimasi Budget</span>
        </button>

        <button class="action-btn" onclick="quickAction('purchase')">
            <i class="fas fa-shopping-cart"></i>
            <span>Saran Pembelian</span>
        </button>

        <button class="action-btn" onclick="quickAction('saving')">
            <i class="fas fa-piggy-bank"></i>
            <span>Tips Menabung</span>
        </button>

        <button class="action-btn" onclick="quickAction('investment')">
            <i class="fas fa-chart-pie"></i>
            <span>Rekomendasi Investasi</span>
        </button>

        <button class="action-btn" onclick="quickAction('debt')">
            <i class="fas fa-hand-holding-usd"></i>
            <span>Kelola Hutang</span>
        </button>
    </div>

    <!-- Chat Section -->
    <div class="chat-section">
        <div class="chat-messages" id="chatMessages">
            @if($history->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-robot"></i>
                    <h3>Halo! Saya AI Financial Assistant</h3>
                    <p>Tanya saya tentang keuangan, investasi, atau keputusan pembelian Anda</p>
                </div>
            @else
                @foreach($history as $log)
                    <!-- User Message -->
                    <div class="message user">
                        <div class="message-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="message-content">
                            {{ $log->prompt }}
                        </div>
                    </div>
                    <div class="message-time">{{ $log->created_at->diffForHumans() }}</div>

                    <!-- AI Response -->
                    <div class="message ai">
                        <div class="message-avatar">
                            <i class="fas fa-sparkles"></i>
                        </div>
                        <div class="message-content markdown-content">
                            {{ $log->response }}
                        </div>
                    </div>
                    <div class="message-time">{{ $log->response_time }}ms</div>
                @endforeach
            @endif
        </div>

        <div class="chat-input-area">
            <form class="chat-input-form" id="chatForm">
                @csrf
                <button type="button" class="voice-btn" id="voiceBtn" title="Speech to Text">
                    <i class="fas fa-microphone"></i>
                </button>
                <textarea 
                    class="chat-input" 
                    id="messageInput" 
                    placeholder="Ketik pesan atau klik mic untuk voice input..."
                    rows="1"
                    required
                ></textarea>
                <button type="button" class="stop-btn" id="stopBtn" onclick="stopGeneration()">
                    <i class="fas fa-stop"></i>
                </button>
                <button type="submit" class="send-btn" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Right Sidebar - History -->
    <div class="right-sidebar" id="rightSidebar">
        <div class="right-sidebar-toggle" onclick="toggleRightSidebar()" title="Toggle History">
            <i class="fas fa-bars"></i>
        </div>

        <button class="action-btn new-chat-btn" onclick="startNewChat()">
            <i class="fas fa-plus"></i>
            <span>New Chat</span>
        </button>

        <div class="section-title">
            <i class="fas fa-history"></i>
            Chat History
        </div>

        <div id="sessionList">
            <div class="loading">
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
                <div class="loading-dot"></div>
            </div>
        </div>
    </div>
</div>

<!-- Context Menu -->
<div id="contextMenu" class="context-menu">
    <div class="context-menu-item" onclick="togglePinContext()">
        <i class="fas fa-thumbtack"></i>
        <span id="pinText">Pin Chat</span>
    </div>
    <div class="context-menu-item danger" onclick="deleteSessionContext()">
        <i class="fas fa-trash"></i>
        <span>Hapus Chat</span>
    </div>
</div>

<!-- Voice Call Overlay -->
<div id="voiceOverlay" class="voice-overlay">
    <div class="voice-content">
        <div class="voice-status" id="voiceStatus">Menghubungkan...</div>
        
        <div class="voice-visualizer">
            <div class="circle-waves" id="circleWaves">
                <div class="wave"></div>
                <div class="wave"></div>
                <div class="wave"></div>
            </div>
            <div class="avatar-container">
                <img src="https://ui-avatars.com/api/?name=AI&background=0D8ABC&color=fff&size=128" alt="AI Avatar">
            </div>
        </div>

        <div class="voice-controls">
            <button class="control-btn mute-btn" onclick="toggleMute()" id="muteBtn">
                <i class="fas fa-microphone"></i>
            </button>
            <button class="control-btn end-btn" onclick="endVoiceCall()">
                <i class="fas fa-phone-slash"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .voice-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(10, 10, 20, 0.95);
        backdrop-filter: blur(10px);
        z-index: 9999;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .voice-overlay.active {
        display: flex;
        opacity: 1;
    }

    .voice-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 60px;
    }

    .voice-status {
        color: var(--text-light);
        font-size: 18px;
        font-weight: 500;
        letter-spacing: 1px;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .voice-visualizer {
        position: relative;
        width: 200px;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-container {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid var(--primary-color);
        box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
        z-index: 2;
        position: relative;
    }

    .avatar-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .circle-waves {
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

    /* Wave Animation */
    .wave {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(59, 130, 246, 0.3);
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0;
    }

    .voice-overlay.speaking .wave {
        animation: ripple 2s infinite cubic-bezier(0, 0.2, 0.8, 1);
    }
    
    .voice-overlay.speaking .wave:nth-child(2) { animation-delay: 0.5s; }
    .voice-overlay.speaking .wave:nth-child(3) { animation-delay: 1.0s; }

    .voice-overlay.listening .avatar-container {
        border-color: #10b981;
        box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
    }

    @keyframes ripple {
        0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; border: 2px solid rgba(59, 130, 246, 0.5); }
        100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; border: 0px solid rgba(59, 130, 246, 0); }
    }

    .voice-controls {
        display: flex;
        gap: 30px;
    }

    .control-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        font-size: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    }

    .control-btn:hover { transform: scale(1.1); }

    .mute-btn {
        background: rgba(255, 255, 255, 0.1);
        color: white;
    }
    
    .mute-btn.muted {
        background: #ef4444; /* Red when muted */
        color: white;
    }

    .end-btn {
        background: #ef4444;
        color: white;
    }
</style>
@endsection

@section('scripts')
<script>
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    const stopBtn = document.getElementById('stopBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const sessionList = document.getElementById('sessionList');
    const rightSidebar = document.getElementById('rightSidebar');

    let abortController = null;
    let isGenerating = false;
    let stopTypingFlag = false;
    let currentSessionId = "{{ request('session_id') }}" || generateUUID();
    let recognition = null;

    function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    // Speech Recognition Setup
    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.lang = 'id-ID';
        recognition.continuous = true; // Keep listening until stopped
        recognition.interimResults = true; // Show real-time text

        let finalTranscript = '';

        recognition.onresult = function(event) {
            let interimTranscript = '';
            
            // Process all results
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                
                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript;
                }
            }
            
            // Update textarea with both final and interim text
            messageInput.value = finalTranscript + interimTranscript;
            
            // Auto-resize textarea
            messageInput.style.height = 'auto';
            messageInput.style.height = (messageInput.scrollHeight) + 'px';
        };

        recognition.onend = function() {
            voiceBtn.classList.remove('recording');
        };

        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            voiceBtn.classList.remove('recording');
        };

        voiceBtn.addEventListener('click', function() {
            if (voiceBtn.classList.contains('recording')) {
                // Stop recording
                recognition.stop();
                voiceBtn.classList.remove('recording');
            } else {
                // Start recording
                finalTranscript = messageInput.value; // Keep existing text
                recognition.start();
                voiceBtn.classList.add('recording');
            }
        });
    } else {
        voiceBtn.style.display = 'none'; // Hide if not supported
    }

    /* --- REAL-TIME VOICE CALL LOGIC (EXTREME VERSION) --- */
    let voiceOverlay = document.getElementById('voiceOverlay');
    let voiceStatus = document.getElementById('voiceStatus');
    let circleWaves = document.getElementById('circleWaves');
    
    let callActive = false;
    let mediaRecorder = null;
    let audioChunks = [];
    let audioContext = null;
    let analyser = null;
    let microphone = null;
    let vadInterval = null;
    let isSpeaking = false;
    let silenceStart = null;
    let isProcessing = false;
    let isAISpeaking = false;
    let currentAudioSource = null;
    
    // Config
    const SILENCE_THRESHOLD = 25; // Increased from 5 to avoid background noise
    const SILENCE_DURATION = 1500; // ms of silence to trigger send

    async function startVoiceCall() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            
            callActive = true;
            voiceOverlay.classList.add('active');
            voiceOverlay.style.display = 'flex';
            
            // Add click listener to avatar for manual stop
            const avatarContainer = document.querySelector('.avatar-container');
            avatarContainer.onclick = () => {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                    voiceStatus.innerText = "Mengirim...";
                }
            };
            avatarContainer.style.cursor = 'pointer';
            avatarContainer.title = "Ketuk untuk mengirim";
            
            // Setup Audio Context for VAD & Visualizer
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
            analyser = audioContext.createAnalyser();
            microphone = audioContext.createMediaStreamSource(stream);
            microphone.connect(analyser);
            analyser.fftSize = 256;
            
            // Setup Recorder
            setupRecorder(stream);
            
            // Start Loop
            voiceStatus.innerText = "Mendengarkan...";
            voiceOverlay.classList.add('listening');
            startVAD();
            startVisualizer();
            
            mediaRecorder.start();
            
        } catch (err) {
            console.error("Mic Error:", err);
            alert("Gagal mengakses mikrofon: " + err.message);
            endVoiceCall();
        }
    }

    function setupRecorder(stream) {
        mediaRecorder = new MediaRecorder(stream);
        
        mediaRecorder.ondataavailable = event => {
            audioChunks.push(event.data);
        };
        
        mediaRecorder.onstop = async () => {
            if (!callActive) return;
            
            const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
            audioChunks = [];
            
            if (audioBlob.size > 3000) { // Reject very short/empty clips
                await processVoiceAudio(audioBlob);
            } else {
                // Too short, restart listening immediately
                if(!isProcessing && !isAISpeaking) {
                    try { mediaRecorder.start(); } catch(e){}
                }
            }
        };
    }

    function startVAD() {
        const dataArray = new Uint8Array(analyser.frequencyBinCount);
        
        vadInterval = setInterval(() => {
            if (isProcessing || isAISpeaking || !callActive) return;
            
            analyser.getByteFrequencyData(dataArray);
            
            // Calculate average volume
            let sum = 0;
            for(let i = 0; i < dataArray.length; i++) sum += dataArray[i];
            const average = sum / dataArray.length;
            
            // VAD Logic
            if (average > SILENCE_THRESHOLD) {
                // User is speaking
                if (!isSpeaking) {
                    isSpeaking = true;
                    voiceStatus.innerText = "Mendengarkan...";
                    voiceOverlay.classList.add('listening');
                }
                silenceStart = null;
            } else {
                // Silence
                if (isSpeaking) {
                    if (!silenceStart) silenceStart = Date.now();
                    
                    if (Date.now() - silenceStart > SILENCE_DURATION) {
                        // User stopped speaking
                        isSpeaking = false;
                        if (mediaRecorder && mediaRecorder.state === 'recording') {
                            mediaRecorder.stop(); // Triggers onstop -> processVoiceAudio
                        }
                    }
                }
            }
            
            // Visualizer Driver
            updateVisualizer(average);
            
        }, 50);
    }

    function updateVisualizer(volume) {
        // Map volume 0-100 to scale 0.8-2.5
        const scale = 0.8 + (volume / 50); 
        const waves = document.querySelectorAll('.wave');
        
        if (volume > SILENCE_THRESHOLD && !isAISpeaking && !isProcessing) {
            waves.forEach(w => w.style.transform = `translate(-50%, -50%) scale(${scale})`);
             // Manual simple pulse if CSS animation isn't enough
        } else if (isAISpeaking) {
             // Let CSS animation handle AI speaking
        } else {
             // Reset
             waves.forEach(w => w.style.transform = `translate(-50%, -50%) scale(0.8)`);
        }
    }
    
    function startVisualizer() {
         // The requestAnimationFrame loop is handled within VAD interval for simplicity efficiently
         // or we can add specific visualizer loop if we want smoother 60fps
    }

    async function processVoiceAudio(audioBlob) {
        isProcessing = true;
        voiceStatus.innerText = "Berpikir...";
        voiceOverlay.classList.remove('listening');
        voiceOverlay.classList.remove('speaking');
        
        // Add ripple effect for thinking
        circleWaves.classList.add('thinking'); 

        const formData = new FormData();
        formData.append('audio', audioBlob, 'recording.webm');

        try {
            const response = await fetch('{{ route("ai.voice-chat") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success && data.audio) {
                // Play Audio
                circleWaves.classList.remove('thinking');
                await playAudio(data.audio);
            } else {
                voiceStatus.innerText = "Gagal memproses.";
                setTimeout(resumeListening, 2000);
            }
        } catch (error) {
            console.error("Upload Error:", error);
            voiceStatus.innerText = "Error koneksi.";
            setTimeout(resumeListening, 2000);
        } finally {
             // Clean up if needed
        }
    }

    function playAudio(base64Audio) {
        return new Promise((resolve) => {
            isProcessing = false;
            isAISpeaking = true;
            voiceStatus.innerText = "Berbicara...";
            voiceOverlay.classList.add('speaking');
            
            const audioUrl = "data:audio/mp3;base64," + base64Audio;
            const audio = new Audio(audioUrl);
            currentAudioSource = audio;
            
            audio.onended = () => {
                isAISpeaking = false;
                voiceOverlay.classList.remove('speaking');
                resumeListening();
                resolve();
            };
            
            audio.play().catch(e => {
                console.error("Play Error:", e);
                isAISpeaking = false;
                resumeListening();
                resolve();
            });
        });
    }

    function resumeListening() {
        if (!callActive) return;
        isProcessing = false;
        voiceStatus.innerText = "Mendengarkan...";
        voiceOverlay.classList.add('listening');
        
        try {
            if (mediaRecorder.state === 'inactive') {
                audioChunks = [];
                mediaRecorder.start();
            }
        } catch(e) {
            console.error("Resume Error:", e);
        }
    }

    function endVoiceCall() {
        callActive = false;
        voiceOverlay.classList.remove('active');
        setTimeout(() => { voiceOverlay.style.display = 'none'; }, 300);
        
        if (vadInterval) clearInterval(vadInterval);
        if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
        if (currentAudioSource) {
            currentAudioSource.pause();
            currentAudioSource = null;
        }
        
        // Close streams
        if (microphone) microphone.disconnect();
        if (analyser) analyser.disconnect();
        // if (audioContext) audioContext.close(); // Optional, keep if creating new one every time
    }

    function toggleMute() {
        // Just stop recording for now
        if (mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            voiceStatus.innerText = "Mic Mati (Ketuk untuk aktif)";
        } else {
            resumeListening();
        }
    }

    // Additional Helpers
    function playSound(type) {
        // Placeholder
    }

    // Toggle Right Sidebar (History)
    function toggleRightSidebar() {
        rightSidebar.classList.toggle('collapsed');
    }

    // Load sessions on open
    loadSessions();

    async function loadSessions() {
        try {
            const res = await fetch('{{ route("ai.sessions") }}');
            const data = await res.json();
            
            if (data.success) {
                renderSessionList(data.sessions);
            }
        } catch (err) {
            console.error('Failed to load sessions', err);
            sessionList.innerHTML = '<div style="padding:10px; color:var(--danger-color); font-size:11px;">Gagal memuat riwayat.</div>';
        }
    }

    function renderSessionList(sessions) {
        if (!sessions || sessions.length === 0) {
            sessionList.innerHTML = '<div style="padding:10px; color:var(--text-muted); font-size:11px; text-align:center;">Belum ada riwayat.</div>';
            return;
        }

        let html = '';
        sessions.forEach(s => {
            const isActive = s.session_id === currentSessionId ? 'active' : '';
            const title = s.title || 'Percakapan Baru';
            const date = new Date(s.last_activity).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'});
            const isPinned = s.is_pinned == 1;
            const pinIcon = isPinned ? '<i class="fas fa-thumbtack" style="color:var(--primary-color); font-size:9px; margin-right:4px;"></i>' : '';

            html += `
                <div class="session-item ${isActive}" 
                    onclick="loadChatHistory('${s.session_id}')" 
                    oncontextmenu="handleContextMenu(event, '${s.session_id}', ${isPinned})">
                    <div class="session-title">${pinIcon}${title}</div>
                    <div class="session-date">${date}</div>
                </div>
            `;
        });
        sessionList.innerHTML = html;
    }

    let contextSessionId = null;
    let contextIsPinned = false;
    const contextMenu = document.getElementById('contextMenu');

    function handleContextMenu(e, sessionId, isPinned) {
        e.preventDefault();
        contextSessionId = sessionId;
        contextIsPinned = isPinned;
        
        const pinText = document.getElementById('pinText');
        pinText.innerText = isPinned ? 'Unpin Chat' : 'Pin Chat';
        
        contextMenu.style.display = 'block';
        contextMenu.style.left = e.pageX + 'px';
        contextMenu.style.top = e.pageY + 'px';
    }

    document.addEventListener('click', function(e) {
        if (contextMenu && !contextMenu.contains(e.target)) {
            contextMenu.style.display = 'none';
        }
    });

    async function togglePinContext() {
        if(!contextSessionId) return;
        try {
            await fetch('{{ route("ai.pin-session") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ session_id: contextSessionId })
            });
            loadSessions();
        } catch(e) { console.error(e); }
        contextMenu.style.display = 'none';
    }

    async function deleteSessionContext() {
        if(!contextSessionId) return;
        if(!confirm('Hapus percakapan ini?')) return;
        
        try {
            await fetch('{{ route("ai.delete-session") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ session_id: contextSessionId })
            });
            
            if(currentSessionId === contextSessionId) {
                startNewChat();
            } else {
                loadSessions();
            }
        } catch(e) { console.error(e); }
        contextMenu.style.display = 'none';
    }

    function startNewChat() {
        currentSessionId = generateUUID();
        chatMessages.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-robot"></i>
                <h3>Chat Baru Dimulai</h3>
                <p>Mari kita diskusikan keuangan Anda</p>
            </div>
        `;
        loadSessions(); 
        
        const url = new URL(window.location);
        url.searchParams.delete('session_id');
        window.history.pushState({}, '', url);
    }

    async function loadChatHistory(sessionId) {
        if (isGenerating) return;
        
        currentSessionId = sessionId;
        chatMessages.innerHTML = `
            <div class="loading" style="justify-content:center; padding:50px;">
                <div class="loading-dot"></div><div class="loading-dot"></div><div class="loading-dot"></div>
            </div>
        `;

        try {
            const res = await fetch(`{{ route("ai.history") }}?session_id=${sessionId}&limit=100`);
            const data = await res.json();
            
            if (data.success) {
                chatMessages.innerHTML = '';
                if (data.history.length === 0) {
                    chatMessages.innerHTML = '<div class="empty-state"><p>Chat kosong.</p></div>';
                } else {
                    data.history.forEach(log => {
                        addMessage('user', log.prompt, null);
                        const msgContent = addMessage('ai', '', log.response_time);
                        msgContent.innerHTML = marked.parse(log.response);
                    });
                }
                setTimeout(() => chatMessages.scrollTop = chatMessages.scrollHeight, 100);
            }
        } catch (err) {
            console.error(err);
            chatMessages.innerHTML = '<div style="color:red; text-align:center;">Gagal memuat chat.</div>';
        }
        
        const url = new URL(window.location);
        url.searchParams.set('session_id', sessionId);
        window.history.pushState({}, '', url);
        
        loadSessions();
    }

    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
    
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (!isGenerating && this.value.trim()) {
                chatForm.dispatchEvent(new Event('submit'));
            }
        }
    });

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;

        messageInput.disabled = true;
        sendBtn.style.display = 'none';
        stopBtn.style.display = 'flex';
        isGenerating = true;

        addMessage('user', message);
        messageInput.value = '';
        messageInput.style.height = 'auto';

        const thinkingId = addThinking();
        abortController = new AbortController();

        try {
            const response = await fetch('{{ route("ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message, session_id: currentSessionId }),
                signal: abortController.signal
            });

            const data = await response.json();
            removeThinking(thinkingId);

            if (data.success) {
                const contentDiv = addMessage('ai', '', data.response_time);
                await typeWriter(data.response, contentDiv);
                loadSessions();
            } else {
                addMessage('ai', data.error || 'Terjadi kesalahan', 0);
            }
        } catch (error) {
            removeThinking(thinkingId);
            if (error.name === 'AbortError') {
                addMessage('ai', '<em>Percakapan dihentikan.</em>', 0);
            } else {
                addMessage('ai', 'Gagal menghubungi AI. Silakan coba lagi.', 0);
            }
        }

        resetUI();
    });

    function resetUI() {
        isGenerating = false;
        abortController = null;
        messageInput.disabled = false;
        sendBtn.style.display = 'flex';
        stopBtn.style.display = 'none';
        messageInput.focus();
    }

    function stopGeneration() {
        if (abortController) abortController.abort();
        if (isGenerating) stopTypingFlag = true;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.markdown-content').forEach(el => {
            const raw = el.textContent.trim();
            el.innerHTML = marked.parse(raw);
        });
    });

    // --- AI JSON & Card Rendering Logic ---

    // Function to safe parse JSON from markdown code blocks or raw strings
    function parseAiJson(text) {
        if (!text) return null;
        
        let jsonStr = text;
        
        // Try to extract from Markdown code blocks
        const codeBlockMatch = text.match(/```json\s*([\s\S]*?)\s*```/);
        if (codeBlockMatch && codeBlockMatch[1]) {
            jsonStr = codeBlockMatch[1];
        } else {
            // Also try removing general code fence if specific json tag missing
            const anyCodeMatch = text.match(/```\s*([\s\S]*?)\s*```/);
            if (anyCodeMatch && anyCodeMatch[1]) {
                jsonStr = anyCodeMatch[1];
            }
        }
        
        try {
            const data = JSON.parse(jsonStr);
            // Verify it has expected structure
            if (data && (data.intent || Array.isArray(data))) {
                return data;
            }
        } catch (e) {
            // Not valid JSON, treat as text
        }
        return null;
    }

    // Render Transaction Card
    function renderTransactionCard(items) {
        let html = '<div class="ai-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; margin-top: 5px;">';
        html += '<div style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-light);"><i class="fas fa-check-circle" style="color: var(--success-color); margin-right: 5px;"></i> Transaksi Berhasil Dicatat</div>';
        
        items.forEach(item => {
            const typeColor = item.type === 'income' ? 'var(--success-color)' : 'var(--danger-color)';
            const icon = item.type === 'income' ? 'fa-arrow-down' : 'fa-arrow-up';
            const amount = new Intl.NumberFormat('id-ID').format(item.amount || 0);
            
            html += `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px; background: rgba(0,0,0,0.2); border-radius: 8px; margin-bottom: 5px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 24px; height: 24px; border-radius: 6px; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: ${typeColor};">
                            <i class="fas ${icon}" style="font-size: 10px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 500; color: var(--text-light); text-transform: capitalize;">${item.description || 'Transaksi'}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">${item.date || 'Hari ini'}</div>
                        </div>
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: ${typeColor};">
                        Rp ${amount}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    // Render Task Card
    function renderTaskCard(items) {
        let html = '<div class="ai-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; margin-top: 5px;">';
        html += '<div style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-light);"><i class="fas fa-tasks" style="color: var(--info-color); margin-right: 5px;"></i> Tugas Baru Dibuat</div>';
        
        items.forEach(item => {
            html += `
                <div style="padding: 10px; background: rgba(0,0,0,0.2); border-radius: 8px; margin-bottom: 5px; border-left: 3px solid var(--info-color);">
                    <div style="font-size: 13px; font-weight: 500; color: var(--text-light);">${item.title || 'Tugas Baru'}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">${item.description || '-'}</div>
                    <div style="display: flex; gap: 8px; margin-top: 6px;">
                        <span style="font-size: 10px; padding: 2px 6px; background: rgba(6, 182, 212, 0.2); color: var(--info-color); border-radius: 4px;">${item.priority || 'medium'}</span>
                        <span style="font-size: 10px; padding: 2px 6px; background: rgba(255,255,255,0.1); color: var(--text-muted); border-radius: 4px;"><i class="fas fa-calendar-alt" style="margin-right: 3px;"></i> ${item.due_date || 'No Date'}</span>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        return html;
    }

    // Render Emergency Fund Card
    function renderEmergencyCard(data) {
         let html = '<div class="ai-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; margin-top: 5px;">';
        html += '<div style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-light);"><i class="fas fa-shield-alt" style="color: var(--warning-color); margin-right: 5px;"></i> Update Dana Darurat</div>';
        
        const action = (data.action || 'update').replace('_', ' ');
        const amount = new Intl.NumberFormat('id-ID').format(data.amount || 0);

        html += `
            <div style="text-align: center; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px;">
                <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">${action}</div>
                <div style="font-size: 20px; font-weight: 700; color: var(--warning-color); margin: 5px 0;">Rp ${amount}</div>
                <div style="font-size: 11px; color: var(--text-muted);">Telah diperbarui sesuai permintaan</div>
            </div>
        `;
        html += '</div>';
        return html;
    }

    // Main Renderer
    function renderAiResponse(text) {
        const jsonData = parseAiJson(text);
        
        if (jsonData) {
            // Handle Structure: { intent, data }
            if (jsonData.intent && jsonData.data) {
                const data = Array.isArray(jsonData.data) ? jsonData.data : [jsonData.data]; // normalize
                
                if (jsonData.intent === 'transaction') {
                    return renderTransactionCard(data);
                } else if (jsonData.intent === 'task') {
                    return renderTaskCard(data);
                } else if (jsonData.intent === 'emergency_fund') {
                    return renderEmergencyCard(jsonData.data); // data is object for EF usually
                } else if (jsonData.intent === 'transaction_list' || Array.isArray(jsonData.data)) {
                     // Fallback for generic lists if intent matches
                    return renderTransactionCard(data);
                }
            }
            // Handle Legacy Array Structure direct
            else if (Array.isArray(jsonData) && jsonData.length > 0 && jsonData[0].type) {
                return renderTransactionCard(jsonData);
            }
        }

        // Fallback: Markdown
        return marked.parse(text);
    }
    
    // ----------------------------------------

    function typeWriter(text, element) {
        return new Promise((resolve) => {
            // Check for JSON first to skip typewriter effect for cards
            const jsonData = parseAiJson(text);
            if (jsonData) {
                element.innerHTML = renderAiResponse(text);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                resolve();
                return;
            }

            let index = 0;
            let buffer = '';
            stopTypingFlag = false;
            const speed = 8;
            
            function type() {
                if (stopTypingFlag) {
                    element.innerHTML = marked.parse(text); 
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    resolve();
                    return;
                }

                if (index < text.length) {
                    buffer += text.charAt(index);
                    index++;
                    // Only parse markdown for complete buffer mostly safe, or just text
                    // For perf, maybe just set textContent then parse at end, but we want live MD
                    // Simply updating innerHTML with marked(buffer) works for simple MD
                    element.innerHTML = marked.parse(buffer);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    setTimeout(type, speed);
                } else {
                    element.innerHTML = marked.parse(text);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    resolve();
                }
            }
            type();
        });
    }

    function addMessage(type, content, time = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        
        const avatar = type === 'ai' 
            ? '<i class="fas fa-sparkles"></i>' 
            : '<i class="fas fa-user"></i>';
        
        const timeHTML = time !== null 
            ? `<div class="message-time">${time}ms</div>` 
            : `<div class="message-time">Baru saja</div>`;
        
        const contentClass = type === 'ai' ? 'message-content markdown-content' : 'message-content';
        
        messageDiv.innerHTML = `
            <div class="message-avatar">${avatar}</div>
            <div class="${contentClass}"></div>
        `;
        
        // Initial Content Set
        const contentEl = messageDiv.querySelector('.message-content');
        if (type === 'user') {
            contentEl.textContent = content;
        } else {
             // For AI, we might render immediately if it's history loading
             if (content) {
                 contentEl.innerHTML = renderAiResponse(content);
             }
        }

        chatMessages.appendChild(messageDiv);
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.innerHTML = timeHTML;
        chatMessages.appendChild(timeDiv);
        
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return contentEl;
    }

    function addThinking() {
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'message ai';
        loadingDiv.id = 'thinking-' + Date.now();
        
        loadingDiv.innerHTML = `
            <div class="message-avatar" style="background: var(--text-muted);"><i class="fas fa-brain"></i></div>
            <div class="message-content" style="font-style: italic; color: var(--text-muted); font-size:12px;">
                Berpikir... <i class="fas fa-spinner fa-spin"></i>
            </div>
        `;
        
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return loadingDiv.id;
    }

    function removeThinking(id) {
        const loading = document.getElementById(id);
        if (loading) loading.remove();
    }

    function quickAction(type) {
        const messages = {
            financial: 'Berikan analisis keuangan saya bulan ini',
            budget: 'Bagaimana cara mengoptimalkan budget saya?',
            purchase: 'Saya ingin membeli laptop gaming seharga 15 juta, apakah sebaiknya saya beli sekarang?',
            saving: 'Berikan tips menabung yang efektif untuk saya',
            investment: 'Apa rekomendasi investasi yang cocok untuk saya?',
            debt: 'Bagaimana strategi terbaik mengelola hutang saya?'
        };
        
        messageInput.value = messages[type];
        messageInput.focus();
    }
    
    // Auto-scroll on load
    setTimeout(() => {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 500);

    chatMessages.scrollTop = chatMessages.scrollHeight;
</script>
@endsection