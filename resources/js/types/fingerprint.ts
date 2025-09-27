export interface FingerprintData {
    // Screen Information
    screen_width?: number;
    screen_height?: number;
    screen_color_depth?: number;
    screen_pixel_ratio?: number;
    available_screen_width?: number;
    available_screen_height?: number;

    // Browser Capabilities
    canvas_fingerprint?: string;
    webgl_vendor?: string;
    webgl_renderer?: string;
    audio_fingerprint?: string;
    fonts_available?: string;

    // Timing and Performance
    timezone?: string;
    timezone_offset?: number;
    performance_timing?: string;

    // Storage and Preferences
    local_storage_enabled?: boolean;
    session_storage_enabled?: boolean;
    cookies_enabled?: boolean;
    languages?: string;

    // Hardware Information
    cpu_cores?: number | string;
    memory_gb?: number | string;
    max_touch_points?: number;
    battery_level?: number;
    battery_charging?: boolean;

    // Plugins and Extensions
    plugins?: string;
    webrtc_ip?: string;
}

export interface FingerprintOptions {
    endpoint?: string;
    autoCollectOnMount?: boolean;
    csrfToken?: string | null;
}

export interface FingerprintHookReturn {
    isCollecting: boolean;
    lastFingerprint: FingerprintData | null;
    error: Error | null;
    collectFingerprint: () => Promise<FingerprintData>;
    sendFingerprint: (customEndpoint?: string) => Promise<any>;
}

export interface FingerprintResponse {
    success: boolean;
    fingerprint_id?: string;
    is_suspicious?: boolean;
    message?: string;
}
