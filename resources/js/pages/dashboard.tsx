import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import Tank from '../components/tank';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEcho } from '@laravel/echo-react';
import { Camera, Droplets, MapPin } from 'lucide-react';
import { Map, AdvancedMarker, MapCameraChangedEvent, useMap } from '@vis.gl/react-google-maps';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
];

type Node = {
    id: number;
    name: string;
};
type Props = {
    node: Node;
    datapoints: Datapoint[];
    photo: Photo;
};

type Datapoint = {
    alias: string;
    x: number;
    y: number;
    node_id: number;
    metric_id: number;
};

type Photo = {
    id: number;
    path: string;
    face_detected: boolean;
};

const GaugeChart = ({ value, min, max, label, unit, color }) => {
    const percentage = ((value - min) / (max - min)) * 100;
    const angle = (percentage / 100) * 180 - 90;

    return (
        <div className="flex h-full flex-col items-center justify-center overflow-hidden rounded-lg bg-white p-4 shadow-lg dark:bg-gray-900">
            <h3 className="mb-1 flex-shrink-0 text-lg font-semibold text-gray-700 dark:text-gray-200">{label}</h3>
            <div className="flex min-h-0 w-full flex-1 items-center justify-center">
                <div className="relative max-h-full w-40">
                    <svg viewBox="0 0 200 120" className="h-auto w-full" preserveAspectRatio="xMidYMid meet">
                        <path d="M 20 90 A 80 80 0 0 1 180 90" fill="none" stroke="#e5e7eb" strokeWidth="20" strokeLinecap="round" />
                        <path
                            d="M 20 90 A 80 80 0 0 1 180 90"
                            fill="none"
                            stroke={color}
                            strokeWidth="20"
                            strokeLinecap="round"
                            strokeDasharray={`${percentage * 2.51} 251`}
                        />
                        <line
                            x1="100"
                            y1="90"
                            x2="100"
                            y2="30"
                            stroke="#374151"
                            strokeWidth="3"
                            strokeLinecap="round"
                            transform={`rotate(${angle} 100 90)`}
                        />
                        <circle cx="100" cy="90" r="8" fill="#374151" />
                    </svg>
                </div>
            </div>
            <div className="flex-shrink-0 text-center">
                <div className="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {value.toFixed(1)}
                    <span className="ml-1 text-lg text-gray-600 dark:text-gray-400">{unit}</span>
                </div>
                <div className="text-xs text-gray-500 dark:text-gray-400">
                    Range: {min} - {max} {unit}
                </div>
            </div>
        </div>
    );
};

const WaterTank = ({ level }) => {
    return (
        <div className="flex h-full flex-col rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                <Droplets className="h-5 w-5" />
                Water Tank
            </h3>
            <div className="flex flex-1 items-center justify-center gap-8">
                <div className="relative h-24 w-64 overflow-hidden rounded-lg border-4 border-gray-700 bg-gray-100 dark:border-gray-400 dark:bg-gray-800">
                    <div className="absolute bottom-0 w-full bg-blue-500 transition-all duration-1000" style={{ height: `${level}%` }}>
                        <div className="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-400 opacity-30"></div>
                    </div>
                    <div className="absolute inset-0 flex items-center justify-center"></div>
                </div>
                <div className="text-center">
                    <div className="text-sm text-gray-600 dark:text-gray-400">Current Level</div>
                    <div className="text-2xl font-semibold text-gray-800 dark:text-gray-100">{level}%</div>
                </div>
            </div>
        </div>
    );
};

const LocationMap = ({ lat, lng }) => {
    return (
        <div className="flex h-full flex-col rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                <MapPin className="h-5 w-5" />
                Location
            </h3>
            <div className="relative flex-1 overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-800">
                <div className="absolute inset-0 filter dark:brightness-75 dark:contrast-125 dark:saturate-50">
                    <Map
                        mapId={'4f6dde3310be51d7'}
                        style={{ width: '100vw', height: '100vh' }}
                        defaultCenter={{ lat: lat, lng: lng }}
                        defaultZoom={15}
                        gestureHandling={'greedy'}
                        disableDefaultUI={true}
                        onCameraChanged={(ev: MapCameraChangedEvent) => console.log('camera changed:', ev.detail.center, 'zoom:', ev.detail.zoom)}
                    >
                        <AdvancedMarker position={{ lat: lat, lng: lng }}>
                            <div className="rounded-full border-4 border-white bg-blue-600 p-3 shadow-lg">
                                <Droplets className="h-6 w-6 text-white" />
                            </div>
                        </AdvancedMarker>
                    </Map>
                </div>
            </div>
        </div>
    );
};

