import { useState, useEffect, useCallback, useRef } from 'react';
import type { FingerprintData, FingerprintOptions, FingerprintHookReturn, FingerprintResponse } from '../types/fingerprint';

export const useFingerprint = (options: FingerprintOptions = {}): FingerprintHookReturn => {
    const [isCollecting, setIsCollecting] = useState<boolean>(false);
    const [lastFingerprint, setLastFingerprint] = useState<FingerprintData | null>(null);
    const [error, setError] = useState<Error | null>(null);

    const fingerprintDataRef = useRef<FingerprintData>({});
    const lastCollectionTimeRef = useRef<number>(0);
    const {
        endpoint = '/api/fingerprint',
        autoCollectOnMount = true,
        csrfToken = null
    } = options;

    // Cache fingerprint for 5 minutes to prevent excessive resource usage
    const FINGERPRINT_CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

    // Screen Information Collection
    const collectScreenInfo = useCallback((): void => {
        try {
            fingerprintDataRef.current.screen_width = screen.width;
            fingerprintDataRef.current.screen_height = screen.height;
            fingerprintDataRef.current.screen_color_depth = screen.colorDepth;
            fingerprintDataRef.current.screen_pixel_ratio = window.devicePixelRatio;
            fingerprintDataRef.current.available_screen_width = screen.availWidth;
            fingerprintDataRef.current.available_screen_height = screen.availHeight;
        } catch (e) {
            console.warn('Could not collect screen info:', e);
        }
    }, []);

    // Canvas Fingerprint Collection
    const collectCanvasFingerprint = useCallback((): void => {
        try {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            if (ctx) {
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('Canvas fingerprint text 🎨', 2, 2);

                ctx.fillStyle = 'rgba(255, 0, 255, 0.5)';
                ctx.fillRect(125, 1, 62, 20);

                fingerprintDataRef.current.canvas_fingerprint = canvas.toDataURL();
            }
        } catch (e) {
            console.warn('Could not collect canvas fingerprint:', e);
        }
    }, []);

    // WebGL Information Collection
    const collectWebGLInfo = useCallback((): void => {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 1;
            canvas.height = 1;

            const gl = canvas.getContext('webgl', {
                failIfMajorPerformanceCaveat: true
            }) || canvas.getContext('experimental-webgl', {
                failIfMajorPerformanceCaveat: true
            });

            if (gl) {
                try {
                    const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                    if (debugInfo) {
                        fingerprintDataRef.current.webgl_vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
                        fingerprintDataRef.current.webgl_renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                    }
                } finally {
                    // Properly dispose of the WebGL context
                    const loseContext = gl.getExtension('WEBGL_lose_context');
                    if (loseContext) {
                        loseContext.loseContext();
                    }
                }
            }

            // Clean up canvas
            canvas.width = 0;
            canvas.height = 0;
        } catch (e) {
            console.warn('Could not collect WebGL info:', e);
        }
    }, []);

    // Audio Fingerprint Collection
    const collectAudioFingerprint = useCallback(async (): Promise<void> => {
        try {
            const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
            if (!AudioContextClass) return;

            const audioContext = new AudioContextClass();

            try {
                const oscillator = audioContext.createOscillator();
                const analyser = audioContext.createAnalyser();
                const gainNode = audioContext.createGain();
                const scriptProcessor = audioContext.createScriptProcessor(4096, 1, 1);

                gainNode.gain.value = 0;
                oscillator.connect(analyser);
                analyser.connect(scriptProcessor);
                scriptProcessor.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.start(0);

                return new Promise<void>((resolve) => {
                    const timeout = setTimeout(() => {
                        cleanup();
                        resolve();
                    }, 1000);

                    const cleanup = () => {
                        try {
                            oscillator.stop();
                            oscillator.disconnect();
                            scriptProcessor.disconnect();
                            gainNode.disconnect();
                            analyser.disconnect();
                        } catch (e) {
                            // Ignore cleanup errors
                        }
                        clearTimeout(timeout);
                        audioContext.close().catch(() => {
                            // Ignore close errors
                        });
                    };

                    scriptProcessor.onaudioprocess = (e: AudioProcessingEvent) => {
                        const samples = e.inputBuffer.getChannelData(0);
                        let sum = 0;
                        for (let i = 0; i < samples.length; i++) {
                            sum += Math.abs(samples[i]);
                        }
                        fingerprintDataRef.current.audio_fingerprint = sum.toString();

                        cleanup();
                        resolve();
                    };
                });
            } catch (e) {
                // Ensure context is closed even if setup fails
                audioContext.close().catch(() => {});
                throw e;
            }
        } catch (e) {
            console.warn('Could not collect audio fingerprint:', e);
        }
    }, []);

    // Font Information Collection
    const collectFontInfo = useCallback((): void => {
        try {
            const testFonts = [
                'Arial', 'Arial Black', 'Arial Narrow', 'Arial Unicode MS',
                'Calibri', 'Cambria', 'Comic Sans MS', 'Courier', 'Courier New',
                'Garamond', 'Georgia', 'Helvetica', 'Impact', 'Lucida Console',
                'Tahoma', 'Times', 'Times New Roman', 'Trebuchet MS', 'Verdana'
            ];

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            if (!context) return;

            const text = 'mmmmmmmmmmlli';

            const measureText = (font: string): { width: number; height: number } => {
                context.font = font;
                const metrics = context.measureText(text);
                return {
                    width: metrics.width,
                    height: (metrics.actualBoundingBoxAscent || 0) + (metrics.actualBoundingBoxDescent || 0)
                };
            };

            const baselineSize = measureText('72px monospace');

            const availableFonts = testFonts.filter(font => {
                const size = measureText(`72px ${font}, monospace`);
                return size.width !== baselineSize.width || size.height !== baselineSize.height;
            });

            fingerprintDataRef.current.fonts_available = availableFonts.sort().join(',');
        } catch (e) {
            console.warn('Could not collect font info:', e);
        }
    }, []);

    // Timezone and Language Information
    const collectTimezoneInfo = useCallback((): void => {
        try {
            fingerprintDataRef.current.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            fingerprintDataRef.current.timezone_offset = new Date().getTimezoneOffset();
            fingerprintDataRef.current.languages = navigator.languages.join(',');
        } catch (e) {
            console.warn('Could not collect timezone info:', e);
        }
    }, []);

    // Storage Capabilities
    const collectStorageInfo = useCallback((): void => {
        try {
            // Test localStorage
            try {
                localStorage.setItem('test', 'test');
                localStorage.removeItem('test');
                fingerprintDataRef.current.local_storage_enabled = true;
            } catch (e) {
                fingerprintDataRef.current.local_storage_enabled = false;
            }

            // Test sessionStorage
            try {
                sessionStorage.setItem('test', 'test');
                sessionStorage.removeItem('test');
                fingerprintDataRef.current.session_storage_enabled = true;
            } catch (e) {
                fingerprintDataRef.current.session_storage_enabled = false;
            }

            fingerprintDataRef.current.cookies_enabled = navigator.cookieEnabled;
        } catch (e) {
            console.warn('Could not collect storage info:', e);
        }
    }, []);

    // Hardware Information
    const collectHardwareInfo = useCallback(async (): Promise<void> => {
        try {
            fingerprintDataRef.current.cpu_cores = navigator.hardwareConcurrency || 'unknown';
            fingerprintDataRef.current.memory_gb = (navigator as any).deviceMemory || 'unknown';
            fingerprintDataRef.current.max_touch_points = navigator.maxTouchPoints || 0;

            // Battery API (if available)
            if ('getBattery' in navigator) {
                try {
                    const battery = await (navigator as any).getBattery();
                    fingerprintDataRef.current.battery_level = Math.round(battery.level * 100);
                    fingerprintDataRef.current.battery_charging = battery.charging;
                } catch (e) {
                    console.warn('Battery API not available');
                }
            }
        } catch (e) {
            console.warn('Could not collect hardware info:', e);
        }
    }, []);

    // Plugin Information
    const collectPluginInfo = useCallback((): void => {
        try {
            const plugins = Array.from(navigator.plugins).map(plugin => ({
                name: plugin.name,
                filename: plugin.filename
            }));
            fingerprintDataRef.current.plugins = JSON.stringify(plugins);
        } catch (e) {
            console.warn('Could not collect plugin info:', e);
        }
    }, []);

    // WebRTC Information
    const collectWebRTCInfo = useCallback((): Promise<void> => {
        return new Promise<void>((resolve) => {
            try {
                const rtc = new RTCPeerConnection({
                    iceServers: [{ urls: 'stun:stun.l.google.com:19302' }]
                });

                rtc.createDataChannel('');
                rtc.createOffer()
                    .then(offer => rtc.setLocalDescription(offer))
                    .catch(() => resolve());

                rtc.onicecandidate = (event: RTCPeerConnectionIceEvent) => {
                    if (event.candidate) {
                        const candidate = event.candidate.candidate;
                        const match = candidate.match(/([0-9]{1,3}\.){3}[0-9]{1,3}/);
                        if (match) {
                            fingerprintDataRef.current.webrtc_ip = match[0];
                            rtc.close();
                            resolve();
                        }
                    }
                };

                setTimeout(() => {
                    rtc.close();
                    resolve();
                }, 2000);
            } catch (e) {
                console.warn('Could not collect WebRTC info:', e);
                resolve();
            }
        });
    }, []);

    // Main collection function
    const collectFingerprint = useCallback(async (): Promise<FingerprintData> => {
        // Check if we have a recent cached fingerprint
        const now = Date.now();
        if (lastFingerprint && (now - lastCollectionTimeRef.current) < FINGERPRINT_CACHE_DURATION) {
            return lastFingerprint;
        }

        // If we're already collecting, wait for it to complete
        if (isCollecting) {
            return new Promise((resolve) => {
                const checkForCompletion = () => {
                    if (!isCollecting && lastFingerprint) {
                        resolve(lastFingerprint);
                    } else {
                        setTimeout(checkForCompletion, 100);
                    }
                };
                checkForCompletion();
            });
        }

        setIsCollecting(true);
        setError(null);

        try {
            fingerprintDataRef.current = {};

            // Run collections in smaller batches to reduce resource usage
            await Promise.all([
                collectScreenInfo(),
                collectTimezoneInfo(),
                collectStorageInfo(),
                collectPluginInfo()
            ]);

            // Run resource-intensive collections separately with small delays
            await collectCanvasFingerprint();
            await new Promise(resolve => setTimeout(resolve, 10)); // Small delay

            await collectWebGLInfo();
            await new Promise(resolve => setTimeout(resolve, 10));

            await collectFontInfo();
            await collectHardwareInfo();
            await new Promise(resolve => setTimeout(resolve, 10));

            // Run the most resource-intensive ones last
            // await collectAudioFingerprint();
            await new Promise(resolve => setTimeout(resolve, 10));

            await collectWebRTCInfo();

            const fingerprint = { ...fingerprintDataRef.current };
            setLastFingerprint(fingerprint);
            lastCollectionTimeRef.current = now;
            return fingerprint;

        } catch (err) {
            const error = err instanceof Error ? err : new Error('Unknown error occurred');
            setError(error);
            throw error;
        } finally {
            setIsCollecting(false);
        }
    }, [isCollecting, lastFingerprint, collectScreenInfo, collectCanvasFingerprint,
        collectWebGLInfo, collectFontInfo, collectTimezoneInfo,
        collectStorageInfo, collectHardwareInfo, collectPluginInfo, collectWebRTCInfo,
        FINGERPRINT_CACHE_DURATION]);

    // Send fingerprint to server
    const sendFingerprint = useCallback(async (customEndpoint?: string): Promise<FingerprintResponse> => {
        try {
            const fingerprintData = await collectFingerprint();
            const url = customEndpoint || endpoint;

            const headers: Record<string, string> = {
                'Content-Type': 'application/json',
            };

            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers,
                body: JSON.stringify(fingerprintData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (err) {
            const error = err instanceof Error ? err : new Error('Failed to send fingerprint');
            setError(error);
            throw error;
        }
    }, [collectFingerprint, endpoint, csrfToken]);

    // Auto-collect on mount (with proper cleanup)
    useEffect(() => {
        if (autoCollectOnMount && !lastFingerprint) {
            const timeoutId = setTimeout(() => {
                collectFingerprint().catch(console.error);
            }, 100); // Small delay to prevent issues during initial render

            return () => clearTimeout(timeoutId);
        }
    }, [autoCollectOnMount, lastFingerprint, collectFingerprint]);

    return {
        isCollecting,
        lastFingerprint,
        error,
        collectFingerprint,
        sendFingerprint
    };
};
