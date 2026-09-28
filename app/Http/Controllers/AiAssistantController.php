<?php

namespace App\Http\Controllers;

use App\Services\AiInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAssistantController extends Controller
{
    public function chat(AiInsightService $ai): View
    {
        $user = auth()->user();

        return view('ai.chat', [
            'forecasts' => $ai->inventoryForecasts(8, 14),
            'summary' => $ai->monthlySummary(),
            'suggestions' => $user ? $ai->chatSuggestions($user) : ['Low stock', 'Help'],
            'role' => $user?->getRoleNames()->first(),
        ]);
    }

    public function restock(AiInsightService $ai): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Supply Personnel', 'Administrator']), 403);

        return view('ai.restock', [
            'recommendations' => $ai->restockRecommendations(50),
            'summary' => $ai->monthlySummary(),
        ]);
    }

    public function ask(Request $request, AiInsightService $ai): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        if (! $user) {
            return response()->json([
                'reply' => 'Please sign in to use the AI assistant.',
            ], 401);
        }

        return response()->json([
            'reply' => $ai->chatResponse($user, $data['message']),
        ]);
    }
}
