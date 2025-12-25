<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServerConfigController extends Controller
{
    public function index()
    {
        $config = [
            'docker' => config('docker'),
            'kbpanel' => config('kbpanel'),
            'monitoring' => config('monitoring'),
        ];

        return view('admin.settings.server', compact('config'));
    }

    public function update(Request $request)
    {
        // Placeholder for config update logic
        return back()->with('success', 'Configuration updated successfully');
    }
}
