<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\VoiceRecognitionService;
use Illuminate\Http\Request;

class UserVoiceController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'audio' => ['required', 'file', 'mimes:webm'],
        ]);

        $base64 = base64_encode($request->file('audio')->get());

        $service = new VoiceRecognitionService();
        $success = $service->register($base64);

        if ($success) {
            // Redirect to dashboard on success
            return redirect('/dashboard')->with('message', 'Voice registration successful!');
        } else {
            // Return back with error
            return back()->withErrors(['audio' => 'Voice registration failed']);
        }
    }

    public function compare(Request $request) {
        $data = $request->validate([
            'audio' => ['required', 'file', 'mimes:webm'],
        ]);

        $base64 = base64_encode($request->file('audio')->get());

        $service = new VoiceRecognitionService();
        $success = $service->compare($base64);

        if ($success) {
            // Redirect to dashboard on success
            return redirect('/dashboard')->with('message', 'Voice registration successful!');
        } else {
            // Return back with error
            return back()->withErrors(['audio' => 'Voice did not match']);
        }
    }
}
