<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Device;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/devices', function (Request $request) {
        return Device::where('is_active', true)->get();
    });

    Route::post('/devices/{device}/control', function (Request $request, Device $device) {
        // Simple sample endpoint that requires token authentication.
        $action = $request->input('action', 'view');

        return response()->json([
            'device' => $device->only(['id', 'name', 'status']),
            'action' => $action,
            'message' => 'Invoke the web dashboard for full control logic.',
        ]);
    });
});

