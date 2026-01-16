<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdeaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ideas = $request->user()
            ->ideas()
            ->with('tags:id')
            ->orderByDesc('created_at')
            ->get();

        $result = $ideas->map(fn($idea) => $this->formatIdea($idea));

        return response()->json(['ideas' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $content = trim($request->input('content', ''));
        $tagIds = $request->input('tagIds', []);

        if (!$content) {
            return response()->json(['message' => 'Inhoud is verplicht'], 400);
        }

        $idea = $request->user()->ideas()->create([
            'content' => $content,
        ]);

        $tagIds = array_filter($tagIds, fn($tid) => !empty($tid));
        if (!empty($tagIds)) {
            $idea->tags()->attach($tagIds);
        }

        $idea->load('tags:id');

        return response()->json(['idea' => $this->formatIdea($idea)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $idea = $request->user()->ideas()->find($id);

        if (!$idea) {
            return response()->json(['message' => 'Idee niet gevonden'], 404);
        }

        $content = $request->input('content');
        $tagIds = $request->input('tagIds');

        if ($content !== null) {
            $idea->content = trim($content);
            $idea->save();
        }

        if ($tagIds !== null) {
            $tagIds = array_filter($tagIds, fn($tid) => !empty($tid));
            $idea->tags()->sync($tagIds);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $idea = $request->user()->ideas()->find($id);

        if (!$idea) {
            return response()->json(['message' => 'Idee niet gevonden'], 404);
        }

        $idea->delete();

        return response()->json(['success' => true]);
    }

    private function formatIdea(Idea $idea): array
    {
        return [
            'id' => $idea->id,
            'content' => $idea->content,
            'tagIds' => $idea->tags->pluck('id')->toArray(),
            'createdAt' => $idea->created_at->getTimestampMs(),
        ];
    }
}
