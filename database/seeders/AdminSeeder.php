<?php

namespace Database\Seeders;

use App\Models\Idea;
use App\Models\Tag;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin user with test data.
     */
    public function run(): void
    {
        // Clear existing data to ensure admin gets ID 1
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TodoItem::truncate();
        TodoList::truncate();
        DB::table('idea_tag')->truncate();
        Idea::truncate();
        Tag::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create admin user (will get ID 1)
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@recollectie.nl',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create test user (will get ID 2)
        $testUser = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('test123'),
            'role' => 'user',
        ]);

        // =====================
        // Admin's tags
        // =====================
        $adminTagWork = Tag::create([
            'user_id' => $admin->id,
            'name' => 'Work',
            'emoji' => '💼',
            'color' => '#60a5fa',
        ]);

        $adminTagPersonal = Tag::create([
            'user_id' => $admin->id,
            'name' => 'Personal',
            'emoji' => '🏠',
            'color' => '#f0a5c0',
        ]);

        $adminTagIdea = Tag::create([
            'user_id' => $admin->id,
            'name' => 'Idea',
            'emoji' => '💡',
            'color' => '#fbbf24',
        ]);

        $adminTagImportant = Tag::create([
            'user_id' => $admin->id,
            'name' => 'Important',
            'emoji' => '⭐',
            'color' => '#f87171',
        ]);

        $adminTagProject = Tag::create([
            'user_id' => $admin->id,
            'name' => 'Project',
            'emoji' => '🚀',
            'color' => '#a78bfa',
        ]);

        // =====================
        // Admin's ideas
        // =====================
        $idea1 = Idea::create([
            'user_id' => $admin->id,
            'content' => 'Add a new feature to the app: dark mode support for all pages.',
        ]);
        $idea1->tags()->attach([$adminTagWork->id, $adminTagProject->id]);

        $idea2 = Idea::create([
            'user_id' => $admin->id,
            'content' => 'Maybe start a podcast about software development and design.',
        ]);
        $idea2->tags()->attach([$adminTagIdea->id, $adminTagPersonal->id]);

        $idea3 = Idea::create([
            'user_id' => $admin->id,
            'content' => 'Remember to update the documentation for the new API endpoints.',
        ]);
        $idea3->tags()->attach([$adminTagWork->id, $adminTagImportant->id]);

        $idea4 = Idea::create([
            'user_id' => $admin->id,
            'content' => 'A weekend trip to the mountains would be nice, some time to relax in nature.',
        ]);
        $idea4->tags()->attach([$adminTagPersonal->id]);

        $idea5 = Idea::create([
            'user_id' => $admin->id,
            'content' => 'Research AI integration for automatic tagging of ideas.',
        ]);
        $idea5->tags()->attach([$adminTagIdea->id, $adminTagProject->id, $adminTagImportant->id]);

        // =====================
        // Admin's checklists
        // =====================
        $list1 = TodoList::create([
            'user_id' => $admin->id,
            'name' => 'Groceries',
            'emoji' => '🛒',
            'color' => '#86efac',
            'position' => 0,
        ]);

        TodoItem::create(['todo_list_id' => $list1->id, 'title' => 'Milk', 'position' => 0]);
        TodoItem::create(['todo_list_id' => $list1->id, 'title' => 'Bread', 'position' => 1, 'completed_at' => now()]);
        TodoItem::create(['todo_list_id' => $list1->id, 'title' => 'Cheese', 'position' => 2]);
        TodoItem::create(['todo_list_id' => $list1->id, 'title' => 'Apples', 'position' => 3]);

        $list2 = TodoList::create([
            'user_id' => $admin->id,
            'name' => 'Sprint Tasks',
            'emoji' => '🏃',
            'color' => '#60a5fa',
            'position' => 1,
        ]);

        TodoItem::create([
            'todo_list_id' => $list2->id,
            'title' => 'Build admin dashboard',
            'description' => 'Including user management and statistics',
            'priority' => 'high',
            'position' => 0,
            'completed_at' => now(),
        ]);
        TodoItem::create(['todo_list_id' => $list2->id, 'title' => 'Write unit tests', 'priority' => 'medium', 'position' => 1]);
        TodoItem::create(['todo_list_id' => $list2->id, 'title' => 'Code review', 'position' => 2]);

        $list3 = TodoList::create([
            'user_id' => $admin->id,
            'name' => 'Vacation Planning',
            'emoji' => '✈️',
            'color' => '#fbbf24',
            'position' => 2,
        ]);

        TodoItem::create(['todo_list_id' => $list3->id, 'title' => 'Book flight', 'due_date' => now()->addDays(7), 'priority' => 'high', 'position' => 0]);
        TodoItem::create(['todo_list_id' => $list3->id, 'title' => 'Reserve hotel', 'due_date' => now()->addDays(14), 'position' => 1]);
        TodoItem::create(['todo_list_id' => $list3->id, 'title' => 'Check travel documents', 'position' => 2, 'completed_at' => now()]);

        // =====================
        // Test user's tags
        // =====================
        $userTagBooks = Tag::create([
            'user_id' => $testUser->id,
            'name' => 'Books',
            'emoji' => '📚',
            'color' => '#8b5cf6',
        ]);

        $userTagHealth = Tag::create([
            'user_id' => $testUser->id,
            'name' => 'Health',
            'emoji' => '💪',
            'color' => '#10b981',
        ]);

        $userTagRecipes = Tag::create([
            'user_id' => $testUser->id,
            'name' => 'Recipes',
            'emoji' => '🍳',
            'color' => '#f59e0b',
        ]);

        // =====================
        // Test user's ideas
        // =====================
        $userIdea1 = Idea::create([
            'user_id' => $testUser->id,
            'content' => 'Read "Atomic Habits" by James Clear - heard great things about it.',
        ]);
        $userIdea1->tags()->attach([$userTagBooks->id]);

        $userIdea2 = Idea::create([
            'user_id' => $testUser->id,
            'content' => 'Try that new pasta recipe with sun-dried tomatoes and basil.',
        ]);
        $userIdea2->tags()->attach([$userTagRecipes->id]);

        $userIdea3 = Idea::create([
            'user_id' => $testUser->id,
            'content' => 'Start going for a morning jog, even just 15 minutes would help.',
        ]);
        $userIdea3->tags()->attach([$userTagHealth->id]);

        // =====================
        // Test user's checklists
        // =====================
        $userList1 = TodoList::create([
            'user_id' => $testUser->id,
            'name' => 'Reading List',
            'emoji' => '📖',
            'color' => '#8b5cf6',
            'position' => 0,
        ]);

        TodoItem::create(['todo_list_id' => $userList1->id, 'title' => 'Atomic Habits', 'position' => 0]);
        TodoItem::create(['todo_list_id' => $userList1->id, 'title' => 'Deep Work', 'position' => 1]);
        TodoItem::create(['todo_list_id' => $userList1->id, 'title' => 'The Pragmatic Programmer', 'position' => 2, 'completed_at' => now()]);

        $userList2 = TodoList::create([
            'user_id' => $testUser->id,
            'name' => 'Fitness Goals',
            'emoji' => '🏋️',
            'color' => '#10b981',
            'position' => 1,
        ]);

        TodoItem::create(['todo_list_id' => $userList2->id, 'title' => 'Run 5km', 'priority' => 'high', 'position' => 0]);
        TodoItem::create(['todo_list_id' => $userList2->id, 'title' => '50 pushups daily', 'position' => 1]);
        TodoItem::create(['todo_list_id' => $userList2->id, 'title' => 'Drink 2L water', 'position' => 2, 'completed_at' => now()]);
    }
}
