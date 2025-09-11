import Layout from '@/layouts/auth-layout'
import { Head, router } from '@inertiajs/react'
import { FaMicrophone } from 'react-icons/fa';
import { useCallback, useEffect, useRef, useState } from 'react';


export default function VerifyVoice() {

    const [isRecording, setIsRecording] = useState(false)
    const [status, setStatus] = useState('Click the microphone to begin')

    const mediaRecorderRef = useRef(null)
    const audioChunksRef = useRef([])
    const audioContextRef = useRef(null)
    const analyserRef = useRef(null)
    const microphoneRef = useRef(null)
    const animationRef = useRef(null)
    const visualizerRef = useRef(null)

    const audioFormat = 'audio/webm';

    const createVisualizer = useCallback(() => {
        if (!visualizerRef.current) return

        console.log("create visualizer");

        // Clear existing waves
        visualizerRef.current.innerHTML = ''

        // Create 20 wave bars
        for (let i = 0; i < 20; i++) {
            const wave = document.createElement('div')
            wave.className = 'w-1 bg-green-500 mx-0.5 rounded-sm transition-all duration-100'
            wave.style.height = '10px'
            visualizerRef.current.appendChild(wave)
        }
    }, [])

    const visualize = useCallback(() => {
        if (!isRecording || !analyserRef.current) return

        if (!audioContextRef.current || audioContextRef.current.state === 'closed') {
            console.log('AudioContext is closed, stopping visualization')
            return
        }

        const bufferLength = analyserRef.current.frequencyBinCount
        const dataArray = new Uint8Array(bufferLength)

        // Try time domain data first (raw audio waveform)
        analyserRef.current.getByteTimeDomainData(dataArray)

        // Check if we're getting any audio data at all
        const hasAudioData = dataArray.some(value => value !== 128) // 128 is silence in time domain

        if (!hasAudioData) {
            // If no time domain data, try frequency data
            analyserRef.current.getByteFrequencyData(dataArray)
        }

        // Debug: Log some values to see what we're getting
        const maxValue = Math.max(...dataArray)
        const minValue = Math.min(...dataArray)
        const avgValue = dataArray.reduce((a, b) => a + b, 0) / dataArray.length

        if (Math.random() < 0.02) { // Log occasionally to avoid spam
            console.log('Audio data - Max:', maxValue, 'Min:', minValue, 'Avg:', avgValue.toFixed(1), 'HasData:', hasAudioData)
            console.log('AudioContext state:', audioContextRef.current?.state)
            console.log('Sample values:', dataArray.slice(0, 10))
        }

        const waves = visualizerRef.current?.querySelectorAll('div')
        if (waves) {
            console.log(dataArray);
            console.log("height");
            waves.forEach((wave, index) => {

                const dataIndex = Math.floor(index * bufferLength / waves.length)
                console.log("dataIndex", dataArray[dataIndex]);

                let value = dataArray[dataIndex] || 0
                // For time domain data, convert to a useful range
                if (hasAudioData) {
                    value = Math.abs(value - 128) * 2 // Convert from center-around-128 to 0-255
                }

                const height = (value / 255) * 80 + 10
                wave.style.height = `${height}px`
            })
        }

        animationRef.current = requestAnimationFrame(visualize)
    }, [isRecording])

    const sendToBackend = useCallback(async (audioBlob) => {
        try {

            return;
            
            const formData = new FormData()
            const filename = `recording_${Date.now()}.webm`
            formData.append('audio', audioBlob, filename)

            router.post('/api/voice/compare', formData, {
                onSuccess: (page) => {

                    console.log(`Upload successful: ${JSON.stringify(page.props)}`)
                    setStatus('Upload successful!')
                    router.visit('/dashboard')
                },
                onError: (errors) => {
                    console.error('Upload failed:', errors)
                    setStatus('Upload failed!')
                }
            })

        } catch (error) {
            console.log(`Upload failed: ${error.message}`)
            setStatus('Upload failed - Check console')
        }
    })

    const processRecording = useCallback(async () => {
        try {
            console.log('Processing audio data...')

            const audioBlob = new Blob(audioChunksRef.current, {
                type: 'audio/webm'
            })

            const fileSizeMB = (audioBlob.size / (1024 * 1024)).toFixed(2)
            console.log(`Audio recorded: ${fileSizeMB} MB`)

            await sendToBackend(audioBlob)

        } catch (error) {
            console.log(`Error processing recording: ${error.message}`)
            console.error('Error processing recording:', error)
        }
    }, [sendToBackend])



    const startRecording = useCallback(async () => {
        try {
            console.log('Requesting microphone access...')

            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100
                }
            })

            console.log('Microphone access granted')

            // Set up audio context for visualization
            audioContextRef.current = new (window.AudioContext || window.webkitAudioContext)()

            // if (audioContextRef.current.state === 'suspended') {
            //     await audioContextRef.current.resume()
            //     console.log('AudioContext resumed')
            // }

            analyserRef.current = audioContextRef.current.createAnalyser()
            microphoneRef.current = audioContextRef.current.createMediaStreamSource(stream)
            microphoneRef.current.connect(analyserRef.current)
            analyserRef.current.fftSize = 64

            // Set up MediaRecorder
            if (!MediaRecorder.isTypeSupported(audioFormat)) {
                throw new Error(`MIME type ${audioFormat} is not supported`)
            }

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

            mediaRecorderRef.current.onstop = () => {
                processRecording()
            }

            mediaRecorderRef.current.start(1000)
            setIsRecording(true)
            setStatus('Recording...')
            console.log('Recording started')

        } catch (error) {
            console.log(`Error starting recording: ${error.message}`)
            console.error('Error starting recording:', error)
        }
    }, [processRecording])

    const stopRecording = useCallback(() => {
        if (mediaRecorderRef.current && isRecording) {
            mediaRecorderRef.current.stop()
            setIsRecording(false)

            // Cancel animation
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current)
            }

            // Stop all tracks
            if (mediaRecorderRef.current.stream) {
                mediaRecorderRef.current.stream.getTracks().forEach(track => track.stop())
            }

            // Close audio context
            if (audioContextRef.current && audioContextRef.current.state !== 'closed') {
                audioContextRef.current.close()
            }



            setStatus('Processing...')
            console.log('Recording stopped')
        }
    }, [isRecording])

    const toggleRecording = useCallback(async () => {
        if (isRecording) {
            stopRecording();
            return;
        }

        startRecording();
    }, [isRecording, startRecording, stopRecording]);

    // Initialize visualizer on mount
    useEffect(() => {
        createVisualizer()
    }, [createVisualizer])

    // Start visualization when recording starts
    useEffect(() => {
        if (isRecording) {
            visualize()
        }
        return () => {
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current)
            }
        }
    }, [isRecording, visualize])

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            if (mediaRecorderRef.current && isRecording) {
                mediaRecorderRef.current.stop()
            }
            if (audioContextRef.current && audioContextRef.current.state !== 'closed') {
                audioContextRef.current.close()
            }
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current)
            }
        }
    }, [isRecording])


    return (
        <Layout title={"Voice Authentication Required"} description={"Please click the microphone button and speak the following sentence."}>
            <Head title="verifyVoice"/>
            <h1 className={"italic text-4xl text-center mb-8"}>"Great shot kid, that was one-in-a-million"</h1>

            <div className="flex justify-center">
                <button
                    className="w-24 h-24 rounded-full border-4 border-gray-400 bg-white hover:bg-gray-50 flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-105"
                    onClick={toggleRecording}
                >
                    <FaMicrophone className={"text-5xl " + (isRecording ? "text-red-800" : "text-gray-700")} />
                </button>

            </div>

            {/* Status */}
            <div className={`text-center mb-6 text-lg font-medium ${
                status.includes('Recording') ? 'text-red-400' :
                    status.includes('successful') ? 'text-green-400' : 'text-white'
            }`}>
                {status}
            </div>

            {/* Visualizer */}
            <div className="p-4 mb-6 h-24 flex items-center justify-center overflow-hidden">
                <div ref={visualizerRef} className="flex items-center justify-center h-full">
                    {/* Waves will be created here */}
                </div>
            </div>

        </Layout>
    )
}
