<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductivityRoutineTask;
use App\Models\ProductivityRoutineLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductivityRoutineController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = $request->get('year', Carbon::now()->year);
        $selectedUserId = $request->get('user_id'); // Filter dropdown dari Atasan

        $isKTU = $user->isKTU();
        $isKasubag = $user->isKasubag();
        $subBagian = optional($user->tendikDetail)->sub_bagian;

        $tasksQuery = ProductivityRoutineTask::with(['user.tendikDetail', 'logs', 'assigner'])
                        ->where('year', $year)
                        ->orderBy('created_at', 'desc');

        if ($isKTU) {
            // KTU lihat semua, filter jika ada
            if ($selectedUserId) $tasksQuery->where('user_id', $selectedUserId);
        } elseif ($isKasubag) {
            // Kasubag lihat sub-bagiannya, filter jika ada
            $tasksQuery->whereHas('user.tendikDetail', function($q) use ($subBagian) {
                $q->where('sub_bagian', $subBagian);
            });
            if ($selectedUserId) $tasksQuery->where('user_id', $selectedUserId);
        } else {
            // Staf hanya lihat miliknya sendiri
            $tasksQuery->where('user_id', $user->id);
        }

        $tasks = $tasksQuery->get();
        
        // MENGELOMPOKKAN TUGAS BERDASARKAN PEGAWAI
        $groupedTasks = $tasks->groupBy('user_id');

        $subordinates = collect();
        if ($isKTU || $isKasubag) {
            $subordinates = User::whereHas('tendikDetail', function($q) use ($isKTU, $subBagian) {
                if (!$isKTU) {
                    $q->where('sub_bagian', $subBagian)->where('nama_jabatan', 'not like', '%Kepala Sub%');
                } else {
                    $q->where('nama_jabatan', 'not like', '%Kepala Bagian Tata Usaha%');
                }
            })->get();
        }

        return view('admin.productivity.routine', compact('groupedTasks', 'year', 'subordinates', 'selectedUserId'));
    }

    public function store(Request $request) { /* ... Kode lama store ... */
        $request->validate(['title' => 'required|string|max:255', 'user_id' => 'required|exists:users,id', 'target_months' => 'required|array', 'year' => 'required|integer']);
        ProductivityRoutineTask::create(['assigned_by' => Auth::id(), 'user_id' => $request->user_id, 'title' => $request->title, 'target_months' => array_map('intval', $request->target_months), 'year' => $request->year, 'is_active' => true]);
        return response()->json(['success' => true]);
    }

    // --- FUNGSI BARU: UPDATE TUGAS (EDIT) ---
    public function update(Request $request, $id)
    {
        $request->validate(['title' => 'required|string|max:255', 'target_months' => 'required|array']);
        if (!Auth::user()->isKTU() && !Auth::user()->isKasubag()) return response()->json(['message' => 'Unauthorized'], 403);
        
        $task = ProductivityRoutineTask::findOrFail($id);
        $task->update(['title' => $request->title, 'target_months' => array_map('intval', $request->target_months)]);
        return response()->json(['success' => true]);
    }

    public function submitLog(Request $request, $taskId) { /* ... Kode lama submitLog ... */
        $request->validate(['month' => 'required|integer|min:1|max:12', 'completed_at' => 'required|date', 'proof_file' => 'required|file|max:5120']);
        $task = ProductivityRoutineTask::findOrFail($taskId);
        if ($task->user_id !== Auth::id()) return response()->json(['message' => 'Unauthorized'], 403);
        $filePath = $request->file('proof_file')->storeAs('productivity/routines', time() . '_' . $request->file('proof_file')->getClientOriginalName(), 'public');
        ProductivityRoutineLog::updateOrCreate(['routine_task_id' => $task->id, 'month' => $request->month], ['completed_at' => $request->completed_at, 'proof_file_path' => $filePath, 'notes' => $request->notes, 'status' => 'pending_approval']);
        return response()->json(['success' => true]);
    }

    // --- FUNGSI BARU: VERIFIKASI BUKTI OLEH ATASAN ---
    public function verifyLog(Request $request, $logId)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        if (!Auth::user()->isKTU() && !Auth::user()->isKasubag()) return response()->json(['message' => 'Unauthorized'], 403);
        
        $log = ProductivityRoutineLog::findOrFail($logId);
        $log->update(['status' => $request->status]);
        return response()->json(['success' => true]);
    }
}