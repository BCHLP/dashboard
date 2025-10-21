import { useCallback, useEffect } from 'react';
export default function AudioVisualizer({ visualizerRef }: { visualizerRef }) {
    const createVisualizer = useCallback(() => {
        if (!visualizerRef.current) return;

        console.log('create visualizer');

        // Clear existing waves
        visualizerRef.current.innerHTML = '';

        // Create 20 wave bars
        for (let i = 0; i < 20; i++) {
            const wave = document.createElement('div');
            wave.className = 'w-1 bg-green-500 mx-0.5 rounded-sm transition-all duration-100';
            wave.style.height = '10px';
            visualizerRef.current.appendChild(wave);
        }
    }, []);

    // Initialize visualizer on mount
    useEffect(() => {
        createVisualizer();
    }, [createVisualizer]);

    return (
        <div>
            <div className="mb-6 flex h-24 items-center justify-center overflow-hidden rounded-xl bg-white/10 p-4">
                <div className="flex h-full items-center justify-center">{/* Waves will be created here */}</div>
            </div>
        </div>
    );
}
