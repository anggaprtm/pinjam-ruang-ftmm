<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DisplayScheduleController extends Controller
{
    public function store(Request $request)
    {
        \App\Models\DisplaySchedule::create($request->all());

        return back()->with('success', 'Schedule ditambahkan');
    }
    public function destroy($id)
    {
        \App\Models\DisplaySchedule::findOrFail($id)->delete();
        return back()->with('success', 'Schedule dihapus');
    }
}
