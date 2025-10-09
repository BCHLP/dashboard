import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React, { useState, useEffect } from 'react';
import Tank from '../components/tank';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEcho } from "@laravel/echo-react";
import { Camera, Droplets, MapPin } from 'lucide-react';
import {Map, AdvancedMarker, MapCameraChangedEvent, useMap} from '@vis.gl/react-google-maps';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
];

type Node = {
    id:number;
    name: string;
}
type Props = {
    node: Node;
    datapoints: Datapoint[];
};

type Datapoint = {
    alias: string;
    x: number;
    y: number;
    node_id: number;
    metric_id: number;
}

const GaugeChart = ({ value, min, max, label, unit, color }) => {
    const percentage = ((value - min) / (max - min)) * 100;
    const angle = (percentage / 100) * 180 - 90;

    return (
        <div className="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-4 flex flex-col items-center justify-center h-full overflow-hidden">
            <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-1 flex-shrink-0">{label}</h3>
            <div className="flex-1 flex items-center justify-center w-full min-h-0">
                <div className="relative w-40 max-h-full">
                    <svg viewBox="0 0 200 120" className="w-full h-auto" preserveAspectRatio="xMidYMid meet">
                        <path
                            d="M 20 90 A 80 80 0 0 1 180 90"
                            fill="none"
                            stroke="#e5e7eb"
                            strokeWidth="20"
                            strokeLinecap="round"
                        />
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
            <div className="text-center flex-shrink-0">
                <div className="text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {value.toFixed(1)}
                    <span className="text-lg text-gray-600 dark:text-gray-400 ml-1">{unit}</span>
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
        <div className="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 h-full flex flex-col">
            <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                <Droplets className="w-5 h-5" />
                Water Tank
            </h3>
            <div className="flex-1 flex items-center justify-center gap-8">
                <div className="relative w-64 h-24 border-4 border-gray-700 dark:border-gray-400 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <div
                        className="absolute bottom-0 w-full bg-blue-500 transition-all duration-1000"
                        style={{ height: `${level}%` }}
                    >
                        <div className="absolute inset-0 opacity-30 bg-gradient-to-r from-blue-600 to-blue-400"></div>
                    </div>
                    <div className="absolute inset-0 flex items-center justify-center">
                    </div>
                </div>
                <div className="text-center">
                    <div className="text-sm text-gray-600 dark:text-gray-400">Current Level</div>
                    <div className="text-2xl font-semibold text-gray-800 dark:text-gray-100">{level}%</div>
                </div>
            </div>
        </div>
    );
};

const LocationMap = ({lat, lng}) => {
    return (
        <div className="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 h-full flex flex-col">
            <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                <MapPin className="w-5 h-5" />
                Location
            </h3>
            <div className="relative flex-1 bg-gray-200 dark:bg-gray-800 rounded-lg overflow-hidden">
                <div className="absolute inset-0 filter dark:brightness-75 dark:contrast-125 dark:saturate-50">
                    <Map
                        // mapId={"4f6dde3310be51d7"}
                        style={{width: '100vw', height: '100vh'}}
                        defaultCenter={{lat: lat, lng: lng }}
                        defaultZoom={15}
                        gestureHandling={'greedy'}
                        disableDefaultUI={true}
                        onCameraChanged={ (ev: MapCameraChangedEvent) =>
                            console.log('camera changed:', ev.detail.center, 'zoom:', ev.detail.zoom)
                        }
                    >
                        {/*<AdvancedMarker*/}
                        {/*    position={{lat: lat, lng: lng}}*/}
                        {/*>*/}
                        {/*    <div className="bg-blue-600 p-3 rounded-full shadow-lg border-4 border-white">*/}
                        {/*        <Droplets className="w-6 h-6 text-white" />*/}
                        {/*    </div>*/}
                        {/*</AdvancedMarker>*/}

                    </Map>
                </div>
            </div>
        </div>
    );
};

