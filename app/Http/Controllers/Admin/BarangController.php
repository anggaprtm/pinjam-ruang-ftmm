<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBarangRequest;
use App\Http\Requests\UpdateBarangRequest;
use App\Models\Barang;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('barang_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {

        $query = DB::table('barang_kegiatan')
            ->join('barangs', 'barang_kegiatan.barang_id', '=', 'barangs.id')
            ->join('kegiatan', 'barang_kegiatan.kegiatan_id', '=', 'kegiatan.id')
            ->join('users', 'kegiatan.user_id', '=', 'users.id')
            ->where('barang_kegiatan.status', 'dipinjam')
            ->select(
                'barangs.nama_barang as nama_barang',
                'kegiatan.nama_kegiatan',
                'kegiatan.id as kegiatan_id',
                'kegiatan.waktu_mulai as waktu_mulai',
                'kegiatan.waktu_selesai as waktu_selesai',
                'users.name as nama_peminjam',
                'barang_kegiatan.jumlah as jumlah'
            );

        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan.id', $request->kegiatan_id);
        }

        if ($request->filled('barang_id')) {
            $query->where('barangs.id', $request->barang_id);
        }

        if ($request->filled('user_id')) {
            $query->where('users.id', $request->user_id);
        }


        $table = DataTables::of($query);

        $table->addColumn('placeholder', '&nbsp;');

        $table->addColumn('kegiatan_url', function ($row) {
            return route('admin.kegiatan.show', $row->kegiatan_id);
        });

        // ✅ ini yang bikin waktu muncul di JSON
        $table->addColumn('waktu_mulai_formatted', function ($row) {
            return !empty($row->waktu_mulai)
                ? \Carbon\Carbon::parse($row->waktu_mulai)->translatedFormat('d M Y, H:i')
                : '-';
        });

        $table->addColumn('waktu_selesai_formatted', function ($row) {
            return !empty($row->waktu_selesai)
                ? \Carbon\Carbon::parse($row->waktu_selesai)->translatedFormat('d M Y, H:i')
                : '-';
        });

        $table->rawColumns(['placeholder']);

        return $table->make(true);


    }


        $kegiatans = \App\Models\Kegiatan::query()
            ->whereHas('barangs', function ($q) {
                $q->where('barang_kegiatan.status', 'dipinjam');
            })
            ->select('id', 'nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $barangs = \App\Models\Barang::query()
            ->whereHas('kegiatans', function ($q) {
                $q->where('barang_kegiatan.status', 'dipinjam');
            })
            ->select('id', 'nama_barang')
            ->orderBy('nama_barang')
            ->get();
        
        $users = \App\Models\User::query()
            ->whereHas('kegiatans', function ($q) {
                $q->whereHas('barangs', function ($qq) {
                    $qq->where('barang_kegiatan.status', 'dipinjam');
                });
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();



        return view('admin.barangs.index', compact('kegiatans', 'barangs', 'users'));

    }


    public function create()
    {
        abort_if(Gate::denies('barang_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.create');
    }

    public function store(StoreBarangRequest $request)
    {
        $data = $request->all();

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('uploads/barangs', 'public');
        }

        Barang::create($data);

        return redirect()->route('admin.barangs.master')->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Barang $barang)
    {
        abort_if(Gate::denies('barang_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        abort_if(Gate::denies('barang_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.barangs.edit', compact('barang'));
    }

    public function update(UpdateBarangRequest $request, Barang $barang)
    {
        $data = $request->all();

        if ($request->hasFile('foto')) {
            if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                Storage::disk('public')->delete($barang->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/barangs', 'public');
        }

        $barang->update($data);

        return redirect()->route('admin.barangs.master')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        abort_if(Gate::denies('barang_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
            Storage::disk('public')->delete($barang->foto);
        }

        $barang->delete();

        return back()->with('success', 'Barang berhasil dihapus.');
    }

    public function master(Request $request)
    {
        abort_if(Gate::denies('barang_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {

            $query = Barang::query();

            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('nama_barang', fn ($row) => $row->nama_barang ?? '-');
            $table->editColumn('stok', fn ($row) => $row->stok ?? 0);
            $table->editColumn('deskripsi', fn ($row) => $row->deskripsi ?? '-');

            $table->editColumn('actions', function ($row) {
                $buttons = '';

                if (auth()->user()->can('barang_show')) {
                    $buttons .= '<a class="btn btn-xs btn-info" href="' . route('admin.barangs.show', $row->id) . '" title="Detail"><i class="fas fa-eye"></i></a> ';
                }
                if (auth()->user()->can('barang_edit')) {
                    $buttons .= '<a class="btn btn-xs btn-success" href="' . route('admin.barangs.edit', $row->id) . '" title="Edit"><i class="fas fa-edit"></i></a> ';
                }
                if (auth()->user()->can('barang_delete')) {
                    $buttons .= '<button type="button" class="btn btn-xs btn-danger js-delete-btn" data-url="' . route('admin.barangs.destroy', $row->id) . '" title="Hapus"><i class="fas fa-trash"></i></button>';
                }

                return $buttons;
            });

            $table->rawColumns(['actions', 'placeholder']);

            return $table->make(true);
        }

        return view('admin.barangs.master');
    }


}
