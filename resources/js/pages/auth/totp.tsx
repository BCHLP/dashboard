import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle, Smartphone, Shield, CheckCircle, AlertCircle } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AuthLayout from '@/layouts/auth-layout';

type TotpForm = {
    token: string;
};

type Props = {
    qrCode: string;
    secret?: string;
};

export default function Totp({ qrCode, secret }: Props) {
    const [step, setStep] = useState(1);
    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm<Required<TotpForm>>({
        token: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('totp.update'), {
            onSuccess: () => {
                setStep(3);
                reset('token');
            },
        });
    };

    return (
        <AuthLayout
            title="Set up Two-Factor Authentication"
            description="Secure your account with an additional layer of protection"
        >
            <Head title="Two-Factor Authentication Setup" />

            <div className="space-y-6">
                {/* Progress Steps */}
                <div className="flex items-center justify-center space-x-4 mb-8">
                    <div className={`flex items-center space-x-2 ${step >= 1 ? 'text-blue-600' : 'text-gray-400'}`}>
                        <div className={`w-8 h-8 rounded-full border-2 flex items-center justify-center ${step >= 1 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300'}`}>
                            1
                        </div>
                        <span className="text-sm font-medium">Scan</span>
                    </div>
                    <div className="w-12 h-px bg-gray-300"></div>
                    <div className={`flex items-center space-x-2 ${step >= 2 ? 'text-blue-600' : 'text-gray-400'}`}>
                        <div className={`w-8 h-8 rounded-full border-2 flex items-center justify-center ${step >= 2 ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300'}`}>
                            2
                        </div>
                        <span className="text-sm font-medium">Verify</span>
                    </div>
                    <div className="w-12 h-px bg-gray-300"></div>
                    <div className={`flex items-center space-x-2 ${step >= 3 ? 'text-green-600' : 'text-gray-400'}`}>
                        <div className={`w-8 h-8 rounded-full border-2 flex items-center justify-center ${step >= 3 ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300'}`}>
                            {step >= 3 ? <CheckCircle className="h-4 w-4" /> : '3'}
                        </div>
                        <span className="text-sm font-medium">Complete</span>
                    </div>
                </div>

                {step === 1 && (
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                <Smartphone className="h-6 w-6 text-blue-600" />
                            </div>
                            <CardTitle>Scan QR Code</CardTitle>
                            <CardDescription>
                                Use your authenticator app to scan the QR code below
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="flex justify-center">
                                <div className="bg-white p-4 rounded-lg border">
                                    <img src={qrCode} alt="QR Code for TOTP setup" className="w-48 h-48" />
                                </div>
                            </div>

                            <Alert>
                                <AlertCircle className="h-4 w-4" />
                                <AlertDescription>
                                    <strong>Don't have an authenticator app?</strong><br />
                                    Download Google Authenticator, Authy, or any TOTP-compatible app from your device's app store.
                                </AlertDescription>
                            </Alert>

                            {secret && (
                                <div className="bg-gray-50 p-4 rounded-lg">
                                    <Label className="text-sm font-medium text-gray-700">Manual Entry Key</Label>
                                    <p className="text-sm text-gray-600 mt-1 mb-2">
                                        If you can't scan the QR code, enter this key manually:
                                    </p>
                                    <code className="bg-white px-2 py-1 rounded border text-sm font-mono break-all">
                                        {secret}
                                    </code>
                                </div>
                            )}

                            <Button
                                onClick={() => setStep(2)}
                                className="w-full"
                            >
                                I've Added the Account
                            </Button>
                        </CardContent>
                    </Card>
                )}

                {step === 2 && (
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <Shield className="h-6 w-6 text-green-600" />
                            </div>
                            <CardTitle>Verify Your Setup</CardTitle>
                            <CardDescription>
                                Enter the 6-digit code from your authenticator app to complete setup
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="token">Authentication Code</Label>
                                    <Input
                                        id="token"
                                        type="text"
                                        required
                                        autoFocus
                                        maxLength={6}
                                        pattern="[0-9]{6}"
                                        placeholder="000000"
                                        value={data.token}
                                        onChange={(e) => setData('token', e.target.value.replace(/\D/g, ''))}
                                        className="text-center text-lg tracking-widest font-mono"
                                    />
                                    <InputError message={errors.token} />
                                    <p className="text-sm text-gray-600">
                                        Enter the 6-digit code displayed in your authenticator app
                                    </p>
                                </div>

                                <div className="flex space-x-3">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setStep(1)}
                                        className="flex-1"
                                    >
                                        Back
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={processing || data.token.length !== 6}
                                        className="flex-1"
                                    >
                                        {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                                        Verify & Enable
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                )}

                {step === 3 && (
                    <Card>
                        <CardHeader className="text-center">
                            <div className="mx-auto w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <CheckCircle className="h-6 w-6 text-green-600" />
                            </div>
                            <CardTitle className="text-green-600">Setup Complete!</CardTitle>
                            <CardDescription>
                                Two-factor authentication has been successfully enabled on your account
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Alert className="border-green-200 bg-green-50">
                                <Shield className="h-4 w-4 text-green-600" />
                                <AlertDescription className="text-green-700">
                                    Your account is now protected with two-factor authentication.
                                    You'll need to enter a code from your authenticator app each time you log in.
                                </AlertDescription>
                            </Alert>

                            <Button
                                onClick={() => window.location.href = route('home')}
                                className="w-full mt-6"
                            >
                                Continue to Dashboard
                            </Button>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AuthLayout>
    );
}
