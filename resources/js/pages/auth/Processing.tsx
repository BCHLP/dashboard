import Layout from '@/layouts/auth-layout';
import { useMfa } from '@/MfaProvider';
import { MfaDecision } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEchoPublic } from '@laravel/echo-react';
import { LoaderCircle, Shield } from 'lucide-react';

type Props = {
    eventId: string;
};

type TotpDecisionEvent = {
    eventId: string;
    totp: false;
    voice: false;
};

export default function Processing({ eventId }: Props) {
    const { requireMfa } = useMfa();

    console.log('eventId', eventId);
    useEchoPublic(`MfaProcess.${eventId}`, ['MfaDecisionEvent'], (e: TotpDecisionEvent) => {
        console.log('e', e);

        if (e.voice) {
            router.visit('/login/voice');
            return;
        }

        if (e.totp) {
            requireMfa({
                action: 'complete-login',
                message: 'Please verify your identity to complete login',
                endpoint: 'auth.totp',
                onSuccess: (response: MfaDecision) => {
                    // Redirect to dashboard or intended page
                    console.log('redirect to home');
                    // window.location.href = route('home');

                    if (response.success) {
                        router.visit('/');
                    }
                },
                onCancel: () => {
                    // Maybe redirect back to login or show a message
                    console.log('MFA cancelled during login');
                },
            });
            return;
        }

        router.visit('/login/validate/' + eventId);
    });

    return (
        <Layout title="Verifying your credentials" description="We're performing additional security checks to ensure your account safety">
            <Head title="Please wait..." />

            <div className="flex flex-col items-center justify-center gap-8 py-8">
                {/* Animated loader with shield icon */}
                <div className="relative">
                    <div className="absolute inset-0 flex items-center justify-center">
                        <Shield className="h-8 w-8 text-primary" />
                    </div>
                    <LoaderCircle className="h-16 w-16 animate-spin text-muted-foreground/30" />
                </div>

                {/* Status message */}
                <div className="space-y-2 text-center">
                    <p className="text-sm font-medium text-foreground">Analyzing your login attempt</p>
                    <p className="max-w-sm text-xs text-muted-foreground">
                        This usually takes just a few seconds. We're checking various security factors to protect your account.
                    </p>
                </div>

                {/* Progress indicator dots */}
                <div className="flex gap-2">
                    <div className="h-2 w-2 animate-pulse rounded-full bg-primary" style={{ animationDelay: '0ms' }} />
                    <div className="h-2 w-2 animate-pulse rounded-full bg-primary" style={{ animationDelay: '150ms' }} />
                    <div className="h-2 w-2 animate-pulse rounded-full bg-primary" style={{ animationDelay: '300ms' }} />
                </div>
            </div>
        </Layout>
    );
}
