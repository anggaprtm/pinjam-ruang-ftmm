<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrmawaProgramItem;
use App\Models\SikApplication;
use App\Models\SikApplicationStep;
use App\Models\SikHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SikApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = SikApplication::with(['ormawa', 'programItem.plan', 'steps', 'issuer'])
            ->orderByDesc('created_at');

        if (! $user->isAdmin()) {
            $ormawaIds = $user->ormawas()->pluck('ormawas.id');
            $query->whereIn('ormawa_id', $ormawaIds);
        }

        if ($request->filled('status_sik')) {
            $query->where('status_sik', $request->input('status_sik'));
        }

        return response()->json($query->paginate(15));
    }

    public function activeProgramItems(Request $request)
    {
        $user = auth()->user();
        $year = (int) ($request->input('tahun') ?: now()->year);

        $query = OrmawaProgramItem::with(['plan.ormawa'])
            ->whereHas('plan', function ($q) use ($year) {
                $q->where('tahun', $year)->where('status_plan', 'published');
            })
            ->whereDoesntHave('sikApplication')
            ->orderBy('nama_rencana');

        if (! $user->isAdmin()) {
            $query->whereHas('plan.ormawa.users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'program_item_id' => ['required', 'integer', 'exists:ormawa_program_items,id'],
            'judul_final_kegiatan' => ['required', 'string', 'max:255'],
            'timeline_mulai_final' => ['required', 'date'],
            'timeline_selesai_final' => ['required', 'date', 'after_or_equal:timeline_mulai_final'],
            'rencana_tempat' => ['nullable', 'string', 'max:255'],
            'proposal' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $programItem = OrmawaProgramItem::with('plan.ormawa.jenisOrmawa')->findOrFail($validated['program_item_id']);

        if ($programItem->sikApplication()->exists()) {
            return response()->json(['message' => 'Proker ini sudah memiliki SIK.'], 422);
        }

        if (! $user->isAdmin()) {
            $isMember = $programItem->plan->ormawa->users()->where('users.id', $user->id)->exists();
            if (! $isMember) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke proker ini.'], 403);
            }
        }

        $flow = $programItem->plan->ormawa->jenisOrmawa
            ->verificationFlows()
            ->where('is_active', true)
            ->with('steps')
            ->first();

        if (! $flow || $flow->steps->isEmpty()) {
            return response()->json(['message' => 'Flow verifikasi untuk jenis ormawa ini belum dikonfigurasi.'], 422);
        }

        return DB::transaction(function () use ($validated, $request, $programItem, $flow, $user) {
            $proposalPath = $request->hasFile('proposal')
                ? $request->file('proposal')->store('sik/proposal', 'public')
                : null;

            $suratPath = $request->hasFile('surat_permohonan')
                ? $request->file('surat_permohonan')->store('sik/surat_permohonan', 'public')
                : null;

            $sik = SikApplication::create([
                'program_item_id' => $programItem->id,
                'ormawa_id' => $programItem->plan->ormawa_id,
                'flow_id' => $flow->id,
                'judul_final_kegiatan' => $validated['judul_final_kegiatan'],
                'timeline_mulai_final' => $validated['timeline_mulai_final'],
                'timeline_selesai_final' => $validated['timeline_selesai_final'],
                'rencana_tempat' => $validated['rencana_tempat'] ?? null,
                'proposal_path' => $proposalPath,
                'surat_permohonan_path' => $suratPath,
                'status_sik' => 'on_verification',
                'submitted_at' => now(),
            ]);

            foreach ($flow->steps as $step) {
                SikApplicationStep::create([
                    'sik_application_id' => $sik->id,
                    'step_order' => $step->step_order,
                    'role_target' => $step->role_target,
                    'status_step' => 'pending',
                    'due_at' => $step->sla_days ? now()->addDays($step->sla_days) : null,
                    'sla_days' => $step->sla_days,
                ]);
            }

            $programItem->update(['status_item' => 'proses']);

            SikHistory::create([
                'sik_application_id' => $sik->id,
                'actor_user_id' => $user->id,
                'event' => 'submitted',
                'payload_json' => [
                    'judul_final_kegiatan' => $sik->judul_final_kegiatan,
                    'timeline' => [$sik->timeline_mulai_final?->toDateString(), $sik->timeline_selesai_final?->toDateString()],
                ],
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Pengajuan SIK berhasil dibuat dan masuk proses verifikasi.',
                'data' => $sik->load(['steps', 'programItem.plan.ormawa']),
            ], 201);
        });
    }

    public function processStep(Request $request, SikApplication $sikApplication)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,revise'],
            'notes' => ['nullable', 'string'],
        ]);

        $sikApplication->load('steps', 'programItem');

        $currentStep = $sikApplication->steps()->where('status_step', 'pending')->orderBy('step_order')->first();
        if (! $currentStep) {
            return response()->json(['message' => 'Tidak ada step verifikasi yang pending.'], 422);
        }

        $userRoles = $user->roles->pluck('title')->map(fn ($role) => strtolower(trim($role)));
        if (! $user->isAdmin() && ! $userRoles->contains(strtolower(trim($currentStep->role_target)))) {
            return response()->json(['message' => 'Anda tidak memiliki hak verifikasi pada step ini.'], 403);
        }

        return DB::transaction(function () use ($validated, $sikApplication, $currentStep, $user) {
            $statusStep = $validated['action'] === 'approve' ? 'approved' : ($validated['action'] === 'reject' ? 'rejected' : 'revised');
            $currentStep->update([
                'status_step' => $statusStep,
                'acted_by_user_id' => $user->id,
                'acted_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($validated['action'] === 'approve') {
                $nextStep = $sikApplication->steps()->where('status_step', 'pending')->orderBy('step_order')->first();
                if ($nextStep) {
                    $sikApplication->update(['status_sik' => 'on_verification']);
                } else {
                    $sikApplication->update(['status_sik' => 'approved_final']);
                }
            } elseif ($validated['action'] === 'reject') {
                $sikApplication->update([
                    'status_sik' => 'cancelled',
                    'catatan_terakhir' => $validated['notes'] ?? 'Ditolak pada proses verifikasi.',
                ]);
                $sikApplication->programItem()->update(['status_item' => 'ditolak']);
            } else {
                $sikApplication->update([
                    'status_sik' => 'need_revision',
                    'catatan_terakhir' => $validated['notes'] ?? 'Perlu revisi.',
                ]);
            }

            SikHistory::create([
                'sik_application_id' => $sikApplication->id,
                'actor_user_id' => $user->id,
                'event' => 'step_' . $validated['action'],
                'payload_json' => [
                    'step_order' => $currentStep->step_order,
                    'role_target' => $currentStep->role_target,
                    'notes' => $validated['notes'] ?? null,
                ],
                'created_at' => now(),
            ]);

            return response()->json([
                'message' => 'Proses verifikasi berhasil diperbarui.',
                'data' => $sikApplication->fresh()->load('steps'),
            ]);
        });
    }

    public function issue(Request $request, SikApplication $sikApplication)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nomor_sik_eoffice' => ['required', 'string', 'max:255'],
        ]);

        if (! $user->isAdmin() && ! $user->roles()->whereIn('title', ['Kemahasiswaan', 'Staf Kemahasiswaan'])->exists()) {
            return response()->json(['message' => 'Hanya admin/kemahasiswaan yang dapat menerbitkan SIK.'], 403);
        }

        if ($sikApplication->status_sik !== 'approved_final') {
            return response()->json(['message' => 'SIK belum mencapai status approved final.'], 422);
        }

        $sikApplication->update([
            'status_sik' => 'issued',
            'issued_at' => now(),
            'issued_by_user_id' => $user->id,
            'nomor_sik_eoffice' => $validated['nomor_sik_eoffice'],
        ]);

        $sikApplication->programItem()->update(['status_item' => 'sik_terbit']);

        SikHistory::create([
            'sik_application_id' => $sikApplication->id,
            'actor_user_id' => $user->id,
            'event' => 'issued',
            'payload_json' => [
                'nomor_sik_eoffice' => $validated['nomor_sik_eoffice'],
            ],
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'SIK berhasil diterbitkan.',
            'data' => $sikApplication->fresh(['issuer', 'programItem']),
        ]);
    }
}
