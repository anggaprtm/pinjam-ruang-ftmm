<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisOrmawa;
use App\Models\SikVerificationFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SikVerificationFlowController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        $flows = SikVerificationFlow::with(['jenisOrmawa', 'steps'])
            ->orderByDesc('is_active')
            ->orderBy('nama_flow')
            ->paginate(15);

        return view('admin.sik-flows.index', compact('flows'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        $jenisOrmawas = JenisOrmawa::where('is_active', true)->orderBy('nama_jenis')->get();

        return view('admin.sik-flows.create', compact('jenisOrmawas'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $this->validateFlow($request);

        DB::transaction(function () use ($validated) {
            $flow = SikVerificationFlow::create([
                'jenis_ormawa_id' => $validated['jenis_ormawa_id'],
                'nama_flow' => $validated['nama_flow'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            if ($flow->is_active) {
                SikVerificationFlow::where('jenis_ormawa_id', $flow->jenis_ormawa_id)
                    ->where('id', '!=', $flow->id)
                    ->update(['is_active' => false]);
            }

            $this->syncSteps($flow, $validated['steps']);
        });

        return redirect()->route('admin.sik-flows.index')->with('success', 'Flow verifikasi berhasil dibuat.');
    }

    public function edit(SikVerificationFlow $sikFlow)
    {
        $this->authorizeAdmin();

        $sikFlow->load('steps');
        $jenisOrmawas = JenisOrmawa::where('is_active', true)->orderBy('nama_jenis')->get();

        return view('admin.sik-flows.edit', compact('sikFlow', 'jenisOrmawas'));
    }

    public function update(Request $request, SikVerificationFlow $sikFlow)
    {
        $this->authorizeAdmin();

        $validated = $this->validateFlow($request);

        DB::transaction(function () use ($validated, $sikFlow) {
            $sikFlow->update([
                'jenis_ormawa_id' => $validated['jenis_ormawa_id'],
                'nama_flow' => $validated['nama_flow'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            if ($sikFlow->is_active) {
                SikVerificationFlow::where('jenis_ormawa_id', $sikFlow->jenis_ormawa_id)
                    ->where('id', '!=', $sikFlow->id)
                    ->update(['is_active' => false]);
            }

            $sikFlow->steps()->delete();
            $this->syncSteps($sikFlow, $validated['steps']);
        });

        return redirect()->route('admin.sik-flows.index')->with('success', 'Flow verifikasi berhasil diperbarui.');
    }

    public function destroy(SikVerificationFlow $sikFlow)
    {
        $this->authorizeAdmin();

        $sikFlow->delete();

        return redirect()->route('admin.sik-flows.index')->with('success', 'Flow verifikasi berhasil dihapus.');
    }

    private function validateFlow(Request $request): array
    {
        return $request->validate([
            'jenis_ormawa_id' => ['required', 'integer', 'exists:jenis_ormawas,id'],
            'nama_flow' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.label_step' => ['required', 'string', 'max:255'],
            'steps.*.role_target' => ['required', 'string', 'max:120'],
            'steps.*.action_type' => ['required', 'in:verify,approve,issue'],
            'steps.*.sla_days' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);
    }

    private function syncSteps(SikVerificationFlow $flow, array $steps): void
    {
        foreach (array_values($steps) as $index => $step) {
            $flow->steps()->create([
                'step_order' => $index + 1,
                'label_step' => $step['label_step'],
                'role_target' => $step['role_target'],
                'action_type' => $step['action_type'],
                'sla_days' => $step['sla_days'] ?? null,
            ]);
        }
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
