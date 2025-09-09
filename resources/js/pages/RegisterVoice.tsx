import Layout from '@/layouts/auth-layout'
import { Head } from '@inertiajs/react'
import { useState, useRef, useCallback, useEffect } from 'react'

export default function RegisterVoice({}) {
    const [isRecording, setIsRecording] = useState(false)
    const [status, setStatus] = useState('Click "Start Recording" to begin')
    const apiEndpoint = '/api/voice/register';
    const [audioFormat, setAudioFormat] = useState('audio/webm')

    const mediaRecorderRef = useRef(null)
    const audioChunksRef = useRef([])
    const audioContextRef = useRef(null)
    const analyserRef = useRef(null)
    const microphoneRef = useRef(null)
    const animationRef = useRef(null)
    const visualizerRef = useRef(null)

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

        const bufferLength = analyserRef.current.frequencyBinCount
        const dataArray = new Uint8Array(bufferLength)
        analyserRef.current.getByteFrequencyData(dataArray)

        const waves = visualizerRef.current?.querySelectorAll('div')
        if (waves) {
            console.log(dataArray);
            console.log("height");
            waves.forEach((wave, index) => {

                const dataIndex = Math.floor(index * bufferLength / waves.length);
                console.log("dataIndex", dataArray[dataIndex]);


                const height = (dataArray[dataIndex] || 0) / 255 * 80 + 10
                console.log(height);
                wave.style.height = `${height}px`
            })
        }

        animationRef.current = requestAnimationFrame(visualize)
    }, [isRecording])

    const getFileExtension = useCallback(() => {
        switch (audioFormat) {
            case 'audio/webm': return 'webm'
            case 'audio/mp4': return 'm4a'
            case 'audio/wav': return 'wav'
            default: return 'audio'
        }
    }, [audioFormat])

    const sendToBackend = useCallback(async (audioBlob) => {
        try {
            if (!apiEndpoint) {
                console.log('No API endpoint configured')
                setStatus('Ready - No endpoint configured')
                return
            }

            console.log(`Uploading to ${apiEndpoint}...`)

            const formData = new FormData()
            const filename = `recording_${Date.now()}.${getFileExtension()}`
            formData.append('audio', audioBlob, filename)
            formData.append('timestamp', new Date().toISOString())

            const response = await fetch(apiEndpoint, {
                method: 'POST',
                body: formData,
            })

            if (response.ok) {
                const result = await response.json()
                console.log(`Upload successful: ${JSON.stringify(result)}`)
                setStatus('Upload successful!')
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`)
            }

        } catch (error) {
            console.log(`Upload failed: ${error.message}`)
            setStatus('Upload failed - Check console')
        }
    }, [apiEndpoint, getFileExtension])

    const processRecording = useCallback(async () => {
        try {
            console.log('Processing audio data...')

            const audioBlob = new Blob(audioChunksRef.current, {
                type: audioFormat
            })

            const fileSizeMB = (audioBlob.size / (1024 * 1024)).toFixed(2)
            console.log(`Audio recorded: ${fileSizeMB} MB`)

            await sendToBackend(audioBlob)

        } catch (error) {
            console.log(`Error processing recording: ${error.message}`)
            console.error('Error processing recording:', error)
        }
    }, [audioFormat, sendToBackend])

    const startRecording = useCallback(async () => {
        try {
            console.log('Requesting microphone access...')

            const stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 16000
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
                bitsPerSecond: 16000
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
    }, [audioFormat, processRecording])

    const stopRecording = useCallback(() => {
        if (mediaRecorderRef.current && isRecording) {
            mediaRecorderRef.current.stop()
            setIsRecording(false)

            // Stop all tracks
            if (mediaRecorderRef.current.stream) {
                mediaRecorderRef.current.stream.getTracks().forEach(track => track.stop())
            }

            // Close audio context
            if (audioContextRef.current && audioContextRef.current.state !== 'closed') {
                audioContextRef.current.close()
            }

            // Cancel animation
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current)
            }

            setStatus('Processing...')
            console.log('Recording stopped')
        }
    }, [isRecording])

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
        <Layout title={"Voice MFA"} description={"In order to continue, we need you to record a 10 second audio sample."}>
            <Head title="Register Voice"/>

            <div className="max-w-2xl mx-auto p-6">
                <div className="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-8 shadow-2xl">
                    <h1 className="text-3xl font-light text-center mb-8 text-white">
                        🎤 Voice Registration
                    </h1>

                    {/* Controls */}
                    <div className="flex gap-4 justify-center mb-8">
                        <button
                            onClick={startRecording}
                            disabled={isRecording}
                            className="px-8 py-4 bg-green-500 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-full transition-all duration-300 hover:-translate-y-1 disabled:transform-none min-w-[140px]"
                        >
                            Start Recording
                        </button>
                        <button
                            onClick={stopRecording}
                            disabled={!isRecording}
                            className="px-8 py-4 bg-red-500 hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-full transition-all duration-300 hover:-translate-y-1 disabled:transform-none min-w-[140px]"
                        >
                            Stop Recording
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
                    <div className="bg-white/10 rounded-xl p-4 mb-6 h-24 flex items-center justify-center overflow-hidden">
                        <div ref={visualizerRef} className="flex items-center justify-center h-full">
                            {/* Waves will be created here */}
                        </div>
                    </div>

                    {/* Configuration */}
                    <div className="bg-white/10 rounded-xl p-6 mb-6">
                        <h3 className="text-lg font-medium text-white mb-4">Configuration</h3>

                        <div className="grid gap-4">

                            <div>
                                <label className="block text-sm font-medium text-white mb-2">
                                    Audio Format:
                                </label>
                                <select
                                    value={audioFormat}
                                    onChange={(e) => setAudioFormat(e.target.value)}
                                    className="w-full px-3 py-2 bg-white/90 border border-gray-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="audio/webm">WebM</option>
                                    <option value="audio/mp4">MP4</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    )
}
