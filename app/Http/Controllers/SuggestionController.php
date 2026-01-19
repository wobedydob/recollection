<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suggestions = $request->user()
            ->suggestions()
            ->orderByDesc('created_at')
            ->get();

        $result = $suggestions->map(fn($suggestion) => $this->formatSuggestion($suggestion));

        return response()->json(['suggestions' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $content = trim($request->input('content', ''));

        if (!$content) {
            return response()->json(['message' => 'Inhoud is verplicht'], 400);
        }

        $suggestion = $request->user()->suggestions()->create([
            'content' => $content,
            'status' => 'new',
        ]);

        return response()->json(['suggestion' => $this->formatSuggestion($suggestion)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $suggestion = $request->user()->suggestions()->find($id);

        if (!$suggestion) {
            return response()->json(['message' => 'Suggestie niet gevonden'], 404);
        }

        $suggestion->delete();

        return response()->json(['success' => true]);
    }

    private function formatSuggestion(Suggestion $suggestion): array
    {
        return [
            'id' => $suggestion->id,
            'content' => $suggestion->content,
            'status' => $suggestion->status,
            'createdAt' => $suggestion->created_at->getTimestampMs(),
        ];
    }
}