const SecurityCamera = (photo: Photo) => {
    console.log("photo", photo.photo);
    return (
        <div className="flex h-full flex-col rounded-lg bg-white p-6 shadow-lg dark:bg-gray-900">
            <h3 className="mb-4 flex items-center gap-2 text-lg font-semibold text-gray-700 dark:text-gray-200">
                <Camera className="h-5 w-5" />
                Security Camera
            </h3>
            <div className="relative flex-1 overflow-hidden rounded-lg bg-gray-800">
                <div className="absolute inset-0 bg-gradient-to-br from-gray-700 to-gray-900">
                    {photo ? (
                        <img src={photo.photo.path}  alt={"Capture"}/>
                    ) : (
                        <svg className="h-full w-full opacity-20" viewBox="0 0 400 300">
                            <rect x="50" y="80" width="120" height="140" fill="#4b5563" />
                            <rect x="230" y="80" width="120" height="140" fill="#4b5563" />
                            <ellipse cx="110" cy="150" rx="30" ry="40" fill="#6b7280" />
                            <ellipse cx="290" cy="150" rx="30" ry="40" fill="#6b7280" />
                        </svg>
                    )}
                </div>
                <div className="absolute top-2 left-2 flex items-center gap-1 rounded bg-red-600 px-2 py-1 text-xs text-white">
                    <div className="h-2 w-2 animate-pulse rounded-full bg-white"></div>
                    LIVE
                </div>
                <div className="bg-opacity-60 absolute top-2 right-2 rounded bg-black px-2 py-1 font-mono text-xs text-white">
                    {new Date().toLocaleTimeString()}
                </div>
                <div className="absolute inset-0 border-2 border-green-500 opacity-30"></div>
            </div>
        </div>
    );
};

const Dashboard = (props: Props) => {
    console.log(props);

    const [waterLevel, setWaterLevel] = useState(
        props.datapoints.find((d) => {
            return d.alias == 'wl';
        })?.y ?? 80,
    );
    const [temperature, setTemperature] = useState(
        props.datapoints.find((d) => {
            return d.alias == 'temp';
        })?.y ?? 0,
    );
    const [phLevel, setPhLevel] = useState(
        props.datapoints.find((d) => {
            return d.alias == 'pH';
        })?.y ?? 0,
    );
    const [lat, setLat] = useState(
        props.datapoints.find((d) => {
            return d.alias == 'lat';
        })?.y ?? 0,
    );
    const [lng, setLng] = useState(
        props.datapoints.find((d) => {
            return d.alias == 'lng';
        })?.y ?? 0,
    );

    useEcho(`NewDatapointEvent.${props.node.id}`, ['DatapointCreatedEvent'], (e: Datapoint) => {
        switch (e.alias) {
            case 'pH':
                setPhLevel(e.y);
                break;
            case 'temp':
                setTemperature(e.y);
                break;
            case 'wl':
                setWaterLevel(e.y);
                break;
            case 'lat':
                setLat(e.y);
                break;
            case 'lng':
                setLng(e.y);
                break;
        }
    });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="h-screen overflow-hidden bg-gradient-to-br from-blue-50 to-gray-100 p-8 dark:from-slate-950 dark:to-gray-950">
                <div className="mx-auto flex h-full max-w-7xl flex-col">
                    {/* Top Row - Large Gauges - 1/3 height */}
                    <div className="mb-4 grid flex-1 grid-cols-2 gap-6">
                        <GaugeChart value={temperature} min={0} max={100} label="Water Temperature" unit="°C" color="#f59e0b" />
                        <GaugeChart value={phLevel} min={0} max={14} label="pH Level" unit="" color="#8b5cf6" />
                    </div>

                    {/* Middle Row - Tank and Camera - 1/3 height */}
                    <div className="mb-4 grid flex-1 grid-cols-2 gap-6">
                        <WaterTank level={waterLevel} />
                        <SecurityCamera photo={props.photo} />
                    </div>

                    {/* Bottom Row - Full Width Map - 1/3 height */}
                    <div className="flex-1">
                        <LocationMap lat={lat} lng={lng} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Dashboard;
