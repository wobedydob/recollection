<?php

namespace App\Http\Controllers;

use App\Models\TodoItem;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoItemController extends Controller
{
    public function index(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        return response()->json([
            'items' => $list->items->map(fn($item) => $this->formatItem($item))
        ]);
    }

    public function store(Request $request, TodoList $list): JsonResponse
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $title = trim($request->input('title', ''));

        if (!$title) {
            return response()->json(['message' => 'Titel is verplicht'], 400);
        }

        $maxPosition = $list->items()->max('position') ?? 0;

        $item = $list->items()->create([
            'title' => $title,
            'description' => $request->input('description'),
            'due_date' => $request->input('due_date'),
            'priority' => $request->input('priority', 'medium'),
            'position' => $maxPosition + 1,
        ]);

        return response()->json(['item' => $this->formatItem($item)]);
    }

    public function update(Request $request, TodoItem $item): JsonResponse
    {
        if ($item->todoList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $item->update($request->only(['title', 'description', 'due_date', 'priority', 'position']));

        return response()->json(['item' => $this->formatItem($item)]);
    }

    public function destroy(Request $request, TodoItem $item): JsonResponse
    {
        if ($item->todoList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $item->delete();

        return response()->json(['success' => true]);
    }

    public function toggle(Request $request, TodoItem $item): JsonResponse
    {
        if ($item->todoList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Niet gevonden'], 404);
        }

        $item->completed_at = $item->is_completed ? null : now();
        $item->save();

        return response()->json(['item' => $this->formatItem($item)]);
    }

    private function formatItem(TodoItem $item): array
    {
        return [
            'id' => $item->id,
            'listId' => $item->todo_list_id,
            'title' => $item->title,
            'description' => $item->description,
            'dueDate' => $item->due_date?->format('Y-m-d'),
            'priority' => $item->priority,
            'completedAt' => $item->completed_at?->getTimestampMs(),
            'isCompleted' => $item->is_completed,
            'position' => $item->position,
        ];
    }
}
