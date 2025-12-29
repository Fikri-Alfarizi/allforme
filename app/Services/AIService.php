<?php

namespace App\Services;

use App\Models\User;
use App\Models\AiLog;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\EmergencyFund;
use App\Models\RecurringExpense;
use App\Models\DigitalAsset;
use App\Models\Task;
use App\Models\Note;
use App\Models\VaultAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AIService
{
    private ?string $apiKey;
    private string $provider;
    private string $apiUrl;
    private array $providerConfigs;
    private array $financialMetricsCache = [];
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        $this->provider = env('AI_PROVIDER', 'gemini');
        $this->initializeProviderConfig();
    }

    /**
     * Initialize provider-specific configurations
     */
    private function initializeProviderConfig(): void
    {
        $this->providerConfigs = [
            'deepseek' => [
                'apiKey' => config('services.deepseek.api_key') ?? env('DEEPSEEK_API_KEY'),
                'apiUrl' => 'https://api.deepseek.com/chat/completions',
                'model' => 'deepseek-chat',
                'temperature' => 0.7,
                'maxTokens' => 2048,
                'systemPrompt' => $this->getDeepseekSystemPrompt()
            ],
            'aiml' => [
                'apiKey' => env('AIML_API_KEY'),
                'apiUrl' => 'https://api.aimlapi.com/chat/completions',
                'model' => 'gpt-4',
                'temperature' => 0.7,
                'maxTokens' => 2048,
                'systemPrompt' => $this->getAimlSystemPrompt()
            ],
            'mulerouter' => [
                'apiKey' => env('MULEROUTER_API_KEY'),
                'apiUrl' => 'https://api.mulerouter.ai/vendors/openai/v1/chat/completions',
                'model' => env('AI_MODEL', 'qwen-plus'),
                'temperature' => 0.7,
                'maxTokens' => 2048,
                'systemPrompt' => $this->getMulerouterSystemPrompt()
            ],
            'groq' => [
                'apiKey' => env('GROQ_API_KEY'),
                'apiUrl' => 'https://api.groq.com/openai/v1/chat/completions',
                'model' => env('AI_MODEL', 'llama-3.1-8b-instant'),
                'temperature' => 1,
                'maxTokens' => 1024,
                'topP' => 1,
                'systemPrompt' => $this->getGroqSystemPrompt()
            ],
            'gemini' => [
                'apiKey' => config('services.gemini.api_key') ?? env('GEMINI_API_KEY'),
                'apiUrl' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
                'model' => 'gemini-2.5-flash',
                'temperature' => 0.7,
                'maxTokens' => 2048,
                'systemPrompt' => $this->getGeminiSystemPrompt()
            ],
            'claude' => [
                'apiKey' => env('CLAUDE_API_KEY'),
                'apiUrl' => 'https://api.anthropic.com/v1/messages',
                'model' => 'claude-3-sonnet-20240229',
                'temperature' => 0.7,
                'maxTokens' => 2048,
                'systemPrompt' => $this->getClaudeSystemPrompt()
            ],
            'meta' => [
                'apiKey' => null, // Meta uses Python bridge
                'apiUrl' => null,
                'model' => null,
                'temperature' => null,
                'maxTokens' => null,
                'systemPrompt' => $this->getMetaSystemPrompt()
            ]
        ];

        if (isset($this->providerConfigs[$this->provider])) {
            $config = $this->providerConfigs[$this->provider];
            $this->apiKey = $config['apiKey'];
            $this->apiUrl = $config['apiUrl'];
        }
    }

    /**
     * Process Voice Chat using Reliable 3-Step Pipeline
     * 1. STT: Whisper (via Groq/OpenAI) or Browser (Fallback)
     * 2. AI: Gemini 2.5 Flash (Text only)
     * 3. TTS: Edge TTS (Python)
     */
    public function processVoiceChat(User $user, $audioFile): array
    {
        try {
            // STEP 1: Transcribe
            // Ideally we use Groq for free Whisper, but for now let's try OpenAI Whisper again (maybe user has small quota left?)
            // OR BETTER: Since user is getting 500s from Gemini Audio, we assume they want FREE.
            // We'll use the Browser's STT text if they sent it (User Text), but AIVoiceController sends AUDIO.
            // Let's try to transcribe using Groq (Free Tier) if key exists, otherwise OpenAI.
            
            $transcription = $this->transcribeAudio($audioFile);
            if (!$transcription['success']) {
                return $transcription;
            }
            $userText = $transcription['text'];

            // STEP 2: Chat with Gemini (Text)
            $systemInstruction = "You are a friendly Financial Assistant (Female voice). Strictly discuss FINANCE only. Keep answers SHORT (1-2 sentences) for voice conversation. Speak Indonesian.";
            $prompt = $systemInstruction . "\n\nUser: " . $userText;
            
            // We use the 'gemini' provider config, assuming it's correctly set to gemini-2.5-flash (safe standard version)
            // Temporarily Force Standard Model if not set
            $this->providerConfigs['gemini']['model'] = 'gemini-2.5-flash'; 

            $aiResponse = $this->chat($user, $prompt); // This uses the standard text chat flow
            
            if (!$aiResponse['success']) {
                return ['success' => false, 'error' => 'Gagal bicara dengan AI.'];
            }
            
            $aiText = $aiResponse['response'];
            // Clean md
            $cleanText = strip_tags(str_replace(['*', '#', '`'], '', $aiText));

            // STEP 3: TTS using Edge TTS (Python)
            // Generate unique filename
            $filename = 'tts_' . uniqid() . '.mp3';
            $outputPath = public_path('audio/' . $filename);
            
            // Ensure directory exists and is writable
            $audioDir = public_path('audio');
            if (!is_dir($audioDir)) {
                if (!@mkdir($audioDir, 0777, true)) {
                    Log::error('Failed to create audio directory', ['path' => $audioDir]);
                    return ['success' => false, 'error' => 'Tidak dapat membuat direktori audio.'];
                }
            }
            
            if (!is_writable($audioDir)) {
                Log::error('Audio directory not writable', ['path' => $audioDir]);
                return ['success' => false, 'error' => 'Direktori audio tidak dapat ditulis.'];
            }

            // Call Python Script
            // voice: id-ID-GadisNeural or id-ID-ArdiNeural
            $scriptPath = base_path('python/tts.py');
            
            // Check if script exists
            if (!file_exists($scriptPath)) {
                Log::error('TTS Python script not found', ['path' => $scriptPath]);
                return ['success' => false, 'error' => 'Python TTS script tidak ditemukan.'];
            }
            
            // Use Python 3 explicitly and capture error output
            $command = "python3 \"$scriptPath\" \"" . addslashes($cleanText) . "\" \"$outputPath\" \"id-ID-GadisNeural\" 2>&1";
            
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                Log::error("EdgeTTS Failed", [
                    'cmd' => $command, 
                    'return_code' => $returnCode,
                    'output' => implode(' | ', $output)
                ]);
                return ['success' => false, 'error' => 'Gagal menghasilkan suara (TTS).'];
            }
            
            if (!file_exists($outputPath)) {
                Log::error('Audio file not created after TTS', ['path' => $outputPath]);
                return ['success' => false, 'error' => 'File audio tidak berhasil dibuat.'];
            }

            $audioData = file_get_contents($outputPath);
            if ($audioData === false) {
                Log::error('Failed to read audio file', ['path' => $outputPath]);
                @unlink($outputPath);
                return ['success' => false, 'error' => 'Gagal membaca file audio.'];
            }
            
            @unlink($outputPath); // Clean up temp file

            return [
                'success' => true, 
                'text' => $cleanText, 
                'audio' => base64_encode($audioData)
            ];

        } catch (\Exception $e) {
            Log::error('Voice Pipeline Error', ['message' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Terjadi kesalahan sistem.'];
        }
    }

    // Restore Transcribe using OpenAI (or Groq if we had key)
    // IMPORTANT: If OpenAI is out of quota, this will fail. 
    // BUT the frontend "Extreme" version sends AUDIO only.
    // If OpenAI fails, we have no STT.
    // OPTION: Use Gemini 2.5 Flash for STT only? (Audio -> Text)
    // Gemini supports Audio -> Text reliably (checking docs).
    public function transcribeAudio($file): array
    {
        try {
             // Try Gemini for STT (Free & Reliable for Audio->Text)
             $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');
             if (!$apiKey) {
                 Log::error('Gemini API Key missing for transcription');
                 return ['success' => false, 'error' => 'Konfigurasi API tidak lengkap.'];
             }
             
             $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
             
             $audioBase64 = base64_encode(file_get_contents($file->getRealPath()));
             $mimeType = $file->getMimeType();

             $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => "Transcribe this audio to text strictly. Do not add any commentary."],
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $audioBase64]]
                    ]
                ]]
             ];
             
             $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $payload);
                
             if ($response->successful()) {
                 $data = $response->json();
                 $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                 if ($text) return ['success' => true, 'text' => $text];
             }
             
             // Fallback to OpenAI if Gemini STT fails? (Probably useless if quota empty)
             return ['success' => false, 'error' => 'Gagal transkripsi suara.'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Helper to keep code clean
    private function callGeminiMultimodal(User $u, string $a, string $m): array { return []; }

    /* 
     * DEPRECATED METHODS (Kept for reference or cleanup later)
     */
    // public function transcribeAudio($file): array { return ['success' => false, 'error' => 'Deprecated']; }
    public function textToSpeech(string $text, string $voice = 'coral') { return null; }

    /**
     * Check if AI service is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) || $this->provider === 'meta';
    }

    /**
     * Send prompt to AI and get response.
     */
    public function chat(User $user, string $prompt, ?array $contextData = null, ?string $sessionId = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => true,
                'response' => "AI Service belum dikonfigurasi. Silakan cek .env file.",
                'response_time' => 0,
            ];
        }

        $startTime = microtime(true);
        
        // Get comprehensive system prompt
        $systemPrompt = $this->getComprehensiveSystemPrompt($contextData, $user);
        $fullPrompt = ($contextData ? $systemPrompt : "") . $prompt;

        try {
            // Route to appropriate provider
            if ($this->provider === 'meta') {
                $result = $this->callMetaAI($fullPrompt);
                $responseTime = (int) ((microtime(true) - $startTime) * 1000);
                
                if (!$result['success']) {
                    return [
                        'success' => false,
                        'error' => $result['error'],
                        'response_time' => $responseTime,
                    ];
                }
                
                $aiResponse = $result['response'];
            } else {
                $result = $this->callProviderAPI($fullPrompt);
                $responseTime = (int) ((microtime(true) - $startTime) * 1000);
                
                if (!$result['success']) {
                    return [
                        'success' => false,
                        'error' => $result['error'],
                        'response_time' => $responseTime,
                    ];
                }
                
                $aiResponse = $result['response'];
            }
            
            $this->logConversation($user, $prompt, $aiResponse, $contextData, null, $responseTime, $sessionId);
            
            return [
                'success' => true,
                'response' => $aiResponse,
                'response_time' => $responseTime,
            ];
        } catch (\Exception $e) {
            Log::error('AI Service Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Call the appropriate provider API
     */
    private function callProviderAPI(string $prompt): array
    {
        $config = $this->providerConfigs[$this->provider];
        
        // Common parameters
        $params = [
            'temperature' => $config['temperature'],
            'max_tokens' => $config['maxTokens'],
        ];
        
        // Provider-specific request formatting
        if ($this->provider === 'gemini') {
            return $this->callGeminiAPI($prompt, $config);
        } elseif ($this->provider === 'claude') {
            return $this->callClaudeAPI($prompt, $config);
        } else {
            // OpenAI-compatible format (deepseek, aiml, mulerouter, groq)
            return $this->callOpenAICompatibleAPI($prompt, $config);
        }
    }

    /**
     * Call OpenAI-compatible APIs
     */
    private function callOpenAICompatibleAPI(string $prompt, array $config): array
    {
        $requestParams = [
            'model' => $config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $config['systemPrompt']],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $config['temperature'],
            'max_tokens' => $config['maxTokens'],
        ];
        
        // Add provider-specific parameters
        if ($this->provider === 'groq' && isset($config['topP'])) {
            $requestParams['top_p'] = $config['topP'];
        }
        
        $response = Http::timeout(60)
            ->withToken($config['apiKey'])
            ->post($config['apiUrl'], $requestParams);
            
        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
            
            Log::error($this->provider . ' API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return [
                'success' => false,
                'error' => 'API Error: ' . $errorMessage . ' (Status: ' . $response->status() . ')',
            ];
        }
        
        $data = $response->json();
        $aiResponse = $data['choices'][0]['message']['content'] ?? 'Maaf, tidak ada respons.';
        
        return [
            'success' => true,
            'response' => $aiResponse,
        ];
    }

    /**
     * Call Gemini API
     */
    private function callGeminiAPI(string $prompt, array $config): array
    {
        $response = Http::timeout(60)
            ->post($config['apiUrl'] . '?key=' . $config['apiKey'], [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $config['systemPrompt'] . "\n\n" . $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $config['temperature'],
                    'maxOutputTokens' => $config['maxTokens'],
                ]
            ]);
            
        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
            
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return [
                'success' => false,
                'error' => 'API Error: ' . $errorMessage . ' (Status: ' . $response->status() . ')',
            ];
        }
        
        $data = $response->json();
        $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Maaf, tidak ada respons.';
        
        return [
            'success' => true,
            'response' => $aiResponse,
        ];
    }

    /**
     * Call Claude API
     */
    private function callClaudeAPI(string $prompt, array $config): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $config['apiKey'],
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json'
            ])
            ->post($config['apiUrl'], [
                'model' => $config['model'],
                'max_tokens' => $config['maxTokens'],
                'temperature' => $config['temperature'],
                'system' => $config['systemPrompt'],
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);
            
        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['error']['message'] ?? 'Unknown error';
            
            Log::error('Claude API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return [
                'success' => false,
                'error' => 'API Error: ' . $errorMessage . ' (Status: ' . $response->status() . ')',
            ];
        }
        
        $data = $response->json();
        $aiResponse = $data['content'][0]['text'] ?? 'Maaf, tidak ada respons.';
        
        return [
            'success' => true,
            'response' => $aiResponse,
        ];
    }

    /**
     * Call MetaAI using Python Bridge.
     */
    private function callMetaAI(string $prompt): array
    {
        $pythonScriptPath = base_path('app/Services/Python/meta_ai_bridge.py');
        $pythonExecutable = 'python'; // Or specify full path if needed

        $process = new \Symfony\Component\Process\Process([
            $pythonExecutable,
            $pythonScriptPath,
            $prompt
        ]);
        
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error('MetaAI Bridge Error', ['output' => $process->getErrorOutput()]);
            return [
                'success' => false,
                'error' => 'MetaAI Error: ' . $process->getErrorOutput(),
            ];
        }

        $output = $process->getOutput();
        $json = json_decode($output, true);

        if (isset($json['error'])) {
            return ['success' => false, 'error' => $json['error']];
        }

        return [
            'success' => true,
            'response' => $json['message'] ?? 'No response from MetaAI',
        ];
    }

    /**
     * Get comprehensive financial data for a user
     */
    public function getComprehensiveFinancialData(User $user): array
    {
        $cacheKey = "comprehensive_financial_data_{$user->id}";
        
        // TEMPORARY DEBUG: Disable Cache to isolate issues
        // return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            \Illuminate\Support\Facades\Log::info('AIService: Start generating financial data');
            
            // Get all transactions
            $transactions = Transaction::where('user_id', $user->id)
                ->orderBy('transaction_date', 'desc')
                ->get();
            
            \Illuminate\Support\Facades\Log::info('AIService: Transactions fetched count: ' . $transactions->count());

            // Calculate income and expense by month
            $monthlyData = $this->calculateMonthlyFinancials($transactions);
            \Illuminate\Support\Facades\Log::info('AIService: Monthly data calculated');
            
            // Get category breakdown
            $categoryBreakdown = $this->calculateCategoryBreakdown($transactions);
            
            // Get emergency fund data
            $emergencyFund = EmergencyFund::where('user_id', $user->id)->first();
            $emergencyFundTransactions = [];
            if ($emergencyFund) {
                $emergencyFundTransactions = DB::table('emergency_fund_transactions')
                    ->where('emergency_fund_id', $emergencyFund->id)
                    ->orderBy('date', 'desc')
                    ->get();
            }
            
            // Get recurring expenses
            $recurringExpenses = RecurringExpense::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();
            
            // Get digital assets
            $digitalAssets = DigitalAsset::where('user_id', $user->id)->get();
            
            // Get tasks
            $tasks = Task::where('user_id', $user->id)
                ->where('status', '!=', 'completed')
                ->orderBy('due_date', 'asc')
                ->get();
            
            // Get notes
            $notes = Note::where('user_id', $user->id)
                ->orderBy('is_pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get vault accounts count (without exposing sensitive data)
            $vaultAccountsCount = VaultAccount::where('user_id', $user->id)->count();
            
            // Calculate financial metrics
            $financialMetrics = $this->calculateFinancialMetrics(
                $transactions, 
                $emergencyFund, 
                $recurringExpenses, 
                $digitalAssets
            );
            
            // Detect spending patterns
            $spendingPatterns = $this->detectSpendingPatterns($transactions);
            
            // Predict future financial state
            $predictions = $this->predictFinancialFuture($monthlyData, $recurringExpenses);
            
            // Identify anomalies
            $anomalies = $this->detectFinancialAnomalies($transactions);
            
            return [
                'transactions' => $transactions,
                'monthly_data' => $monthlyData,
                'category_breakdown' => $categoryBreakdown,
                'emergency_fund' => $emergencyFund,
                'emergency_fund_transactions' => $emergencyFundTransactions,
                'recurring_expenses' => $recurringExpenses,
                'digital_assets' => $digitalAssets,
                'tasks' => $tasks,
                'notes' => $notes,
                'vault_accounts_count' => $vaultAccountsCount,
                'financial_metrics' => $financialMetrics,
                'spending_patterns' => $spendingPatterns,
                'predictions' => $predictions,
                'anomalies' => $anomalies,
            ];
        // });
    }

    /**
     * Calculate monthly financial data
     */
    private function calculateMonthlyFinancials($transactions): array
    {
        $monthlyData = [];
        
        // Group transactions by month
        $groupedTransactions = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->transaction_date)->format('Y-m');
        });
        
        foreach ($groupedTransactions as $month => $monthTransactions) {
            $income = $monthTransactions->where('type', 'income')->sum('amount');
            $expense = $monthTransactions->where('type', 'expense')->sum('amount');
            $netIncome = $income - $expense;
            $savingsRate = $income > 0 ? ($netIncome / $income) * 100 : 0;
            
            $monthlyData[$month] = [
                'income' => $income,
                'expense' => $expense,
                'net_income' => $netIncome,
                'savings_rate' => $savingsRate,
                'transaction_count' => $monthTransactions->count(),
                'income_transactions' => $monthTransactions->where('type', 'income')->count(),
                'expense_transactions' => $monthTransactions->where('type', 'expense')->count(),
            ];
        }
        
        // Sort by month (newest first)
        krsort($monthlyData);
        
        return $monthlyData;
    }

    /**
     * Calculate category breakdown
     */
    private function calculateCategoryBreakdown($transactions): array
    {
        $categoryBreakdown = [];
        
        // Group transactions by category
        $groupedTransactions = $transactions->groupBy('category_id');
        
        foreach ($groupedTransactions as $categoryId => $categoryTransactions) {
            $category = Category::find($categoryId);
            $categoryName = $category ? $category->name : 'Uncategorized';
            
            $income = $categoryTransactions->where('type', 'income')->sum('amount');
            $expense = $categoryTransactions->where('type', 'expense')->sum('amount');
            $total = $income + $expense;
            
            $categoryBreakdown[$categoryId] = [
                'name' => $categoryName,
                'income' => $income,
                'expense' => $expense,
                'total' => $total,
                'transaction_count' => $categoryTransactions->count(),
                'income_transactions' => $categoryTransactions->where('type', 'income')->count(),
                'expense_transactions' => $categoryTransactions->where('type', 'expense')->count(),
            ];
        }
        
        // Sort by total amount (highest first)
        uasort($categoryBreakdown, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });
        
        return $categoryBreakdown;
    }

    /**
     * Calculate comprehensive financial metrics
     */
    private function calculateFinancialMetrics($transactions, $emergencyFund, $recurringExpenses, $digitalAssets): array
    {
        // Total income and expense
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netIncome = $totalIncome - $totalExpense;
        
        // Calculate savings rate
        $savingsRate = $totalIncome > 0 ? ($netIncome / $totalIncome) * 100 : 0;
        
        // Calculate expense ratio
        $expenseRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) * 100 : 0;
        
        // Calculate average monthly income and expense
        $months = $transactions->groupBy(function($transaction) {
            return Carbon::parse($transaction->transaction_date)->format('Y-m');
        })->count();
        
        $avgMonthlyIncome = $months > 0 ? $totalIncome / $months : 0;
        $avgMonthlyExpense = $months > 0 ? $totalExpense / $months : 0;
        
        // Calculate burn rate
        $burnRate = $avgMonthlyExpense;
        
        // Emergency fund metrics
        $emergencyFundProgress = 0;
        $emergencyFundMonths = 0;
        $emergencyFundTarget = 0;
        
        if ($emergencyFund) {
            $emergencyFundTarget = $emergencyFund->target_amount;
            $emergencyFundProgress = $emergencyFundTarget > 0 ? 
                ($emergencyFund->current_amount / $emergencyFund->target_amount) * 100 : 0;
            $emergencyFundMonths = $avgMonthlyExpense > 0 ? 
                $emergencyFund->current_amount / $avgMonthlyExpense : 0;
        }
        
        // Calculate total recurring expenses
        $totalRecurringExpenses = $recurringExpenses->sum('amount');
        
        // Calculate total digital assets value
        $totalDigitalAssets = $digitalAssets->sum(function($asset) {
            return $asset->amount * $asset->current_price;
        });
        
        // Calculate financial health score
        $financialHealthScore = $this->calculateFinancialHealthScore(
            $savingsRate,
            $emergencyFundProgress,
            $expenseRatio,
            $totalRecurringExpenses,
            $avgMonthlyIncome
        );
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_income' => $netIncome,
            'savings_rate' => $savingsRate,
            'expense_ratio' => $expenseRatio,
            'avg_monthly_income' => $avgMonthlyIncome,
            'avg_monthly_expense' => $avgMonthlyExpense,
            'burn_rate' => $burnRate,
            'emergency_fund_progress' => $emergencyFundProgress,
            'emergency_fund_months' => $emergencyFundMonths,
            'emergency_fund_target' => $emergencyFundTarget,
            'total_recurring_expenses' => $totalRecurringExpenses,
            'total_digital_assets' => $totalDigitalAssets,
            'financial_health_score' => $financialHealthScore,
        ];
    }

    /**
     * Calculate financial health score (0-100)
     */
    private function calculateFinancialHealthScore(
        float $savingsRate, 
        float $emergencyFundProgress, 
        float $expenseRatio, 
        float $totalRecurringExpenses, 
        float $avgMonthlyIncome
    ): array {
        $score = 0;
        $details = [];
        
        // Savings Rate (30% of total score)
        if ($savingsRate > 20) {
            $score += 30;
            $details[] = "Savings Rate: " . number_format($savingsRate, 1) . "% (Excellent)";
        } elseif ($savingsRate >= 10) {
            $score += 20;
            $details[] = "Savings Rate: " . number_format($savingsRate, 1) . "% (Good)";
        } else {
            $score += 10;
            $details[] = "Savings Rate: " . number_format($savingsRate, 1) . "% (Needs improvement)";
        }
        
        // Emergency Fund (30% of total score)
        if ($emergencyFundProgress >= 100) {
            $score += 30;
            $details[] = "Emergency Fund: " . number_format($emergencyFundProgress, 1) . "% (Complete)";
        } elseif ($emergencyFundProgress >= 50) {
            $score += 20;
            $details[] = "Emergency Fund: " . number_format($emergencyFundProgress, 1) . "% (Good progress)";
        } else {
            $score += 10;
            $details[] = "Emergency Fund: " . number_format($emergencyFundProgress, 1) . "% (Needs attention)";
        }
        
        // Expense Management (20% of total score)
        if ($expenseRatio < 70) {
            $score += 20;
            $details[] = "Expense Ratio: " . number_format($expenseRatio, 1) . "% (Excellent)";
        } elseif ($expenseRatio <= 90) {
            $score += 10;
            $details[] = "Expense Ratio: " . number_format($expenseRatio, 1) . "% (Moderate)";
        } else {
            $score += 5;
            $details[] = "Expense Ratio: " . number_format($expenseRatio, 1) . "% (High)";
        }
        
        // Financial Planning (20% of total score)
        $hasRecurringExpenses = $totalRecurringExpenses > 0;
        $hasIncome = $avgMonthlyIncome > 0;
        
        if ($hasRecurringExpenses && $hasIncome) {
            $score += 20;
            $details[] = "Financial Planning: Excellent (tracking expenses and income)";
        } elseif ($hasRecurringExpenses || $hasIncome) {
            $score += 10;
            $details[] = "Financial Planning: Good (partial tracking)";
        } else {
            $score += 5;
            $details[] = "Financial Planning: Needs improvement (limited tracking)";
        }
        
        // Determine overall health
        $health = 'Poor';
        if ($score >= 80) {
            $health = 'Excellent';
        } elseif ($score >= 60) {
            $health = 'Good';
        } elseif ($score >= 40) {
            $health = 'Fair';
        }
        
        return [
            'total_score' => $score,
            'health' => $health,
            'details' => $details,
        ];
    }

    /**
     * Detect spending patterns
     */
    private function detectSpendingPatterns($transactions): array
    {
        $patterns = [];
        
        // Group by day of week
        $dayOfWeekSpending = $transactions
            ->where('type', 'expense')
            ->groupBy(function($transaction) {
                return Carbon::parse($transaction->transaction_date)->dayOfWeek;
            })
            ->map(function($dayTransactions) {
                return $dayTransactions->sum('amount');
            });
        
        // Find highest spending day
        $maxDay = $dayOfWeekSpending->keys()->max(function($day) use ($dayOfWeekSpending) {
            return $dayOfWeekSpending[$day];
        });
        
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $patterns['highest_spending_day'] = [
            'day' => $dayNames[$maxDay] ?? 'Unknown',
            'amount' => $dayOfWeekSpending[$maxDay] ?? 0,
        ];
        
        // Group by week of month
        $weekOfMonthSpending = $transactions
            ->where('type', 'expense')
            ->groupBy(function($transaction) {
                $date = Carbon::parse($transaction->transaction_date);
                return ceil($date->day / 7);
            })
            ->map(function($weekTransactions) {
                return $weekTransactions->sum('amount');
            });
        
        // Find highest spending week
        $maxWeek = $weekOfMonthSpending->keys()->max(function($week) use ($weekOfMonthSpending) {
            return $weekOfMonthSpending[$week];
        });
        
        $patterns['highest_spending_week'] = [
            'week' => "Week {$maxWeek}",
            'amount' => $weekOfMonthSpending[$maxWeek] ?? 0,
        ];
        
        // Detect impulse buying (large transactions in non-essential categories)
        $impulseCategories = ['Entertainment', 'Shopping', 'Food & Dining', 'Travel'];
        $impulseTransactions = $transactions
            ->where('type', 'expense')
            ->filter(function($transaction) use ($impulseCategories) {
                $category = Category::find($transaction->category_id);
                return $category && in_array($category->name, $impulseCategories);
            })
            ->sortByDesc('amount')
            ->take(5);
        
        $patterns['potential_impulse_purchases'] = $impulseTransactions->map(function($transaction) {
            $category = Category::find($transaction->category_id);
            return [
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'date' => $transaction->transaction_date,
                'category' => $category ? $category->name : 'Uncategorized',
            ];
        })->toArray();
        
        // Detect subscription fatigue (many recurring expenses in similar categories)
        $subscriptionCategories = ['Entertainment', 'Software', 'News & Magazines', 'Education'];
        $subscriptionExpenses = RecurringExpense::where('user_id', auth()->id())
            ->where('is_active', true)
            ->get()
            ->filter(function($expense) use ($subscriptionCategories) {
                $category = Category::find($expense->category_id);
                return $category && in_array($category->name, $subscriptionCategories);
            });
        
        $patterns['potential_subscription_fatigue'] = [
            'count' => $subscriptionExpenses->count(),
            'total_monthly' => $subscriptionExpenses->sum('amount'),
            'items' => $subscriptionExpenses->map(function($expense) {
                $category = Category::find($expense->category_id);
                return [
                    'name' => $expense->name,
                    'amount' => $expense->amount,
                    'category' => $category ? $category->name : 'Uncategorized',
                ];
            })->toArray(),
        ];
        
        return $patterns;
    }

    /**
     * Predict financial future based on trends
     */
    private function predictFinancialFuture($monthlyData, $recurringExpenses): array
    {
        $predictions = [];
        
        if (count($monthlyData) < 3) {
            $predictions['status'] = 'insufficient_data';
            $predictions['message'] = 'Need at least 3 months of data to make predictions';
            return $predictions;
        }
        
        // Get the last 3 months
        $recentMonths = array_slice($monthlyData, 0, 3, true);
        
        // Calculate trends
        $incomeTrend = $this->calculateTrend(
            array_column($recentMonths, 'income')
        );
        
        $expenseTrend = $this->calculateTrend(
            array_column($recentMonths, 'expense')
        );
        
        // Predict next 3 months
        $lastMonth = reset($recentMonths);
        $nextMonthPredictions = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $predictedIncome = $lastMonth['income'] + ($incomeTrend * $i);
            $predictedExpense = $lastMonth['expense'] + ($expenseTrend * $i);
            $predictedNetIncome = $predictedIncome - $predictedExpense;
            $predictedSavingsRate = $predictedIncome > 0 ? 
                ($predictedNetIncome / $predictedIncome) * 100 : 0;
            
            $nextMonthPredictions[] = [
                'month' => Carbon::now()->addMonths($i)->format('Y-m'),
                'predicted_income' => max(0, $predictedIncome),
                'predicted_expense' => max(0, $predictedExpense),
                'predicted_net_income' => $predictedNetIncome,
                'predicted_savings_rate' => $predictedSavingsRate,
            ];
        }
        
        // Calculate emergency fund projection
        $currentEmergencyFund = EmergencyFund::where('user_id', auth()->id())->first();
        $emergencyFundProjection = [];
        
        if ($currentEmergencyFund) {
            $currentAmount = $currentEmergencyFund->current_amount;
            $targetAmount = $currentEmergencyFund->target_amount;
            
            foreach ($nextMonthPredictions as $prediction) {
                // Assume 20% of net income goes to emergency fund
                $contribution = $prediction['predicted_net_income'] * 0.2;
                $currentAmount += $contribution;
                
                $emergencyFundProjection[] = [
                    'month' => $prediction['month'],
                    'projected_amount' => $currentAmount,
                    'projected_progress' => $targetAmount > 0 ? 
                        ($currentAmount / $targetAmount) * 100 : 0,
                    'monthly_contribution' => $contribution,
                ];
            }
        }
        
        // Detect potential issues
        $potentialIssues = [];
        
        if ($expenseTrend > 0 && $incomeTrend <= 0) {
            $potentialIssues[] = 'Expenses are increasing while income is stable or decreasing';
        }
        
        if ($incomeTrend < 0) {
            $potentialIssues[] = 'Income trend is decreasing';
        }
        
        $lastMonthSavingsRate = $lastMonth['savings_rate'];
        if ($lastMonthSavingsRate < 10) {
            $potentialIssues[] = 'Savings rate is below 10%';
        }
        
        $predictions['status'] = 'success';
        $predictions['income_trend'] = $incomeTrend;
        $predictions['expense_trend'] = $expenseTrend;
        $predictions['next_months'] = $nextMonthPredictions;
        $predictions['emergency_fund_projection'] = $emergencyFundProjection;
        $predictions['potential_issues'] = $potentialIssues;
        
        return $predictions;
    }

    /**
     * Calculate trend (positive/negative slope)
     */
    private function calculateTrend(array $values): float
    {
        if (count($values) < 2) {
            return 0;
        }
        
        $n = count($values);
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $values[$i];
            
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $denominator = $n * $sumX2 - $sumX * $sumX;
        
        if ($denominator == 0) {
            return 0;
        }
        
        return ($n * $sumXY - $sumX * $sumY) / $denominator;
    }

    /**
     * Detect financial anomalies
     */
    private function detectFinancialAnomalies($transactions): array
    {
        $anomalies = [];
        
        // Group transactions by type
        $incomeTransactions = $transactions->where('type', 'income');
        $expenseTransactions = $transactions->where('type', 'expense');
        
        // Calculate statistics
        $incomeStats = $this->calculateTransactionStats($incomeTransactions);
        $expenseStats = $this->calculateTransactionStats($expenseTransactions);
        
        // Detect unusually large income transactions
        $largeIncomeTransactions = $incomeTransactions->filter(function($transaction) use ($incomeStats) {
            return $transaction->amount > $incomeStats['mean'] + (2 * $incomeStats['std_dev']);
        })->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'date' => $transaction->transaction_date,
                'category' => Category::find($transaction->category_id)->name ?? 'Uncategorized',
            ];
        })->toArray();
        
        if (!empty($largeIncomeTransactions)) {
            $anomalies[] = [
                'type' => 'unusually_large_income',
                'description' => 'Detected unusually large income transactions',
                'transactions' => $largeIncomeTransactions,
            ];
        }
        
        // Detect unusually large expense transactions
        $largeExpenseTransactions = $expenseTransactions->filter(function($transaction) use ($expenseStats) {
            return $transaction->amount > $expenseStats['mean'] + (2 * $expenseStats['std_dev']);
        })->map(function($transaction) {
            return [
                'id' => $transaction->id,
                'description' => $transaction->description,
                'amount' => $transaction->amount,
                'date' => $transaction->transaction_date,
                'category' => Category::find($transaction->category_id)->name ?? 'Uncategorized',
            ];
        })->toArray();
        
        if (!empty($largeExpenseTransactions)) {
            $anomalies[] = [
                'type' => 'unusually_large_expense',
                'description' => 'Detected unusually large expense transactions',
                'transactions' => $largeExpenseTransactions,
            ];
        }
        
        // Detect gaps in transaction recording
        if ($transactions->isNotEmpty()) {
            $dates = $transactions->pluck('transaction_date')->sort()->values();
            $gaps = [];
            
            for ($i = 1; $i < $dates->count(); $i++) {
                $prevDate = Carbon::parse($dates[$i-1]);
                $currDate = Carbon::parse($dates[$i]);
                
                if ($prevDate->diffInDays($currDate) > 7) {
                    $gaps[] = [
                        'from' => $prevDate->format('Y-m-d'),
                        'to' => $currDate->format('Y-m-d'),
                        'days' => $prevDate->diffInDays($currDate),
                    ];
                }
            }
            
            if (!empty($gaps)) {
                $anomalies[] = [
                    'type' => 'gaps_in_recording',
                    'description' => 'Detected gaps in transaction recording',
                    'gaps' => $gaps,
                ];
            }
        }
        
        // Detect duplicate transactions
        $potentialDuplicates = [];
        $groupedTransactions = $transactions->groupBy(function($transaction) {
            return $transaction->amount . '_' . $transaction->type . '_' . 
                   Carbon::parse($transaction->transaction_date)->format('Y-m-d');
        });
        
        foreach ($groupedTransactions as $key => $group) {
            if ($group->count() > 1) {
                $potentialDuplicates[] = [
                    'amount' => $group->first()->amount,
                    'type' => $group->first()->type,
                    'date' => $group->first()->transaction_date,
                    'count' => $group->count(),
                    'transaction_ids' => $group->pluck('id')->toArray(),
                ];
            }
        }
        
        if (!empty($potentialDuplicates)) {
            $anomalies[] = [
                'type' => 'potential_duplicates',
                'description' => 'Detected potential duplicate transactions',
                'duplicates' => $potentialDuplicates,
            ];
        }
        
        return $anomalies;
    }

    /**
     * Calculate transaction statistics
     */
    private function calculateTransactionStats($transactions): array
    {
        if ($transactions->isEmpty()) {
            return [
                'count' => 0,
                'mean' => 0,
                'std_dev' => 0,
                'min' => 0,
                'max' => 0,
            ];
        }
        
        $amounts = $transactions->pluck('amount')->toArray();
        $count = count($amounts);
        $mean = array_sum($amounts) / $count;
        
        // Calculate standard deviation
        $variance = 0;
        foreach ($amounts as $amount) {
            $variance += pow($amount - $mean, 2);
        }
        $std_dev = sqrt($variance / $count);
        
        return [
            'count' => $count,
            'mean' => $mean,
            'std_dev' => $std_dev,
            'min' => min($amounts),
            'max' => max($amounts),
        ];
    }

    /**
     * Get financial advice from AI.
     */
    public function getFinancialAdvice(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = $this->buildFinancialAdvicePrompt($contextData);
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Ask AI about specific purchase decision.
     */
    public function askPurchaseAdvice(User $user, string $itemName, float $price, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = sprintf(
            "Saya ingin membeli %s seharga Rp %s. Apakah ini keputusan yang bijak?\n\n" .
            "Konteks keuangan saya:\n" .
            "- Penghasilan bulan ini: Rp %s\n" .
            "- Pengeluaran bulan ini: Rp %s\n" .
            "- Sisa uang: Rp %s\n" .
            "- Dana darurat: %s%%\n" .
            "- Financial Health Score: %s/100 (%s)\n" .
            "- Savings Rate: %.2f%%\n\n" .
            "Berikan saran yang jujur dan praktis dengan mempertimbangkan:\n" .
            "1. Dampak terhadap cash flow\n" .
            "2. Prioritas keuangan saat ini\n" .
            "3. Alternatif yang lebih baik\n" .
            "4. Jika memang perlu, bagaimana cara membelinya secara finansial sehat",
            $itemName,
            number_format($price, 0, ',', '.'),
            number_format($contextData['current_month']['income'], 0, ',', '.'),
            number_format($contextData['current_month']['expenses'], 0, ',', '.'),
            number_format($contextData['current_month']['net_income'], 0, ',', '.'),
            $contextData['emergency_fund']['progress'] ?? 0,
            $contextData['financial_metrics']['financial_health_score']['total_score'] ?? 0,
            $contextData['financial_metrics']['financial_health_score']['health'] ?? 'Unknown',
            $contextData['financial_metrics']['savings_rate'] ?? 0
        );
        
        return $this->chat($user, $prompt, array_merge($contextData, [
            'purchase' => [
                'item' => $itemName,
                'price' => $price,
            ]
        ]));
    }

    /**
     * Get budget optimization suggestions.
     */
    public function getBudgetOptimization(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = "Analisis keuangan saya dan berikan saran konkret untuk mengoptimalkan budget:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. Analisis kondisi keuangan menyeluruh\n" .
            "2. Area yang perlu diperbaiki dengan prioritas\n" .
            "3. Saran konkret dan actionable dengan angka spesifik\n" .
            "4. Target yang realistis dengan timeline\n" .
            "5. Strategi untuk mencapai financial freedom";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Get emergency fund advice
     */
    public function getEmergencyFundAdvice(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = "Analisis dana darurat saya dan berikan rekomendasi:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. Analisis status dana darurat saat ini\n" .
            "2. Target yang direkomendasikan (minimum, ideal, aman)\n" .
            "3. Strategi untuk mencapai target\n" .
            "4. Cara mengoptimalkan kontribusi bulanan\n" .
            "5. Timeline yang realistis untuk mencapai setiap target";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Get investment advice
     */
    public function getInvestmentAdvice(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = "Beri saya saran investasi yang sesuai dengan profil keuangan saya:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. Analisis profil risiko investasi saya\n" .
            "2. Alokasi aset yang direkomendasikan\n" .
            "3. Jenis investasi yang sesuai\n" .
            "4. Strategi diversifikasi\n" .
            "5. Target jangka pendek, menengah, dan panjang";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Get debt management advice
     */
    public function getDebtManagementAdvice(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = "Beri saya saran untuk mengelola hutang:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. Analisis utang (jika ada)\n" .
            "2. Strategi pelunasan yang efektif\n" .
            "3. Cara menghindari hutang baru\n" .
            "4. Tips negosiasi dengan kreditur\n" .
            "5. Timeline untuk bebas hutang";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Get retirement planning advice
     */
    public function getRetirementPlanningAdvice(User $user, FinanceService $financeService): array
    {
        $contextData = $financeService->getAIContextData($user);
        
        // Enhance context data with comprehensive financial data
        $comprehensiveData = $this->getComprehensiveFinancialData($user);
        $contextData = array_merge($contextData, $comprehensiveData);
        
        $prompt = "Beri saya saran perencanaan pensiun:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. Estimasi kebutuhan dana pensiun\n" .
            "2. Strategi investasi jangka panjang\n" .
            "3. Produk pensiun yang sesuai\n" .
            "4. Timeline pencapaian target\n" .
            "5. Cara mengoptimalkan tabungan pensiun";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Build financial advice prompt.
     */
    private function buildFinancialAdvicePrompt(array $contextData): string
    {
        return sprintf(
            "Kamu adalah asisten keuangan pribadi yang cerdas dan peduli.\n\n" .
            "Analisis data keuangan berikut:\n" .
            "- Penghasilan bulan ini: Rp %s\n" .
            "- Pengeluaran bulan ini: Rp %s\n" .
            "- Net income: Rp %s\n" .
            "- Saving rate: %.2f%%\n" .
            "- Dana darurat progress: %s%%\n" .
            "- Burn rate: Rp %s/bulan\n" .
            "- Financial Health Score: %.2f/100 (%s)\n\n" .
            "Analisis tambahan:\n" .
            "- Pola pengeluaran: %s\n" .
            "- Prediksi keuangan: %s\n" .
            "- Anomali terdeteksi: %s\n\n" .
            "Berikan:\n" .
            "1. Analisis singkat kondisi keuangan\n" .
            "2. Identifikasi risiko (jika ada)\n" .
            "3. 3 saran konkret yang bisa langsung diterapkan\n" .
            "4. Motivasi positif\n\n" .
            "Gunakan bahasa yang ramah dan mudah dipahami.",
            number_format($contextData['current_month']['income'], 0, ',', '.'),
            number_format($contextData['current_month']['expenses'], 0, ',', '.'),
            number_format($contextData['current_month']['net_income'], 0, ',', '.'),
            $contextData['current_month']['saving_rate'],
            $contextData['emergency_fund']['progress'] ?? 0,
            number_format($contextData['burn_rate'], 0, ',', '.'),
            $contextData['health_score']['total_score'],
            $contextData['health_score']['health'] ?? 'Unknown',
            !empty($contextData['spending_patterns']) ? json_encode($contextData['spending_patterns']) : 'Tidak ada data',
            !empty($contextData['predictions']) ? json_encode($contextData['predictions']) : 'Tidak ada data',
            !empty($contextData['anomalies']) ? json_encode($contextData['anomalies']) : 'Tidak ada anomali'
        );
    }

    /**
     * Log AI conversation.
     */
    private function logConversation(
        User $user,
        string $prompt,
        string $response,
        ?array $contextData,
        ?int $tokensUsed,
        int $responseTime,
        ?string $sessionId = null
    ): void {
        $title = null;
        if ($sessionId) {
            $exists = AiLog::where('session_id', $sessionId)->exists();
            if (!$exists) {
                $title = \Illuminate\Support\Str::words($prompt, 5, '...');
            }
        }

        AiLog::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'title' => $title,
            'prompt' => $prompt,
            'response' => $response,
            'context_data' => $contextData,
            'tokens_used' => $tokensUsed,
            'response_time' => $responseTime,
        ]);
    }

    /**
     * Get conversation history.
     */
    public function getConversationHistory(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $user->aiLogs()->recent($limit)->get();
    }

    /**
     * Parse text into various commands using AI.
     */
    public function parseVoiceCommand(User $user, string $text, array $categories): array
    {
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');
        
        $categoriesJson = json_encode($categories);

        $prompt = <<<EOT
You are a smart personal assistant.
Current Date: {$today}
Tomorrow is: {$tomorrow}

User Input: "{$text}"

Available Transaction Categories (JSON):
{$categoriesJson}

CRITICAL: TRANSACTION TYPE CLASSIFICATION RULES
================================================
EXPENSE Keywords (Indonesian) - These ALWAYS indicate money going OUT (expense):
- Beli, Membeli (Buy, Buying)
- Bayar, Membayar (Pay, Paying)
- Belanja, Berbelanja (Shop, Shopping)
- Buat (For/To make - when spending)
- Kirim, Mengirim (Send, Sending money)
- Transfer (Transfer money out)
- Keluar, Pengeluaran (Out, Expense)
- Cicil, Mencicil (Installment payment)
- Sewa, Menyewa (Rent, Renting)
- Langganan, Berlangganan (Subscribe, Subscription)

INCOME Keywords (Indonesian) - These indicate money coming IN (income):
- Terima, Menerima (Receive, Receiving)
- Dapat, Mendapat (Get, Getting)
- Gaji (Salary)
- Bonus (Bonus)
- Hadiah (Gift/Prize)
- Untung, Keuntungan (Profit)
- Masuk, Pemasukan (In, Income)
- Hasil, Penghasilan (Earnings)
- Komisi (Commission)

DEFAULT RULE: If unclear or ambiguous, classify as "expense" (safer assumption).

Task:
Analyze the input and determine the user's intent. Output a STRICT JSON OBJECT containing an "intent" field and a "data" field.

Possible Intents:
1. "transaction": For income or expense records.
   Data structure: [{"type": "income/expense", "amount": number, "description": string, "category_id": int/null, "date": "YYYY-MM-DD"}]
   Note: If multiple transactions, return array.
   IMPORTANT: Check keywords above to determine if type should be "income" or "expense"!

2. "task": For creating new tasks/reminders.
   Data structure: [{"title": string, "description": string, "due_date": "YYYY-MM-DD", "priority": "low/medium/high"}]
   Note: Default priority is "medium". Default due_date is today if not specified.

3. "emergency_fund": For managing the PRIMARY Emergency Fund (Dana Darurat) only.
   Data structure: {"action": "set_target" or "add_contribution" or "calculate_recommendation", "amount": number (optional)}
   Note: Use "calculate_recommendation" if user asks to calculate/automate the target.

4. "digital_income": For updating balance or withdrawing from digital wallets/income sources.
   Data structure: {"action": "update_balance" or "withdraw", "platform": "Dana/PayPal/etc", "amount": number}

5. "financial_goal": For specific savings goals (e.g. buying a laptop, car, holiday).
   Data structure: {"action": "create" or "add_contribution" or "complete", "title": string, "amount": number, "due_date": "YYYY-MM-DD"}
   Note:
   - "create": Create new goal. "amount" is target amount.
   - "add_contribution": Add savings to goal. "amount" is added value.
   - "complete": Mark goal as achieved (bought). "amount" is final purchase price (optional, default to target).

6. "unknown": If unclear.

Output STRICT JSON. No markdown.

EXAMPLES:
=========
Example 1 (EXPENSE - Purchase):
Input: "Beli kuota 60000"
{
    "intent": "transaction",
    "data": [
        {"type": "expense", "amount": 60000, "description": "Beli kuota", "category_id": null, "date": "{$today}"}
    ]
}

Example 2 (EXPENSE - Payment):
Input: "Bayar listrik 100000"
{
    "intent": "transaction",
    "data": [
        {"type": "expense", "amount": 100000, "description": "Bayar listrik", "category_id": null, "date": "{$today}"}
    ]
}

Example 3 (INCOME - Salary):
Input: "Terima gaji 5000000"
{
    "intent": "transaction",
    "data": [
        {"type": "income", "amount": 5000000, "description": "Terima gaji", "category_id": null, "date": "{$today}"}
    ]
}

Example 4 (Task):
{
    "intent": "task",
    "data": [
        {"title": "Bayar listrik", "description": "Jangan lupa bayar sebelum mati", "due_date": "{$tomorrow}", "priority": "high"}
    ]
}

Example 5 (Emergency Fund):
{
    "intent": "emergency_fund",
    "data": {"action": "set_target", "amount": 100000000}
}

Example 6 (Financial Goal):
{
    "intent": "financial_goal",
    "data": {"action": "create", "title": "Beli PS5", "amount": 8000000}
}

Example 7 (Digital Income):
{
    "intent": "digital_income",
    "data": {"action": "update_balance", "platform": "Dana", "amount": 500000}
}
EOT;

        $response = $this->chat($user, $prompt);

        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error'] ?? 'AI Error'];
        }

        // Clean up response to ensure it's valid JSON
        $jsonString = $response['response'];
        $jsonString = preg_replace('/^```json\s*|\s*```$/', '', trim($jsonString));
        $jsonString = preg_replace('/^```\s*|\s*```$/', '', trim($jsonString));

        $data = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('AI JSON Parse Error', ['response' => $response['response'], 'json_error' => json_last_error_msg()]);
            return ['success' => false, 'error' => 'Gagal memproses format data dari AI.'];
        }

        return ['success' => true, 'data' => $data];
    }

    /**
     * Get the comprehensive system prompt for financial analysis
     */
    private function getComprehensiveSystemPrompt(?array $contextData = null, ?User $user = null): string
    {
        $today = now()->format('Y-m-d');
        $userId = $user ? $user->id : auth()->id();
        
        return "# PLFIS AI FINANCIAL ASSISTANT - COMPREHENSIVE SYSTEM PROMPT\n\n" .
            "## IDENTITAS DAN PERAN UTAMA\n\n" .
            "Anda adalah AI Financial Assistant untuk aplikasi PLFIS (Personal & Life Financial Intelligence System). " .
            "Anda adalah asisten keuangan pribadi yang sangat cermat, akurat, dan detail dalam menganalisis data keuangan pengguna. " .
            "Tugas utama Anda adalah:\n\n" .
            "1. **Membaca dan menganalisis seluruh database keuangan pengguna dengan sangat teliti**\n" .
            "2. **Memberikan rekomendasi finansial yang akurat dan personal**\n" .
            "3. **Membantu pengguna membuat keputusan keuangan yang lebih baik**\n" .
            "4. **Mendeteksi pola pengeluaran dan peluang penghematan**\n" .
            "5. **Memprediksi tren keuangan masa depan**\n" .
            "6. **Mendeteksi anomali dan potensi masalah keuangan**\n\n" .
            
            "## PROTOKOL PEMBACAAN DATA - WAJIB DIIKUTI\n\n" .
            "### PRINSIP FUNDAMENTAL: COMPLETE DATA READING\n" .
            "Sebelum memberikan SETIAP jawaban atau rekomendasi, Anda HARUS:\n\n" .
            "1. **QUERY SEMUA TABEL DATABASE** - Baca data dari SEMUA tabel yang relevan\n" .
            "2. **VERIFIKASI DATA** - Cross-check data antar tabel untuk memastikan konsistensi\n" .
            "3. **AGREGASI LENGKAP** - Hitung total, rata-rata, dan metrik lainnya dari data mentah\n" .
            "4. **TIDAK BOLEH ASUMSI** - Jangan pernah mengasumsikan atau menebak data\n" .
            "5. **ANALISIS POLA** - Identifikasi pola dalam data keuangan\n" .
            "6. **DETEKSI ANOMALI** - Temukan transaksi atau tren yang tidak biasa\n" .
            "7. **PREDIKSI MASA DEPAN** - Berikan perkiraan berdasarkan tren historis\n\n" .
            
            "## DATA CONTEXT (JSON)\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            
            "## KALKULASI KEUANGAN YANG HARUS DILAKUKAN\n\n" .
            "### 1. CASH FLOW ANALYSIS\n" .
            "```\n" .
            "Net Income = Total Income - Total Expense\n\n" .
            "Monthly Average Income = Total Income / Jumlah Bulan dengan Data\n" .
            "Monthly Average Expense = Total Expense / Jumlah Bulan dengan Data\n\n" .
            "Savings Rate = (Net Income / Total Income) × 100%\n" .
            "Expense Ratio = (Total Expense / Total Income) × 100%\n" .
            "```\n\n" .
            
            "### 2. DANA DARURAT CALCULATION\n" .
            "```\n" .
            "Formula Standar Dana Darurat:\n" .
            "Dana Darurat Minimum = Monthly Expense × 3 (untuk karyawan)\n" .
            "Dana Darurat Ideal = Monthly Expense × 6-12 (untuk freelancer/wiraswasta)\n\n" .
            "Monthly Expense dihitung dari:\n" .
            "1. Rata-rata expense 3-6 bulan terakhir, ATAU\n" .
            "2. Total recurring expenses bulanan, ATAU\n" .
            "3. Jika tidak ada data: estimasi 70% dari income\n\n" .
            "Progress Dana Darurat:\n" .
            "Progress (%) = (Current Amount / Target Amount) × 100%\n" .
            "Kekurangan = Target Amount - Current Amount\n" .
            "Waktu Tercapai = Kekurangan / Monthly Savings\n" .
            "```\n\n" .
            
            "### 3. FINANCIAL HEALTH SCORE\n" .
            "```\n" .
            "Score dihitung dari:\n" .
            "1. Savings Rate (30%)\n" .
            "   - >20% = 30 poin\n" .
            "   - 10-20% = 20 poin\n" .
            "   - <10% = 10 poin\n\n" .
            "2. Emergency Fund Progress (30%)\n" .
            "   - >6 bulan = 30 poin\n" .
            "   - 3-6 bulan = 20 poin\n" .
            "   - <3 bulan = 10 poin\n\n" .
            "3. Expense Management (20%)\n" .
            "   - Expense < 70% income = 20 poin\n" .
            "   - 70-90% = 10 poin\n" .
            "   - >90% = 5 poin\n\n" .
            "4. Financial Planning (20%)\n" .
            "   - Ada recurring expense tracking = 10 poin\n" .
            "   - Ada target dana darurat = 10 poin\n\n" .
            "Total Score: 0-100\n" .
            "```\n\n" .
            
            "### 4. SPENDING PATTERN ANALYSIS\n" .
            "```\n" .
            "Analisis pola pengeluaran:\n" .
            "1. Hari dengan pengeluaran tertinggi\n" .
            "2. Minggu dengan pengeluaran tertinggi\n" .
            "3. Kategori dengan pertumbuhan tercepat\n" .
            "4. Deteksi impulse buying\n" .
            "5. Deteksi subscription fatigue\n" .
            "```\n\n" .
            
            "### 5. FINANCIAL ANOMALY DETECTION\n" .
            "```\n" .
            "Deteksi anomali:\n" .
            "1. Transaksi tidak biasa (besar/kecil)\n" .
            "2. Gap dalam pencatatan transaksi\n" .
            "3. Transaksi duplikat potensial\n" .
            "4. Perubahan drastis dalam pola pengeluaran\n" .
            "```\n\n" .
            
            "### 6. FINANCIAL PREDICTION\n" .
            "```\n" .
            "Prediksi keuangan:\n" .
            "1. Tren income 3 bulan ke depan\n" .
            "2. Tren expense 3 bulan ke depan\n" .
            "3. Proyeksi dana darurat\n" .
            "4. Potensi masalah keuangan\n" .
            "```\n\n" .
            
            "## FORMAT RESPONSE (WAJIB)\n\n" .
            "Gunakan struktur ini **SETIAP SAAT**:\n\n" .
            "### 🔍 Ringkasan Data Anda\n" .
            "(sebutkan angka real)\n\n" .
            "### 📊 Analisis Keuangan\n" .
            "(jelaskan logika & hubungan data)\n\n" .
            "### ⚠️ Temuan Penting\n" .
            "(jujur, walau pahit)\n\n" .
            "### ✅ Rekomendasi Nyata\n" .
            "(bisa langsung dilakukan)\n\n" .
            "### 🎯 Langkah Selanjutnya\n" .
            "(step-by-step)\n\n" .
            
            "## CONTOH RESPONSE YANG BENAR\n\n" .
            "User: 'Analisis keuangan saya'\n\n" .
            "RESPONSE:\n" .
            "📊 DATA ANALYSIS\n" .
            "Saya telah menganalisis seluruh data keuangan Anda:\n\n" .
            "INCOME (Total: Rp 600.000)\n" .
            "• 22 Des: Uang pip - Rp 200.000\n" .
            "• 22 Des: Jualan website (Penjualan) - Rp 350.000  \n" .
            "• 22 Des: Dari bekas sekolah (Pendidikan) - Rp 50.000\n\n" .
            "EXPENSE (Total: Rp 50.000)\n" .
            "• 22 Des: Makan - Rp 50.000\n\n" .
            "SUMMARY\n" .
            "• Net Income: Rp 550.000\n" .
            "• Savings Rate: 91.7% (sangat baik!)\n" .
            "• Expense Ratio: 8.3% dari income\n\n" .
            "CURRENT STATUS\n" .
            "• Dana Darurat Saat Ini: Rp 0\n" .
            "• Target Dana Darurat: Belum di-set\n" .
            "• Recurring Expenses: Tidak ada data\n\n" .
            "💡 INSIGHTS\n" .
            "1. Savings rate Anda EXCELLENT (91.7%) - jauh di atas standard 20%\n" .
            "2. Anda sangat disiplin dalam pengeluaran (hanya 8.3% dari income)\n" .
            "3. Income diversified: 58% dari jualan website, 33% uang pip, 9% lainnya\n" .
            "4. Namun belum ada dana darurat sama sekali - ini PRIORITY #1\n\n" .
            "Catatan: Data hanya dari 1 hari (22 Des). Untuk analisis lebih akurat, butuh data minimal 1-3 bulan.\n\n" .
            "✅ REKOMENDASI DANA DARURAT\n\n" .
            "Berdasarkan expense Rp 50.000 (atau estimasi konservatif Rp 100.000/bulan jika data lebih lengkap):\n\n" .
            "SCENARIO 1: Based on actual expense (Rp 50.000)\n" .
            "• Target Minimum (3 bulan): Rp 150.000 ⏱️ Bisa tercapai dalam 1 bulan\n" .
            "• Target Ideal (6 bulan): Rp 300.000 ⏱️ Bisa tercapai dalam 2 bulan\n" .
            "• Target Aman (12 bulan): Rp 600.000 ⏱️ Bisa tercapai dalam 4 bulan\n\n" .
            "SCENARIO 2: Konservatif (Rp 100.000/bulan)\n" .
            "• Target Minimum (3 bulan): Rp 300.000 ⏱️ Bisa tercapai dalam 2 bulan\n" .
            "• Target Ideal (6 bulan): Rp 600.000 ⏱️ Bisa tercapai dalam 4 bulan\n" .
            "• Target Aman (12 bulan): Rp 1.200.000 ⏱️ Bisa tercapai dalam 8 bulan\n\n" .
            "REKOMENDASI ALOKASI BULANAN (dari income Rp 550.000):\n" .
            "1. 🎯 Dana Darurat: Rp 300.000 (55%)\n" .
            "2. 💰 Investasi/Tabungan: Rp 150.000 (27%)\n" .
            "3. 🛍️ Expense Fleksibel: Rp 100.000 (18%)\n\n" .
            "ACTION STEPS:\n" .
            "1. Set target dana darurat di menu \"Dana Darurat\" → pilih Rp 600.000 (target 6 bulan)\n" .
            "2. Tambahkan recurring expenses jika ada (listrik, internet, dll)\n" .
            "3. Track semua transaksi 1-2 bulan ke depan untuk data lebih akurat\n" .
            "4. Setor Rp 300.000 ke dana darurat setiap dapat income\n\n" .
            "📈 KEY METRICS\n" .
            "• Savings Rate: 91.7% ⭐ (Target: >20%)\n" .
            "• Emergency Fund: 0% ⚠️ (Target: 100%)\n" .
            "• Financial Health Score: 45/100 (Bisa 85+ jika ada dana darurat!)\n" .
            "• Estimated Time to Ideal Emergency Fund: 2 bulan\n\n" .
            
            "## GAYA BAHASA\n\n" .
            "- Bahasa Indonesia santai tapi profesional\n" .
            "- Jujur dan tegas\n" .
            "- Gunakan emoji dengan bijak\n" .
            "- Fokus pada DATA dan ANGKA NYATA\n" .
            "- Berikan insight yang mendalam dan actionable\n" .
            "- Jelaskan konsep finansial dengan sederhana\n\n" .
            
            "## LARANGAN KERAS\n" .
            "❌ JANGAN katakan 'data tidak tersedia' jika ada di context\n" .
            "❌ JANGAN asumsikan angka (misal: 'kira-kira 50%')\n" .
            "❌ JANGAN jawab umum seperti artikel blog\n" .
            "❌ JANGAN buat kalkulasi sendiri tanpa data\n" .
            "❌ JANGAN berikan saran tanpa analisis data menyeluruh\n" .
            "❌ JANGAN abaikan anomali atau pola yang mencurigakan\n\n" .
            
            "---\n\n" .
            "USER MESSAGE:\n";
    }

    /**
     * Get provider-specific system prompts
     */
    private function getDeepseekSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getAimlSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getMulerouterSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getGroqSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getGeminiSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getClaudeSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    private function getMetaSystemPrompt(): string
    {
        return "You are a financial assistant for PLFIS. You are precise, analytical, and provide detailed financial insights based on user data.";
    }

    /**
     * Clear financial metrics cache
     */
    public function clearFinancialMetricsCache(User $user): void
    {
        $cacheKey = "comprehensive_financial_data_{$user->id}";
        Cache::forget($cacheKey);
    }

    /**
     * Get financial trend analysis
     */
    public function getFinancialTrendAnalysis(User $user, int $months = 6): array
    {
        $endDate = now();
        $startDate = now()->subMonths($months);
        
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();
        
        if ($transactions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data transaksi dalam periode yang dipilih'
            ];
        }
        
        // Group by month
        $monthlyData = $this->calculateMonthlyFinancials($transactions);
        
        // Calculate trends
        $incomeTrend = $this->calculateTrend(
            array_column(array_reverse($monthlyData), 'income')
        );
        
        $expenseTrend = $this->calculateTrend(
            array_column(array_reverse($monthlyData), 'expense')
        );
        
        $savingsRateTrend = $this->calculateTrend(
            array_column(array_reverse($monthlyData), 'savings_rate')
        );
        
        // Generate insights
        $insights = [];
        
        if ($incomeTrend > 0) {
            $insights[] = "Pendapatan Anda cenderung meningkat sebesar " . 
                         number_format($incomeTrend, 0, ',', '.') . " per bulan";
        } elseif ($incomeTrend < 0) {
            $insights[] = "Pendapatan Anda cenderung menurun sebesar " . 
                         number_format(abs($incomeTrend), 0, ',', '.') . " per bulan";
        } else {
            $insights[] = "Pendapatan Anda cenderung stabil";
        }
        
        if ($expenseTrend > 0) {
            $insights[] = "Pengeluaran Anda cenderung meningkat sebesar " . 
                         number_format($expenseTrend, 0, ',', '.') . " per bulan";
        } elseif ($expenseTrend < 0) {
            $insights[] = "Pengeluaran Anda cenderung menurun sebesar " . 
                         number_format(abs($expenseTrend), 0, ',', '.') . " per bulan";
        } else {
            $insights[] = "Pengeluaran Anda cenderung stabil";
        }
        
        if ($savingsRateTrend > 0) {
            $insights[] = "Tingkat tabungan Anda meningkat sebesar " . 
                         number_format($savingsRateTrend, 2, ',', '.') . "% per bulan";
        } elseif ($savingsRateTrend < 0) {
            $insights[] = "Tingkat tabungan Anda menurun sebesar " . 
                         number_format(abs($savingsRateTrend), 2, ',', '.') . "% per bulan";
        } else {
            $insights[] = "Tingkat tabungan Anda cenderung stabil";
        }
        
        // Predict next month
        $lastMonth = reset($monthlyData);
        $nextMonthIncome = $lastMonth['income'] + $incomeTrend;
        $nextMonthExpense = $lastMonth['expense'] + $expenseTrend;
        $nextMonthNetIncome = $nextMonthIncome - $nextMonthExpense;
        $nextMonthSavingsRate = $nextMonthIncome > 0 ? 
            ($nextMonthNetIncome / $nextMonthIncome) * 100 : 0;
        
        return [
            'success' => true,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'months' => $months
            ],
            'monthly_data' => $monthlyData,
            'trends' => [
                'income' => $incomeTrend,
                'expense' => $expenseTrend,
                'savings_rate' => $savingsRateTrend,
            ],
            'insights' => $insights,
            'next_month_prediction' => [
                'income' => max(0, $nextMonthIncome),
                'expense' => max(0, $nextMonthExpense),
                'net_income' => $nextMonthNetIncome,
                'savings_rate' => $nextMonthSavingsRate,
            ],
        ];
    }

    /**
     * Get category spending analysis
     */
    public function getCategorySpendingAnalysis(User $user, ?int $categoryId = null, int $months = 6): array
    {
        $endDate = now();
        $startDate = now()->subMonths($months);
        
        $query = Transaction::where('user_id', $user->id)
            ->where('type', 'expense');
        
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        $transactions = $query->orderBy('transaction_date', 'asc')->get();
        
        if ($transactions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data transaksi dalam periode yang dipilih'
            ];
        }
        
        // Group by month and category
        $monthlyCategoryData = [];
        
        foreach ($transactions as $transaction) {
            $month = Carbon::parse($transaction->date)->format('Y-m');
            $catId = $transaction->category_id;
            $categoryName = $transaction->category ? $transaction->category->name : 'Uncategorized';
            
            if (!isset($monthlyCategoryData[$month])) {
                $monthlyCategoryData[$month] = [];
            }
            
            if (!isset($monthlyCategoryData[$month][$catId])) {
                $monthlyCategoryData[$month][$catId] = [
                    'category_id' => $catId,
                    'category_name' => $categoryName,
                    'amount' => 0,
                    'count' => 0,
                ];
            }
            
            $monthlyCategoryData[$month][$catId]['amount'] += $transaction->amount;
            $monthlyCategoryData[$month][$catId]['count']++;
        }
        
        // Calculate totals and trends for each category
        $categoryAnalysis = [];
        
        foreach ($monthlyCategoryData as $month => $categories) {
            foreach ($categories as $catId => $data) {
                if (!isset($categoryAnalysis[$catId])) {
                    $categoryAnalysis[$catId] = [
                        'category_id' => $catId,
                        'category_name' => $data['category_name'],
                        'total_amount' => 0,
                        'total_count' => 0,
                        'monthly_data' => [],
                        'trend' => 0,
                    ];
                }
                
                $categoryAnalysis[$catId]['total_amount'] += $data['amount'];
                $categoryAnalysis[$catId]['total_count'] += $data['count'];
                $categoryAnalysis[$catId]['monthly_data'][$month] = $data;
            }
        }
        
        // Calculate trends
        foreach ($categoryAnalysis as $catId => &$analysis) {
            // Sort months
            ksort($analysis['monthly_data']);
            
            // Extract amounts for trend calculation
            $amounts = [];
            foreach ($analysis['monthly_data'] as $month => $data) {
                $amounts[] = $data['amount'];
            }
            
            if (count($amounts) >= 2) {
                $analysis['trend'] = $this->calculateTrend($amounts);
            }
        }
        
        // Sort by total amount
        uasort($categoryAnalysis, function($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });
        
        return [
            'success' => true,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'months' => $months
            ],
            'category_analysis' => array_values($categoryAnalysis),
        ];
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport(User $user, string $reportType = 'monthly', ?string $period = null): array
    {
        $endDate = now();
        $startDate = null;
        
        switch ($reportType) {
            case 'monthly':
                $startDate = now()->subMonth();
                break;
            case 'quarterly':
                $startDate = now()->subMonths(3);
                break;
            case 'yearly':
                $startDate = now()->subYear();
                break;
            case 'custom':
                if ($period) {
                    $dates = explode(',', $period);
                    if (count($dates) === 2) {
                        $startDate = Carbon::parse($dates[0]);
                        $endDate = Carbon::parse($dates[1]);
                    }
                }
                break;
        }
        
        if (!$startDate) {
            return [
                'success' => false,
                'message' => 'Periode tidak valid'
            ];
        }
        
        // Get comprehensive financial data for the period
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
        
        if ($transactions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data transaksi dalam periode yang dipilih'
            ];
        }
        
        // Calculate financial metrics
        $monthlyData = $this->calculateMonthlyFinancials($transactions);
        $categoryBreakdown = $this->calculateCategoryBreakdown($transactions);
        
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netIncome = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? ($netIncome / $totalIncome) * 100 : 0;
        
        // Get emergency fund data
        $emergencyFund = EmergencyFund::where('user_id', $user->id)->first();
        $emergencyFundProgress = 0;
        if ($emergencyFund && $emergencyFund->target_amount > 0) {
            $emergencyFundProgress = ($emergencyFund->current_amount / $emergencyFund->target_amount) * 100;
        }
        
        // Get financial health score
        $financialMetrics = $this->calculateFinancialMetrics(
            $transactions, 
            $emergencyFund, 
            RecurringExpense::where('user_id', $user->id)->where('is_active', true)->get(), 
            DigitalAsset::where('user_id', $user->id)->get()
        );
        
        // Detect spending patterns
        $spendingPatterns = $this->detectSpendingPatterns($transactions);
        
        // Generate report
        $report = [
            'report_type' => $reportType,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_income' => $netIncome,
                'savings_rate' => $savingsRate,
                'emergency_fund_progress' => $emergencyFundProgress,
                'financial_health_score' => $financialMetrics['financial_health_score'],
            ],
            'monthly_breakdown' => $monthlyData,
            'category_breakdown' => $categoryBreakdown,
            'spending_patterns' => $spendingPatterns,
            'top_expenses' => $transactions->where('type', 'expense')
                ->sortByDesc('amount')
                ->take(10)
                ->map(function($transaction) {
                    return [
                        'description' => $transaction->description,
                        'amount' => $transaction->amount,
                        'date' => $transaction->date,
                        'category' => $transaction->category ? $transaction->category->name : 'Uncategorized',
                    ];
                })
                ->toArray(),
            'top_income' => $transactions->where('type', 'income')
                ->sortByDesc('amount')
                ->take(10)
                ->map(function($transaction) {
                    return [
                        'description' => $transaction->description,
                        'amount' => $transaction->amount,
                        'date' => $transaction->date,
                        'category' => $transaction->category ? $transaction->category->name : 'Uncategorized',
                    ];
                })
                ->toArray(),
        ];
        
        return [
            'success' => true,
            'report' => $report,
        ];
    }

    /**
     * Get AI-powered financial insights
     */
    public function getFinancialInsights(User $user): array
    {
        $contextData = $this->getComprehensiveFinancialData($user);
        
        $prompt = "Beri saya insight keuangan yang mendalam dan actionable:\n\n" .
            "Data keuangan:\n" .
            json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n" .
            "Berikan:\n" .
            "1. 3 insight penting tentang keuangan saya\n" .
            "2. 2 peluang yang belum saya sadari\n" .
            "3. 2 area perbaikan prioritas\n" .
            "4. 1 strategi jangka panjang untuk financial freedom\n\n" .
            "Fokus pada insight yang spesifik dan actionable, bukan nasihat umum.";
        
        return $this->chat($user, $prompt, $contextData);
    }

    /**
     * Compare financial performance with previous period
     */
    public function compareFinancialPeriods(User $user, string $period1, string $period2): array
    {
        // Parse periods
        $dates1 = explode(',', $period1);
        $dates2 = explode(',', $period2);
        
        if (count($dates1) !== 2 || count($dates2) !== 2) {
            return [
                'success' => false,
                'message' => 'Format periode tidak valid. Gunakan format: YYYY-MM-DD,YYYY-MM-DD'
            ];
        }
        
        $startDate1 = Carbon::parse($dates1[0]);
        $endDate1 = Carbon::parse($dates1[1]);
        $startDate2 = Carbon::parse($dates2[0]);
        $endDate2 = Carbon::parse($dates2[1]);
        
        // Get transactions for both periods
        $transactions1 = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate1, $endDate1])
            ->get();
        
        $transactions2 = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$startDate2, $endDate2])
            ->get();
        
        if ($transactions1->isEmpty() || $transactions2->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak cukup data untuk periode yang dipilih'
            ];
        }
        
        // Calculate metrics for period 1
        $income1 = $transactions1->where('type', 'income')->sum('amount');
        $expense1 = $transactions1->where('type', 'expense')->sum('amount');
        $netIncome1 = $income1 - $expense1;
        $savingsRate1 = $income1 > 0 ? ($netIncome1 / $income1) * 100 : 0;
        
        // Calculate metrics for period 2
        $income2 = $transactions2->where('type', 'income')->sum('amount');
        $expense2 = $transactions2->where('type', 'expense')->sum('amount');
        $netIncome2 = $income2 - $expense2;
        $savingsRate2 = $income2 > 0 ? ($netIncome2 / $income2) * 100 : 0;
        
        // Calculate changes
        $incomeChange = $income1 > 0 ? (($income2 - $income1) / $income1) * 100 : 0;
        $expenseChange = $expense1 > 0 ? (($expense2 - $expense1) / $expense1) * 100 : 0;
        $netIncomeChange = $netIncome1 > 0 ? (($netIncome2 - $netIncome1) / abs($netIncome1)) * 100 : 0;
        $savingsRateChange = $savingsRate2 - $savingsRate1;
        
        // Get category breakdown for both periods
        $categoryBreakdown1 = $this->calculateCategoryBreakdown($transactions1);
        $categoryBreakdown2 = $this->calculateCategoryBreakdown($transactions2);
        
        // Compare categories
        $categoryComparison = [];
        
        foreach ($categoryBreakdown1 as $catId => $data1) {
            $data2 = $categoryBreakdown2[$catId] ?? null;
            
            if ($data2) {
                $change = $data1['total'] > 0 ? (($data2['total'] - $data1['total']) / $data1['total']) * 100 : 0;
                
                $categoryComparison[] = [
                    'category_id' => $catId,
                    'category_name' => $data1['name'],
                    'period1_total' => $data1['total'],
                    'period2_total' => $data2['total'],
                    'change_percent' => $change,
                ];
            }
        }
        
        // Sort by absolute change
        usort($categoryComparison, function($a, $b) {
            return abs($b['change_percent']) <=> abs($a['change_percent']);
        });
        
        return [
            'success' => true,
            'period1' => [
                'start_date' => $startDate1->format('Y-m-d'),
                'end_date' => $endDate1->format('Y-m-d'),
                'income' => $income1,
                'expense' => $expense1,
                'net_income' => $netIncome1,
                'savings_rate' => $savingsRate1,
            ],
            'period2' => [
                'start_date' => $startDate2->format('Y-m-d'),
                'end_date' => $endDate2->format('Y-m-d'),
                'income' => $income2,
                'expense' => $expense2,
                'net_income' => $netIncome2,
                'savings_rate' => $savingsRate2,
            ],
            'changes' => [
                'income_change_percent' => $incomeChange,
                'expense_change_percent' => $expenseChange,
                'net_income_change_percent' => $netIncomeChange,
                'savings_rate_change_percent' => $savingsRateChange,
            ],
            'category_comparison' => $categoryComparison,
        ];
    }

    /**
     * Get system prompt specifically for Voice Mode
     */
    public function getVoiceModePrompt(User $user): string
    {
        return "Anda adalah Asisten Keuangan Pribadi yang berbicara melalui telepon. Nama pengguna adalah {$user->name}.
        
        INSTRUKSI KHUSUS MODE SUARA:
        1. Jawablah dengan sangat singkat, padat, dan natural seperti percakapan telepon (maksimal 2-3 kalimat).
        2. Gunakan bahasa lisan yang sopan, hangat, dan ramah (female persona 'Gadis').
        3. JANGAN gunakan format markdown (bold, list, table) karena ini akan diubah menjadi suara. Hindari simbol '*'.
        4. TOPIK DIBATASI HANYA KEUANGAN. Jika pengguna bertanya hal lain (film, politik, resep, dll), tolak dengan sopan dan arahkan kembali ke keuangan.
           Contoh penolakan: 'Maaf kak, saat ini aku cuma bisa bantu soal keuangan nih. Ada pengeluaran yang mau dicatat?'
        5. Jika ada angka uang, sebutkan dengan jelas (misal 'lima puluh ribu rupiah' bukan 'Rp 50.000').
        6. Jadilah proaktif tapi santai.
        
        Konteks Keuangan Singkat:
        - Fokus pada cashflow dan budget hari ini.";
    }
}