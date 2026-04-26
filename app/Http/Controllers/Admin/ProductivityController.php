<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductivityTask;
use App\Models\ProductivityNote;
use App\Models\ProductivityHabit;
use App\Models\ProductivityHabitLog;
use App\Models\AbsensiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ProductivityController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $today  = Carbon::today()->format('Y-m-d');

        // ── Filter Temporal (bukan status) ──────────────────────────────
        // today | upcoming | all | delegated | archived
        $filter = $request->get('filter', 'today');
        $sort   = $request->get('sort', 'deadline');
        $tag    = $request->get('tag', '');
        $search = $request->get('search', '');

        if (!empty($search)) {
            $filter = 'all'; 
        }

        $taskQuery = ProductivityTask::with(['subTasks', 'attachments', 'comments.user', 'user', 'assigner']);

        // ── Logika Filter Utama ──────────────────────────────────────────
        if ($filter === 'delegated') {
            // Tugas keluar: saya yang memberi, orang lain yang mengerjakan
            $taskQuery->where('assigned_by', $userId)
                      ->where('user_id', '!=', $userId)
                      ->where('is_archived', false);

        } elseif ($filter === 'archived') {
            $taskQuery->where('user_id', $userId)
                      ->where('is_archived', true);

        } elseif ($filter === 'upcoming') {
            // Tugas aktif dengan deadline BESOK atau lebih, plus tanpa deadline
            $taskQuery->where('user_id', $userId)
                      ->where('is_archived', false)
                      ->whereIn('status', ['pending', 'in_progress'])
                      ->where(function ($q) use ($today) {
                          $q->whereNull('deadline_at')
                            ->orWhere('deadline_at', '>', Carbon::today()->endOfDay());
                      });

        } elseif ($filter === 'all') {
            $taskQuery->where('user_id', $userId)
                      ->where('is_archived', false);

        } else {
            // DEFAULT: 'today' — Overdue + Deadline hari ini + In Progress tanpa tenggat + SELESAI HARI INI
            $taskQuery->where('user_id', $userId)
                      ->where('is_archived', false)
                      ->where(function ($q) use ($today) {
                          // 1. Tugas Aktif (Overdue, Hari Ini, Tanpa Tenggat)
                          $q->where(function ($q1) use ($today) {
                              $q1->whereIn('status', ['pending', 'in_progress'])
                                 ->where(function ($q2) use ($today) {
                                     $q2->whereNotNull('deadline_at')
                                        ->where('deadline_at', '<', Carbon::today()->startOfDay())
                                        ->orWhereDate('deadline_at', $today)
                                        ->orWhereNull('deadline_at');
                                 });
                          })
                          // 2. PERBAIKAN: Tugas yang diselesaikan hari ini
                          ->orWhere(function ($q1) use ($today) {
                              $q1->where('status', 'completed')
                                 ->whereDate('updated_at', $today); 
                          });
                      });
        }

        // ── Tag & Search ─────────────────────────────────────────────────
        if ($tag) {
            $taskQuery->where('tag', $tag);
        }
        if ($search) {
            $taskQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // ── Sorting ──────────────────────────────────────────────────────
        if ($sort === 'priority') {
            $taskQuery->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
        } elseif ($sort === 'created') {
            $taskQuery->latest();
        } else {
            // Default deadline — overdue dulu, lalu urutan deadline
            $taskQuery->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed')")
                      ->orderByRaw("CASE WHEN deadline_at IS NULL THEN 1 ELSE 0 END")
                      ->orderBy('deadline_at', 'asc');
        }

        $tasks = $taskQuery->get();

        // ── Kelompokkan untuk tampilan "Hari Ini" ────────────────────────
        $tasksOverdueGroup  = $tasks->filter(fn($t) =>
            $t->deadline_at &&
            Carbon::parse($t->deadline_at)->lt(Carbon::today()->startOfDay()) &&
            $t->status !== 'completed'
        );
        $tasksTodayGroup    = $tasks->filter(fn($t) =>
            // Masuk sini jika deadline-nya hari ini ATAU diselesaikan hari ini
            ($t->deadline_at && Carbon::parse($t->deadline_at)->isToday()) || 
            ($t->status === 'completed' && Carbon::parse($t->updated_at)->isToday())
        );
        $tasksOtherGroup    = $tasks->filter(fn($t) =>
            !$t->deadline_at && $t->status !== 'completed'
        );

        // ── Semua Tag unik ───────────────────────────────────────────────
        $allTags = ProductivityTask::where('user_id', $userId)
            ->whereNotNull('tag')->where('tag', '!=', '')
            ->distinct()->pluck('tag');

        // ── Stats Header ─────────────────────────────────────────────────
        $statsTotal     = ProductivityTask::where('user_id', $userId)->where('is_archived', false)->count();
        $statsCompleted = ProductivityTask::where('user_id', $userId)->where('status', 'completed')->where('is_archived', false)->count();
        $statsPending   = ProductivityTask::where('user_id', $userId)->whereIn('status', ['pending', 'in_progress'])->where('is_archived', false)->count();
        $statsOverdue   = ProductivityTask::where('user_id', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('is_archived', false)
            ->whereNotNull('deadline_at')
            ->where('deadline_at', '<', now())
            ->count();
        $statsDelegated = ProductivityTask::where('assigned_by', $userId)
            ->where('user_id', '!=', $userId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('is_archived', false)
            ->count();

        // ── Stats Harian (khusus untuk header hari ini) ──────────────────
        $statsTodayTotal = ProductivityTask::where('user_id', $userId)
            ->where('is_archived', false)
            ->where(function ($q) use ($today) {
                // 1. Deadline hari ini (wajib dikerjakan hari ini)
                $q->whereDate('deadline_at', $today)
                // 2. Terlambat tapi belum selesai (harus diselesaikan hari ini)
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereNotNull('deadline_at')
                         ->where('deadline_at', '<', Carbon::today()->startOfDay())
                         ->whereIn('status', ['pending', 'in_progress']);
                  })
                // 3. SELESAI HARI INI (Apa pun deadlinenya, ini poin produktivitas ekstra!)
                  ->orWhere(function ($q3) use ($today) {
                      $q3->where('status', 'completed')
                         ->whereDate('updated_at', $today);
                  });
            })->count();

        // Hanya hitung tugas yang benar-benar DISELESAIKAN HARI INI
        $statsTodayDone = ProductivityTask::where('user_id', $userId)
            ->where('is_archived', false)
            ->where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        // ── Notes & Habits ───────────────────────────────────────────────
        $notes = ProductivityNote::where('user_id', $userId)->latest()->get();

        $habits = ProductivityHabit::with(['logs' => fn($q) => $q->where('is_completed', true)])
            ->where('user_id', $userId)
            ->get()
            ->map(function ($habit) use ($today) {
                $completedDates = $habit->logs->pluck('tanggal')->map(
                    fn($date) => Carbon::parse($date)->format('Y-m-d')
                )->toArray();

                $habit->is_completed_today = in_array($today, $completedDates);

                $streak    = 0;
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

        $coworkers = \App\Models\User::whereHas('roles', fn($q) => $q->where('title', 'Pegawai'))
            ->where('id', '!=', $userId)
            ->orderBy('name', 'asc')
            ->get();

        $absensiHariIni = AbsensiLog::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();

        return view('admin.productivity.index', compact(
            'tasks', 'notes', 'habits', 'today', 'coworkers',
            'allTags', 'filter', 'sort', 'tag', 'search',
            'statsTotal', 'statsCompleted', 'statsPending', 'statsOverdue', 'statsDelegated',
            'statsTodayTotal', 'statsTodayDone',
            'tasksOverdueGroup', 'tasksTodayGroup', 'tasksOtherGroup','absensiHariIni'
        ));
    }

    // =========================================================
    // COMMENT METHODS
    // =========================================================
    public function storeComment(Request $request, $taskId)
    {
        $request->validate(['comment' => 'required|string']);

        $task = ProductivityTask::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($taskId);

        $comment = \App\Models\ProductivityTaskComment::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment,
        ]);

        if ($task->assigned_by && $task->user_id != $task->assigned_by) {
            $targetUserId = (Auth::id() == $task->user_id) ? $task->assigned_by : $task->user_id;
            $targetUser   = \App\Models\User::find($targetUserId);

            if ($targetUser && $targetUser->telegram_chat_id) {
                $commenterName = Auth::user()->name;
                $msg  = "💬 <b>Komentar Baru di Tugas Anda!</b>\n\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n";
                $msg .= "🗣 <b>Dari:</b> {$commenterName}\n";
                $msg .= "📝 <i>\"{$request->comment}\"</i>\n\n";
                $msg .= "Cek dashboard FTMM untuk membalas.";

                Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                    'chat_id'    => $targetUser->telegram_chat_id,
                    'text'       => $msg,
                    'parse_mode' => 'HTML',
                ]);
            }
        }

        return response()->json(['success' => true, 'comment' => $comment->load('user')]);
    }

    // =========================================================
    // TASK CRUD
    // =========================================================
    public function storeTask(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $assigneeId = $request->assigned_to ?: Auth::id();
        $assignedBy = ($request->assigned_to && $request->assigned_to != Auth::id()) ? Auth::id() : null;

        $task = ProductivityTask::create([
            'user_id'         => $assigneeId,
            'assigned_by'     => $assignedBy,
            'title'           => $request->title,
            'description'     => $request->description,
            'tag'             => $request->tag,
            'priority'        => $request->priority ?? 'low',
            'status'          => 'pending',
            'deadline_at'     => $request->deadline_at ?: null,
            'recurrence'      => $request->recurrence ?? 'none',
            'remind_morning'  => $request->boolean('remind_morning'),
            'remind_h_minus_1'=> $request->boolean('remind_h_minus_1'),
        ]);
        $task->load(['subTasks', 'attachments', 'comments', 'user', 'assigner']);

        $deadlineText = 'Tanpa Deadline';
        $deadlineBadgeHtml = '';

        if ($task->deadline_at) {
            $dl = \Carbon\Carbon::parse($task->deadline_at);
            if ($dl->lt(\Carbon\Carbon::today()->startOfDay())) {
                $deadlineBadgeHtml = '<span class="task-badge badge-overdue"><i class="fas fa-exclamation-circle"></i> ' . $dl->translatedFormat('d M Y, H:i') . '</span>';
                $deadlineText = $dl->translatedFormat('d M Y, H:i');
            } elseif ($dl->isToday()) {
                $deadlineBadgeHtml = '<span class="task-badge badge-today"><i class="far fa-clock"></i> Hari ini, ' . $dl->format('H:i') . '</span>';
                $deadlineText = 'Hari ini, ' . $dl->format('H:i');
            } else {
                $deadlineBadgeHtml = '<span class="task-badge badge-deadline"><i class="far fa-calendar-alt"></i> ' . $dl->translatedFormat('d M Y, H:i') . '</span>';
                $deadlineText = $dl->translatedFormat('d M Y, H:i');
            }
        }

        $task->formatted_deadline = $deadlineText;
        $task->deadline_badge_html = $deadlineBadgeHtml;

        // Notifikasi Telegram jika delegasi
        if ($assignedBy) {
            $assignee = \App\Models\User::find($assigneeId);
            if ($assignee && $assignee->telegram_chat_id) {
                $assignerName = Auth::user()->name;
                $deadline     = $task->deadline_at
                    ? Carbon::parse($task->deadline_at)->translatedFormat('d F Y, H:i')
                    : 'Tanpa tenggat';
                $msg  = "📋 <b>Tugas Baru Didelegasikan!</b>\n\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n";
                $msg .= "👤 <b>Dari:</b> {$assignerName}\n";
                $msg .= "⏰ <b>Deadline:</b> {$deadline}\n\n";
                $msg .= "Cek dashboard FTMM untuk detail selengkapnya.";

                Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                    'chat_id'    => $assignee->telegram_chat_id,
                    'text'       => $msg,
                    'parse_mode' => 'HTML',
                ]);
            }
        }

        return response()->json([
            'success' => true, 
            'task' => $task, 
            'stats' => $this->getDailyStats() 
        ]);
    }

    public function updateTask(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $task = ProductivityTask::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($id);

        $assigneeId = $request->assigned_to ?: Auth::id();
        $assignedBy = ($request->assigned_to && $request->assigned_to != Auth::id()) ? Auth::id() : null;

        $task->update([
            'user_id'          => $assigneeId,
            'assigned_by'      => $assignedBy,
            'title'            => $request->title,
            'description'      => $request->description,
            'tag'              => $request->tag,
            'priority'         => $request->priority ?? $task->priority,
            'deadline_at'      => $request->deadline_at ?: null,
            'recurrence'       => $request->recurrence ?? $task->recurrence,
            'remind_morning'   => $request->boolean('remind_morning'),
            'remind_h_minus_1' => $request->boolean('remind_h_minus_1'),
        ]);

        return response()->json(['success' => true, 'task' => $task->fresh()]);
    }

    public function updateTaskStatus(Request $request, $id)
    {
        $task = ProductivityTask::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($id);

        $task->update(['status' => $request->status]);

        // Notifikasi delegasi selesai
        if ($request->status === 'completed' && $task->assigned_by) {
            $delegator = \App\Models\User::find($task->assigned_by);
            if ($delegator && $delegator->telegram_chat_id) {
                $executorName = Auth::user()->name;
                $msg  = "✅ <b>TUGAS DELEGASI SELESAI!</b>\n\n";
                $msg .= "👤 <b>Dikerjakan oleh:</b> {$executorName}\n";
                $msg .= "📌 <b>Tugas:</b> {$task->title}\n\n";
                $msg .= "Kerja bagus! Tim Anda makin produktif. 💪";

                Http::post("https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage", [
                    'chat_id'    => $delegator->telegram_chat_id,
                    'text'       => $msg,
                    'parse_mode' => 'HTML',
                ]);
            }
        }

        // Recurring task
        if ($request->status === 'completed' && in_array($task->recurrence, ['daily', 'weekly', 'monthly'])) {
            $nextDeadline = $task->deadline_at
                ? Carbon::parse($task->deadline_at)
                : Carbon::today()->setHour(23)->setMinute(59);

            if ($task->recurrence === 'daily')        $nextDeadline->addDay();
            elseif ($task->recurrence === 'weekly')   $nextDeadline->addWeek();
            elseif ($task->recurrence === 'monthly')  $nextDeadline->addMonth();

            ProductivityTask::create([
                'user_id'          => $task->user_id,
                'assigned_by'      => $task->assigned_by,
                'title'            => $task->title,
                'description'      => $task->description,
                'tag'              => $task->tag,
                'priority'         => $task->priority,
                'recurrence'       => $task->recurrence,
                'status'           => 'pending',
                'deadline_at'      => $nextDeadline,
                'is_archived'      => false,
                // Pastikan nilai boolean default terbawa agar tidak error constraint DB
                'remind_morning'   => $task->remind_morning ?? false, 
                'remind_h_minus_1' => $task->remind_h_minus_1 ?? false,
            ]);

            $task->update(['recurrence' => 'none']);
        }

        return response()->json(['success' => true, 'stats' => $this->getDailyStats()]);
    }

    public function storeSubTask(Request $request, $taskId)
    {
        $request->validate(['title' => 'required|string|max:255']);
        $task = ProductivityTask::where(function ($q) {
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
        $request->validate(['file' => 'required|file|max:5120']);
        $task = ProductivityTask::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($taskId);

        $file     = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store('tasks/attachments', 'public');

        $attachment = $task->attachments()->create([
            'file_name' => $fileName,
            'file_path' => $filePath,
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
        $task = ProductivityTask::where(function ($q) {
            $q->where('user_id', Auth::id())->orWhere('assigned_by', Auth::id());
        })->findOrFail($id);

        if ($task->assigned_by && $task->assigned_by != Auth::id() && $task->user_id == Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak menghapus tugas delegasi. Hubungi pemberi tugas.',
            ], 403);
        }

        $task->delete();
        return response()->json(['success' => true, 'stats' => $this->getDailyStats()]);
    }

    // =========================================================
    // NOTE METHODS
    // =========================================================
    public function storeNote(Request $request)
    {
        $request->validate(['content' => 'required|string']);
        $bgColor = $request->bg_color ?? '#fef08a';
        $note    = ProductivityNote::create([
            'user_id'  => Auth::id(),
            'title'    => $request->title,
            'content'  => $request->content,
            'bg_color' => $bgColor,
        ]);
        return response()->json(['success' => true, 'note' => $note]);
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

    public function destroyNote($id)
    {
        ProductivityNote::where('user_id', Auth::id())->findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // =========================================================
    // HABIT METHODS
    // =========================================================
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
        $log   = ProductivityHabitLog::firstOrNew([
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

    private function getDailyStats() {
        $userId = Auth::id();
        $today = \Carbon\Carbon::today()->format('Y-m-d');
        
        $statsTodayTotal = ProductivityTask::where('user_id', $userId)
            ->where('is_archived', false)
            ->where(function ($q) use ($today) {
                $q->whereDate('deadline_at', $today)
                ->orWhere(function ($q2) use ($today) {
                    $q2->whereNotNull('deadline_at')->where('deadline_at', '<', \Carbon\Carbon::today()->startOfDay())->whereIn('status', ['pending', 'in_progress']);
                })
                ->orWhere(function ($q3) use ($today) {
                    $q3->where('status', 'completed')->whereDate('updated_at', $today);
                });
            })->count();

        $statsTodayDone = ProductivityTask::where('user_id', $userId)
            ->where('is_archived', false)->where('status', 'completed')->whereDate('updated_at', $today)->count();

        $pct = $statsTodayTotal > 0 ? round(($statsTodayDone / $statsTodayTotal) * 100) : 0;
        
        return [
            'total' => $statsTodayTotal,
            'done'  => $statsTodayDone,
            'pct'   => $pct,
            'remaining' => $statsTodayTotal - $statsTodayDone
        ];
    }
}