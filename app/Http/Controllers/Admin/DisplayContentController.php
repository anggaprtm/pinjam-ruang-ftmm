<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DisplayContent;

class DisplayContentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'display_config_id' => 'required',
            'type' => 'required',
            'value' => 'nullable',
            'duration' => 'required|integer',
            'image' => 'nullable|image'
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('signage', 'public');
            $data['image_path'] = $path;
        }

        DisplayContent::create($data);

        return back()->with('success', 'Slide ditambahkan');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $index => $id) {
            \App\Models\DisplayContent::where('id', $id)
                ->update(['order' => $index]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function destroy($id)
    {
        DisplayContent::findOrFail($id)->delete();
        return back()->with('success', 'Slide dihapus');
    }
}