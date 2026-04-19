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
    public function index(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->format('Y-m-d');

        // --- Filter & Sort Logic ---
        $filter   = $request->get('filter', 'active');   // active | completed | archived | all
        $sort     = $request->get('sort', 'deadline');   // deadline | priority | created
        $tag      = $request->get('tag', '');
        $search   = $request->get('search', '');

        $taskQuery = ProductivityTask::where('user_id', $userId);

        // Filter berdasarkan status
        if ($filter === 'active') {
            $taskQuery->whereIn('status', ['pending', 'in_progress'])->where('is_archived', false);
        } elseif ($filter === 'completed') {
            $taskQuery->where('status', 'completed')->where('is_archived', false);
        } elseif ($filter === 'archived') {
            $taskQuery->where('is_archived', true);
        } else {
            // all — tidak filter status, tapi exclude archive
            $taskQuery->where('is_archived', false);
        }

        // Filter tag
        if ($tag) {
            $taskQuery->where('tag', $tag);
        }

        // Search
        if ($search) {
            $taskQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        if ($sort === 'priority') {
            $taskQuery->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
        } elseif ($sort === 'created') {
            $taskQuery->latest();
        } else {
            // Default: deadline
            $taskQuery->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
                      ->orderByRaw("CASE WHEN deadline_at IS NULL THEN 1 ELSE 0 END")
                      ->orderBy('deadline_at', 'asc');
        }

        $tasks = $taskQuery->get();

        // Semua tag unik milik user (untuk filter dropdown)
        $allTags = ProductivityTask::where('user_id', $userId)
            ->whereNotNull('tag')->where('tag', '!=', '')
            ->distinct()->pluck('tag');

        // Stats untuk header
        $statsTotal     = ProductivityTask::where('user_id', $userId)->where('is_archived', false)->count();
        $statsCompleted = ProductivityTask::where('user_id', $userId)->where('status', 'completed')->where('is_archived', false)->count();
        $statsPending   = ProductivityTask::where('user_id', $userId)->whereIn('status', ['pending','in_progress'])->where('is_archived', false)->count();
        $statsOverdue   = ProductivityTask::where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('is_archived', false)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->count();

        // Ambil Data Notes
        $notes = ProductivityNote::where('user_id', $userId)->latest()->get();

        // Ambil Data Habits beserta log hari ini
        $habits = ProductivityHabit::with(['logs' => function($q) {
            $q->where('is_completed', true);
        }])
        ->where('user_id', $userId)
        ->get()
        ->map(function ($habit) use ($today) {
            // Ambil daftar tanggal dari memory (bukan hit database berulang kali)
            $completedDates = $habit->logs->pluck('tanggal')->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray();

            $habit->is_completed_today = in_array($today, $completedDates);

            // Hitung Streak
            $streak = 0;
            $checkDate = Carbon::today();
            
            if ($habit->is_completed_today) { $streak++; }
            $checkDate->subDay();

            while (in_array($checkDate->format('Y-m-d'), $completedDates)) {
                $streak++;
                $checkDate->subDay();
            }

            $habit->streak = $streak;
            return $habit;
        });

        return view('admin.productivity.index', compact(
            'tasks', 'notes', 'habits', 'today',
            'allTags', 'filter', 'sort', 'tag', 'search',
            'statsTotal', 'statsCompleted', 'statsPending', 'statsOverdue'
        ));
    }

    // --- TASK METHODS ---
    public function storeTask(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $task = ProductivityTask::create([
            'user_id'       => Auth::id(),
            'title'         => $request->title,
            'description'   => $request->description,
            'tag'           => $request->tag,
            'priority'      => $request->priority ?? 'medium',
            'deadline_at'   => $request->deadline_at ?: null,
            'recurrence'    => $request->recurrence ?? 'none',
            'is_archived'   => false,
        ]);

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $task = ProductivityTask::where('user_id', Auth::id())->findOrFail($id);

        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'tag'         => $request->tag,
            'priority'    => $request->priority ?? $task->priority,
            'deadline_at' => $request->deadline_at ?: null,
            'recurrence'  => $request->recurrence ?? $task->recurrence,
        ]);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'telegram_remind_morning'  => $request->has('telegram_remind_morning'),
            'telegram_remind_deadline' => $request->has('telegram_remind_deadline'),
        ]);
        return response()->json(['success' => true]);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = ProductivityTask::where('user_id', Auth::id())->findOrFail($id);
        $task->update(['status' => $request->status]);

        // 🔥 LOGIC RECURRING TASK
        if ($request->status === 'completed' && $task->recurrence !== 'none') {
            $nextDeadline = $task->deadline_at
                ? Carbon::parse($task->deadline_at)
                : Carbon::today()->setHour(23)->setMinute(59);

            if ($task->recurrence === 'daily')   $nextDeadline->addDay();
            elseif ($task->recurrence === 'weekly')  $nextDeadline->addWeek();
            elseif ($task->recurrence === 'monthly') $nextDeadline->addMonth();

            ProductivityTask::create([
                'user_id'     => $task->user_id,
                'title'       => $task->title,
                'description' => $task->description,
                'tag'         => $task->tag,
                'priority'    => $task->priority,
                'recurrence'  => $task->recurrence,
                'status'      => 'pending',
                'deadline_at' => $nextDeadline,
                'is_archived' => false,
            ]);

            $task->update(['recurrence' => 'none']);
        }

        return response()->json(['success' => true]);
    }

    public function archiveTask($id)
    {
        $task = ProductivityTask::where('user_id', Auth::id())->findOrFail($id);
        $task->update(['is_archived' => true]);
        return response()->json(['success' => true]);
    }

    public function unarchiveTask($id)
    {
        $task = ProductivityTask::where('user_id', Auth::id())->findOrFail($id);
        $task->update(['is_archived' => false, 'status' => 'pending']);
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
        $bgColor = $request->bg_color ?? '#fef08a';
        $note = ProductivityNote::create([
            'user_id'  => Auth::id(),
            'title'    => $request->title,
            'content'  => $request->content,
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
            'name'    => $request->name,
            'icon'    => $request->icon ?? 'fas fa-check-circle',
        ]);
        return response()->json(['success' => true, 'habit' => $habit]);
    }

    public function toggleHabit(Request $request, $id)
    {
        $habit = ProductivityHabit::where('user_id', Auth::id())->findOrFail($id);
        $today = Carbon::today()->format('Y-m-d');
        $log = ProductivityHabitLog::firstOrNew([
            'habit_id' => $habit->id,
            'tanggal'  => $today,
        ]);
        $log->is_completed = !$log->is_completed;
        $log->save();
        return response()->json(['success' => true, 'is_completed' => $log->is_completed]);
    }

    public function destroyHabit($id)
    {
        ProductivityHabit::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function updateNote(Request $request, $id)
    {
        $request->validate(['content' => 'required|string']);
 
        $note = ProductivityNote::where('user_id', Auth::id())->findOrFail($id);
 
        $note->update([
            'title'    => $request->title,
            'content'  => $request->content,
            'bg_color' => $request->bg_color ?? $note->bg_color,
        ]);
 
        return response()->json(['success' => true, 'note' => $note->fresh()]);
    }
}