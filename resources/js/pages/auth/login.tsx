import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';
import { useMfa } from '@/MfaProvider';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

type AuditAction = {
    id: string;
    voice:boolean;
    voice_completed_at:string;
    totp:boolean;
    totp_completed_at:string;
}

interface LoginProps {
    status?: string;
    auditAction?: AuditAction;
}

export default function Login({ status, auditAction }: LoginProps) {
    const { requireMfa } = useMfa();
    const { props } = usePage();
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: 'admin@example.com',
        password: '',
        remember: false,
    });

    console.log("auditAction", auditAction);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login.store', {audit:auditAction?.id??''}), {
            onSuccess: (page) => {
                const pageAuditAction = page.props.auditAction;

                console.log("on success:",  page);

                console.log("auditAction", pageAuditAction);

                // return;

                if (pageAuditAction === null || pageAuditAction === undefined) {
                    window.location.href = route('home');
                    return;
                }

                if (pageAuditAction.totp === true) {
                    requireMfa({
                        action: 'complete-login',
                        audit_id:pageAuditAction.id,
                        message: 'Please verify your identity to complete login',
                        endpoint: 'auth.totp',
                        email: data.email,
                        password: data.password,
                        onSuccess: () => {
                            // Redirect to dashboard or intended page
                            console.log("redirect to home");
                            // window.location.href = route('home');
                        },
                        onCancel: () => {
                            // Maybe redirect back to login or show a message
                            console.log('MFA cancelled during login');
                        }
                    });

                } else if (pageAuditAction.voice) {
                    console.log("request voice");
                } else {
                    console.log("ok what now?");
                   // window.location.href = route('home');
                }
            },
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title="Log in to your account" description="Enter your email and password below to log in">
            <Head title="Log in" />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">Password</Label>

                                <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                    Forgot password?
                                </TextLink>

                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder="Password"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onClick={() => setData('remember', !data.remember)}
                            tabIndex={3}
                        />
                        <Label htmlFor="remember">Remember me</Label>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        Log in
                    </Button>
                </div>
            </form>

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
