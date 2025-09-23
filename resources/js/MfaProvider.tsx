import React, { createContext, useContext, useState, ReactNode, useCallback } from 'react';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useRef } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';

interface MfaChallenge {
    action: string; // The route or action that triggered MFA
    onSuccess?: () => void; // Callback for successful MFA
    onCancel?: () => void; // Callback for cancelled MFA
    message?: string; // Custom message for the modal
    endpoint?: string;
    email?:string;
    password?:string;
}

interface MfaContextType {
    requireMfa: (challenge: MfaChallenge) => void;
    dismissMfa: () => void;
    isActive: boolean;
    endpoint?: string;
}

const MfaContext = createContext<MfaContextType | undefined>(undefined);

export const useMfa = () => {
    const context = useContext(MfaContext);
    if (!context) {
        throw new Error('useMfa must be used within an MfaProvider');
    }
    return context;
};

interface MfaProviderProps {
    children: ReactNode;
}

export const MfaProvider: React.FC<MfaProviderProps> = ({ children }) => {
    const [challenge, setChallenge] = useState<MfaChallenge | null>(null);
    const [isOpen, setIsOpen] = useState(false);

    const requireMfa = useCallback((newChallenge: MfaChallenge) => {
        setChallenge(newChallenge);
        setIsOpen(true);
    }, []);

    const dismissMfa = useCallback(() => {
        if (challenge?.onCancel) {
            challenge.onCancel();
        }
        setChallenge(null);
        setIsOpen(false);
    }, [challenge]);

    const handleMfaSuccess = useCallback(() => {
        if (challenge?.onSuccess) {
            challenge.onSuccess();
        }
        setChallenge(null);
        setIsOpen(false);
    }, [challenge]);

    return (
        <MfaContext.Provider value={{ requireMfa, dismissMfa, isActive: isOpen }}>
            {children}
            <MfaModal
                open={isOpen}
                challenge={challenge}
                onSuccess={handleMfaSuccess}
                onCancel={dismissMfa}
            />
        </MfaContext.Provider>
    );
};

// The modal component
interface MfaModalProps {
    open: boolean;
    challenge: MfaChallenge | null;
    onSuccess: () => void;
    onCancel: () => void;
    endpoint?: string;
    email?:string;
    password?:string;
}

const MfaModal: React.FC<MfaModalProps> = ({ open, challenge, onSuccess, onCancel }) => {
    const tokenInput = useRef<HTMLInputElement>(null);
    const { data, setData, post, processing, reset, errors, clearErrors } = useForm<{ token: string }>({
        token: ''
    });

    const sendToken: FormEventHandler = (e) => {
        e.preventDefault();

        if (!challenge) return;

        const formdata = {token:data.token, action:challenge.action, email: challenge.email, password:challenge.password};

        console.log("formdata", formdata);
        // Post to a dedicated MFA verification endpoint
        post(route(challenge.endpoint), {
            data: formdata,
            preserveScroll: true,
            onSuccess: () => {
                reset();
                clearErrors();
                onSuccess();
            },
            onError: () => {
                tokenInput.current?.focus();
            },
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        clearErrors();
        reset();
        onCancel();
    };

    return (
        <Dialog open={open} onOpenChange={(open) => !open && closeModal()}>
            <DialogContent>
                <DialogTitle>Authentication required</DialogTitle>
                <DialogDescription>
                    {challenge?.message || 'Please enter your MFA code to continue'}
                </DialogDescription>
                <form className="space-y-6" onSubmit={sendToken}>
                    <div className="grid gap-2">
                        <Label htmlFor="token" className="sr-only">
                            MFA Code
                        </Label>

                        <Input
                            id="token"
                            type="text"
                            name="token"
                            ref={tokenInput}
                            value={data.token}
                            onChange={(e) => setData('token', e.target.value)}
                            placeholder="Enter MFA code"
                            autoComplete="one-time-code"
                        />

                        <InputError message={errors.token} />
                    </div>

                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary" onClick={closeModal}>
                                Cancel
                            </Button>
                        </DialogClose>

                        <Button disabled={processing} type="submit">
                            {processing ? 'Verifying...' : 'Continue'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
};
