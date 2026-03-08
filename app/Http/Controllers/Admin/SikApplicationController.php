<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrmawaProgramItem;
use App\Models\SikAmendment;
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

        $applications = $query->paginate(15)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($applications);
        }

        return view('admin.sik.index', compact('applications'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $selectedYear = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        $query = OrmawaProgramItem::with(['plan.ormawa'])
            ->whereHas('plan', function ($q) use ($selectedYear) {
                $q->where('status_plan', 'published');
                if ($selectedYear) {
                    $q->where('tahun', $selectedYear);
                }
            })
            ->whereDoesntHave('sikApplication')
            ->orderBy('nama_rencana');

        if (! $user->isAdmin()) {
            $query->whereHas('plan.ormawa.users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        $activeProgramItems = $query->get();
        $availableYears = OrmawaProgramItem::query()
            ->select('ormawa_program_plans.tahun')
            ->join('ormawa_program_plans', 'ormawa_program_plans.id', '=', 'ormawa_program_items.plan_id')
            ->where('ormawa_program_plans.status_plan', 'published')
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                $q->whereExists(function ($sq) use ($user) {
                    $sq->selectRaw('1')
                        ->from('ormawas')
                        ->join('ormawa_user', 'ormawa_user.ormawa_id', '=', 'ormawas.id')
                        ->whereColumn('ormawas.id', 'ormawa_program_plans.ormawa_id')
                        ->where('ormawa_user.user_id', $user->id);
                });
            })
            ->distinct()
            ->orderByDesc('ormawa_program_plans.tahun')
            ->pluck('ormawa_program_plans.tahun');

        return view('admin.sik.create', compact('activeProgramItems', 'selectedYear', 'availableYears'));
    }

    public function show(SikApplication $sikApplication)
    {
        $user = auth()->user();
        $sikApplication->load(['ormawa', 'programItem.plan', 'flow.steps', 'steps.actor', 'issuer', 'histories.actor', 'amendments.requester']);

        $this->authorize('view', $sikApplication);

        return view('admin.sik.show', compact('sikApplication'));
    }

    public function requestAmendment(Request $request, SikApplication $sikApplication)
    {
        $user = auth()->user();

        $this->authorize('requestAmendment', $sikApplication);

        if (! $user->isAdmin()) {
            abort_if(! $sikApplication->is_amendment_open, 403, 'Akses amendment belum dibuka oleh Kemahasiswaan.');
        }

        $validated = $request->validate([
            'judul_final_kegiatan' => ['required', 'string', 'max:255'],
            'timeline_mulai_final' => ['required', 'date'],
            'timeline_selesai_final' => ['required', 'date', 'after_or_equal:timeline_mulai_final'],
            'rencana_tempat' => ['nullable', 'string', 'max:255'],
            'alasan_perubahan' => ['required', 'string', 'max:1000'],
        ]);

        $amendment = SikAmendment::create([
            'sik_application_id' => $sikApplication->id,
            'requested_by_user_id' => $user->id,
            'alasan_perubahan' => $validated['alasan_perubahan'],
            'old_payload_json' => [
                'judul_final_kegiatan' => $sikApplication->judul_final_kegiatan,
                'timeline_mulai_final' => $sikApplication->timeline_mulai_final?->toDateString(),
                'timeline_selesai_final' => $sikApplication->timeline_selesai_final?->toDateString(),
                'rencana_tempat' => $sikApplication->rencana_tempat,
            ],
            'new_payload_json' => [
                'judul_final_kegiatan' => $validated['judul_final_kegiatan'],
                'timeline_mulai_final' => $validated['timeline_mulai_final'],
                'timeline_selesai_final' => $validated['timeline_selesai_final'],
                'rencana_tempat' => $validated['rencana_tempat'] ?? null,
            ],
            'status_amendment' => 'submitted',
        ]);

        SikHistory::create([
            'sik_application_id' => $sikApplication->id,
            'actor_user_id' => $user->id,
            'event' => 'amendment_submitted',
            'payload_json' => [
                'amendment_id' => $amendment->id,
                'alasan_perubahan' => $validated['alasan_perubahan'],
            ],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.sik.show', $sikApplication->id)
            ->with('success', 'Permintaan perubahan timeline/judul berhasil diajukan.');
    }


    public function toggleAmendmentAccess(Request $request, SikApplication $sikApplication)
    {
        $user = auth()->user();
        $this->authorize('toggleAmendmentAccess', SikApplication::class);

        $validated = $request->validate([
            'is_open' => ['required', 'boolean'],
        ]);

        $isOpen = (bool) $validated['is_open'];
        $sikApplication->update([
            'is_amendment_open' => $isOpen,
            'amendment_opened_by_user_id' => $isOpen ? $user->id : null,
            'amendment_opened_at' => $isOpen ? now() : null,
        ]);

        return redirect()->route('admin.sik.show', $sikApplication->id)
            ->with('success', $isOpen ? 'Akses amendment dibuka.' : 'Akses amendment ditutup.');
    }

    public function processAmendment(Request $request, SikApplication $sikApplication, SikAmendment $amendment)
    {
        $user = auth()->user();
        $this->authorize('process', $amendment);
        abort_if((int) $amendment->sik_application_id !== (int) $sikApplication->id, 404);

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject'],
        ]);

        if ($amendment->status_amendment !== 'submitted') {
            return redirect()->route('admin.sik.show', $sikApplication->id)
                ->with('error', 'Amendment ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($validated, $sikApplication, $amendment, $user) {
            if ($validated['action'] === 'approve') {
                $newPayload = $amendment->new_payload_json ?? [];

                $sikApplication->update([
                    'judul_final_kegiatan' => $newPayload['judul_final_kegiatan'] ?? $sikApplication->judul_final_kegiatan,
                    'timeline_mulai_final' => $newPayload['timeline_mulai_final'] ?? $sikApplication->timeline_mulai_final,
                    'timeline_selesai_final' => $newPayload['timeline_selesai_final'] ?? $sikApplication->timeline_selesai_final,
                    'rencana_tempat' => $newPayload['rencana_tempat'] ?? $sikApplication->rencana_tempat,
                ]);

                $amendment->update([
                    'status_amendment' => 'approved',
                    'effective_at' => now(),
                ]);
            } else {
                $amendment->update([
                    'status_amendment' => 'rejected',
                ]);
            }

            SikHistory::create([
                'sik_application_id' => $sikApplication->id,
                'actor_user_id' => $user->id,
                'event' => 'amendment_' . $validated['action'],
                'payload_json' => [
                    'amendment_id' => $amendment->id,
                ],
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.sik.show', $sikApplication->id)
            ->with('success', 'Amendment berhasil diproses.');
    }

    public function edit(SikApplication $sikApplication)
    {
        $this->authorize('updateAfterRevision', $sikApplication);
        abort_if($sikApplication->status_sik !== 'need_revision', 403, 'Pengajuan ini tidak dalam status revisi.');

        return view('admin.sik.edit', compact('sikApplication'));
    }

    public function update(Request $request, SikApplication $sikApplication)
    {
        $this->authorize('updateAfterRevision', $sikApplication);
        abort_if($sikApplication->status_sik !== 'need_revision', 403, 'Pengajuan ini tidak dalam status revisi.');

        $validated = $request->validate([
            'judul_final_kegiatan' => ['required', 'string', 'max:255'],
            'timeline_mulai_final' => ['required', 'date'],
            'timeline_selesai_final' => ['required', 'date', 'after_or_equal:timeline_mulai_final'],
            'rencana_tempat' => ['nullable', 'string', 'max:255'],
            'proposal' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if ($request->hasFile('proposal')) {
            $validated['proposal_path'] = $request->file('proposal')->store('sik/proposal', 'public');
        }
        if ($request->hasFile('surat_permohonan')) {
            $validated['surat_permohonan_path'] = $request->file('surat_permohonan')->store('sik/surat_permohonan', 'public');
        }

        $sikApplication->update([
            'judul_final_kegiatan' => $validated['judul_final_kegiatan'],
            'timeline_mulai_final' => $validated['timeline_mulai_final'],
            'timeline_selesai_final' => $validated['timeline_selesai_final'],
            'rencana_tempat' => $validated['rencana_tempat'] ?? null,
            'proposal_path' => $validated['proposal_path'] ?? $sikApplication->proposal_path,
            'surat_permohonan_path' => $validated['surat_permohonan_path'] ?? $sikApplication->surat_permohonan_path,
            'status_sik' => 'on_verification',
            'catatan_terakhir' => null,
        ]);

        SikHistory::create([
            'sik_application_id' => $sikApplication->id,
            'actor_user_id' => auth()->id(),
            'event' => 'revision_resubmitted',
            'payload_json' => [
                'judul_final_kegiatan' => $validated['judul_final_kegiatan'],
            ],
            'created_at' => now(),
        ]);

        return redirect()->route('admin.sik.show', $sikApplication->id)->with('success', 'Perbaikan pengajuan berhasil disimpan dan dikirim ulang.');
    }

    public function activeProgramItems(Request $request)
    {
        $user = auth()->user();
        $selectedYear = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        $query = OrmawaProgramItem::with(['plan.ormawa'])
            ->whereHas('plan', function ($q) use ($selectedYear) {
                $q->where('status_plan', 'published');
                if ($selectedYear) {
                    $q->where('tahun', $selectedYear);
                }
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

        $rules = [
            'program_item_id' => ['required', 'integer', 'exists:ormawa_program_items,id'],
            'judul_final_kegiatan' => ['required', 'string', 'max:255'],
            'timeline_mulai_final' => ['required', 'date'],
            'timeline_selesai_final' => ['required', 'date', 'after_or_equal:timeline_mulai_final'],
            'rencana_tempat' => ['nullable', 'string', 'max:255'],
            'proposal' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'surat_permohonan' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ];

        if (! $user->isAdmin() && $user->ormawas()->exists()) {
            $rules['proposal'] = ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'];
            $rules['surat_permohonan'] = ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'];
        }

        $validated = $request->validate($rules);

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

            $responseData = [
                'message' => 'Pengajuan SIK berhasil dibuat dan masuk proses verifikasi.',
                'data' => $sik->load(['steps', 'programItem.plan.ormawa']),
            ];

            if ($request->expectsJson()) {
                return response()->json($responseData, 201);
            }

            return redirect()->route('admin.sik.show', $sik->id)->with('success', $responseData['message']);
        });
    }

    public function processStep(Request $request, SikApplication $sikApplication)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,revise,issue'],
            'notes' => ['nullable', 'string'],
            'step_order' => ['nullable', 'integer', 'min:1'],
            'nomor_sik_eoffice' => ['nullable', 'string', 'max:255'],
        ]);

        $sikApplication->load('steps', 'programItem', 'flow.steps');

        $currentStep = $sikApplication->steps()->where('status_step', 'pending')->orderBy('step_order')->first();
        if (! $currentStep) {
            return response()->json(['message' => 'Tidak ada step verifikasi yang pending.'], 422);
        }

        if (! empty($validated['step_order']) && (int) $validated['step_order'] !== (int) $currentStep->step_order) {
            return response()->json(['message' => 'Aksi hanya boleh pada step pending aktif.'], 422);
        }

        $flowStep = optional($sikApplication->flow)
            ? $sikApplication->flow->steps->firstWhere('step_order', $currentStep->step_order)
            : null;
        $actionType = $flowStep->action_type ?? 'verify';

        $allowedActions = $actionType === 'issue'
            ? ['issue']
            : ['approve', 'reject', 'revise'];

        if (! in_array($validated['action'], $allowedActions, true)) {
            return response()->json(['message' => 'Aksi tidak sesuai jenis step flow (' . $actionType . ').'], 422);
        }

        $userRoles = $user->roles->pluck('title')->map(fn ($role) => strtolower(trim($role)));
        if (! $user->isAdmin() && ! $userRoles->contains(strtolower(trim($currentStep->role_target)))) {
            return response()->json(['message' => 'Anda tidak memiliki hak verifikasi pada step ini.'], 403);
        }

        if ($validated['action'] === 'issue') {
            if (empty($validated['nomor_sik_eoffice'])) {
                return response()->json(['message' => 'Nomor SIK e-office wajib diisi untuk step issue.'], 422);
            }

            if (! $user->isAdmin() && ! $user->roles()->whereIn('title', ['Kemahasiswaan', 'Staf Kemahasiswaan'])->exists()) {
                return response()->json(['message' => 'Hanya admin/kemahasiswaan yang dapat menerbitkan SIK.'], 403);
            }
        }

        return DB::transaction(function () use ($validated, $sikApplication, $currentStep, $user, $request, $actionType) {
            if ($validated['action'] === 'issue') {
                $currentStep->update([
                    'status_step' => 'approved',
                    'acted_by_user_id' => $user->id,
                    'acted_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $this->issueApplication($sikApplication, $user->id, $validated['nomor_sik_eoffice']);

                SikHistory::create([
                    'sik_application_id' => $sikApplication->id,
                    'actor_user_id' => $user->id,
                    'event' => 'step_issue',
                    'payload_json' => [
                        'step_order' => $currentStep->step_order,
                        'role_target' => $currentStep->role_target,
                        'action_type' => $actionType,
                        'nomor_sik_eoffice' => $validated['nomor_sik_eoffice'],
                        'notes' => $validated['notes'] ?? null,
                    ],
                    'created_at' => now(),
                ]);
            } else {
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
                    // Tetap di step yang sama sampai pemohon melakukan revisi
                    $currentStep->update(['status_step' => 'pending']);
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
                        'action_type' => $actionType,
                        'notes' => $validated['notes'] ?? null,
                    ],
                    'created_at' => now(),
                ]);
            }

            $responseData = [
                'message' => 'Proses verifikasi berhasil diperbarui.',
                'data' => $sikApplication->fresh()->load('steps'),
            ];

            if ($request->expectsJson()) {
                return response()->json($responseData);
            }

            return redirect()->route('admin.sik.show', $sikApplication->id)->with('success', $responseData['message']);
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

        $this->issueApplication($sikApplication, $user->id, $validated['nomor_sik_eoffice']);

        SikHistory::create([
            'sik_application_id' => $sikApplication->id,
            'actor_user_id' => $user->id,
            'event' => 'issued',
            'payload_json' => [
                'nomor_sik_eoffice' => $validated['nomor_sik_eoffice'],
                'source' => 'legacy_issue_endpoint',
            ],
            'created_at' => now(),
        ]);

        $responseData = [
            'message' => 'SIK berhasil diterbitkan.',
            'data' => $sikApplication->fresh(['issuer', 'programItem']),
        ];

        if ($request->expectsJson()) {
            return response()->json($responseData);
        }

        return redirect()->route('admin.sik.show', $sikApplication->id)->with('success', $responseData['message']);
    }

    private function issueApplication(SikApplication $sikApplication, int $issuerId, string $nomorSik): void
    {
        $sikApplication->update([
            'status_sik' => 'issued',
            'issued_at' => now(),
            'issued_by_user_id' => $issuerId,
            'nomor_sik_eoffice' => $nomorSik,
        ]);

        $sikApplication->programItem()->update(['status_item' => 'sik_terbit']);
    }
}
