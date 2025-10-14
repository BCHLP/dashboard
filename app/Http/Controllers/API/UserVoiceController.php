<?php

namespace App\Http\Controllers\API;

use App\Actions\UserLoginAuditAction;
use App\Facades\AdaptiveMfaFacade;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLoginAudit;
use App\Services\VoiceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class  UserVoiceController extends Controller
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
            return redirect('/')->with('message', 'Voice registration successful!');
        } else {
            // Return back with error
            return back()->withErrors(['audio' => 'Voice registration failed']);
        }
    }

    public function compare(Request $request, UserLoginAuditAction $userLoginAudit) {
        $data = $request->validate([
            'audio' => ['required', 'file', 'mimes:webm'],
        ]);

        $base64 = base64_encode($request->file('audio')->get());

        $event = AdaptiveMfaFacade::load();
        ray("event", $event);
        $user = User::find($event['user_id']);
        abort_if(!$user, 404);

        $service = new VoiceRecognitionService();
        $success = $service->compare($base64, $user);
        $voiceStillRequired = !$success;

        if ($success) {

            ray("VOICE RECOGNITION WAS SUCCESSFUL")->green();

            AdaptiveMfaFacade::setVoice(false);
        }

        if ($success && $event['totp'] === false) {
            // Redirect to dashboard on success
            Auth::login($user);
            $userLoginAudit($user->email, true);
            AdaptiveMfaFacade::clear();
        }

        return response()->json(['totp'=> $event['totp'], 'voice' => $voiceStillRequired]);
    }
}
