<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AIController extends Controller
{
    protected $aiService;
    protected $financeService;

    public function __construct(AIService $aiService, FinanceService $financeService)
    {
        $this->aiService = $aiService;
        $this->financeService = $financeService;
    }

    /**
     * Display AI assistant interface.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get conversation history
        $history = $this->aiService->getConversationHistory($user, 20);

        return view('ai.index', compact('history'));
    }

    /**
     * Send message to AI.
     */
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = auth()->user();
        $sessionId = $request->input('session_id');
        
        $contextData = $this->financeService->getAIContextData($user);
        $result = $this->aiService->chat($user, $request->message, $contextData, $sessionId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'response' => $result['response'],
                'response_time' => $result['response_time'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Get financial advice from AI.
     */
    public function financialAdvice()
    {
        $user = auth()->user();
        $result = $this->aiService->getFinancialAdvice($user, $this->financeService);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'advice' => $result['response'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Ask AI about purchase decision.
     */
    public function purchaseAdvice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = auth()->user();
        $result = $this->aiService->askPurchaseAdvice(
            $user,
            $request->item_name,
            $request->price,
            $this->financeService
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'advice' => $result['response'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Get budget optimization suggestions.
     */
    public function budgetOptimization()
    {
        $user = auth()->user();
        $result = $this->aiService->getBudgetOptimization($user, $this->financeService);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'suggestions' => $result['response'],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Get conversation history.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $limit = $request->get('limit', 50); // Increased limit
        $sessionId = $request->get('session_id');
        
        $query = $user->aiLogs()->orderBy('created_at', 'asc'); // Chronological for chat view

        if ($sessionId) {
            $query->where('session_id', $sessionId);
        } else {
             // For main view, maybe just recent? Or empty?
             // If no session ID, maybe return nothing or specific default logic.
             // But existing logic was recent(limit).
             if (!$sessionId) $query->recent($limit); 
        }

        $history = $query->get();

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }

    /**
     * Get list of chat sessions.
     */
    public function sessions()
    {
        $user = auth()->user();
        
        $sessions = \App\Models\AiLog::where('user_id', $user->id)
            ->whereNotNull('session_id')
            ->select('session_id', 'title')
            ->selectRaw('MAX(created_at) as last_activity, MAX(is_pinned) as is_pinned')
            ->groupBy('session_id', 'title')
            ->orderByRaw('MAX(is_pinned) DESC, MAX(created_at) DESC')
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    public function deleteSession(Request $request)
    {
        $request->validate(['session_id' => 'required|string']);
        
        \App\Models\AiLog::where('user_id', auth()->id())
            ->where('session_id', $request->session_id)
            ->delete();
            
        return response()->json(['success' => true]);
    }

    public function togglePinSession(Request $request)
    {
        $request->validate(['session_id' => 'required|string']);
        $uuid = $request->session_id;
        
        $exists = \App\Models\AiLog::where('user_id', auth()->id())
            ->where('session_id', $uuid)
            ->first();
            
        if($exists) {
            $newStatus = !$exists->is_pinned;
            \App\Models\AiLog::where('user_id', auth()->id())
                ->where('session_id', $uuid)
                ->update(['is_pinned' => $newStatus]);
        }
        
        return response()->json(['success' => true]);
    }
}
