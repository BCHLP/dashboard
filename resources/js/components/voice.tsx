import React, { useState, useEffect, useRef, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import Layout from '@/layouts/auth-layout';
import { FaMicrophone } from 'react-icons/fa';
import axios from 'axios';
import { MfaDecision } from '@/types';
import { useMfa } from '@/MfaProvider';

type Props = {
    apiEndpoint: string;
}

const Voice = (props:Props) => {
    const [isRecording, setIsRecording] = useState(false)
    const [status, setStatus] = useState('Click the microphone to begin')

    const mediaRecorderRef = useRef(null)
    const audioChunksRef = useRef([])
    const audioContextRef = useRef(null)
    const analyserRef = useRef(null)
    const microphoneRef = useRef(null)
    const animationRef = useRef(null)
    const visualizerRef = useRef(null)
    const streamRef = useRef(null)
    const isRecordingRef = useRef(false) // This tracks recording state for animation

    const audioFormat = 'audio/webm';
    const { requireMfa } = useMfa();

    const createVisualizer = useCallback(() => {
        if (!visualizerRef.current) return

        console.log("Creating visualizer");
        visualizerRef.current.innerHTML = ''

        for (let i = 0; i < 20; i++) {
            const wave = document.createElement('div')
            wave.className = 'w-1 bg-green-500 mx-0.5 rounded-sm transition-all duration-75'
            wave.style.height = '10px'
            wave.style.minHeight = '10px'
            visualizerRef.current.appendChild(wave)
        }
    }, [])

    const animate = useCallback(() => {
        console.log('ANIMATE CALLED - analyser exists:', !!analyserRef.current)
        console.log('ANIMATE CALLED - isRecordingRef.current:', isRecordingRef.current)

        if (!analyserRef.current) {
            console.log('No analyser, stopping animation')
            return
        }

        const bufferLength = analyserRef.current.frequencyBinCount
        const dataArray = new Uint8Array(bufferLength)
        analyserRef.current.getByteFrequencyData(dataArray)

        const maxValue = Math.max(...dataArray)
        const avgValue = dataArray.reduce((a, b) => a + b, 0) / dataArray.length
        console.log('AUDIO DATA - Max:', maxValue, 'Avg:', avgValue.toFixed(1))

        const waves = visualizerRef.current?.querySelectorAll('div')
        if (waves && waves.length > 0) {
            console.log('Updating', waves.length, 'wave elements')
            waves.forEach((wave, index) => {
                const dataIndex = Math.floor((index / waves.length) * bufferLength)
                const value = dataArray[dataIndex] || 0
                const height = Math.max(10, (value / 255) * 70 + 10)
                wave.style.height = `${height}px`
            })
        } else {
            console.log('No waves found to update')
        }

        // USE THE REF INSTEAD OF STATE
        if (isRecordingRef.current) {
            console.log('Continuing animation - isRecordingRef is true')
            animationRef.current = requestAnimationFrame(animate)
        } else {
            console.log('Stopping animation - isRecordingRef is false')
        }
    }, [])

    const startVisualization = useCallback(() => {
        console.log('START VISUALIZATION CALLED')
        console.log('- isRecording state:', isRecording)
        console.log('- isRecordingRef:', isRecordingRef.current)
        console.log('- analyser exists:', !!analyserRef.current)
        console.log('- audioContext exists:', !!audioContextRef.current)
        console.log('- visualizer div exists:', !!visualizerRef.current)

        if (analyserRef.current && audioContextRef.current) {
            console.log('Starting animation loop')
            animate()
        } else {
            console.log('Cannot start visualization - missing requirements')
        }
    }, [animate])

    const sendToBackend = useCallback(async (audioBlob) => {
        try {
            const formData = new FormData()
            const filename = `recording_${Date.now()}.webm`
            formData.append('audio', audioBlob, filename)

            const response = await axios.post(props.apiEndpoint, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })

            if (response.data.voice) {
                setStatus('Voice recognition failed');
                return;
            }

            if (response.data.totp) {
                console.log("activate totp");
                requireMfa({
                    action: 'complete-login',
                    message: 'Please verify your identity to complete login',
                    endpoint: 'auth.totp',
                    onSuccess: (response:MfaDecision) => {
                        // Redirect to dashboard or intended page
                        console.log("redirect to home");
                        // window.location.href = route('home');

                        if (response.success) {
                            router.visit('/');
                        }
                    },
                    onCancel: () => {
                        // Maybe redirect back to login or show a message
                        console.log('MFA cancelled during login');
                    }
                });

                return;
            }

            return router.visit('/');

            console.log(`Upload successful: ${JSON.stringify(response.data)}`)
            setStatus('Please wait');

        } catch (error) {
            console.log(`Upload failed: ${error.message}`)
            setStatus('Upload failed')
        }
    }, [])

    const processRecording = useCallback(async () => {
        try {
            console.log('Processing audio data...')
            const audioBlob = new Blob(audioChunksRef.current, { type: 'audio/webm' })
            const fileSizeMB = (audioBlob.size / (1024 * 1024)).toFixed(2)
            console.log(`Audio recorded: ${fileSizeMB} MB`)
            await sendToBackend(audioBlob)
        } catch (error) {
            console.log(`Error processing recording: ${error.message}`)
        }
    }, [sendToBackend])

    const startRecording = useCallback(async () => {
        try {
            console.log('=== STARTING RECORDING ===')

            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,
                    autoGainControl: false,
                    sampleRate: 44100
                }
            })

            console.log('Got microphone stream')
            streamRef.current = stream

            // Audio context setup
            audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)()
            console.log('AudioContext created, state:', audioContextRef.current.state)

            if (audioContextRef.current.state === 'suspended') {
                await audioContextRef.current.resume()
                console.log('AudioContext resumed, new state:', audioContextRef.current.state)
            }

            // Analyser setup
            analyserRef.current = audioContextRef.current.createAnalyser()
            analyserRef.current.fftSize = 256
            analyserRef.current.smoothingTimeConstant = 0.8
            console.log('Analyser created, bufferLength:', analyserRef.current.frequencyBinCount)

            microphoneRef.current = audioContextRef.current.createMediaStreamSource(stream)
            microphoneRef.current.connect(analyserRef.current)
            console.log('Microphone connected to analyser')

            // MediaRecorder setup
            mediaRecorderRef.current = new MediaRecorder(stream, {
                mimeType: audioFormat,
                bitsPerSecond: 128000
            })

            audioChunksRef.current = []

            mediaRecorderRef.current.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunksRef.current.push(event.data)
                }
            }

            mediaRecorderRef.current.onstop = processRecording

            mediaRecorderRef.current.start(1000)

            console.log('Setting both isRecording state and ref to true')
            setIsRecording(true)
            isRecordingRef.current = true  // THIS IS THE KEY CHANGE
            setStatus('Recording... Speak now!')

            console.log('About to start visualization')
            // Start visualization with a slight delay
            setTimeout(() => {
                console.log('Timeout fired - starting visualization')
                console.log('isRecordingRef.current at timeout:', isRecordingRef.current)
                startVisualization()
            }, 200)

        } catch (error) {
            console.error('Error starting recording:', error)
            setStatus('Error: Could not access microphone')
        }
    }, [processRecording, startVisualization])

    const stopRecording = useCallback(() => {
        console.log('=== STOPPING RECORDING ===')

        // STOP THE ANIMATION FIRST
        isRecordingRef.current = false
        console.log('Set isRecordingRef.current to false')

        if (animationRef.current) {
            cancelAnimationFrame(animationRef.current)
            animationRef.current = null
            console.log('Animation cancelled')
        }

        if (mediaRecorderRef.current && isRecording) {
            mediaRecorderRef.current.stop()
        }

        setIsRecording(false)

        if (streamRef.current) {
            streamRef.current.getTracks().forEach(track => {
                track.stop()
                console.log('Stopped track:', track.kind)
            })
            streamRef.current = null
        }

        if (audioContextRef.current && audioContextRef.current.state !== 'closed') {
            audioContextRef.current.close()
            console.log('AudioContext closed')
        }

        // Reset visualizer
        const waves = visualizerRef.current?.querySelectorAll('div')
        if (waves) {
            waves.forEach(wave => wave.style.height = '10px')
        }

        setStatus('Processing...')
        console.log('Recording stopped')
    }, [isRecording])

    const toggleRecording = useCallback(async () => {
        console.log('TOGGLE RECORDING - current state:', isRecording)
        if (isRecording) {
            stopRecording()
        } else {
            await startRecording()
        }
    }, [isRecording, startRecording, stopRecording])

    // Effects
    useEffect(() => {
        createVisualizer()
    }, [createVisualizer])

    // Monitor isRecording changes
    useEffect(() => {
        console.log('useEffect: isRecording changed to:', isRecording)
    }, [isRecording])

    return (
        <div>
            <div className="flex justify-center">
                <button
                    className="w-24 h-24 rounded-full border-4 border-gray-400 bg-white hover:bg-gray-50 flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-105"
                    onClick={toggleRecording}
                >
                    <FaMicrophone className={"text-5xl " + (isRecording ? "text-red-500 animate-pulse" : "text-gray-700")} />
                </button>
            </div>

            <div className={`text-center mb-6 text-lg font-medium ${
                status.includes('Recording') ? 'text-red-400' :
                    status.includes('successful') ? 'text-green-400' : 'text-white'
            }`}>
                {status}
            </div>

            <div className="p-4 mb-6 h-24 flex items-center justify-center overflow-hidden">
                <div ref={visualizerRef} className="flex items-center justify-center h-full">
                    {/* Waves will be created here */}
                </div>
            </div>
        </div>
    )
};

export default Voice;
