<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AssistantMessage;
use App\Services\HuggingFaceService;
use App\Support\AiContentFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AssistantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $messages = $request->user()->assistantMessages()->latest('id')->limit(30)->get()->reverse()->values();

        return response()->json(['messages' => $messages->map(fn ($message) => $this->formatMessage($message))]);
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

        return response()->json([
            'user_message' => $this->formatMessage($userMessage),
            'assistant_message' => $this->formatMessage($assistantMessage),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->assistantMessages()->delete();

        return response()->json(['message' => 'Riwayat percakapan dihapus.']);
    }

    private function formatMessage(AssistantMessage $message): array
    {
        return [
            'id' => $message->id,
            'role' => $message->role,
            'content' => $message->content,
            'rendered_content' => $message->role === 'assistant'
                ? AiContentFormatter::toHtml($message->content)
                : null,
        ];
    }
}
