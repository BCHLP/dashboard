<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FAQRCode\Google2FA;

class TotpController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('settings/totp');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function update(Request $request) {
        $data = $request->validate(['token' => 'required|string']);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(auth()->user()->totp_secret, $data['token']);
        if ($valid) {
            auth()->user()->update(['totp_activated_at' => Carbon::now()]);
            return back();
        }

        return back()->withErrors(['token' => 'The token is invalid.']);
    }
}
