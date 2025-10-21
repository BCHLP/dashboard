import React, { createContext, ReactNode, useContext } from 'react';
import { useFingerprint } from '../hooks/useFingerprint';
import type { FingerprintHookReturn, FingerprintOptions } from '../types/fingerprint';

const FingerprintContext = createContext<FingerprintHookReturn | undefined>(undefined);

interface FingerprintProviderProps extends FingerprintOptions {
    children: ReactNode;
}

export const FingerprintProvider: React.FC<FingerprintProviderProps> = ({
    children,
    endpoint = '/api/fingerprint',
    csrfToken = null,
    autoCollectOnMount = true,
}) => {
    const fingerprint = useFingerprint({
        endpoint,
        csrfToken,
        autoCollectOnMount,
    });

    return <FingerprintContext.Provider value={fingerprint}>{children}</FingerprintContext.Provider>;
};

export const useContextFingerprint = (): FingerprintHookReturn => {
    const context = useContext(FingerprintContext);
    if (!context) {
        throw new Error('useContextFingerprint must be used within a FingerprintProvider');
    }
    return context;
};
