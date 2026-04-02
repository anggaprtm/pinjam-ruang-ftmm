<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $fillable = [
        'location',
        'command',
        'executed'
    ];
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
