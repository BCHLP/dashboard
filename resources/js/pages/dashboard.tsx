import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React, { useState } from 'react';
import Tank from '../components/tank';
import '@patternfly/react-core/dist/styles/base-no-reset.css';
import { useEchoModel } from "@laravel/echo-react";
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
};


export default function Dashboard ({sensors, tanks} : Props ) {

    const [tank1Wl, setTank1Wl] = useState<number>(0);
    const uniqueLineIds = [...new Set(tanks.map(tank => tank.treatment_line_id))];

    console.log("uniqueLineIds",uniqueLineIds);

    useEchoModel('App.Models.Node', '5', ['App\\Events\\DatapointCreatedEvent'], (e) => {
        flushSync(() => {
            console.log(e);
            setTank1Wl(e.y);
        });
    });


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <svg viewBox="0 0 1200 900" xmlns="http://www.w3.org/2000/svg">
                { /* A - Tanks*/}
                {uniqueLineIds.map((id: number) => (
                    tanks.find( tank => tank.treatment_line_id === id )
                        .map(tank:Node => (
                            <Tank id={tank.id} water_level={tank1Wl} x_offset={150} y_offset={210} label={tank.name}  />
                    ))
                ))}

                <Tank id={"tank2"} water_level={50} x_offset={450} y_offset={210} label={"Aeration"} />
                <Tank id={"tank3"} water_level={50} x_offset={750} y_offset={210} label={"Second Sedimentation"} />

                <rect x="20" y="175" width="85" height="10" fill="#718096" stroke="#4a5568" strokeWidth="1"/>
                <rect x="15" y="173" width="8" height="14" fill="#718096" stroke="#4a5568" strokeWidth="1"/>

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
