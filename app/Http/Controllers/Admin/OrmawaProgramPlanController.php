<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\OrmawaProgramItemsImport;
use App\Models\Ormawa;
use App\Models\OrmawaProgramItem;
use App\Models\OrmawaProgramPlan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrmawaProgramPlanController extends Controller
{
    public function index()
    {
        $this->authorizeMasterAccess();

        $plans = OrmawaProgramPlan::with(['ormawa', 'programItems'])
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
            'kode_proker' => ['nullable', 'string', 'max:100'],
            'nama_rencana' => ['nullable', 'string', 'max:255'],
            'timeline_mulai_rencana' => ['nullable', 'date'],
            'timeline_selesai_rencana' => ['nullable', 'date', 'after_or_equal:timeline_mulai_rencana'],
            'deskripsi_rencana' => ['nullable', 'string'],
        ]);

        $plan = OrmawaProgramPlan::create([
            'ormawa_id' => $data['ormawa_id'],
            'tahun' => $data['tahun'],
            'status_plan' => $data['status_plan'],
            'dibuat_oleh_user_id' => auth()->id(),
        ]);

        if (! empty($data['nama_rencana'])) {
            $plan->items()->create([
                'kode_proker' => $data['kode_proker'] ?? null,
                'nama_rencana' => $data['nama_rencana'],
                'timeline_mulai_rencana' => $data['timeline_mulai_rencana'] ?? null,
                'timeline_selesai_rencana' => $data['timeline_selesai_rencana'] ?? null,
                'deskripsi_rencana' => $data['deskripsi_rencana'] ?? null,
            ]);
        }

        return redirect()->route('admin.ormawa-plans.edit', $plan->id)->with('success', 'Plan proker berhasil dibuat.');
    }

    public function edit(OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $ormawaPlan->load('programItems');
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


    public function importItems(Request $request, OrmawaProgramPlan $ormawaPlan)
    {
        $this->authorizeMasterAccess();

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        Excel::import(new OrmawaProgramItemsImport($ormawaPlan), $request->file('file'));

        return redirect()->route('admin.ormawa-plans.edit', $ormawaPlan->id)
            ->with('success', 'Import proker berhasil diproses.');
    }

    public function downloadTemplate()
    {
        $this->authorizeMasterAccess();

        $headers = [
            'kode_proker',
            'nama_rencana',
            'timeline_mulai_rencana',
            'timeline_selesai_rencana',
            'deskripsi_rencana',
            'status_item',
        ];

        $sample = [
            ['PROKER-001', 'Seminar Nasional', now()->addMonth()->format('Y-m-d'), now()->addMonth()->addDay()->format('Y-m-d'), 'Kegiatan seminar nasional', 'belum_diajukan'],
            ['PROKER-002', 'Workshop Organisasi', now()->addMonths(2)->format('Y-m-d'), now()->addMonths(2)->addDay()->format('Y-m-d'), 'Workshop internal ormawa', 'belum_diajukan'],
        ];

        $filename = 'template_import_proker_ormawa.csv';

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($sample as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function authorizeMasterAccess(): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || $user->hasRole('Kemahasiswaan') || $user->hasRole('Staf Kemahasiswaan')), 403);
    }
}
