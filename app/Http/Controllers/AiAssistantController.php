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
            'demandMemory' => $ai->monthlyDemandMemory(),
            'summary' => $ai->monthlySummary(),
            'summaryReport' => $ai->monthlySummaryReport(),
            'suggestions' => $user ? $ai->chatSuggestions($user) : ['Low stock', 'Help'],
            'role' => $user?->getRoleNames()->first(),
        ]);
    }

    public function restock(AiInsightService $ai): View
    {
        abort_unless(auth()->user()?->hasAnyRole(['Supply Personnel', 'Administrator']), 403);

        return view('ai.restock', [
            'recommendations' => $ai->restockRecommendations(50),
            'demandMemory' => $ai->monthlyDemandMemory(),
            'summary' => $ai->monthlySummary(),
            'summaryReport' => $ai->monthlySummaryReport(),
            'semesterForecast' => $ai->currentSemesterTrendForecast(8),
        ]);
    }

    public function ask(Request $request, AiInsightService $ai): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['nullable', 'array', 'max:6'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:500'],
        ]);

        if (! $user) {
            return response()->json([
                'reply' => 'Please sign in to use the AI assistant.',
            ], 401);
        }

        try {
            return response()->json([
                'reply' => $ai->chatResponse($user, $data['message'], $data['history'] ?? []),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'I could not finish that answer. Try "How many items?", an item name, or pick a listed question.',
            ]);
        }
    }
}
