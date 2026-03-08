<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ormawa;
use App\Models\OrmawaProgramItem;
use App\Models\OrmawaProgramPlan;
use Illuminate\Http\Request;

class OrmawaProgramPlanController extends Controller
{
    public function index()
    {
        $this->authorizeMasterAccess();

        $plans = OrmawaProgramPlan::with(['ormawa', 'items'])
            ->orderByDesc('tahun')
            ->orderBy('ormawa_id')
            ->paginate(20);

        return view('admin.ormawa-masters.plans.index', compact('plans'));
    }

    public function create()
    {
        $this->authorizeMasterAccess();

        $ormawas = Ormawa::where('is_active', true)->orderBy('nama')->get();

        return view('admin.ormawa-masters.plans.create', compact('ormawas'));
    }

    public function store(Request $request)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'ormawa_id' => ['required', 'integer', 'exists:ormawas,id'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status_plan' => ['required', 'in:draft,published,locked'],
        ]);

        OrmawaProgramPlan::create([
            'ormawa_id' => $data['ormawa_id'],
            'tahun' => $data['tahun'],
            'status_plan' => $data['status_plan'],
            'dibuat_oleh_user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.ormawa-plans.index')->with('success', 'Plan proker berhasil dibuat.');
    }

    public function edit(OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $ormawaPlan->load('items');
        $ormawas = Ormawa::where('is_active', true)->orderBy('nama')->get();

        return view('admin.ormawa-masters.plans.edit', compact('ormawaPlan', 'ormawas'));
    }

    public function update(Request $request, OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'ormawa_id' => ['required', 'integer', 'exists:ormawas,id'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'status_plan' => ['required', 'in:draft,published,locked'],
        ]);

        $ormawaPlan->update($data);

        return redirect()->route('admin.ormawa-plans.edit', $ormawaPlan->id)->with('success', 'Plan proker berhasil diperbarui.');
    }

    public function destroy(OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $ormawaPlan->delete();

        return redirect()->route('admin.ormawa-plans.index')->with('success', 'Plan proker berhasil dihapus.');
    }

    public function storeItem(Request $request, OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $data = $request->validate([
            'kode_proker' => ['nullable', 'string', 'max:100'],
            'nama_rencana' => ['required', 'string', 'max:255'],
            'timeline_mulai_rencana' => ['nullable', 'date'],
            'timeline_selesai_rencana' => ['nullable', 'date', 'after_or_equal:timeline_mulai_rencana'],
            'deskripsi_rencana' => ['nullable', 'string'],
        ]);

        $ormawaPlan->items()->create($data);

        return redirect()->route('admin.ormawa-plans.edit', $ormawaPlan->id)->with('success', 'Item proker berhasil ditambahkan.');
    }

    public function updateItem(Request $request, OrmawaProgramPlan $ormawaPlan, OrmawaProgramItem $item)
    {
        $this->authorizeMasterAccess();
        abort_if((int) $item->plan_id !== (int) $ormawaPlan->id, 404);

        $data = $request->validate([
            'kode_proker' => ['nullable', 'string', 'max:100'],
            'nama_rencana' => ['required', 'string', 'max:255'],
            'timeline_mulai_rencana' => ['nullable', 'date'],
            'timeline_selesai_rencana' => ['nullable', 'date', 'after_or_equal:timeline_mulai_rencana'],
            'deskripsi_rencana' => ['nullable', 'string'],
            'status_item' => ['required', 'in:belum_diajukan,diajukan,proses,sik_terbit,ditolak,arsip'],
        ]);

        $item->update($data);

        return redirect()->route('admin.ormawa-plans.edit', $ormawaPlan->id)->with('success', 'Item proker berhasil diperbarui.');
    }

    public function destroyItem(OrmawaProgramPlan $ormawaPlan, OrmawaProgramItem $item)
    {
        $this->authorizeMasterAccess();
        abort_if((int) $item->plan_id !== (int) $ormawaPlan->id, 404);

        $item->delete();

        return redirect()->route('admin.ormawa-plans.edit', $ormawaPlan->id)->with('success', 'Item proker berhasil dihapus.');
    }

    private function authorizeMasterAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan')), 403);
    }
}
