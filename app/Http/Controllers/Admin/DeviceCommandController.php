<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceCommand;

class DeviceCommandController extends Controller
{
    public function index()
    {
        return view('admin.device-command.index');
    }
    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required',
            'command' => 'required'
        ]);

        DeviceCommand::create([
            'location' => $request->location,
            'command' => $request->command,
            'executed' => false
        ]);

        return back()->with('success', 'Command berhasil dikirim');
    }
}