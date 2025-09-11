<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class VoiceRecognitionController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            return response()->redirectToRoute('login');
        }
        return Inertia::render('RegisterVoice');
    }
}
