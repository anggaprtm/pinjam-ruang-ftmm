<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRuanganRequest;
use App\Http\Requests\StoreRuanganRequest;
use App\Http\Requests\UpdateRuanganRequest;
use App\Models\Ruangan;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RuanganController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('ruangan_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangans = Ruangan::all();

        return view('admin.ruangans.index', compact('ruangans'));
    }

    public function create()
    {
        abort_if(Gate::denies('ruangan_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangans.create');
    }

    public function store(StoreRuanganRequest $request)
    {
        $ruangan = Ruangan::create($request->all());

        return redirect()->route('admin.ruangans.index');
    }

    public function edit(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangans.edit', compact('ruangan'));
    }

    public function update(UpdateRuanganRequest $request, Ruangan $ruangan)
    {
        $ruangan->update($request->all());

        return redirect()->route('admin.ruangans.index');
    }

    public function show(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.ruangans.show', compact('ruangan'));
    }

    public function destroy(Ruangan $ruangan)
    {
        abort_if(Gate::denies('ruangan_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ruangan->delete();

        return back();
    }

    public function massDestroy(MassDestroyRuanganRequest $request)
    {
        $ruangans = Ruangan::find(request('ids'));

        foreach ($ruangans as $ruangan) {
            $ruangan->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}