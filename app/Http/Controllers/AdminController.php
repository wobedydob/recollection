<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Suggestion;
use App\Models\Tag;
use App\Models\TodoList;
use App\Models\TodoItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'ideas' => Idea::count(),
            'tags' => Tag::count(),
            'checklists' => TodoList::count(),
            'tasks' => TodoItem::count(),
            'suggestions' => Suggestion::count(),
            'new_suggestions' => Suggestion::where('status', 'new')->count(),
        ];

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }

    public function users(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->withCount(['ideas', 'todoLists'])->latest()->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'users' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'ideas_count' => $user->ideas_count,
                        'todo_lists_count' => $user->todo_lists_count,
                        'created_at' => $user->created_at->translatedFormat('d M Y'),
                        'show_url' => route('admin.users.show', $user),
                    ];
                }),
                'has_more_pages' => $users->hasMorePages(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);
        }

        return view('admin.users.index', compact('users'));
    }

    public function showUser(User $user): View
    {
        $stats = [
            'ideas' => $user->ideas()->count(),
            'tags' => $user->tags()->count(),
            'checklists' => $user->todoLists()->count(),
            'tasks' => TodoItem::whereIn('todo_list_id', $user->todoLists()->pluck('id'))->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function editUser(User $user): View
    {
        $user->loadCount(['ideas', 'tags', 'todoLists']);

        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:user,admin',
        ]);

        // Prevent removing the last admin
        if ($user->isAdmin() && $request->role !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'Er moet minimaal één admin zijn.']);
            }
        }

        $user->update($request->only(['name', 'email', 'role']));

        return redirect()->route('admin.users.show', $user)->with('success', 'Gebruiker bijgewerkt.');
    }

    public function deleteUser(User $user): RedirectResponse
    {
        // Prevent deleting the last admin
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['delete' => 'Je kunt de laatste admin niet verwijderen.']);
            }
        }

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return back()->withErrors(['delete' => 'Je kunt jezelf niet verwijderen via het admin panel.']);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Gebruiker verwijderd.');
    }

    public function suggestions(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $stats = [
            'total' => Suggestion::count(),
            'new' => Suggestion::where('status', 'new')->count(),
            'reviewed' => Suggestion::where('status', 'reviewed')->count(),
            'planned' => Suggestion::where('status', 'planned')->count(),
            'done' => Suggestion::where('status', 'done')->count(),
        ];

        // If JSON request, return suggestions data
        if ($request->expectsJson()) {
            $query = Suggestion::with('user');

            if ($status = $request->get('status')) {
                $query->where('status', $status);
            }

            $suggestions = $query->latest()->paginate(20);

            return response()->json([
                'suggestions' => $suggestions->map(function ($suggestion) {
                    return [
                        'id' => $suggestion->id,
                        'content' => $suggestion->content,
                        'status' => $suggestion->status,
                        'created_at' => $suggestion->created_at->translatedFormat('d M Y H:i'),
                        'user' => [
                            'name' => $suggestion->user->name,
                            'avatar' => strtoupper(substr($suggestion->user->name, 0, 1)),
                        ],
                    ];
                }),
            ]);
        }

        // For initial page load, just return view with stats
        return view('admin.suggestions.index', compact('stats'));
    }

    public function updateSuggestionStatus(Request $request, Suggestion $suggestion)
    {
        $status = $request->input('status');

        if (!in_array($status, ['new', 'reviewed', 'planned', 'done'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ongeldige status.'], 400);
            }
            return back()->withErrors(['status' => 'Ongeldige status.']);
        }

        $suggestion->update(['status' => $status]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $status]);
        }

        return back()->with('success', 'Status bijgewerkt.');
    }

    public function deleteSuggestion(Request $request, Suggestion $suggestion)
    {
        $suggestion->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.suggestions')->with('success', 'Suggestie verwijderd.');
    }
}
