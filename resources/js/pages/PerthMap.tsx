// import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React, {useEffect, useState, useRef} from 'react';
import {Map, MapCameraChangedEvent} from '@vis.gl/react-google-maps';

import {GeoJsonLayer} from '@deck.gl/layers';
import {DeckGlOverlay} from '@/lib/deckgl-overlay';

// Initialize WebGL adapter for deck.gl/luma.gl (only when deck.gl is actually used)
import {luma} from '@luma.gl/core';
import {webgl2Adapter} from '@luma.gl/webgl';
luma.registerAdapters([webgl2Adapter]);

// const DATA_URL = '/api/map';
//    'https://raw.githubusercontent.com/visgl/deck.gl-data/master/website/bart.geo.json';

import type {Feature, GeoJSON} from 'geojson';
// import ControlPanel from './control-panel';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
];


function getDeckGlLayers(data: GeoJSON | null) {
    console.log('getDeckGlLayers called with:', data);
    if (!data) return [];

    return [
        new GeoJsonLayer({
            id: 'geojson-layer',
            data,
            stroked: false,
            filled: true,
            extruded: true,
            pointType: 'circle',
            lineWidthScale: 1,
            lineWidthMinPixels: 4,
            getFillColor: [160, 160, 180, 200],
            getLineColor: (f: Feature) => {
                const hex = f?.properties?.color;

                if (!hex) return [0, 0, 0];

                return hex.match(/[0-9a-f]{2}/g)!.map((x: string) => parseInt(x, 16));
            },
            getPointRadius: 2,
            getLineWidth: 1,
            getElevation: 30
        })
    ];
}

export default function PerthMap() {
    const [data, setData] = useState<GeoJSON | null>(null);
    const [zoom, setZoom] = useState<number>(15);
    const mapTimer = useRef<NodeJS.Timeout | null>(null);

    // Cleanup timer on component unmount
    useEffect(() => {
        return () => {
            if (mapTimer.current) {
                clearTimeout(mapTimer.current);
            }
        };
    }, []);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="PerthMap" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Map
                        //mapId={"map"}
                        style={{width: '100vw', height: '100vh'}}
                        onCameraChanged={ function(ev: MapCameraChangedEvent) {
                            console.log(ev);
                            // console.log('camera changed:', ev.detail.center, 'zoom:', ev.detail.zoom);
                            if (ev.detail.zoom > 15 && Math.round(ev.detail.zoom) != Math.round(zoom)) {
                                // Clear existing timer to debounce the API call
                                if (mapTimer.current) {
                                    clearTimeout(mapTimer.current);
                                }

                                // Set new timer with 500ms delay
                                mapTimer.current = setTimeout(function() {
                                    fetch("/api/map/"+ev.detail.bounds.north+"/"+ev.detail.bounds.east+"/"+
                                        ev.detail.bounds.south+"/"+ev.detail.bounds.west)
                                        .then(res => res.json())
                                        .then(data => {
                                            console.log('API response data:', data);
                                            setData(data as GeoJSON);
                                        })
                                        .catch(err => console.error('API fetch error:', err));
                                }, 500);
                            }
                            setZoom(ev.detail.zoom);
                        }}
                        defaultCenter={{lat: -31.743739, lng: 115.770545 }}
                        defaultZoom={15}
                        mapId={'4f6dde3310be51d7'}
                        gestureHandling={'greedy'}
                        disableDefaultUI={true}
                    >
                        <DeckGlOverlay layers={getDeckGlLayers(data)} />
                    </Map>
                </div>
                <div
                    className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                </div>
            </div>
        </AppLayout>
    );
}
