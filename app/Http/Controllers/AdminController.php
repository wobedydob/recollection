<?php

namespace App\Http\Controllers;

use App\Models\Idea;
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
        ];

        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers'));
    }

    public function users(Request $request): View
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
}
