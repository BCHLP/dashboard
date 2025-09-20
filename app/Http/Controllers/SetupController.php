<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\Google2FA;
class SetupController extends Controller
{
    /**
     * @throws MissingQrCodeServiceException
     */
    public function totp()
    {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();
        $qrCode = $google2fa->getQRCodeInline(
            config('app.name'),
            auth()->user()->email,
            $secretKey
        );

        auth()->user()->update(['totp_secret' => $secretKey]);

        return Inertia::render('auth/totp', ['qrCode' => $qrCode]);
    }

    public function password() {
        if (filled(auth()->user()->password)) {
            return response()->redirectToRoute('password.edit');
        }

        return Inertia::render('auth/SetPassword');
    }

    public function voice() {
        return Inertia::render('RegisterVoice');
    }
}
