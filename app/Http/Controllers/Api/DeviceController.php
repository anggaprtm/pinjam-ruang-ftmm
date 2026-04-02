<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function getCommand($location)
    {
        $command = \App\Models\DeviceCommand::where('location', $location)
            ->where('executed', false)
            ->latest()
            ->first();

        if (!$command) {
            return response()->json(['command' => null]);
        }

        $command->executed = true;
        $command->save();

        return response()->json([
            'command' => $command->command
        ]);
    }
}
