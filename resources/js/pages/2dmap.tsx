// import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import {Map, AdvancedMarker, MapCameraChangedEvent, useMap} from '@vis.gl/react-google-maps';
import { useEffect } from 'react';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

type Sensor = {name:string, coordinates: google.maps.LatLngLiteral};
type Pipe = {name:string, path:google.maps.LatLngLiteral[]};
type Props = {sensors:{data:Sensor[]}, pipes:{data:Pipe[]}};

const SensorMarkers = (props: {sensors: Sensor[]}) => {
    return (
        <>
            {props.sensors.map( (sensor: Sensor) => (
                <AdvancedMarker
                    key={sensor.name}
                    position={sensor.coordinates}>
                    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                        <rect width="20" height="20" fill="red" stroke="#333" strokeWidth="1"/>
                    </svg>
                </AdvancedMarker>
            ))}
        </>
    );
};

const PipeLines = (props: {pipes: Pipe[]}) => {
    const map = useMap(); // This gives you access to the Google Maps instance

    useEffect(() => {
        if (!map || props.pipes.length < 1) return;

        // Clear any existing polylines
        const polylines: google.maps.Polyline[] = [];

        props.pipes.forEach(function(pipe:Pipe) {
            console.log("pipe",pipe);
            const polyline1 = new google.maps.Polyline({
                path: [pipe.path[0], pipe.path[1]],
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 1.0,
                strokeWeight: 3
            });
            polyline1.setMap(map);
            polylines.push(polyline1);
        });

        // Cleanup function - remove polylines when component unmounts
        return () => {
            polylines.forEach(polyline => {
                polyline.setMap(null);
            });
        };
    }, [map, props.pipes]);

    // This component doesn't render anything visible itself
    return null;
};

export default function Dashboard(props:Props) {
    console.log('props',props);

    // const locations = props.sensors.data.map(function (sensor) {
    //     return {"key":sensor.label, "location":{
    //             lat: sensor.lat,
    //             lng: sensor.lng}
    //     };
    // });
    //
    // console.log("locations", locations)



    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Map
                        mapId={"map"}
                        style={{width: '100vw', height: '100vh'}}
                        defaultCenter={{lat: -31.743739, lng: 115.770545 }}
                        defaultZoom={15}
                        gestureHandling={'greedy'}
                        disableDefaultUI={true}
                        onCameraChanged={ (ev: MapCameraChangedEvent) =>
                            console.log('camera changed:', ev.detail.center, 'zoom:', ev.detail.zoom)
                        }
                    >
                        <SensorMarkers sensors={props.sensors.data} />
                        <PipeLines pipes={props.pipes.data} />

                    </Map>
                </div>
                <div
                    className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                </div>
            </div>
        </AppLayout>
    );
}
