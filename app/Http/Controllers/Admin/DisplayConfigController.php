<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DisplayConfig;

class DisplayConfigController extends Controller
{
    public function index()
    {
        $configs = DisplayConfig::latest()->get();
        return view('admin.display-config.index', compact('configs'));
    }

    public function create()
    {
        return view('admin.display-config.create');
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'location' => 'required',
            'mode' => 'required',
            'content_type' => 'nullable',
            'content_value' => 'nullable',
            'image' => 'nullable|file|max:20480'
        ]);

        // HANDLE UPLOAD
        $file = $request->file('image');
        if ($file && $file->isValid()) {
            $path = $file->store('signage', 'public');
            $data['image_path'] = $path;

            if (!$request->content_type) {
                $data['content_type'] = 'image';
            }

            $data['content_value'] = '/storage/' . $path;
        }

        DisplayConfig::create($data);

        return redirect()->route('admin.display-config.index')
            ->with('success', 'Config berhasil ditambahkan');
    }

    public function edit(DisplayConfig $displayConfig)
    {
        $displayConfig->load(['contents' => function ($q) {
            $q->orderBy('order');
        }]);

        return view('admin.display-config.edit', compact('displayConfig'));
    }

    public function update(Request $request, DisplayConfig $displayConfig)
    {
        $data = $request->validate([
            'location' => 'required',
            'mode' => 'required',
            'content_type' => 'nullable',
            'content_value' => 'nullable',
            'image' => 'nullable|file|max:20480'
        ]);

        // HANDLE UPLOAD (SAFE)
        $file = $request->file('image');
        if ($file) {
            if (!$file->isValid()) {
                return back()->withErrors([
                    'image' => 'Upload gagal: ' . $file->getErrorMessage()
                ]);
            }

            $path = $file->store('signage', 'public');
            $data['image_path'] = $path;
            $data['content_value'] = '/storage/' . $path;
        }

        $displayConfig->update($data);

        return redirect()->route('admin.display-config.index')
            ->with('success', 'Config berhasil diupdate');
    }

    public function destroy(DisplayConfig $displayConfig)
    {
        $displayConfig->delete();
        return back()->with('success', 'Config dihapus');
    }

    public function toggle($id)
    {
        $config = DisplayConfig::findOrFail($id);
        $config->is_active = !$config->is_active;
        $config->save();

        return back()->with('success', 'Status berhasil diubah');
    }
}