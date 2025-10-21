import { useMfa } from '@/MfaProvider';
import { MfaDecision } from '@/types';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useCallback, useEffect, useRef, useState } from 'react';
import { FaMicrophone } from 'react-icons/fa';

type Props = {
    apiEndpoint: string;
};

const Voice = (props: Props) => {
    const [isRecording, setIsRecording] = useState(false);
    const [status, setStatus] = useState('Click the microphone to begin');
    const [countdown, setCountdown] = useState(10);

    const mediaRecorderRef = useRef(null);
    const audioChunksRef = useRef([]);
    const audioContextRef = useRef(null);
    const analyserRef = useRef(null);
    const microphoneRef = useRef(null);
    const animationRef = useRef(null);
    const visualizerRef = useRef(null);
    const streamRef = useRef(null);
    const isRecordingRef = useRef(false); // This tracks recording state for animation
    const countdownIntervalRef = useRef(null);

    const audioFormat = 'audio/webm';
    const { requireMfa } = useMfa();

    const createVisualizer = useCallback(() => {
        if (!visualizerRef.current) return;

        visualizerRef.current.innerHTML = '';

        for (let i = 0; i < 20; i++) {
            const wave = document.createElement('div');
            wave.className = 'w-1 bg-green-500 mx-0.5 rounded-sm transition-all duration-75';
            wave.style.height = '10px';
            wave.style.minHeight = '10px';
            visualizerRef.current.appendChild(wave);
        }
    }, []);

    const animate = useCallback(() => {
        if (!analyserRef.current) {
            return;
        }

        const bufferLength = analyserRef.current.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        analyserRef.current.getByteFrequencyData(dataArray);

        // const maxValue = Math.max(...dataArray)
        // const avgValue = dataArray.reduce((a, b) => a + b, 0) / dataArray.length

        const waves = visualizerRef.current?.querySelectorAll('div');
        if (waves && waves.length > 0) {
            console.log('Updating', waves.length, 'wave elements');
            waves.forEach((wave, index) => {
                const dataIndex = Math.floor((index / waves.length) * bufferLength);
                const value = dataArray[dataIndex] || 0;
                const height = Math.max(10, (value / 255) * 70 + 10);
                wave.style.height = `${height}px`;
            });
        } else {
            console.log('No waves found to update');
        }

        // USE THE REF INSTEAD OF STATE
        if (isRecordingRef.current) {
            animationRef.current = requestAnimationFrame(animate);
        }
    }, []);

    const startVisualization = useCallback(() => {
        if (analyserRef.current && audioContextRef.current) {
            animate();
        }
    }, [animate]);

    const sendToBackend = useCallback(async (audioBlob) => {
        try {
            const formData = new FormData();
            const filename = `recording_${Date.now()}.webm`;
            formData.append('audio', audioBlob, filename);

            const response = await axios.post(props.apiEndpoint, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (response.data.voice) {
                setStatus('Voice recognition failed');
                return;
            }

            if (response.data.totp) {
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

            return router.visit('/');

            setStatus('Please wait');
        } catch (error) {
            setStatus('Upload failed');
        }
    }, []);

    const processRecording = useCallback(async () => {
        try {
            const audioBlob = new Blob(audioChunksRef.current, { type: 'audio/webm' });
            const fileSizeMB = (audioBlob.size / (1024 * 1024)).toFixed(2);
            await sendToBackend(audioBlob);
        } catch (error) {
            console.log(`Error processing recording: ${error.message}`);
        }
    }, [sendToBackend]);

    const startRecording = useCallback(async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: false,
                    sampleRate: 44100,
                },
            });

            streamRef.current = stream;

            // Audio context setup
            audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)();

            if (audioContextRef.current.state === 'suspended') {
                await audioContextRef.current.resume();
            }

            // Analyser setup
            analyserRef.current = audioContextRef.current.createAnalyser();
            analyserRef.current.fftSize = 256;
            analyserRef.current.smoothingTimeConstant = 0.8;

            microphoneRef.current = audioContextRef.current.createMediaStreamSource(stream);
            microphoneRef.current.connect(analyserRef.current);

            // MediaRecorder setup
            mediaRecorderRef.current = new MediaRecorder(stream, {
                mimeType: audioFormat,
                bitsPerSecond: 128000,
            });

            audioChunksRef.current = [];

            mediaRecorderRef.current.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunksRef.current.push(event.data);
                }
            };

            mediaRecorderRef.current.onstop = processRecording;

            mediaRecorderRef.current.start(1000);

            setIsRecording(true);
            isRecordingRef.current = true; // THIS IS THE KEY CHANGE
            setStatus('Recording... Speak now!');
            setCountdown(10); // Reset countdown to 10 seconds

            // Start countdown timer
            countdownIntervalRef.current = setInterval(() => {
                setCountdown((prevCountdown) => {
                    if (prevCountdown <= 1) {
                        // Timer reached zero, stop recording
                        clearInterval(countdownIntervalRef.current);
                        countdownIntervalRef.current = null;
                        // Use setTimeout to ensure state is updated before stopping
                        setTimeout(() => stopRecording(), 0);
                        return 0;
                    }
                    return prevCountdown - 1;
                });
            }, 1000);

            // Start visualization with a slight delay
            setTimeout(() => {
                startVisualization();
            }, 200);
        } catch (error) {
            console.error('Error starting recording:', error);
            setStatus('Error: Could not access microphone');
        }
    }, [processRecording, startVisualization]);

    const stopRecording = useCallback(() => {
        // Clear countdown timer
        if (countdownIntervalRef.current) {
            clearInterval(countdownIntervalRef.current);
            countdownIntervalRef.current = null;
        }

        // STOP THE ANIMATION FIRST
        isRecordingRef.current = false;

        if (animationRef.current) {
            cancelAnimationFrame(animationRef.current);
            animationRef.current = null;
        }

        if (mediaRecorderRef.current && isRecording) {
            mediaRecorderRef.current.stop();
        }

        setIsRecording(false);

        if (streamRef.current) {
            streamRef.current.getTracks().forEach((track) => {
                track.stop();
            });
            streamRef.current = null;
        }

        if (audioContextRef.current && audioContextRef.current.state !== 'closed') {
            audioContextRef.current.close();
        }

        // Reset visualizer
        const waves = visualizerRef.current?.querySelectorAll('div');
        if (waves) {
            waves.forEach((wave) => (wave.style.height = '10px'));
        }

        setStatus('Processing...');
    }, [isRecording]);

    const toggleRecording = useCallback(async () => {
        if (isRecording) {
            stopRecording();
        } else {
            await startRecording();
        }
    }, [isRecording, startRecording, stopRecording]);

    // Effects
    useEffect(() => {
        createVisualizer();
    }, [createVisualizer]);

    // Monitor isRecording changes
    useEffect(() => {}, [isRecording]);

    return (
        <div>
            <div className="flex justify-center">
                <button
                    className="flex h-24 w-24 items-center justify-center rounded-full border-4 border-gray-400 bg-white shadow-lg transition-all duration-200 hover:scale-105 hover:bg-gray-50"
                    onClick={toggleRecording}
                >
                    <FaMicrophone className={'text-5xl ' + (isRecording ? 'animate-pulse text-red-500' : 'text-gray-700')} />
                </button>
            </div>

            {isRecording && <div className="mt-4 text-center text-3xl font-bold text-white">{countdown}s</div>}

            <div
                className={`mb-6 text-center text-lg font-medium ${
                    status.includes('Recording') ? 'text-red-400' : status.includes('successful') ? 'text-green-400' : 'text-white'
                }`}
            >
                {status}
            </div>

            <div className="mb-6 flex h-24 items-center justify-center overflow-hidden p-4">
                <div ref={visualizerRef} className="flex h-full items-center justify-center">
                    {/* Waves will be created here */}
                </div>
            </div>
        </div>
    );
};

export default Voice;
