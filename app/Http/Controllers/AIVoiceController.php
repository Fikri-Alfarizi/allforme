<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIVoiceController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Handle Voice Chat Request
     * 
     * Flow:
     * 1. Receive Audio Blob
     * 2. Transcribe (Whisper)
     * 3. AI Processing (Finance Guard)
     * 4. TTS (Coral Voice)
     * 5. Return Audio + Text
     */
    public function chat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file|mimes:webm,wav,mp3,ogg|max:10240', // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()->first()], 400);
            }

            $user = auth()->user();
            $audioFile = $request->file('audio');

            Log::info("Voice Chat Request from User ID: {$user->id}");

            // Process the voice chat
            $result = $this->aiService->processVoiceChat($user, $audioFile);

            if (!$result['success']) {
                return response()->json([
                    'success' => false, 
                    'error' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'user_text' => $result['text'] ?? 'Voice Message', // Generic text as Gemini Audio-to-Audio likely doesn't return user transcript
                'ai_text' => $result['text'] ?? 'Voice Response',
                'audio_url' => 'data:audio/mp3;base64,' . $result['audio'] // Handle base64 directly or ensure frontend handles it
            ]);

        } catch (\Exception $e) {
            Log::error('Voice Controller Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}
