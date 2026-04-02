<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DisplayContent;
use getID3;

class DisplayContentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'display_config_id' => 'required',
            'type' => 'required|in:image,video,text',
            'value' => 'nullable',
            'duration' => 'required|integer',
            'image' => 'nullable|file|max:20480'
        ]);

        // IMAGE / VIDEO
        if (in_array($request->type, ['image', 'video'])) {
            $file = $request->file('image');

            if (!$file || !$file->isValid()) {
                return back()->withErrors([
                    'image' => 'File wajib diupload dan harus valid'
                ]);
            }

            $path = $file->store('signage', 'public');
            $data['image_path'] = $path;

            // 🔥 AUTO DETECT VIDEO DURATION
            if ($request->type === 'video') {
                $getID3 = new getID3;
                $filePath = storage_path('app/public/' . $path);

                $fileInfo = $getID3->analyze($filePath);

                if (isset($fileInfo['playtime_seconds'])) {
                    $data['duration'] = ceil($fileInfo['playtime_seconds']);
                }
            }
        }

        // TEXT
        if ($request->type === 'text') {
            $data['image_path'] = null;
        }

        DisplayContent::create($data);

        return back()->with('success', 'Slide ditambahkan');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $index => $id) {
            DisplayContent::where('id', $id)
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