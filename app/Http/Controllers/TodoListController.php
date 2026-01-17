<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TodoListController extends Controller
{
    public function index(): View
    {
        return view('todo.index');
    }

    public function show(Request $request, TodoList $list): View
    {
        if ($list->user_id !== $request->user()->id) {
            abort(404);
        }

        return view('todo.show', ['listId' => $list->id]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        $lists = $request->user()
            ->todoLists()
            ->withCount('items')
            ->orderBy('position')
            ->get();

        return response()->json([
            'lists' => $lists->map(fn($list) => $this->formatListSummary($list))
        ]);
    }

    public function apiShow(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $list->load('items');

        return response()->json(['list' => $this->formatList($list)]);
    }

    public function store(Request $request): JsonResponse
    {
        $name = trim($request->input('name', ''));

        if (!$name) {
            return response()->json(['message' => 'Naam is verplicht'], 400);
        }

        $maxPosition = $request->user()->todoLists()->max('position') ?? 0;

        $list = $request->user()->todoLists()->create([
            'name' => $name,
            'emoji' => $request->input('emoji'),
            'color' => $request->input('color'),
            'position' => $maxPosition + 1,
        ]);

        return response()->json(['list' => $this->formatList($list)]);
    }

    public function update(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $list->update($request->only(['name', 'emoji', 'color', 'position']));

        return response()->json(['list' => $this->formatList($list)]);
    }

    public function destroy(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $list->delete();

        return response()->json(['success' => true]);
    }

    private function formatListSummary(TodoList $list): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name,
            'emoji' => $list->emoji,
            'color' => $list->color,
            'position' => $list->position,
            'itemCount' => $list->items_count ?? 0,
        ];
    }

    private function formatList(TodoList $list): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name,
            'emoji' => $list->emoji,
            'color' => $list->color,
            'position' => $list->position,
            'items' => $list->items->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'dueDate' => $item->due_date?->format('Y-m-d'),
                'priority' => $item->priority,
                'completedAt' => $item->completed_at?->getTimestampMs(),
                'isCompleted' => $item->is_completed,
                'position' => $item->position,
            ])->toArray(),
        ];
    }
}
