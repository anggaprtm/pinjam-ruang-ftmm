<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductivityTask;
use App\Models\ProductivityNote;
use App\Models\ProductivityHabit;
use App\Models\ProductivityHabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductivityController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today()->format('Y-m-d');

        // Ambil Data Tasks (Urutkan yang belum selesai duluan, lalu berdasarkan deadline terdekat)
        $tasks = ProductivityTask::where('user_id', $userId)
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
            ->orderBy('deadline_at', 'asc')
            ->get();

        // Ambil Data Notes
        $notes = ProductivityNote::where('user_id', $userId)->latest()->get();

        // Ambil Data Habits beserta log hari ini
        $habits = ProductivityHabit::where('user_id', $userId)->get()->map(function ($habit) use ($today) {
            $habit->is_completed_today = ProductivityHabitLog::where('habit_id', $habit->id)
                ->where('tanggal', $today)
                ->where('is_completed', true)
                ->exists();
            return $habit;
        });

        return view('admin.productivity.index', compact('tasks', 'notes', 'habits', 'today'));
    }

    // --- TASK METHODS ---
    public function storeTask(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        
        $task = ProductivityTask::create([
            'user_id'     => Auth::id(),
            'title'       => $request->title,
            'tag'         => $request->tag, // Tangkap input tag
            'priority'    => $request->priority ?? 'medium',
            'deadline_at' => $request->deadline_at,
        ]);

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = ProductivityTask::where('user_id', Auth::id())->findOrFail($id);
        $task->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }

    public function destroyTask($id)
    {
        ProductivityTask::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // --- NOTE METHODS ---
    public function storeNote(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        
        // Pilihan warna pastel acak untuk sticky notes
        $colors = ['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#fed7aa', '#e9d5ff'];
        $bgColor = $colors[array_rand($colors)];

        $note = ProductivityNote::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'bg_color' => $bgColor,
        ]);

        return response()->json(['success' => true, 'note' => $note]);
    }

    public function destroyNote($id)
    {
        ProductivityNote::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // --- HABIT METHODS ---
    public function storeHabit(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        $habit = ProductivityHabit::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'icon' => $request->icon ?? 'fas fa-check-circle',
        ]);

        return response()->json(['success' => true, 'habit' => $habit]);
    }

    public function toggleHabit(Request $request, $id)
    {
        $habit = ProductivityHabit::where('user_id', Auth::id())->findOrFail($id);
        $today = Carbon::today()->format('Y-m-d');
        
        $log = ProductivityHabitLog::firstOrNew([
            'habit_id' => $habit->id,
            'tanggal' => $today,
        ]);

        $log->is_completed = !$log->is_completed; // Toggle status
        $log->save();

        return response()->json(['success' => true, 'is_completed' => $log->is_completed]);
    }

    public function destroyHabit($id)
    {
        ProductivityHabit::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}