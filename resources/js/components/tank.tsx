import React from 'react';

type Props = {
    id:string|number;
    x_offset:number;
    y_offset:number;
    water_level:number;
    label:string;

}

const Tank = ({ id, x_offset = 150, y_offset = 210, water_level, label }: Props) => {

    // 80 = 100% water level
    const water_level_height = 0.8*water_level;
    const water_level_y = y_offset - water_level_height;


    return (<g id={id}>
        {/* Tank Shadow */}
        <ellipse cx={x_offset+5} cy={y_offset+18} rx="45" ry="8" fill="#000000" opacity="0.2" />
        {/* // Tank Base */}
        <ellipse cx={x_offset} cy={y_offset} rx="40" ry="8" fill="#2d3748" stroke="#4a5568" strokeWidth="2" />
        {/* //Tank Walls */}
        <rect x={x_offset-40} y={y_offset-80} width="80" height="80" fill="none" stroke="#4a5568" strokeWidth="2" />
        {/* //Tank Top */}
        <ellipse cx={x_offset} cy={y_offset-80} rx="40" ry="8" fill="#e2e8f0" stroke="#4a5568" strokeWidth="2"/>
        {/* //Water Fill */}
        {/* // Water Fill */}
        <rect x={x_offset-38} y={water_level_y} width="76" height={water_level_height} fill="#2563eb" opacity="0.6"/>
        {/* // Water Surface */}
        <ellipse cx={x_offset} cy={water_level_y} rx="38" ry="6" fill="#1d4ed8" opacity="0.8">
            <animate attributeName="opacity" values="0.6;0.9;0.6" dur="2.5s" repeatCount="indefinite" />
        </ellipse>
        <rect x={x_offset-25} y={y_offset} width="4" height="15" fill="#4a5568" />
        <rect x={x_offset+21} y={y_offset} width="4" height="15" fill="#4a5568" />
        {/* //Digital Display */}
        <rect x={x_offset+50} y={y_offset-70} width="30" height="40" fill="#1a202c" stroke="#4a5568" strokeWidth="1" rx="3" />
        <rect x={x_offset+53} y={y_offset-67} width="24" height="15" fill="#0f172a" stroke="#22d3ee" strokeWidth="1" />
        <text x={x_offset+65} y={y_offset-58} fontFamily="monospace" fontSize="6" textAnchor="middle" fill="#22d3ee">
            {water_level}%
        </text>
        {/* //Status LED */}
        <circle cx={x_offset+65} cy={y_offset-40} r="2" fill="#22c55e" opacity="0.8">
            <animate attributeName="opacity" values="0.3;1;0.3" dur="2s" repeatCount="indefinite" />
        </circle>
        {/* //Tank Label */}
        <text x={x_offset} y={y_offset-95} fontFamily="Arial, sans-serif" fontSize="10" fontWeight="bold" textAnchor="middle" fill="#2d3748">
            {label}
        </text>
        <text x={x_offset} y={y_offset+20} fontFamily="Arial, sans-serif" fontSize="8" textAnchor="middle" fill="#718096">
            {id}
        </text>
    </g>);
}

export default Tank;
