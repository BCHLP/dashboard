import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React, { useState } from 'react';
import Tank from '../components/tank';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEchoModel } from "@laravel/echo-react";
import { useEcho } from "@laravel/echo-react";

import { flushSync } from 'react-dom';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

type Node = {
    id:number;
    name: string;
    treatment_line_id: number;
}
type Props = {
    sensors: Node[],
    tanks: Node[],
    nodeMetrics: object[]
};



export default function Dashboard ({sensors, tanks, nodeMetrics} : Props ) {

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
                                        <rect x={195+((tankIndex)*x_spacing)} y={175+(id-1)*y_spacing} width="210" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                                    : null}

                                    <Tank id={tank.id}
                                          water_level={metrics[tank.id]['wl']}
                                          x_offset={150+((id-1)*x_spacing)}
                                          y_offset={210+((tankIndex)*y_spacing)}
                                          label={tank.name}  />
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
