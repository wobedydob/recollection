<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tags = $request->user()
            ->tags()
            ->orderBy('created_at')
            ->get();

        $result = $tags->map(fn($tag) => $this->formatTag($tag));

        return response()->json(['tags' => $result]);
    }

    public function store(Request $request): JsonResponse
    {
        $name = trim($request->input('name', ''));
        $color = $request->input('color', '');
        $emoji = $request->input('emoji');

        if (!$name) {
            return response()->json(['message' => 'Naam is verplicht'], 400);
        }

        if (strlen($name) > 25) {
            return response()->json(['message' => 'Naam mag maximaal 25 tekens zijn'], 400);
        }

        if (!$color) {
            return response()->json(['message' => 'Kleur is verplicht'], 400);
        }

        $tag = $request->user()->tags()->create([
            'name' => $name,
            'color' => $color,
            'emoji' => $emoji ?: null,
        ]);

        return response()->json(['tag' => $this->formatTag($tag)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $tag = $request->user()->tags()->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag niet gevonden'], 404);
        }

        $name = $request->input('name');
        $color = $request->input('color');
        $emoji = $request->input('emoji');

        if ($name !== null) {
            $name = trim($name);
            if (!$name) {
                return response()->json(['message' => 'Naam is verplicht'], 400);
            }
            if (strlen($name) > 25) {
                return response()->json(['message' => 'Naam mag maximaal 25 tekens zijn'], 400);
            }
            $tag->name = $name;
        }

        if ($color !== null) {
            $tag->color = $color;
        }

        if ($emoji !== null) {
            $tag->emoji = $emoji ?: null;
        }

        $tag->save();

        return response()->json(['tag' => $this->formatTag($tag)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $tag = $request->user()->tags()->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag niet gevonden'], 404);
        }

        $tag->delete();

        return response()->json(['success' => true]);
    }

    private function formatTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
            'emoji' => $tag->emoji ?: null,
        ];
    }
}
