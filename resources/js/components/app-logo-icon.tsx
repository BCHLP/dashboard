import { SVGAttributes } from 'react';
import { IoWater } from "react-icons/io5";

interface Props extends SVGAttributes<SVGElement> {
    showCompanyName?: boolean;
}

export default function AppLogoIcon({showCompanyName = false, ...props}: Props) {
    return (
        <div className="flex items-center space-x-2">
            <IoWater className="app-logo-icon text-blue-500" />
            {showCompanyName &&
                <span className="text-2xl font-bold text-gray-800 dark:text-gray-100">City WasteWater</span>
            }
        </div>
    );
}
