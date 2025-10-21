// Components
import { Head } from '@inertiajs/react';

import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <AuthLayout title="Verify email" description="Please verify your email address by clicking on the link we just emailed to you.">
            <Head title="Email verification" />

            <div className="mb-4 text-center text-sm font-medium text-green-600">
                A new verification link has been sent to the email address you provided during registration. If you have not received this email,
                please inform your manager.
            </div>
        </AuthLayout>
    );
}
