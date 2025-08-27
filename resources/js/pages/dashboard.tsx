import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import React from 'react';
import { useState, useMemo } from 'react';
import '@patternfly/react-core/dist/styles/base-no-reset.css';

// // eslint-disable-next-line patternfly-react/import-tokens-icons
// import { RegionsIcon as Icon1 } from '@patternfly/react-icons';
// // eslint-disable-next-line patternfly-react/import-tokens-icons
// import { FolderOpenIcon as Icon2 } from '@patternfly/react-icons';

import { TbAntenna } from "react-icons/tb";
import { CiServer, CiRouter } from "react-icons/ci";



import {
    ColaLayout,
    ComponentFactory,
    DefaultEdge,
    DefaultGroup,
    DefaultNode,
    EdgeStyle,
    Graph,
    GraphComponent,
    Layout,
    LayoutFactory,
    Model,
    ModelKind,
    Node,
    NodeModel,
    NodeShape,
    NodeStatus,
    SELECTION_EVENT,
    Visualization,
    VisualizationProvider,
    VisualizationSurface
} from '@patternfly/react-topology';
import { AntennaIcon } from 'lucide-react';
import { RouterModal } from '@/components/modals/router-modal';

interface CustomNodeProps {
    element: Node;
}


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];




const BadgeColors = [
    {
        name: 'A',
        badgeColor: '#ace12e',
        badgeTextColor: '#0f280d',
        badgeBorderColor: '#486b00'
    },
    {
        name: 'B',
        badgeColor: '#F2F0FC',
        badgeTextColor: '#5752d1',
        badgeBorderColor: '#CBC1FF'
    }
];

const CustomNode: React.FC<CustomNodeProps> = ({ element }) => {
    const data = element.getData();
    const Icon = data.icon;
    const badgeColors = BadgeColors.find((badgeColor) => badgeColor.name === data.badge);

    return (
        <DefaultNode
            element={element}
            showStatusDecorator
            badge={data.badge}
            badgeColor={badgeColors?.badgeColor}
            badgeTextColor={badgeColors?.badgeTextColor}
            badgeBorderColor={badgeColors?.badgeBorderColor}
        >
            <g transform={`translate(25, 25)`}>
                <Icon size={25} />
            </g>
        </DefaultNode>
    );
};

const customLayoutFactory: LayoutFactory = (type: string, graph: Graph): Layout | undefined => {
    switch (type) {
        case 'Cola':
            return new ColaLayout(graph);
        default:
            return new ColaLayout(graph, { layoutOnDrag: false });
    }
};

const customComponentFactory: ComponentFactory = (kind: ModelKind, type: string) => {
    switch (type) {
        case 'group':
            return DefaultGroup;
        default:
            switch (kind) {
                case ModelKind.graph:
                    return GraphComponent;
                case ModelKind.node:
                    return CustomNode;
                case ModelKind.edge:
                    return DefaultEdge;
                default:
                    return undefined;
            }
    }
};

const NODE_DIAMETER = 75;

const NODES: NodeModel[] = [
    {
        id: 'node-0',
        type: 'node',
        label: 'MQTT Proxy',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.ellipse,
        status: NodeStatus.danger,
        data: {
            badge: 'B',
            icon: CiServer
        }
    },
    {
        id: 'node-1',
        type: 'node',
        label: 'Router',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.hexagon,
        status: NodeStatus.warning,
        data: {
            badge: 'B',
            icon: CiRouter
        }
    },
    {
        id: 'node-2',
        type: 'node',
        label: 'SCADA',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.octagon,
        status: NodeStatus.success,
        data: {
            badge: 'A',
            icon: CiServer
        }
    },
    {
        id: 'node-3',
        type: 'node',
        label: 'Sensor 1',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.rhombus,
        status: NodeStatus.info,
        data: {
            badge: 'A',
            icon: AntennaIcon
        }
    },
    {
        id: 'node-4',
        type: 'node',
        label: 'Sensor 2',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.hexagon,
        status: NodeStatus.default,
        data: {
            badge: 'C',
            icon: AntennaIcon
        }
    },
    {
        id: 'node-5',
        type: 'node',
        label: 'Sensor 3',
        width: NODE_DIAMETER,
        height: NODE_DIAMETER,
        shape: NodeShape.rect,
        data: {
            badge: 'C',
            icon: AntennaIcon
        }
    },
    {
        id: 'Group-1',
        children: ['node-0', 'node-1', 'node-2'],
        type: 'group',
        group: true,
        label: 'Group-1',
        style: {
            padding: 40
        }
    }
];

const EDGES = [
    {
        id: 'edge-node-4-node-5',
        type: 'edge',
        source: 'node-4',
        target: 'node-5',
        edgeStyle: EdgeStyle.solid
    },
    {
        id: 'edge-node-0-node-2',
        type: 'edge',
        source: 'node-0',
        target: 'node-2',
        edgeStyle: EdgeStyle.solid
    },{
        id: 'edge-node-1-node-0',
        type: 'edge',
        source: 'node-1',
        target: 'node-0',
        edgeStyle: EdgeStyle.solid
    }
];

const TopologyCustomNodeDemo: React.FC = () => {
    const [selectedIds, setSelectedIds] = useState<string[]>([]);

    const controller = useMemo(() => {
        const model: Model = {
            nodes: NODES,
            edges: EDGES,
            graph: {
                id: 'g1',
                type: 'graph',
                layout: 'Cola'
            }
        };

        const newController = new Visualization();
        newController.registerLayoutFactory(customLayoutFactory);
        newController.registerComponentFactory(customComponentFactory);

        newController.addEventListener(SELECTION_EVENT, setSelectedIds);

        newController.fromModel(model, false);

        return newController;
    }, []);

    return (
        <VisualizationProvider controller={controller}>
            <VisualizationSurface state={{ selectedIds }} />
        </VisualizationProvider>
    );
};




export default function dashboard() {

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div
                    className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <TopologyCustomNodeDemo />
                </div>
            </div>
            <RouterModal></RouterModal>
        </AppLayout>
    );
}