const SecurityCamera = () => {
    return (
        <div className="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 h-full flex flex-col">
            <h3 className="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                <Camera className="w-5 h-5" />
                Security Camera
            </h3>
            <div className="relative flex-1 bg-gray-800 rounded-lg overflow-hidden">
                <div className="absolute inset-0 bg-gradient-to-br from-gray-700 to-gray-900">
                    <svg className="w-full h-full opacity-20" viewBox="0 0 400 300">
                        <rect x="50" y="80" width="120" height="140" fill="#4b5563" />
                        <rect x="230" y="80" width="120" height="140" fill="#4b5563" />
                        <ellipse cx="110" cy="150" rx="30" ry="40" fill="#6b7280" />
                        <ellipse cx="290" cy="150" rx="30" ry="40" fill="#6b7280" />
                    </svg>
                </div>
                <div className="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded flex items-center gap-1">
                    <div className="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                    LIVE
                </div>
                <div className="absolute top-2 right-2 bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded font-mono">
                    {new Date().toLocaleTimeString()}
                </div>
                <div className="absolute inset-0 border-2 border-green-500 opacity-30"></div>
            </div>
        </div>
    );
};


const Dashboard = (props:Props) => {


    const [waterLevel, setWaterLevel] = useState(props.datapoints.find(d => {
        return d.alias == 'wl'
    })?.y ?? 80);
    const [temperature, setTemperature] = useState(props.datapoints.find(d => {
        return d.alias == 'temp'
    })?.y ?? 0);
    const [phLevel, setPhLevel] = useState(props.datapoints.find(d => {
        return d.alias == 'pH'
    })?.y ?? 0);
    const [lat, setLat] = useState(props.datapoints.find(d => {
        return d.alias == 'lat'
    })?.y ?? 0);
    const [lng, setLng] = useState(props.datapoints.find(d => {
        return d.alias == 'lng'
    })?.y ?? 0);

    useEcho(`NewDatapointEvent.${props.node.id}`, ['DatapointCreatedEvent'], (e:Datapoint) => {
        switch(e.alias) {
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
            <div className="h-screen bg-gradient-to-br from-blue-50 to-gray-100 dark:from-slate-950 dark:to-gray-950 p-8 overflow-hidden">
                <div className="h-full max-w-7xl mx-auto flex flex-col">

                    {/* Top Row - Large Gauges - 1/3 height */}
                    <div className="flex-1 grid grid-cols-2 gap-6 mb-4">
                        <GaugeChart
                            value={temperature}
                            min={0}
                            max={100}
                            label="Water Temperature"
                            unit="°C"
                            color="#f59e0b"
                        />
                        <GaugeChart
                            value={phLevel}
                            min={0}
                            max={14}
                            label="pH Level"
                            unit=""
                            color="#8b5cf6"
                        />
                    </div>

                    {/* Middle Row - Tank and Camera - 1/3 height */}
                    <div className="flex-1 grid grid-cols-2 gap-6 mb-4">
                        <WaterTank level={waterLevel} />
                        <SecurityCamera />
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






















function DashboardNotAsOld ({sensors, tanks, nodeMetrics} : Props ) {

    const [metrics, setMetrics] = useState(nodeMetrics);
    const uniqueLineIds = [...new Set(tanks.map(tank => tank.treatment_line_id))];

    useEcho(`NewDatapointEvent`, ['DatapointCreatedEvent'], (e) => {
        setMetrics(prev => ({
            ...prev,
            [e.node_id]: {
                ...prev[e.node_id],
                [e.alias]: e.y
            }
        }));
    });

    const y_spacing = 200;
    const x_spacing = 300;

    const pipeClicked = () => {
        alert('Rectangle clicked!');
    };

    // @ts-ignore
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
                <svg viewBox="0 0 1200 900" xmlns="http://www.w3.org/2000/svg">
                    {uniqueLineIds.map((id: number) => (
                        <React.Fragment key={"treatmentline"+id}>
                        <rect x={20} y={175+(id-1)*y_spacing} width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                            <rect x={15} y={173+(id-1)*y_spacing} width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                        {tanks.filter( (tank) => tank.treatment_line_id === id )
                            .map((tank, tankIndex) => (
                                <React.Fragment key={"tank"+tankIndex}>
                                    {tankIndex < tanks.filter( (tank) => tank.treatment_line_id === id).length-1 ?
                                        <rect onClick={pipeClicked} className={"pipe"} x={195+((tankIndex)*x_spacing)} y={175+(id-1)*y_spacing} width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                                    : null}

                                    <Tank id={tank.id}
                                          water_level={metrics[tank.id]['wl']}
                                          x_offset={150+((tankIndex)*x_spacing)}
                                          y_offset={210+((id-1)*y_spacing)}
                                          label={tank.name + ' ' + tank.treatment_line_id}  />
                                </React.Fragment>
                        ))}
                        <rect x="795" y={175+(id-1)*y_spacing} width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                        <rect x="877" y={173+(id-1)*y_spacing} width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                    </React.Fragment>
                ))}




                {/*<Tank id={"tank4"} water_level={100} x_offset={150} y_offset={410} label={"Primary Sedimentation"}  />*/}
                {/*<Tank id={"tank5"} water_level={50} x_offset={450} y_offset={410} label={"Aeration"} />*/}
                {/*<Tank id={"tank6"} water_level={50} x_offset={750} y_offset={410} label={"Second Sedimentation"} />*/}

                {/*<Tank id={"tank7"} water_level={100} x_offset={150} y_offset={610} label={"Primary Sedimentation"}  />*/}
                {/*<Tank id={"tank8"} water_level={50} x_offset={450} y_offset={610} label={"Aeration"} />*/}
                {/*<Tank id={"tank9"} water_level={50} x_offset={750} y_offset={610} label={"Second Sedimentation"} />*/}
                {/* A - Inlet Pipe */}
                {/*<rect x="20" y="175" width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="15" y="173" width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*/!* B - Inlet Pipe *!/*/}
                {/*<rect x="20" y="375" width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="15" y="373" width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}

                {/*{ /* A - Pipe between tanks *!/*/}
                {/*<rect x="195" y="175" width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="495" y="175" width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}

                {/*{ /* B - Pipe between tanks *!/*/}
                {/*<rect x="195" y="375" width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="495" y="375" width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}

                {/*{ /* A - Outlet Pipe *!/*/}
                {/*<rect x="795" y="175" width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="877" y="173" width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}

                {/*{ /* B - Outlet Pipe *!/*/}
                {/*<rect x="795" y="375" width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}
                {/*<rect x="877" y="373" width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>*/}

                {/*{ /* Pipe Joints *!/*/}
                {/*<circle cx="195" cy="180" r="6" fill="#4a5568" stroke="#2d3748" strokeWidth="1"/>*/}
                {/*<circle cx="405" cy="180" r="6" fill="#4a5568" stroke="#2d3748" strokeWidth="1"/>*/}
                {/*<circle cx="495" cy="180" r="6" fill="#4a5568" stroke="#2d3748" strokeWidth="1"/>*/}
                {/*<circle cx="705" cy="180" r="6" fill="#4a5568" stroke="#2d3748" strokeWidth="1"/>*/}
                {/*<circle cx="795" cy="180" r="6" fill="#4a5568" stroke="#2d3748" strokeWidth="1"/>*/}

                {/*{ /* Flow Labels *!/*/}
                {/*<text x="60" y="200" fontFamily="Arial, sans-serif" fontSize="9" textAnchor="middle" fill="#4a5568">LINE A</text>*/}
                {/*<text x="60" y="400" fontFamily="Arial, sans-serif" fontSize="9" textAnchor="middle" fill="#4a5568">LINE B</text>*/}

                {/*{ /* Process Status Indicator *!/*/}
                {/*<rect x="950" y="60" width="200" height="120" fill="#f7fafc" stroke="#4a5568" strokeWidth="2" rx="5"/>*/}
                {/*<text x="1050" y="80" fontFamily="Arial, sans-serif" fontSize="12" font-weight="bold" textAnchor="middle" fill="#2d3748">SYSTEM STATUS</text>*/}

                {/*{ /* Status Items *!/*/}
                {/*<circle cx="970" cy="100" r="4" fill="#22c55e"/>*/}
                {/*<text x="985" y="105" fontFamily="Arial, sans-serif" fontSize="9" fill="#2d3748">All Tanks Online</text>*/}

                {/*<circle cx="970" cy="120" r="4" fill="#22c55e"/>*/}
                {/*<text x="985" y="125" fontFamily="Arial, sans-serif" fontSize="9" fill="#2d3748">Flow Rate: Normal</text>*/}

                {/*<circle cx="970" cy="140" r="4" fill="#fbbf24"/>*/}
                {/*<text x="985" y="145" fontFamily="Arial, sans-serif" fontSize="9" fill="#2d3748">Tank 3: Low Level</text>*/}

                {/*<circle cx="970" cy="160" r="4" fill="#22c55e"/>*/}
                {/*<text x="985" y="165" fontFamily="Arial, sans-serif" fontSize="9" fill="#2d3748">No Alarms Active</text>*/}
            </svg>
        </AppLayout>
    );
};
