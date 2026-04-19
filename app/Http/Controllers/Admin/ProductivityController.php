<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductivityTask;
use App\Models\ProductivityNote;
use App\Models\ProductivityHabit;
use App\Models\ProductivityHabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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

       $taskQuery = ProductivityTask::with(['subTasks', 'attachments']);

        // Filter berdasarkan status & kepemilikan
        if ($filter === 'delegated') {
            // TAMPILKAN TUGAS KELUAR: Saya yang memberi tugas, tapi orang lain yang mengerjakan
            $taskQuery->where('assigned_by', $userId)
                      ->where('user_id', '!=', $userId)
                      ->where('is_archived', false);
        } else {
            $taskQuery->where('user_id', $userId);

            if ($filter === 'active') {
                $taskQuery->whereIn('status', ['pending', 'in_progress'])->where('is_archived', false);
            } elseif ($filter === 'completed') {
                $taskQuery->where('status', 'completed')->where('is_archived', false);
            } elseif ($filter === 'archived') {
                $taskQuery->where('is_archived', true);
            } else {
                // all
                $taskQuery->where('is_archived', false);
            }
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
        $statsDelegated = ProductivityTask::where('assigned_by', $userId)
            ->where('user_id', '!=', $userId)
            ->whereIn('status', ['pending', 'in_progress']) // <-- Cukup tambahkan baris ini
            ->where('is_archived', false)
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

        $coworkers = \App\Models\User::whereHas('roles', function($q) {
                $q->where('title', 'Pegawai');
            })
            ->where('id', '!=', $userId)
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.productivity.index', compact(
            'tasks', 'notes', 'habits', 'today', 'coworkers',
            'allTags', 'filter', 'sort', 'tag', 'search',
            'statsTotal', 'statsCompleted', 'statsPending', 'statsOverdue', 'statsDelegated'
        ));
    }

    // --- TASK METHODS ---
    public function storeTask(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        // Tentukan siapa yang mengerjakan dan siapa yang menugaskan
        $assigneeId = $request->assigned_to ?: Auth::id();
    
        // Pastikan kalau dia menugaskan ke dirinya sendiri, assigned_by tetap null
        $assignedBy = ($request->assigned_to && $request->assigned_to != Auth::id()) ? Auth::id() : null;

        $task = ProductivityTask::create([
            'user_id'       => $assigneeId,  
            'assigned_by'   => $assignedBy,
            'title'         => $request->title,
            'description'   => $request->description,
            'tag'           => $request->tag,
            'priority'      => $request->priority ?? 'medium',
            'deadline_at'   => $request->deadline_at ?: null,
            'recurrence'    => $request->recurrence ?? 'none',
            'is_archived'   => false,
        ]);

        // 🔥 LOGIC NOTIFIKASI TELEGRAM DELEGASI 🔥
        if ($assignedBy && $assigneeId != Auth::id()) {
            $assignee = \App\Models\User::find($assigneeId);
            
            if ($assignee && $assignee->telegram_chat_id) {
                $assignerName = Auth::user()->name;
                $deadlineStr = $task->deadline_at ? \Carbon\Carbon::parse($task->deadline_at)->format('d M Y, H:i') : 'Tanpa Tenggat';
                
                $msg = "📢 <b>TUGAS BARU DIDELEGASIKAN KEPADAMU!</b>\n\n";
                $msg .= "👤 <b>Dari:</b> {$assignerName}\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n";
                $msg .= "⏰ <b>Deadline:</b> {$deadlineStr}\n\n";
                $msg .= "Silakan cek dashboard FTMM untuk detailnya. Semangat! 💪";

                // Gunakan token bot dari .env
                $botToken = env('TELEGRAM_BOT_TOKEN');
                if ($botToken) {
                    Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id' => $assignee->telegram_chat_id,
                        'text' => $msg,
                        'parse_mode' => 'HTML'
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $task = ProductivityTask::where(function($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('assigned_by', Auth::id());
            })->findOrFail($id);

        if ($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id()) {
            return response()->json(['message' => 'Anda hanya berhak mengubah status, bukan detail tugas delegasi ini.'], 403);
        }

        // Tentukan siapa yang akan mengerjakan tugas ini sekarang
        $newAssigneeId = $request->assigned_to ?: Auth::id();
        
        // Cek apakah tugas ini dipindah tangankan ke orang yang berbeda dari sebelumnya
        $isReassigned = $newAssigneeId != $task->user_id;

        // Logic untuk menentukan jejak pemberi tugas (delegator)
        $assignedBy = $task->assigned_by; // Default: pertahankan data lama agar tidak hilang jika cuma edit teks

        if ($isReassigned) {
            if ($newAssigneeId == Auth::id()) {
                // Jika tugas ditarik atau diambil alih untuk dikerjakan sendiri, maka set null (bukan tugas delegasi lagi)
                $assignedBy = null;
            } else {
                // Jika tugas diover atau diberikan ke orang lain, maka delegatornya adalah user yang sedang mengedit
                $assignedBy = Auth::id();
            }
        }

        $task->update([
            'user_id'     => $newAssigneeId,
            'assigned_by' => $assignedBy,
            'title'       => $request->title,
            'description' => $request->description,
            'tag'         => $request->tag,
            'priority'    => $request->priority,
            'deadline_at' => $request->deadline_at ?: null,
            'recurrence'  => $request->recurrence,
        ]);

        // Kirim notif Telegram jika tugas didelegasikan ke orang lain saat edit
        if ($isReassigned && $newAssigneeId != Auth::id()) {
            $this->sendTelegramNotification($task, "Tugas telah diperbarui & didelegasikan kepadamu");
        }

        return response()->json(['success' => true]);
    }

    private function sendTelegramNotification($task, $title) {
        $assignee = \App\Models\User::find($task->user_id);
        if ($assignee && $assignee->telegram_chat_id) {
            $msg = "📢 <b>{$title}!</b>\n\n👤 <b>Dari:</b> " . Auth::user()->name . "\n📌 <b>Tugas:</b> {$task->title}\n⏰ <b>Deadline:</b> " . ($task->deadline_at ?: 'Tanpa Tenggat') . "\n\nCek dashboard FTMM 💪";
            \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/sendMessage", [
                'chat_id' => $assignee->telegram_chat_id, 'text' => $msg, 'parse_mode' => 'HTML'
            ]);
        }
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

        if ($request->status === 'completed' && $task->assigned_by) {
            $delegator = \App\Models\User::find($task->assigned_by);
            if ($delegator && $delegator->telegram_chat_id) {
                $executorName = Auth::user()->name;
                $msg = "✅ <b>TUGAS DELEGASI SELESAI!</b>\n\n";
                $msg .= "👤 <b>Dikerjakan oleh:</b> {$executorName}\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n\n";
                $msg .= "Kerja bagus! Tim Anda makin produktif. 💪";

                \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot".env('TELEGRAM_BOT_TOKEN')."/sendMessage", [
                    'chat_id' => $delegator->telegram_chat_id,
                    'text' => $msg,
                    'parse_mode' => 'HTML'
                ]);
            }
        }

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

    public function storeSubTask(Request $request, $taskId)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $task = ProductivityTask::where(function($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($taskId);

        $subTask = $task->subTasks()->create(['title' => $request->title]);
        return response()->json(['success' => true, 'subTask' => $subTask]);
    }

    public function toggleSubTask($taskId, $subTaskId)
    {
        $subTask = \App\Models\ProductivitySubTask::where('task_id', $taskId)->findOrFail($subTaskId);
        $subTask->update(['is_completed' => !$subTask->is_completed]);
        return response()->json(['success' => true, 'is_completed' => $subTask->is_completed]);
    }

    public function destroySubTask($taskId, $subTaskId)
    {
        \App\Models\ProductivitySubTask::where('task_id', $taskId)->findOrFail($subTaskId)->delete();
        return response()->json(['success' => true]);
    }

    // =========================================================
    // ATTACHMENT METHODS
    // =========================================================
    public function storeAttachment(Request $request, $taskId)
    {
        $request->validate(['file' => 'required|file|max:5120']); // Maks 5MB
        $task = ProductivityTask::where(function($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($taskId);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        // Simpan ke storage/app/public/tasks/attachments
        $filePath = $file->store('tasks/attachments', 'public'); 

        $attachment = $task->attachments()->create([
            'file_name' => $fileName,
            'file_path' => $filePath
        ]);

        return response()->json(['success' => true, 'attachment' => $attachment]);
    }

    public function destroyAttachment($taskId, $attachmentId)
    {
        $attachment = \App\Models\ProductivityTaskAttachment::where('task_id', $taskId)->findOrFail($attachmentId);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
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
        // Cari tugas: bisa milik sendiri (user_id) ATAU tugas yang dia delegasikan ke orang (assigned_by)
        $task = ProductivityTask::where(function($q) {
            $q->where('user_id', Auth::id())
            ->orWhere('assigned_by', Auth::id());
        })->findOrFail($id);

        // KUNCI KEAMANAN: Tolak akses jika yang mau hapus adalah penerima delegasi
        if ($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id()) {
            return response()->json([
                'success' => false, 
                'message' => 'Anda tidak berhak menghapus tugas delegasi. Hubungi pemberi tugas.'
            ], 403);
        }

        $task->delete();
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