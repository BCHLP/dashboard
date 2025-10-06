import Layout from '@/layouts/auth-layout'
import { Head, router } from '@inertiajs/react'
import { useEchoPublic } from '@laravel/echo-react'
import { LoaderCircle, Shield } from 'lucide-react'

type Props = {
    eventId: string
};

type MfaDecisionEvent = {
    eventId: string,
    totp:false,
    voice:false
}

export default function Processing({eventId} : Props) {

    console.log("eventId", eventId);
    // channel.listen('.MfaDecisionEvent', handleMfaDecision);
    useEchoPublic(`MfaProcess.${eventId}`, ['MfaDecisionEvent'], (e:MfaDecisionEvent) => {

        console.log("e", e);

        if (e.totp) {
            router.visit('/login/totp');
            return;
        }

        if (e.voice) {
            router.visit('/login/voice');
            return;
        }

        router.visit('/login/validate/'+eventId);
    });

    return (
        <Layout
            title="Verifying your credentials"
            description="We're performing additional security checks to ensure your account safety"
        >
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
                <div className="text-center space-y-2">
                    <p className="text-sm font-medium text-foreground">
                        Analyzing your login attempt
                    </p>
                    <p className="text-xs text-muted-foreground max-w-sm">
                        This usually takes just a few seconds. We're checking various security factors to protect your account.
                    </p>
                </div>

                {/* Progress indicator dots */}
                <div className="flex gap-2">
                    <div className="h-2 w-2 rounded-full bg-primary animate-pulse" style={{ animationDelay: '0ms' }} />
                    <div className="h-2 w-2 rounded-full bg-primary animate-pulse" style={{ animationDelay: '150ms' }} />
                    <div className="h-2 w-2 rounded-full bg-primary animate-pulse" style={{ animationDelay: '300ms' }} />
                </div>
            </div>
        </Layout>
    );
}
