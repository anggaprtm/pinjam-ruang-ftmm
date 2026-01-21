<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMobilRequest;
use App\Http\Requests\StoreMobilRequest;
use App\Http\Requests\UpdateMobilRequest;
use App\Models\Mobil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MobilController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('mobil_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $mobils = Mobil::all();

        return view('admin.mobils.index', compact('mobils'));
    }

    public function create()
    {
        abort_if(Gate::denies('mobil_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.mobils.create');
    }

    public function store(StoreMobilRequest $request)
    {
        $mobil = Mobil::create($request->all());

        return redirect()->route('admin.mobils.index')->with('message', 'Data kendaraan berhasil ditambahkan.');
    }

    public function edit(Mobil $mobil)
    {
        abort_if(Gate::denies('mobil_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.mobils.edit', compact('mobil'));
    }

    public function update(UpdateMobilRequest $request, Mobil $mobil)
    {
        $mobil->update($request->all());

        return redirect()->route('admin.mobils.index')->with('message', 'Data kendaraan berhasil diperbarui.');
    }

    public function show(Mobil $mobil)
    {
        abort_if(Gate::denies('mobil_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.mobils.show', compact('mobil'));
    }

    public function destroy(Mobil $mobil)
    {
        abort_if(Gate::denies('mobil_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $mobil->delete();

        return back()->with('message', 'Data kendaraan berhasil dihapus.');
    }

    public function massDestroy(MassDestroyMobilRequest $request)
    {
        Mobil::whereIn('id', $request->input('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}