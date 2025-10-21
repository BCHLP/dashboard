<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\Google2FA;

class SetupController extends Controller
{
    /**
     * @throws MissingQrCodeServiceException
     */
    public function totp()
    {
        $google2fa = new Google2FA;
        $secretKey = $google2fa->generateSecretKey();
        $qrCode = $google2fa->getQRCodeInline(
            config('app.name'),
            auth()->user()->email,
            $secretKey
        );

        auth()->user()->update(['totp_secret' => $secretKey]);

        return Inertia::render('auth/totp', ['qrCode' => $qrCode]);
    }

    public function password()
    {

        if (filled(auth()->user()->password)) {
            return response()->redirectToRoute('password.edit');
        }

        return Inertia::render('auth/SetPassword');
    }

    public function storePassword(Request $request)
    {
        $rules = ['password' => ['required', Password::defaults(), 'confirmed']];
        $redirect = route('home');

        if (filled(auth()->user()->password)) {
            $rules['current_password'] = ['required', 'current_password'];
            $redirect = null;
        }

        $validated = $request->validate($rules);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        PersonalAccessToken::where('name', 'registration')->where('tokenable_id', $request->user()->id)->delete();

        return response()->redirectToRoute('home');

    }

    public function voice()
    {
        return Inertia::render('RegisterVoice');
    }
}
