<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\HuggingFaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AssistantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = $request->user()->assistantMessages()->latest('id')->limit(30)->get()->reverse()->values();

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, HuggingFaceService $ai): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $user = $request->user();
        $history = $user->assistantMessages()->latest('id')->limit(10)->get()->reverse()
            ->map->only(['role', 'content'])->values()->all();
        $userMessage = $user->assistantMessages()->create(['role' => 'user', 'content' => $data['message']]);

        try {
            $reply = $ai->assistantReply($user, $history, $data['message']);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage(), 'user_message' => $userMessage], 422);
        }

        $assistantMessage = $user->assistantMessages()->create(['role' => 'assistant', 'content' => $reply]);

        return response()->json(['user_message' => $userMessage, 'assistant_message' => $assistantMessage]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->assistantMessages()->delete();

        return response()->json(['message' => 'Riwayat percakapan dihapus.']);
    }
}
